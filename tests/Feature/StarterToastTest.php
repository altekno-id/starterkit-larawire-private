<?php

use App\Livewire\Starter\Profile\EditMyProfile;
use App\Livewire\Starter\UserManagement\Roles;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Models\Starter\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function starterToastLogin(): UserLogin
{
    $client = User::query()->create([
        'name' => 'Acme Client',
        'email' => 'client@example.test',
    ]);

    $role = UserRole::query()->create([
        'user_id' => $client->id,
        'code' => 'admin',
        'name' => 'Administrator',
    ]);

    return UserLogin::query()->create([
        'user_id' => $client->id,
        'user_role_id' => $role->id,
        'name' => 'Aldhi Admin',
        'username' => 'aldhi',
        'email' => 'aldhi@example.test',
        'password' => 'secret',
    ]);
}

test('profile update dispatches starter toast', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->set('accountForm.name', 'Aldhi Updated')
        ->set('accountForm.username', 'aldhi-updated')
        ->set('accountForm.email', 'aldhi.updated@example.test')
        ->call('saveAccount')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Profile berhasil disimpan.';
        });
});

test('role save dispatches starter toast', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(Roles::class)
        ->set('roleForm.code', 'operator')
        ->set('roleForm.name', 'Operator')
        ->set('roleForm.desc', 'Operasional')
        ->set('roleForm.module_ids', [])
        ->call('save')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Role berhasil disimpan.';
        });
});

test('role delete validation dispatches danger starter toast', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(Roles::class)
        ->call('deleteRole', $login->user_role_id)
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'danger'
                && ($params['message'] ?? null) === 'Role admin bawaan tidak boleh dihapus.';
        });
});
