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
use App\Services\Starter\StarterConfigService;
use App\Support\Starter\StarterNavigation;
use App\Support\Starter\StarterNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

afterEach(function (): void {
    Request::setTrustedHosts([]);
});

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
        ->and($session->get('auth.password_confirmed_at'))->toBeInt()
        ->and($session->get('starter.auth_version'))->toBe(1);

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

test('security headers isolate browsing contexts without csp', function () {
    $this->get(route('auth.login'))
        ->assertOk()
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-site')
        ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none')
        ->assertHeaderMissing('Content-Security-Policy');
});

test('authenticated and credential pages are not browser cacheable', function () {
    $context = securityHardeningContext();

    $loginResponse = $this->get(route('auth.login'))->assertOk();

    $profileResponse = $this->actingAs($context['login'])
        ->get(route('starter.profile.edit'))
        ->assertOk();

    foreach ([$loginResponse, $profileResponse] as $response) {
        expect($response->headers->get('Cache-Control'))
            ->toContain('no-store')
            ->toContain('no-cache')
            ->toContain('must-revalidate')
            ->toContain('max-age=0');
    }
});

test('production rejects an untrusted host while allowing the configured domain', function () {
    $this->app->detectEnvironment(fn (): string => 'production');

    $this->get('https://untrusted-host.example/auth/login')->assertBadRequest();
    $this->get(route('auth.login'))->assertOk();
});

test('safe redirects require configured scheme trusted host and safe port', function () {
    $domain = (string) config('app.domain');
    $scheme = (string) parse_url((string) config('app.url'), PHP_URL_SCHEME);
    $otherScheme = $scheme === 'https' ? 'http' : 'https';

    expect(StarterNavigation::isSafeRedirect("{$scheme}://{$domain}/profile/edit"))->toBeTrue()
        ->and(StarterNavigation::isSafeRedirect("{$scheme}://app1.{$domain}/dashboard/index"))->toBeTrue()
        ->and(StarterNavigation::isSafeRedirect("{$otherScheme}://app1.{$domain}/dashboard/index"))->toBeFalse()
        ->and(StarterNavigation::isSafeRedirect("ftp://app1.{$domain}/dashboard/index"))->toBeFalse()
        ->and(StarterNavigation::isSafeRedirect("{$scheme}://user@app1.{$domain}/dashboard/index"))->toBeFalse()
        ->and(StarterNavigation::isSafeRedirect("{$scheme}://app1.{$domain}:8443/dashboard/index"))->toBeFalse()
        ->and(StarterNavigation::isSafeRedirect('https://attacker.example/dashboard/index'))->toBeFalse();
});

test('auth urls use the configured application scheme instead of request headers', function () {
    config()->set('app.url', 'https://'.config('app.domain'));

    $request = Request::create('http://'.config('app.domain').'/');
    $this->app->instance('request', $request);

    expect(StarterNavigation::authLoginUrl())->toStartWith('https://'.config('app.domain').'/auth/login');
});

test('existing sessions receive an auth version without forced logout', function () {
    $context = securityHardeningContext();

    $this->actingAs($context['login'])
        ->get(route('starter.profile.edit'))
        ->assertOk();

    $this->assertAuthenticatedAs($context['login']);
    expect(session('starter.auth_version'))->toBe(1);
});

test('legacy sessions without an auth version are revoked after credentials changed', function () {
    $context = securityHardeningContext();
    $context['login']->forceFill(['auth_version' => 2])->save();

    $this->actingAs($context['login'])
        ->get(route('starter.profile.edit'))
        ->assertRedirect(route('auth.login'));

    $this->assertGuest();
});

test('sessions from before a password change are revoked server side', function () {
    $context = securityHardeningContext();

    $context['login']->forceFill(['auth_version' => 2])->save();

    $this->withSession(['starter.auth_version' => 1])
        ->actingAs($context['login'])
        ->get(route('starter.profile.edit'))
        ->assertRedirect(route('auth.login'));

    $this->assertGuest();
    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.session_revoked',
        'auditable_id' => (string) $context['login']->id,
    ]);
});

