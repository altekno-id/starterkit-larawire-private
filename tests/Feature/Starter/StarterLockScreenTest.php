<?php

use Altekno\StarterKit\Livewire\Starter\Auth\LockScreen;
use Altekno\StarterKit\Models\Starter\Client;
use Altekno\StarterKit\Models\Starter\ClientLogin;
use Altekno\StarterKit\Models\Starter\ClientRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function lockScreenLogin(): ClientLogin
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

test('inactive session is redirected to lock screen without logging user out', function () {
    $login = lockScreenLogin();

    $this->actingAs($login)
        ->withSession(['starter.last_activity_at' => now()->subMinutes(16)->timestamp])
        ->get(route('starter.profile.edit'))
        ->assertRedirect(route('starter.lock-screen'))
        ->assertSessionHas('starter.locked', true);

    $this->assertAuthenticatedAs($login);
});

test('manual lock screen validates password and returns to intended page', function () {
    $login = lockScreenLogin();
    $this->actingAs($login)->withSession([
        'starter.locked' => true,
        'starter.lock.intended' => route('starter.profile.edit'),
    ]);

    Livewire::test(LockScreen::class)
        ->assertSee('Sesi login tetap aktif')
        ->set('password', 'password-salah')
        ->call('unlock')
        ->assertHasErrors(['password'])
        ->assertSee('aria-describedby="lock-screen-password-error"', false)
        ->assertSee('id="lock-screen-password-error" class="invalid-feedback d-block"', false)
        ->set('password', 'Secret12345')
        ->call('unlock')
        ->assertHasNoErrors()
        ->assertRedirect(route('starter.profile.edit'));
});

test('account menu exposes manual lock action when lock screen is enabled', function () {
    $login = lockScreenLogin();

    $this->actingAs($login)
        ->get(route('starter.profile.edit'))
        ->assertOk()
        ->assertSee('Kunci Layar')
        ->assertSee('name="starter-lock-screen-enabled" content="1"', false)
        ->assertSee('name="starter-lock-screen-timeout" content="900"', false)
        ->assertSee(route('starter.session.activity'), false)
        ->assertSee(route('starter.lock-screen', ['manual' => 1]), false);
});

test('browser activity endpoint refreshes the session without requiring a page reload', function () {
    $login = lockScreenLogin();

    $this->actingAs($login)
        ->withSession(['starter.last_activity_at' => now()->subMinutes(5)->timestamp])
        ->post(route('starter.session.activity'))
        ->assertNoContent()
        ->assertSessionHas('starter.last_activity_at', fn (mixed $timestamp): bool => is_int($timestamp)
            && $timestamp >= now()->subSecond()->timestamp);
});

test('browser activity endpoint tells an already locked session to open lock screen', function () {
    $login = lockScreenLogin();

    $this->actingAs($login)
        ->withSession(['starter.locked' => true])
        ->postJson(route('starter.session.activity'))
        ->assertStatus(423)
        ->assertJson([
            'redirect' => route('starter.lock-screen'),
        ]);
});

test('automatic lock keeps the current safe page as the unlock destination', function () {
    $login = lockScreenLogin();
    $intended = route('starter.profile.edit');

    $this->actingAs($login)
        ->get(route('starter.lock-screen', ['redirect' => $intended]))
        ->assertOk()
        ->assertSessionHas('starter.locked', true)
        ->assertSessionHas('starter.lock.intended', $intended);
});
