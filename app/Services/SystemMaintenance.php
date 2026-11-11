<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Server maintenance that a non-technical admin can run from the panel
 * (System Update page) instead of opening a terminal:
 *   composer install -> migrate -> VAPID keys -> clear caches.
 */
class SystemMaintenance
{
    public const STEPS = ['composer', 'migrate', 'vapid', 'clear'];

    // ─── Status ──────────────────────────────────────────────────────────

    public static function status(): array
    {
        $composer = self::findComposer();
        $phpCli = self::phpCli();

        return [
            'web_push'   => class_exists(\Minishlink\WebPush\WebPush::class),
            'pending'    => self::pendingMigrations(),
            'vapid'      => (bool) config('services.webpush.public_key') && (bool) config('services.webpush.private_key'),
            'openssl_ec' => self::opensslEcWorks(),
            'https'      => request()->isSecure() || str_starts_with((string) config('app.url'), 'https://'),
            'shell'      => self::canRunCommands(),
            'composer'   => $composer ? implode(' ', array_map('basename', $composer)) : null,
            'php_cli'    => $phpCli,
            'php'        => PHP_VERSION,
            'env'        => app()->environment(),
        ];
    }

    public static function pendingMigrations(): ?int
    {
        try {
            $files = collect(app('migrator')->getMigrationFiles(database_path('migrations')))->keys();
            $ran = DB::table(config('database.migrations.table', 'migrations'))->pluck('migration');
            return $files->diff($ran)->count();
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function opensslEcWorks(): bool
    {
        if (! function_exists('openssl_pkey_new')) {
            return false;
        }
        $key = @openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        while (openssl_error_string()) { /* clear the error queue */ }
        return $key !== false;
    }

    public static function canRunCommands(): bool
    {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return function_exists('proc_open') && ! in_array('proc_open', $disabled, true);
    }

    // ─── Steps ───────────────────────────────────────────────────────────

    /** @return array{ok: bool, output: string, skipped?: bool} */
    public static function run(string $step): array
    {
        @set_time_limit(900);
        @ignore_user_abort(true);

        return match ($step) {
            'composer'          => self::composerInstall(),
            'download-composer' => self::downloadComposer(),
            'migrate'           => self::artisan('migrate', ['--force' => true]),
            'vapid'             => self::vapid(),
            'clear'             => self::artisan('optimize:clear'),
            default             => ['ok' => false, 'output' => 'Unknown step.'],
        };
    }

    private static function artisan(string $command, array $params = []): array
    {
        try {
            $code = Artisan::call($command, $params);
            return ['ok' => $code === 0, 'output' => trim(Artisan::output()) ?: 'Done.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'output' => $e->getMessage()];
        }
    }

    private static function vapid(): array
    {
        if (config('services.webpush.public_key') && config('services.webpush.private_key')) {
            return ['ok' => true, 'skipped' => true, 'output' => 'Push keys already exist — kept them (changing them would sign every device out of notifications).'];
        }
        if (! class_exists(\Minishlink\WebPush\VAPID::class)) {
            return ['ok' => false, 'output' => 'The web-push library is not installed yet. Run "Install packages" first.'];
        }
        $result = self::artisan('webpush:vapid');
        if ($result['ok']) {
            Artisan::call('config:clear');
        }
        return $result;
    }

    private static function composerInstall(): array
    {
        if (! self::canRunCommands()) {
            return ['ok' => false, 'output' => 'This server does not allow running commands (proc_open is disabled). Ask your host to run "composer install", or enable proc_open.'];
        }
        if (! self::phpCli()) {
            return ['ok' => false, 'output' => 'No command-line PHP (php-cli) was found on this server. Composer needs it. '
                . 'Ask your hosting support for the "PHP CLI path" (e.g. /opt/cpanel/ea-php82/root/usr/bin/php) and enter it under Server health.'];
        }
        $composer = self::findComposer();
        if (! $composer) {
            return ['ok' => false, 'output' => 'Composer was not found on this server. Click "Download Composer" first, then run this step again.'];
        }

        $args = ['install', '--no-interaction', '--prefer-dist', '--optimize-autoloader', '--no-progress'];
        if (app()->environment('production')) {
            $args[] = '--no-dev';
        }

        return self::exec(array_merge($composer, $args), 900);
    }

    /** Fetch composer.phar from getcomposer.org into storage/app and verify its checksum. */
    private static function downloadComposer(): array
    {
        try {
            $phar = Http::timeout(120)->get('https://getcomposer.org/download/latest-stable/composer.phar');
            $sum  = Http::timeout(30)->get('https://getcomposer.org/download/latest-stable/composer.phar.sha256');
            if (! $phar->ok() || ! $sum->ok()) {
                return ['ok' => false, 'output' => 'Could not download Composer (HTTP ' . $phar->status() . ').'];
            }
            $expected = strtolower(trim(strtok($sum->body(), " \n")));
            if (! hash_equals($expected, hash('sha256', $phar->body()))) {
                return ['ok' => false, 'output' => 'Downloaded Composer failed the checksum check — not saved.'];
            }
            file_put_contents(storage_path('app/composer.phar'), $phar->body());
            return ['ok' => true, 'output' => 'Composer downloaded and verified (' . round(strlen($phar->body()) / 1048576, 1) . ' MB).'];
        } catch (Throwable $e) {
            return ['ok' => false, 'output' => 'Download failed: ' . $e->getMessage()];
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /** Command (as argv array) that runs Composer, or null if none found. */
    public static function findComposer(): ?array
    {
        $php = self::phpCli();
        if (! $php) {
            return null;                    // composer is a PHP script: it needs a real PHP CLI
        }

        // A composer.phar we downloaded, one in the project, or a composer found on the
        // PATH / usual hosting locations. Always run it through the verified PHP CLI -
        // its own "#!/usr/bin/env php" may point at a CGI/LiteSpeed PHP on shared hosts.
        $files = [storage_path('app/composer.phar'), base_path('composer.phar')];
        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $dir) {
            if ($dir !== '') {
                $dir = rtrim($dir, '\/') . DIRECTORY_SEPARATOR;
                $files[] = $dir . 'composer.phar';
                if (PHP_OS_FAMILY !== 'Windows') {
                    $files[] = $dir . 'composer';
                }
            }
        }
        array_push($files, '/usr/local/bin/composer', '/usr/bin/composer', '/opt/cpanel/composer/bin/composer');

        foreach (array_unique($files) as $file) {
            if (@is_file($file) && self::looksLikeComposer($file)) {
                return [$php, $file];
            }
        }
        return null;
    }

    /** Check an admin-entered PHP path; returns the reported version if it is a CLI binary. */
    public static function verifyPhpCli(string $path): ?string
    {
        if ($path === '' || ! @is_file($path) || ! self::canRunCommands()) {
            return null;
        }
        $probe = self::exec([$path, '-r', 'echo PHP_SAPI, "|", PHP_VERSION;'], 15);
        return preg_match('/(?:^|\n)cli\|([\d.]+)\s*$/', trim($probe['output']), $m) ? $m[1] : null;
    }

    /** composer.phar, or the composer PHP script (not a bash wrapper). */
    private static function looksLikeComposer(string $file): bool
    {
        if (str_ends_with($file, '.phar')) {
            return true;
        }
        $head = (string) @file_get_contents($file, false, null, 0, 300);
        return str_contains($head, 'php') && ! str_starts_with($head, '#!/bin/bash') && ! str_starts_with($head, '#!/bin/sh');
    }

    private static ?string $phpCliCache = null;
    private static bool $phpCliSearched = false;

    /**
     * Path to a PHP **CLI** binary matching the web PHP version. Under PHP-FPM,
     * LiteSpeed (lsphp) or CGI, PHP_BINARY is not usable for composer, so every
     * candidate is actually run and must report PHP_SAPI === 'cli'.
     */
    public static function phpCli(): ?string
    {
        if (self::$phpCliSearched) {
            return self::$phpCliCache;
        }
        self::$phpCliSearched = true;

        if (! self::canRunCommands()) {
            return null;
        }

        $mm = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;     // e.g. 8.2
        $nd = PHP_MAJOR_VERSION . PHP_MINOR_VERSION;           // e.g. 82
        $win = PHP_OS_FAMILY === 'Windows';

        $candidates = array_filter([
            trim((string) \App\Models\WidgetSetting::get('php_cli_path', '')),   // set by the admin on the System page
            PHP_BINARY ?: null,
            PHP_BINDIR . DIRECTORY_SEPARATOR . ($win ? 'php.exe' : 'php'),
            $win && ($ini = php_ini_loaded_file()) ? dirname($ini) . DIRECTORY_SEPARATOR . 'php.exe' : null,
        ]);
        if (! $win) {
            array_push($candidates,
                "/opt/cpanel/ea-php{$nd}/root/usr/bin/php",     // cPanel EasyApache
                "/usr/local/bin/ea-php{$nd}",
                "/opt/alt/php{$nd}/usr/bin/php",               // CloudLinux alt-php
                "/usr/local/lsws/lsphp{$nd}/bin/php",          // LiteSpeed
                "/usr/local/php{$nd}/bin/php",
                "/usr/local/bin/php{$nd}",
                "/usr/bin/php{$mm}",                           // Debian/Ubuntu
                "/usr/bin/php{$nd}",
                "/usr/local/bin/php",
                "/usr/bin/php",
                '/bin/php'
            );
            foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $dir) {
                if ($dir !== '') {
                    $candidates[] = rtrim($dir, '/') . "/php{$mm}";
                    $candidates[] = rtrim($dir, '/') . '/php';
                }
            }
        }

        $fallback = null;
        foreach (array_unique($candidates) as $bin) {
            if (! @is_file($bin)) {
                continue;
            }
            $probe = self::exec([$bin, '-r', 'echo PHP_SAPI, "|", PHP_MAJOR_VERSION, ".", PHP_MINOR_VERSION;'], 15);
            if (! preg_match('/(?:^|\n)cli\|(\d+\.\d+)\s*$/', trim($probe['output']), $m)) {
                continue;                                       // CGI / LiteSpeed / FPM / broken
            }
            if ($m[1] === $mm) {
                return self::$phpCliCache = $bin;               // same version as the website: best
            }
            if (! $fallback && version_compare($m[1], '8.2', '>=')) {
                $fallback = $bin;                               // newer CLI still runs this app
            }
        }

        return self::$phpCliCache = $fallback;
    }

    /** Run a command without a shell; returns combined output. */
    private static function exec(array $argv, int $timeout): array
    {
        if (! self::canRunCommands()) {
            return ['ok' => false, 'output' => 'Running commands is disabled on this server.'];
        }

        $home = storage_path('app/composer-home');
        if (! is_dir($home)) {
            @mkdir($home, 0755, true);
        }
        $env = array_merge(getenv() ?: [], [
            'COMPOSER_HOME'          => $home,
            'HOME'                   => getenv('HOME') ?: $home,
            'COMPOSER_ALLOW_SUPERUSER' => '1',
            'COMPOSER_NO_INTERACTION'  => '1',
            'COMPOSER_NO_AUDIT'        => '1',   // skip the slow post-install audit network call
        ]);

        try {
            $proc = proc_open($argv, [1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes, base_path(), $env);
            if (! is_resource($proc)) {
                return ['ok' => false, 'output' => 'Could not start: ' . implode(' ', $argv)];
            }
            stream_set_blocking($pipes[1], false);
            $out = '';
            $start = time();
            while (true) {
                $out .= (string) stream_get_contents($pipes[1]);
                $st = proc_get_status($proc);
                if (! $st['running']) {
                    $out .= (string) stream_get_contents($pipes[1]);
                    break;
                }
                if (time() - $start > $timeout) {
                    proc_terminate($proc);
                    $out .= "\n[stopped after {$timeout}s]";
                    break;
                }
                usleep(200000);
            }
            fclose($pipes[1]);
            $code = $st['running'] ? -1 : $st['exitcode'];
            proc_close($proc);

            return ['ok' => $code === 0, 'output' => trim(preg_replace('/\e\[[\d;]*m/', '', $out)) ?: 'Done.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'output' => $e->getMessage()];
        }
    }
}