test('password change keeps the current session and revokes older session versions', function () {
    $context = securityHardeningContext();
    $this->withSession(['starter.auth_version' => 1])->actingAs($context['login']);

    Livewire::test(EditMyProfile::class)
        ->set('passwordForm.current_password', 'Secret12345')
        ->set('passwordForm.password', 'ChangedPassword123')
        ->set('passwordForm.password_confirmation', 'ChangedPassword123')
        ->call('changePassword')
        ->assertHasNoErrors();

    expect($context['login']->fresh()->auth_version)->toBe(2)
        ->and(session('starter.auth_version'))->toBe(2);
    $this->assertAuthenticatedAs($context['login']);
});

test('login failures and successes are recorded as security events without secrets', function () {
    $context = securityHardeningContext();

    Livewire::test(Login::class)
        ->set('form.username', 'security-admin')
        ->set('form.password', 'WrongPassword123')
        ->call('authenticate')
        ->assertHasErrors(['form.username'])
        ->assertSet('form.password', '');

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

test('oversized login password is rejected and removed from livewire state', function () {
    securityHardeningContext();

    Livewire::test(Login::class)
        ->set('form.username', 'security-admin')
        ->set('form.password', str_repeat('A', 1025))
        ->call('authenticate')
        ->assertHasErrors(['form.password'])
        ->assertSet('form.password', '');
});

test('login throttling also limits rotating usernames from one ip address', function () {
    securityHardeningContext();
    $configs = app(StarterConfigService::class);
    $configs->update(['security.login_max_attempts' => 1]);
    $ipThrottleKey = 'login-ip:'.hash('sha256', '127.0.0.1');
    RateLimiter::clear($ipThrottleKey);

    foreach (range(1, 5) as $attempt) {
        try {
            app(AuthLoginService::class)->attempt(
                username: 'unknown-'.$attempt,
                password: 'WrongPassword123',
            );
        } catch (ValidationException) {
            // Expected invalid credentials while the IP-wide limit accumulates.
        }
    }

    expect(fn () => app(AuthLoginService::class)->attempt(
        username: 'another-unknown',
        password: 'WrongPassword123',
    ))->toThrow(ValidationException::class, 'Terlalu banyak percobaan login');

    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.login_blocked',
        'event' => 'security',
    ]);

    $blockedLog = ActivityLog::query()->where('action_key', 'auth.login_blocked')->latest('id')->firstOrFail();

    expect($blockedLog->metadata['scope'])->toBe('ip');
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
        ->assertDontSee('https://legacy.example.test/avatar.png')
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
        ->assertDontSee('https://legacy.example.test/logo.png')
        ->assertDontSee('Image URL / Path')
        ->set('clientForm.name', 'Security Company Updated')
        ->call('save')
        ->assertHasNoErrors();

    expect($context['login']->fresh()->profile_photo)->toBe('https://legacy.example.test/avatar.png')
        ->and($context['client']->fresh()->logo)->toBe('https://legacy.example.test/logo.png');
});

test('image uploads reject excessive dimensions for shared hosting safety', function () {
    $context = securityHardeningContext();
    $this->actingAs($context['login']);

    Livewire::test(EditMyProfile::class)
        ->set('profilePhotoUpload', UploadedFile::fake()->image('too-wide.jpg', 4097, 10))
        ->call('saveAccount')
        ->assertHasErrors(['profilePhotoUpload']);

    Livewire::test(ClientProfile::class)
        ->set('clientPhotoUpload', UploadedFile::fake()->image('too-tall.png', 10, 4097))
        ->call('save')
        ->assertHasErrors(['clientPhotoUpload']);
});

test('numbers and currencies follow the application locale separators', function () {
    app()->setLocale('id');

    expect(StarterNumber::decimal(1234567))->toBe('1.234.567')
        ->and(StarterNumber::decimal(1234567.5))->toBe('1.234.567,5')
        ->and(StarterNumber::decimal(-1234.5))->toBe('-1.234,5')
        ->and(StarterNumber::currency(1234567))->toContain('1.234.567')
        ->and(StarterNumber::currency(1234567.5))->toContain('1.234.567,50');
});
