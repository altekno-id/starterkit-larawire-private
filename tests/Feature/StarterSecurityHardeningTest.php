<?php

use App\Livewire\Starter\Auth\ConfirmPassword;
use App\Livewire\Starter\Auth\Login;
use App\Livewire\Starter\Logs\ActivityLogIndex;
use App\Livewire\Starter\Profile\EditMyProfile;
use App\Livewire\Starter\Settings\ClientProfile;
use App\Models\Starter\ActivityLog;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use App\Services\Starter\AuditLogService;
use App\Services\Starter\AuthLoginService;
use App\Support\Starter\StarterNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * @return array{client: Client, login: ClientLogin}
 */
function securityHardeningContext(): array
{
    $client = Client::query()->create([
        'name' => 'Security Company',
        'email' => 'security-company@example.test',
        'account_status' => 'approved',
        'approved_at' => now(),
    ]);
    $role = ClientRole::query()->create([
        'code' => 'superuser',
        'name' => 'Superuser',
        'is_system' => true,
        'can_manage_settings' => true,
        'can_view_logs' => true,
    ]);
    $login = ClientLogin::query()->create([
        'client_role_id' => $role->id,
        'name' => 'Security Admin',
        'username' => 'security-admin',
        'email' => 'security-admin@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    return compact('client', 'login');
}

test('sensitive settings require a recent password confirmation', function () {
    $context = securityHardeningContext();

    $this->actingAs($context['login'])
        ->get(route('starter.settings'))
        ->assertRedirect(route('password.confirm'));

    $this->withSession(['auth.password_confirmed_at' => now()->timestamp])
        ->actingAs($context['login'])
        ->get(route('starter.settings'))
        ->assertOk()
        ->assertSee('Pengaturan');
});

test('successful login counts as a recent password confirmation', function () {
    $context = securityHardeningContext();
    $session = app('session')->driver();
    $request = Request::create(route('auth.login'), 'POST', server: [
        'REMOTE_ADDR' => '127.0.0.1',
    ]);
    $request->setLaravelSession($session);
    $this->app->instance('request', $request);

    $target = app(AuthLoginService::class)->attempt(
        username: 'security-admin',
        password: 'Secret12345',
        redirect: route('starter.settings'),
    );

    expect($target)->toBe(route('starter.settings'))
        ->and($session->get('auth.password_confirmed_at'))->toBeInt();

    $this->withSession([
        'auth.password_confirmed_at' => $session->get('auth.password_confirmed_at'),
    ])->actingAs($context['login'])
        ->get(route('starter.settings'))
        ->assertOk()
        ->assertSee('Pengaturan');
});

test('expired password confirmation requires verification again', function () {
    $context = securityHardeningContext();
    $expiredAt = now()->subSeconds(((int) config('auth.password_timeout')) + 1)->timestamp;

    $this->withSession(['auth.password_confirmed_at' => $expiredAt])
        ->actingAs($context['login'])
        ->get(route('starter.settings'))
        ->assertRedirect(route('password.confirm'));
});

test('password confirmation validates the current password and returns to intended page', function () {
    $context = securityHardeningContext();
    $this->actingAs($context['login']);
    session(['url.intended' => route('starter.settings')]);

    Livewire::test(ConfirmPassword::class)
        ->assertSee('Konfirmasi Password')
        ->assertSee('Verifikasi diperlukan')
        ->set('password', 'password-salah')
        ->call('confirm')
        ->assertHasErrors(['password'])
        ->assertSee('aria-describedby="confirm-password-error"', false)
        ->assertSee('id="confirm-password-error" class="invalid-feedback d-block"', false)
        ->set('password', 'Secret12345')
        ->call('confirm')
        ->assertHasNoErrors()
        ->assertRedirect(route('starter.settings'));

    expect(session('auth.password_confirmed_at'))->not->toBeNull();

    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.password_confirmation_failed',
        'event' => 'security',
        'auditable_id' => (string) $context['login']->id,
    ]);
    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.password_confirmation_succeeded',
        'event' => 'security',
        'auditable_id' => (string) $context['login']->id,
    ]);
});

test('responses do not send content security policy and use normal livewire assets', function () {
    $response = $this->get(route('auth.login'))
        ->assertOk()
        ->assertHeaderMissing('Content-Security-Policy');

    expect(config('livewire.csp_safe'))->toBeFalse()
        ->and($response->getContent())
        ->toContain('/vendor/livewire/livewire.js?id=')
        ->not->toContain('/vendor/livewire/livewire.csp.js');
});

