<?php

namespace Altekno\StarterKit\Console\Commands\Starter;

use Altekno\StarterKit\Support\Starter\StarterPaths;
use Altekno\StarterKit\Support\Starter\StarterTheme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    protected $signature = 'starter:publish-assets';

    protected $description = 'Synchronize starter-owned static assets into the host Laravel public directory';

    public function handle(): int
    {
        $destination = public_path('assets');
        File::ensureDirectoryExists($destination);
        $fingerprintDirectory = storage_path('framework/cache/starterkit');
        File::ensureDirectoryExists($fingerprintDirectory);

        $sources = [
            'starter' => StarterPaths::path('public/assets/starter'),
        ];

        foreach (File::directories(StarterTheme::assetPath()) as $themeAssetPath) {
            $sources[basename($themeAssetPath)] = $themeAssetPath;
        }

        foreach ($sources as $ownedDirectory => $ownedSource) {
            $ownedDestination = "{$destination}/{$ownedDirectory}";
            $fingerprintPath = "{$fingerprintDirectory}/assets-{$ownedDirectory}.sha256";

            if (! File::isDirectory($ownedSource)) {
                $this->error("Required starter asset directory not found: {$ownedSource}");

                return self::FAILURE;
            }

            $fingerprint = $this->directoryFingerprint($ownedSource);

            if ($this->publishedFingerprintMatches($ownedDestination, $fingerprintPath, $fingerprint)) {
                $this->line("Starter asset directory is already current: {$ownedDirectory}");

                continue;
            }

            File::deleteDirectory($ownedDestination);

            if (! File::copyDirectory($ownedSource, $ownedDestination)) {
                $this->error("Unable to publish starter asset directory: {$ownedDirectory}");

                return self::FAILURE;
            }

            File::put($fingerprintPath, $fingerprint.PHP_EOL);
        }

        if (! $this->publishPowerGridAssets($fingerprintDirectory)) {
            return self::FAILURE;
        }

        $this->info('Starter assets synchronized to public/assets.');

        return self::SUCCESS;
    }

    private function publishPowerGridAssets(string $fingerprintDirectory): bool
    {
        $source = base_path('vendor/power-components/livewire-powergrid/dist');
        $destination = public_path('vendor/livewire-powergrid');
        $fingerprintPath = "{$fingerprintDirectory}/assets-livewire-powergrid.sha256";

        if (! File::isDirectory($source)) {
            $this->error('Livewire PowerGrid assets are unavailable. Run Composer install first.');

            return false;
        }

        $fingerprint = $this->directoryFingerprint($source);

        if ($this->publishedFingerprintMatches($destination, $fingerprintPath, $fingerprint)) {
            $this->line('Starter asset directory is already current: livewire-powergrid');

            return true;
        }

        File::deleteDirectory($destination);

        if (! File::copyDirectory($source, $destination)) {
            $this->error('Unable to publish Livewire PowerGrid assets.');

            return false;
        }

        File::put($fingerprintPath, $fingerprint.PHP_EOL);

        return true;
    }

    private function publishedFingerprintMatches(
        string $destination,
        string $fingerprintPath,
        string $sourceFingerprint,
    ): bool {
        return File::isDirectory($destination)
            && File::isFile($fingerprintPath)
            && hash_equals($sourceFingerprint, trim((string) File::get($fingerprintPath)));
    }

    private function directoryFingerprint(string $source): string
    {
        $files = collect(File::allFiles($source))
            ->sortBy(fn ($file): string => $file->getRelativePathname(), SORT_STRING);
        $hash = hash_init('sha256');

        foreach ($files as $file) {
            hash_update($hash, implode("\0", [
                $file->getRelativePathname(),
                (string) $file->getSize(),
                (string) $file->getMTime(),
            ])."\n");
        }

        return hash_final($hash);
    }
}
