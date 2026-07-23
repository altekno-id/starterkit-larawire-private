<?php

use App\Livewire\Starter\Auth\Login;
use App\Livewire\Starter\Settings\SecuritySettings;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use App\Models\Starter\StarterConfig;
use App\Services\Starter\StarterConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function configurationAdmin(): ClientLogin
{
    Client::query()->create([
        'name' => 'Internal Company',
        'account_status' => 'approved',
        'approved_at' => now(),
    ]);
    $role = ClientRole::query()->create([
        'code' => 'superuser',
        'name' => 'Superuser',
        'is_system' => true,
    ]);

    return ClientLogin::query()->create([
        'client_role_id' => $role->id,
        'name' => 'Developer',
        'username' => 'superuser',
        'email' => 'developer@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);
}

test('private installation keeps one company profile without client foreign keys', function () {
    expect(Schema::hasTable('starter_clients'))->toBeTrue()
        ->and(Schema::hasColumn('starter_client_roles', 'client_id'))->toBeFalse()
        ->and(Schema::hasColumn('starter_client_logins', 'client_id'))->toBeFalse()
        ->and(Schema::hasColumn('starter_logs', 'client_id'))->toBeFalse()
        ->and(Schema::hasTable('starter_configs'))->toBeTrue();
});

test('public responses include baseline security headers', function () {
    $response = $this->get(route('auth.login'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), payment=()');

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

test('security settings update dynamic configuration and invalidate cached values', function () {
    $login = configurationAdmin();
    $login->forceFill(['remember_token' => 'existing-remember-token'])->save();
    $configs = app(StarterConfigService::class);

    expect($configs->integer('security.lock_screen_timeout_minutes'))->toBe(15)
        ->and($configs->uploadImageMaxKilobytes())->toBe(2048);

    $this->actingAs($login);

    Livewire::test(SecuritySettings::class)
        ->assertSee('Sesi dan Lock Screen')
        ->assertSee('Proteksi Login')
        ->assertSee('Kebijakan Upload')
        ->set('securityForm.remember_me_enabled', false)
        ->set('securityForm.lock_screen_enabled', true)
        ->set('securityForm.lock_screen_timeout_minutes', 30)
        ->set('securityForm.login_max_attempts', 4)
        ->set('securityForm.login_decay_seconds', 120)
        ->set('securityForm.max_image_size_kb', 4096)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('starter-toast');

    expect($configs->boolean('security.remember_me_enabled'))->toBeFalse()
        ->and($configs->integer('security.lock_screen_timeout_minutes'))->toBe(30)
        ->and($configs->integer('security.login_max_attempts'))->toBe(4)
        ->and($configs->integer('security.login_decay_seconds'))->toBe(120)
        ->and($configs->uploadImageMaxKilobytes())->toBe(4096)
        ->and($login->fresh()->remember_token)->toBeNull();

    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'config.security.update',
        'table_name' => 'starter_configs',
    ]);
});

test('remember me follows dynamic security configuration', function () {
    $login = configurationAdmin();

    Livewire::test(Login::class)
        ->assertSee('Ingat saya di perangkat ini')
        ->set('form.username', 'superuser')
        ->set('form.password', 'Secret12345')
        ->set('form.remember', true)
        ->call('authenticate')
        ->assertHasNoErrors();

    expect($login->fresh()->remember_token)->not->toBeNull();
});

test('disabled remember me is hidden and never creates a persistent login token', function () {
    $login = configurationAdmin();
    StarterConfig::query()
        ->where('key', 'security.remember_me_enabled')
        ->update(['value' => '0']);
    app(StarterConfigService::class)->forget('security.remember_me_enabled');

    Livewire::test(Login::class)
        ->assertDontSee('Ingat saya di perangkat ini')
        ->set('form.username', 'superuser')
        ->set('form.password', 'Secret12345')
        ->set('form.remember', true)
        ->call('authenticate')
        ->assertHasNoErrors();

    expect($login->fresh()->remember_token)->toBeNull();
});

test('repeated failed logins temporarily lock the account', function () {
    $login = configurationAdmin();
    $throttleKey = 'superuser|127.0.0.1';
    RateLimiter::clear($throttleKey);

    foreach (range(1, 5) as $attempt) {
        Livewire::test(Login::class)
            ->set('form.username', 'superuser')
            ->set('form.password', 'password-salah-'.$attempt)
            ->call('authenticate')
            ->assertHasErrors(['form.username']);
    }

    expect($login->fresh()->failed_login_count)->toBe(5)
        ->and($login->fresh()->locked_until)->not->toBeNull()
        ->and($login->fresh()->locked_until->isFuture())->toBeTrue();

    RateLimiter::clear($throttleKey);
});
