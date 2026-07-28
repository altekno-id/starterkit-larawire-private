<?php

use App\Support\Starter\StarterPaths;
use Illuminate\Support\Facades\File;

test('presentation layer does not own persistence queries', function () {
    $files = [
        ...File::allFiles(app_path('Livewire')),
        ...File::allFiles(app_path('Http/Controllers')),
    ];

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $contents = $file->getContents();
        $source = $file->getRelativePathname();

        expect($contents, $source)
            ->not->toMatch('/[A-Z][A-Za-z0-9_]*::query\s*\(/')
            ->not->toMatch('/\bDB::/')
            ->not->toMatch('/->(?:load|loadMissing)\s*\(/')
            ->not->toMatch('/\bapp\s*\(/');
    }
});

test('services keep model query construction in repositories', function () {
    $files = File::allFiles(app_path('Services'));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $contents = $file->getContents();
        $source = $file->getRelativePathname();

        expect($contents, $source)
            ->not->toMatch('/[A-Z][A-Za-z0-9_]*::query\s*\(/')
            ->not->toMatch('/->(?:load|loadMissing)\s*\(/')
            ->not->toMatch('/\bapp\s*\(/');
    }
});

test('starter infrastructure remains isolated from project extension folders', function () {
    $rootMigrationNames = collect(File::files(database_path('migrations')))
        ->map->getFilename()
        ->filter(fn (string $name): bool => str_contains($name, 'starter')
            || str_contains($name, 'x_packages')
            || str_contains($name, 'x_password_reset_tokens')
            || str_contains($name, 'drop_sessions_table_for_file_session_driver'))
        ->values()
        ->all();
    $rootFeatureTestNames = collect(File::files(base_path('tests/Feature')))
        ->map->getFilename()
        ->filter(fn (string $name): bool => str_contains($name, 'Starter') || $name === 'PrivateAuthenticationTest.php')
        ->values()
        ->all();
    $misplacedMiddleware = collect(File::allFiles(app_path('Http/Middleware')))
        ->map->getPathname()
        ->filter(fn (string $path): bool => str_starts_with(basename($path), 'Starter'))
        ->reject(fn (string $path): bool => str_contains($path, '/Http/Middleware/Starter/'))
        ->values()
        ->all();
    $misplacedRules = collect(File::allFiles(app_path('Rules')))
        ->map->getPathname()
        ->filter(fn (string $path): bool => str_starts_with(basename($path), 'Starter'))
        ->reject(fn (string $path): bool => str_contains($path, '/Rules/Starter/'))
        ->values()
        ->all();

    expect($misplacedMiddleware)->toBe([])
        ->and($misplacedRules)->toBe([])
        ->and($rootMigrationNames)->toBe([])
        ->and(File::exists(resource_path('views/livewire/starter')))->toBeFalse()
        ->and(File::exists(resource_path('views/templates')))->toBeFalse()
        ->and(File::exists(resource_path('views/errors')))->toBeFalse()
        ->and(File::exists(resource_path('views/landing/index.blade.php')))->toBeTrue()
        ->and(File::exists(resource_path('views/starter/landing')))->toBeFalse()
        ->and(File::exists(base_path('routes/starter/global.php')))->toBeTrue()
        ->and(File::exists(base_path('routes/starter/web.php')))->toBeTrue()
        ->and(File::exists(base_path('routes/starter.php')))->toBeFalse()
        ->and(File::exists(public_path('assets/starter/js/starter-runtime.js')))->toBeTrue()
        ->and(File::exists(public_path('assets/starter/images/avatar.png')))->toBeTrue()
        ->and($rootFeatureTestNames)->toBe([]);
});

test('app migration folders are registered dynamically for artisan migration commands', function () {
    $subdomain = 'architecture-test-app';
    $migrationDirectory = database_path("migrations/apps/{$subdomain}");

    File::ensureDirectoryExists($migrationDirectory);

    try {
        $this->refreshApplication();

        expect($this->app->make('migrator')->paths())
            ->toContain($migrationDirectory)
            ->toContain(database_path('migrations/starter'));
    } finally {
        File::deleteDirectory($migrationDirectory);
    }
});

test('layouts expose page-scoped asset stacks', function () {
    $layouts = [
        resource_path('views/starter/templates/layouts/app.blade.php'),
        resource_path('views/starter/templates/layouts/auth.blade.php'),
        resource_path('views/starter/templates/layouts/landing.blade.php'),
    ];

    foreach ($layouts as $layout) {
        expect(File::get($layout), $layout)
            ->toContain("@stack('page-styles')")
            ->toContain("@stack('page-scripts')");
    }
});

test('starter paths resolve the repository root in standalone development', function () {
    expect(realpath(StarterPaths::root()))->toBe(realpath(base_path()))
        ->and(StarterPaths::isEmbedded())->toBeFalse()
        ->and(StarterPaths::path('routes/starter/web.php'))
        ->toBe(base_path('routes/starter/web.php'));
});

test('starter layout exposes only the documented project extension contracts', function () {
    $appLayout = File::get(resource_path('views/starter/templates/layouts/app.blade.php'));
    $authLayout = File::get(resource_path('views/starter/templates/layouts/auth.blade.php'));
    $landingLayout = File::get(resource_path('views/starter/templates/layouts/landing.blade.php'));
    $accountMenu = File::get(resource_path('views/starter/templates/layouts/account-menu.blade.php'));

    expect($appLayout)
        ->toContain("@includeIf('extensions.starter.header-actions.index'")
        ->toContain("@includeIf('extensions.starter.layout.head')")
        ->toContain("@includeIf('extensions.starter.layout.body-end')")
        ->and($authLayout)
        ->toContain("@includeIf('extensions.starter.layout.head')")
        ->toContain("@includeIf('extensions.starter.layout.body-end')")
        ->and($landingLayout)
        ->toContain("@includeIf('extensions.starter.layout.head')")
        ->toContain("@includeIf('extensions.starter.layout.body-end')")
        ->and($accountMenu)
        ->toContain("@includeIf('extensions.starter.profile-menu.index')");
});

test('starter form fields defer ordinary Livewire updates', function () {
    $ordinaryBindings = collect(File::allFiles(resource_path('views/starter')))
        ->flatMap(function ($file): array {
            preg_match_all('/<(?:input|select|textarea)\\b(?:(?!>).)*\\bwire:model="[^"]+"[^>]*>/s', $file->getContents(), $matches);

            return $matches[0];
        })
        ->reject(fn (string $tag): bool => preg_match('/\\btype="file"/', $tag) === 1)
        ->values()
        ->all();

    expect($ordinaryBindings)->toBe([]);
});
