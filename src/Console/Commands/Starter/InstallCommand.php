<?php

namespace Altekno\StarterKit\Console\Commands\Starter;

use Altekno\StarterKit\Services\Starter\StarterAppScaffolder;
use Altekno\StarterKit\Support\Starter\StarterRouteRegistrar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class InstallCommand extends Command
{
    protected $signature = 'starterkit:install
        {--company= : Internal company/client name}
        {--email= : Superuser notification email}
        {--username= : Superuser username}
        {--skip-migration : Skip database migration}
        {--force : Confirm that this is a fresh installation and allow the database reset}';

    protected $description = 'Install the starterkit on a fresh Laravel host and rebuild its database';

    public function handle(StarterAppScaffolder $appScaffolder): int
    {
        if (! $this->option('skip-migration')
            && ! $this->option('force')
            && ! $this->confirmFreshDatabaseReset()) {
            $this->components->warn('Installation cancelled. The database was not reset.');

            return self::FAILURE;
        }

        if ($this->call('starter:publish-assets') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->installLocale() !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->installPestBootstrap() !== self::SUCCESS
            || $this->installLanding() !== self::SUCCESS
            || $this->installDefaultApp($appScaffolder) !== self::SUCCESS) {
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

        if ($this->call('migrate:fresh', ['--force' => true]) !== self::SUCCESS) {
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

    private function confirmFreshDatabaseReset(): bool
    {
        $this->newLine();
        $this->components->warn(
            'Fresh installation only: migrate:fresh will delete every table and all data '
            .'from the database configured in .env.',
        );

        return $this->confirm(
            'Continue only if this is a new Laravel project with a dedicated new database?',
            false,
        );
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

    private function installLanding(): int
    {
        $routePath = base_path('routes/web.php');
        $componentPath = app_path('Livewire/Landing/LandingIndex.php');
        $viewPath = resource_path('views/landing/index.blade.php');
        $routeContents = File::exists($routePath) ? File::get($routePath) : "<?php\n";

        if (str_contains($routeContents, 'LandingIndex::class')
            || File::exists($componentPath)
            || File::exists($viewPath)) {
            $this->components->info('Landing project already exists and was not changed.');

            return self::SUCCESS;
        }

        $defaultWelcomePattern = <<<'REGEX'
~Route::get\(\s*['"]\/['"]\s*,\s*function\s*\(\)\s*\{\s*return\s+view\(\s*['"]welcome['"]\s*\)\s*;\s*\}\s*\)\s*;~
REGEX;
        $defaultViewPattern = '~Route::view\(\s*[\'"]\/[\'"]\s*,\s*[\'"]welcome[\'"]\s*\)\s*;~';

        if (preg_match($defaultWelcomePattern, $routeContents) === 1
            || preg_match($defaultViewPattern, $routeContents) === 1) {
            $routeContents = $this->landingRouteContents();
        } elseif ($this->declaresRootRoute($routeContents)) {
            $this->components->warn(
                'Existing root landing route detected. Starterkit did not overwrite it.',
            );

            return self::SUCCESS;
        } else {
            $routeContents = $this->appendLandingRoute($routeContents);
        }

        File::ensureDirectoryExists(dirname($componentPath));
        File::ensureDirectoryExists(dirname($viewPath));
        File::put($componentPath, $this->landingComponentContents());
        File::put($viewPath, $this->landingViewContents());
        File::put($routePath, $routeContents);

        $this->components->info('Minimal project landing page created.');

        return self::SUCCESS;
    }

    private function installPestBootstrap(): int
    {
        $path = base_path('tests/Pest.php');

        if (File::exists($path)) {
            $this->components->info('Pest project bootstrap already exists.');

            return self::SUCCESS;
        }

        if (! File::exists(base_path('tests/TestCase.php'))) {
            $this->components->error(
                'tests/TestCase.php is required before creating the Pest bootstrap.',
            );

            return self::FAILURE;
        }

        File::put($path, <<<'PHP'
<?php

use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature');
PHP.PHP_EOL);

        $this->components->info('Pest project bootstrap created.');

        return self::SUCCESS;
    }

    private function installDefaultApp(StarterAppScaffolder $scaffolder): int
    {
        if ((glob(config_path('apps/*.php')) ?: []) !== []) {
            $this->components->info('Project app registry already exists; default app1 was not created.');

            return self::SUCCESS;
        }

        try {
            $created = $scaffolder->create(
                'app1',
                'App 1',
                'Aplikasi awal starterkit.',
                'apps',
            );
        } catch (Throwable $exception) {
            $this->components->error("Unable to create default app1: {$exception->getMessage()}");

            return self::FAILURE;
        }

        StarterRouteRegistrar::register('app1');

        foreach ($created as $path) {
            $this->line('Created '.str_replace(base_path().'/', '', $path));
        }

        $this->components->info(
            'Default app1 created with dashboard module and Dashboard submenu structure.',
        );

        return self::SUCCESS;
    }

    private function declaresRootRoute(string $contents): bool
    {
        return preg_match(
            '~Route::(?:get|post|put|patch|delete|match|any|view|livewire)\(\s*[\'"]\/[\'"]~',
            $contents,
        ) === 1;
    }

    private function appendLandingRoute(string $contents): string
    {
        $uses = [
            'use App\\Livewire\\Landing\\LandingIndex;',
            'use Illuminate\\Support\\Facades\\Route;',
        ];

        foreach (array_reverse($uses) as $use) {
            if (! str_contains($contents, $use)) {
                $contents = preg_replace(
                    '/<\?php\s*/',
                    "<?php\n\n{$use}\n",
                    $contents,
                    1,
                ) ?? $contents;
            }
        }

        return rtrim($contents).PHP_EOL.PHP_EOL
            ."Route::livewire('/', LandingIndex::class)->name('landing');".PHP_EOL;
    }

    private function landingRouteContents(): string
    {
        return <<<'PHP'
<?php

use App\Livewire\Landing\LandingIndex;
use Illuminate\Support\Facades\Route;

Route::livewire('/', LandingIndex::class)->name('landing');
PHP.PHP_EOL;
    }

    private function landingComponentContents(): string
    {
        return <<<'PHP'
<?php

namespace App\Livewire\Landing;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::landing')]
class LandingIndex extends Component
{
    public function render()
    {
        return view('landing.index')
            ->title((string) config('app.name'));
    }
}
PHP.PHP_EOL;
    }

    private function landingViewContents(): string
    {
        return <<<'BLADE'
<main class="page">
    <section class="page-wrapper">
        <div class="container-tight py-6">
            <div class="text-center">
                <h1 class="display-5 fw-bold mb-3">Ini landing page</h1>
                <p class="text-secondary mb-4">{{ config('app.name') }}</p>

                <a href="{{ \Altekno\StarterKit\Support\Starter\StarterNavigation::authLoginUrl() }}" class="btn btn-primary" wire:navigate>
                    Login
                </a>
            </div>
        </div>
    </section>
</main>
BLADE.PHP_EOL;
    }
}
