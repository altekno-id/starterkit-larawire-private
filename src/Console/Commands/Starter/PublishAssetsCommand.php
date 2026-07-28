<?php

namespace Altekno\StarterKit\Console\Commands\Starter;

use Altekno\StarterKit\Support\Starter\StarterPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    protected $signature = 'starter:publish-assets';

    protected $description = 'Synchronize starter-owned static assets into the host Laravel public directory';

    public function handle(): int
    {
        $source = StarterPaths::path('public/assets');
        $destination = public_path('assets');

        if (! File::isDirectory($source)) {
            $this->error("Starter asset source not found: {$source}");

            return self::FAILURE;
        }

        if (realpath($source) === realpath($destination)) {
            $this->info('Starter assets already use the host public/assets directory.');

            return self::SUCCESS;
        }

        File::ensureDirectoryExists($destination);

        foreach (['starter', 'tabler'] as $ownedDirectory) {
            $ownedSource = "{$source}/{$ownedDirectory}";
            $ownedDestination = "{$destination}/{$ownedDirectory}";

            if (! File::isDirectory($ownedSource)) {
                $this->error("Required starter asset directory not found: {$ownedSource}");

                return self::FAILURE;
            }

            File::deleteDirectory($ownedDestination);

            if (! File::copyDirectory($ownedSource, $ownedDestination)) {
                $this->error("Unable to publish starter asset directory: {$ownedDirectory}");

                return self::FAILURE;
            }
        }

        $this->info('Starter assets synchronized to public/assets.');

        return self::SUCCESS;
    }
}
