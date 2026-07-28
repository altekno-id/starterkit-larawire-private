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

test('setup runs the security check before changing production data', function () {
    $this->app->detectEnvironment(fn (): string => 'production');
    config()->set('starter.superuser.password', 'rahasia123');

    $this->artisan('starter:setup', [
        '--company' => 'PT Internal',
        '--email' => 'developer@example.test',
    ])
        ->expectsOutputToContain('Security check failed.')
        ->assertFailed();

    expect(ClientLogin::query()->count())->toBe(0);
});

test('setup password reset revokes previous superuser sessions', function () {
    config()->set('starter.superuser.password', 'InitialPassword123');

    $this->artisan('starter:setup', [
        '--company' => 'PT Internal',
        '--email' => 'developer@example.test',
    ])->assertSuccessful();

    $login = ClientLogin::query()->where('username', 'superuser')->firstOrFail();
    $originalRememberToken = $login->remember_token;

    config()->set('starter.superuser.password', 'ChangedPassword123');

    $this->artisan('starter:setup', [
        '--company' => 'PT Internal',
        '--email' => 'developer@example.test',
        '--reset-password' => true,
    ])->assertSuccessful();

    $login->refresh();

    expect($login->auth_version)->toBe(2)
        ->and($login->remember_token)->not->toBe($originalRememberToken)
        ->and(Hash::check('ChangedPassword123', $login->password))->toBeTrue();

    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.password_reset_by_setup',
        'event' => 'security',
        'auditable_id' => (string) $login->id,
    ]);
});

test('security check validates local defaults without production tuning', function () {
    $this->artisan('starter:security-check')
        ->expectsOutputToContain('Local security configuration is valid.')
        ->assertSuccessful();
});

test('publishing assets is a safe no-op in standalone starter development', function () {
    $this->artisan('starter:publish-assets')
        ->expectsOutputToContain('already use the host public/assets directory')
        ->assertSuccessful();
});

test('production security check rejects unsafe deployment configuration', function () {
    $this->app->detectEnvironment(fn (): string => 'production');
    config()->set('app.debug', true);
    config()->set('app.url', 'http://localhost');
    config()->set('app.domain', 'localhost');
    config()->set('session.secure', false);
    config()->set('starter.superuser.password', 'rahasia123');

    $this->artisan('starter:security-check')
        ->expectsOutputToContain('Security check failed.')
        ->assertFailed();
});

test('production security check accepts a hardened shared hosting configuration', function () {
    $this->app->detectEnvironment(fn (): string => 'production');
    config()->set('app.debug', false);
    config()->set('app.url', 'https://internal.example.test');
    config()->set('app.domain', 'internal.example.test');
    config()->set('session.encrypt', true);
    config()->set('session.http_only', true);
    config()->set('session.same_site', 'lax');
    config()->set('session.secure', true);
    config()->set('starter.superuser.password', '');

    $this->artisan('starter:security-check')
        ->expectsOutputToContain('Production security configuration is valid.')
        ->assertSuccessful();
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