test('logout form remains available from an app subdomain without csp', function () {
    $context = securityHardeningContext();
    $logoutUrl = route('auth.logout');

    $this->actingAs($context['login'])
        ->get('https://app1.'.config('app.domain').'/profile/edit')
        ->assertOk()
        ->assertSee('action="'.$logoutUrl.'"', false)
        ->assertHeaderMissing('Content-Security-Policy');
});

test('production https keeps hsts without adding csp', function () {
    $this->app->detectEnvironment(fn (): string => 'production');

    $this->get('https://'.config('app.domain').'/auth/login')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
        ->assertHeaderMissing('Content-Security-Policy');
});

test('login failures and successes are recorded as security events without secrets', function () {
    $context = securityHardeningContext();
    RateLimiter::clear('security-admin|127.0.0.1');

    Livewire::test(Login::class)
        ->set('form.username', 'security-admin')
        ->set('form.password', 'WrongPassword123')
        ->call('authenticate')
        ->assertHasErrors(['form.username']);

    Livewire::test(Login::class)
        ->set('form.username', 'security-admin')
        ->set('form.password', 'Secret12345')
        ->call('authenticate')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.login_failed',
        'event' => 'security',
        'auditable_id' => (string) $context['login']->id,
    ]);
    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.login_succeeded',
        'event' => 'security',
        'auditable_id' => (string) $context['login']->id,
    ]);

    $serializedLogs = ActivityLog::query()
        ->whereIn('action_key', ['auth.login_failed', 'auth.login_succeeded'])
        ->get()
        ->toJson();

    expect($serializedLogs)
        ->not->toContain('WrongPassword123')
        ->not->toContain('Secret12345');
});

test('security event metadata is sanitized before being stored', function () {
    $context = securityHardeningContext();

    app(AuditLogService::class)->recordSecurityEvent(
        'security.test',
        'Pengujian sanitasi keamanan',
        target: $context['login'],
        actor: $context['login'],
        metadata: [
            'reason' => 'test',
            'password' => 'NeverStoreThisSecret',
            'nested' => ['access_token' => 'NeverStoreThisToken'],
        ],
    );

    $log = ActivityLog::query()->where('action_key', 'security.test')->firstOrFail();

    expect($log->metadata)
        ->toMatchArray([
            'reason' => 'test',
            'password' => '[REDACTED]',
            'nested' => ['access_token' => '[REDACTED]'],
        ])
        ->and($log->toJson())
        ->not->toContain('NeverStoreThisSecret')
        ->not->toContain('NeverStoreThisToken');
});

test('security events are visible and filterable in the activity log', function () {
    $context = securityHardeningContext();
    $this->actingAs($context['login']);

    app(AuditLogService::class)->recordSecurityEvent(
        'security.visibility_test',
        'Event keamanan terlihat',
        target: $context['login'],
        actor: $context['login'],
    );

    Livewire::test(ActivityLogIndex::class)
        ->assertSee('Keamanan')
        ->assertSee('Event keamanan terlihat')
        ->set('eventFilter', 'security')
        ->assertSee('Event keamanan terlihat');
});

test('profile and company image paths are server owned state', function () {
    $context = securityHardeningContext();
    $context['login']->forceFill([
        'profile_photo' => 'https://legacy.example.test/avatar.png',
    ])->save();
    $context['client']->forceFill([
        'logo' => 'https://legacy.example.test/logo.png',
    ])->save();

    $this->actingAs($context['login']);

    Livewire::test(EditMyProfile::class)
        ->assertSet('accountForm', [
            'name' => 'Security Admin',
            'email' => 'security-admin@example.test',
        ])
        ->assertDontSee('Image URL / Path')
        ->set('accountForm.name', 'Security Admin Updated')
        ->call('saveAccount')
        ->assertHasNoErrors();

    Livewire::test(ClientProfile::class)
        ->assertSet('clientForm', [
            'name' => 'Security Company',
            'email' => 'security-company@example.test',
            'phone' => '',
            'pic_name' => '',
        ])
        ->assertDontSee('Image URL / Path')
        ->set('clientForm.name', 'Security Company Updated')
        ->call('save')
        ->assertHasNoErrors();

    expect($context['login']->fresh()->profile_photo)->toBe('https://legacy.example.test/avatar.png')
        ->and($context['client']->fresh()->logo)->toBe('https://legacy.example.test/logo.png');
});

test('numbers and currencies follow the application locale separators', function () {
    app()->setLocale('id');

    expect(StarterNumber::decimal(1234567))->toBe('1.234.567')
        ->and(StarterNumber::decimal(1234567.5))->toBe('1.234.567,5')
        ->and(StarterNumber::decimal(-1234.5))->toBe('-1.234,5')
        ->and(StarterNumber::currency(1234567))->toContain('1.234.567')
        ->and(StarterNumber::currency(1234567.5))->toContain('1.234.567,50');
});
