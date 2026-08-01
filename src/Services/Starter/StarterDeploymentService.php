<?php

namespace Altekno\StarterKit\Services\Starter;

use Illuminate\Console\Command;
use Throwable;

class StarterDeploymentService
{
    public function prepare(Command $command, bool $ensureApplicationKey = false): int
    {
        $hadBootCache = app()->configurationIsCached() || app()->routesAreCached();

        if ($command->call('optimize:clear') !== Command::SUCCESS) {
            return Command::FAILURE;
        }

        if ($hadBootCache) {
            $command->info('Cache bootstrap lama telah dibersihkan. Deployment dilanjutkan dalam proses yang sama.');
        }

        if ($ensureApplicationKey && ! $this->ensureApplicationKey($command)) {
            return Command::FAILURE;
        }

        $command->info('Memvalidasi konfigurasi keamanan...');
        if ($command->call('starter:security-check') !== Command::SUCCESS) {
            return Command::FAILURE;
        }

        $command->info('Menyinkronkan asset dan migration...');
        if ($command->call('starter:publish-assets') !== Command::SUCCESS
            || $command->call('migrate', ['--force' => true]) !== Command::SUCCESS) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function finish(Command $command): int
    {
        $command->info('Mempublikasikan asset runtime dan membangun cache production...');
        if ($command->call('livewire:publish', [
            '--assets' => true,
            '--no-interaction' => true,
        ]) !== Command::SUCCESS) {
            return Command::FAILURE;
        }

        $this->ensureStorageLink($command);

        if (app()->isProduction()
            && $command->call('optimize') !== Command::SUCCESS) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function ensureApplicationKey(Command $command): bool
    {
        if (filled(config('app.key'))) {
            return true;
        }

        if ($command->call('key:generate', ['--force' => true]) !== Command::SUCCESS) {
            return false;
        }

        $generatedKey = $this->environmentValue('APP_KEY');

        if ($generatedKey === '') {
            $command->error('APP_KEY tidak dapat dibaca setelah key:generate.');

            return false;
        }

        config(['app.key' => $generatedKey]);

        return true;
    }

    private function ensureStorageLink(Command $command): void
    {
        $link = public_path('storage');

        if (is_link($link) || file_exists($link)) {
            $command->info('Public storage path already exists.');

            return;
        }

        if (! function_exists('symlink') && ! function_exists('exec')) {
            $this->warnManualStorageLink($command);

            return;
        }

        try {
            if ($command->call('storage:link') === Command::SUCCESS) {
                return;
            }
        } catch (Throwable) {
            // Shared hosting may disable both PHP symlink and process functions.
        }

        $this->warnManualStorageLink($command);
    }

    private function warnManualStorageLink(Command $command): void
    {
        $command->warn(
            'PHP hosting tidak mengizinkan pembuatan symlink. Jalankan satu kali dari root Laravel: '
            .'ln -s ../storage/app/public public/storage',
        );
    }

    private function environmentValue(string $key): string
    {
        $contents = @file_get_contents(base_path('.env'));

        if ($contents === false
            || preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $contents, $matches) !== 1) {
            return '';
        }

        return trim(trim($matches[1]), "\"'");
    }
}
