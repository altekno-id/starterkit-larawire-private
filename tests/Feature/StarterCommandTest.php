<?php

use App\Models\Starter\App;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('setup creates one unrestricted Superuser for the private client', function () {
    config()->set('starter.superuser.password', 'rahasia123');

    $this->artisan('starter:setup', [
        '--company' => 'PT Internal',
        '--email' => 'developer@example.test',
    ])->assertSuccessful();

    $role = ClientRole::query()->where('code', 'superuser')->firstOrFail();
    $login = ClientLogin::query()->where('username', 'superuser')->firstOrFail();

    expect($role->is_system)->toBeTrue()
        ->and(Client::query()->count())->toBe(1)
        ->and(ClientRole::query()->count())->toBe(1)
        ->and(ClientLogin::query()->count())->toBe(1)
        ->and($role->hasFullAccess())->toBeTrue()
        ->and($role->mods()->count())->toBe(0)
        ->and($login->status)->toBe('active')
        ->and(Hash::check('rahasia123', $login->password))->toBeTrue();
});

test('setup rejects the development password outside local and testing', function () {
    $this->app->detectEnvironment(fn (): string => 'production');
    config()->set('starter.superuser.password', 'rahasia123');

    $this->artisan('starter:setup', [
        '--company' => 'PT Internal',
        '--email' => 'developer@example.test',
    ])
        ->expectsOutputToContain('The development Superuser password cannot be used outside local/testing.')
        ->assertFailed();

    expect(ClientLogin::query()->count())->toBe(0);
});

test('sync dry run validates configuration without writing metadata', function () {
    $this->artisan('starter:sync', ['app1', '--dry-run' => true])->assertSuccessful();

    expect(App::query()->count())->toBe(0);
});

test('make app generates all required files and syncs its metadata', function () {
    $subdomain = 'testing-app';
    $className = 'TestingApp';
    $paths = [
        config_path("apps/{$subdomain}.php"),
        base_path("routes/apps/{$subdomain}.php"),
        app_path("Livewire/Apps/{$className}/Dashboard/{$className}DashboardIndex.php"),
        resource_path("views/apps/{$subdomain}/dashboard/{$subdomain}-dashboard-index.blade.php"),
        base_path("tests/Feature/Apps/{$className}/{$className}DashboardTest.php"),
    ];

    File::delete($paths);

    try {
        $status = Artisan::call('starter:make-app', [
            'subdomain' => $subdomain,
            '--name' => 'Testing App',
        ]);

        if ($status !== 0) {
            $this->fail(Artisan::output());
        }

        foreach ($paths as $path) {
            expect(File::exists($path))->toBeTrue();
        }

        expect(App::query()->where('subdomain', $subdomain)->exists())->toBeTrue();

        expect(Artisan::call('starter:make-app', ['subdomain' => $subdomain]))->toBe(1);
    } finally {
        File::delete($paths);
    }
});
