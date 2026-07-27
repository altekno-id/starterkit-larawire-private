<?php

use App\Livewire\Starter\Auth\Login;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function privateAuthLogin(array $attributes = []): ClientLogin
{
    $client = Client::query()->create([
        'name' => 'Internal Company',
        'email' => 'company@example.test',
        'account_status' => 'approved',
        'approved_at' => now(),
    ]);
    $role = ClientRole::query()->create([
        'code' => 'superuser',
        'name' => 'Superuser',
        'is_system' => true,
    ]);

    return ClientLogin::query()->create(array_merge([
        'client_role_id' => $role->id,
        'name' => 'Developer',
        'username' => 'superuser',
        'email' => 'developer@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ], $attributes));
}

test('public account creation and password reset endpoints are unavailable', function () {
    $this->get('/auth/register')->assertNotFound();
    $this->get('/auth/forgot-password')->assertNotFound();
    $this->get('/auth/reset-password/token')->assertNotFound();
    $this->get('/auth/google')->assertNotFound();
    $this->get('/')->assertOk()->assertDontSee('Register')->assertDontSee('Pricing');
    $this->get(route('auth.login'))
        ->assertOk()
        ->assertSeeHtml('data-starter-livewire-loader')
        ->assertSee('Memproses...')
        ->assertSee('z-index: 1045', false);
});

test('user logs in with username instead of email', function () {
    $login = privateAuthLogin();

    Livewire::test(Login::class)
        ->set('form.username', 'superuser')
        ->set('form.password', 'Secret12345')
        ->call('authenticate')
        ->assertHasNoErrors();

    $this->assertAuthenticatedAs($login);
});

test('inactive account cannot log in', function () {
    privateAuthLogin(['status' => 'inactive']);

    Livewire::test(Login::class)
        ->set('form.username', 'superuser')
        ->set('form.password', 'Secret12345')
        ->call('authenticate')
        ->assertHasErrors(['form.username']);

    $this->assertGuest();
});

test('temporary password requires profile password change before app access', function () {
    $login = privateAuthLogin(['must_change_password' => true]);
    $passwordChangeUrl = route('starter.profile.edit', ['tab' => 'security']);

    $this->actingAs($login)
        ->get(route('starter.user-management.users'))
        ->assertRedirect($passwordChangeUrl);

    $this->actingAs($login)
        ->get($passwordChangeUrl)
        ->assertOk()
        ->assertSee('Password sementara harus diganti')
        ->assertSee('Password Saat Ini')
        ->assertSee('Password Baru');
});

test('temporary password login opens the password form directly', function () {
    privateAuthLogin(['must_change_password' => true]);

    Livewire::test(Login::class)
        ->set('form.username', 'superuser')
        ->set('form.password', 'Secret12345')
        ->call('authenticate')
        ->assertRedirect(route('app1.dashboard'));

    $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http';

    $this->get(route('app1.dashboard'))
        ->assertRedirect($scheme.'://app1.'.config('app.domain').'/profile/edit?tab=security');
});
