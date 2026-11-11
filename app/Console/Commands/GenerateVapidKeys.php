<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid
                            {--show : Only print the keys, do not write .env}
                            {--force : Replace keys that already exist in .env}';

    protected $description = 'Generate VAPID keys for Web Push and store them in .env';

    public function handle(): int
    {
        $envPath = base_path('.env');
        $env = file_exists($envPath) ? file_get_contents($envPath) : '';

        if (! $this->option('show') && ! $this->option('force') && preg_match('/^VAPID_PUBLIC_KEY=.+$/m', $env)) {
            $this->warn('VAPID keys already exist in .env. Changing them logs every device out of notifications.');
            $this->line('Run with --force to replace them anyway.');
            return self::FAILURE;
        }

        $keys = VAPID::createVapidKeys();

        if ($this->option('show')) {
            $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
            $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
            return self::SUCCESS;
        }

        foreach (['VAPID_PUBLIC_KEY' => $keys['publicKey'], 'VAPID_PRIVATE_KEY' => $keys['privateKey']] as $name => $value) {
            $line = $name . '=' . $value;
            $env = preg_match("/^{$name}=.*$/m", $env)
                ? preg_replace("/^{$name}=.*$/m", $line, $env)
                : rtrim($env) . PHP_EOL . $line . PHP_EOL;
        }
        file_put_contents($envPath, $env);

        $this->info('VAPID keys written to .env');
        $this->line('If config is cached, run: php artisan config:clear');
        return self::SUCCESS;
    }
}
