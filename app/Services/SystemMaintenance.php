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

        return [
            'web_push'   => class_exists(\Minishlink\WebPush\WebPush::class),
            'pending'    => self::pendingMigrations(),
            'vapid'      => (bool) config('services.webpush.public_key') && (bool) config('services.webpush.private_key'),
            'openssl_ec' => self::opensslEcWorks(),
            'https'      => request()->isSecure() || str_starts_with((string) config('app.url'), 'https://'),
            'shell'      => self::canRunCommands(),
            'composer'   => $composer ? implode(' ', array_map('basename', $composer)) : null,
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

        // A composer.phar we downloaded, one in the project, or one next to a
        // system "composer" on the PATH (Windows installs composer.bat + composer.phar).
        $phars = [storage_path('app/composer.phar'), base_path('composer.phar')];
        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $dir) {
            if ($dir !== '') {
                $phars[] = rtrim($dir, '\\/') . DIRECTORY_SEPARATOR . 'composer.phar';
            }
        }
        foreach ($phars as $phar) {
            if ($php && @is_file($phar)) {
                return [$php, $phar];
            }
        }
        if (! self::canRunCommands()) {
            return null;
        }
        foreach (['composer', '/usr/local/bin/composer', '/usr/bin/composer', '/opt/cpanel/composer/bin/composer'] as $bin) {
            $probe = self::exec([$bin, '--version', '--no-ansi'], 20);
            if ($probe['ok'] && stripos($probe['output'], 'composer') !== false) {
                return [$bin];
            }
        }
        return null;
    }

    /** Path to a PHP *CLI* binary (under PHP-FPM, PHP_BINARY is the FPM daemon). */
    public static function phpCli(): ?string
    {
        $candidates = [];
        if (PHP_BINARY && ! str_contains(basename(PHP_BINARY), 'fpm') && ! str_contains(basename(PHP_BINARY), 'httpd')) {
            $candidates[] = PHP_BINARY;
        }
        $exe = PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php';
        $candidates[] = PHP_BINDIR . DIRECTORY_SEPARATOR . $exe;
        if (PHP_OS_FAMILY === 'Windows' && ($ini = php_ini_loaded_file())) {
            $candidates[] = dirname($ini) . DIRECTORY_SEPARATOR . 'php.exe';
        }
        $candidates[] = '/usr/local/bin/php';
        $candidates[] = '/usr/bin/php';

        foreach ($candidates as $c) {
            if ($c && @is_file($c)) {
                return $c;
            }
        }
        return null;
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
