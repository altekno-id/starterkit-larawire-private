<?php

namespace Altekno\StarterKit\Console\Commands\Starter;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class InstallCommand extends Command
{
    protected $signature = 'starterkit:install
        {--company= : Internal company/client name}
        {--email= : Superuser notification email}
        {--username= : Superuser username}
        {--skip-migration : Skip database migration}';

    protected $description = 'Finish an idempotent starterkit installation on the Laravel host';

    public function handle(): int
    {
        if ($this->call('starter:publish-assets') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->installLocale() !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->option('skip-migration')) {
            if ($this->call('starter:security-check') !== self::SUCCESS) {
                return self::FAILURE;
            }

            $this->components->warn(
                'Database installation skipped. Run starterkit:install again after the database is ready.',
            );

            return self::SUCCESS;
        }

        if ($this->call('migrate', ['--force' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $setupOptions = array_filter([
            '--company' => $this->option('company'),
            '--email' => $this->option('email'),
            '--username' => $this->option('username'),
        ], fn (mixed $value): bool => is_string($value) && $value !== '');

        if ($this->call('starter:setup', $setupOptions) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->call('starter:security-check') !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Starterkit installation completed successfully.');

        return self::SUCCESS;
    }

    private function installLocale(): int
    {
        $locale = (string) config('app.locale', 'id');

        if (File::isDirectory(lang_path($locale))) {
            $this->components->info("Locale [{$locale}] is already installed.");

            return self::SUCCESS;
        }

        try {
            $this->components->task(
                "Installing locale [{$locale}]",
                fn (): bool => $this->callSilently('lang:add', ['locales' => [$locale]]) === self::SUCCESS,
            );
        } catch (Throwable $exception) {
            $this->components->error(
                "Unable to install locale [{$locale}]: {$exception->getMessage()}",
            );

            return self::FAILURE;
        }

        return File::isDirectory(lang_path($locale))
            ? self::SUCCESS
            : self::FAILURE;
    }
}
