<?php

use App\Livewire\Starter\Auth\Login;
use App\Livewire\Starter\Profile\EditMyProfile;
use App\Livewire\Starter\Settings\ClientProfile;
use App\Livewire\Starter\UserManagement\RoleForm;
use App\Livewire\Starter\UserManagement\Roles;
use App\Livewire\Starter\UserManagement\UserForm;
use App\Livewire\Starter\UserManagement\Users;
use App\Models\Starter\App;
use App\Models\Starter\AppMenu;
use App\Models\Starter\AppMod;
use App\Models\Starter\AppRoute;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use App\Models\Starter\ClientRoleAppLanding;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Services\Starter\UserManagementRoleService;
use App\Services\Starter\UserManagementUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function starterToastLogin(): ClientLogin
{
    $client = Client::query()->create([
        'name' => 'Acme Client',
        'email' => 'client@example.test',
        'account_status' => 'approved',
        'approved_at' => now(),
    ]);

    $role = ClientRole::query()->create([
        'code' => 'superuser',
        'name' => 'Superuser',
        'is_system' => true,
    ]);

    $login = ClientLogin::query()->create([
        'client_role_id' => $role->id,
        'name' => 'Aldhi Admin',
        'username' => 'aldhi-admin',
        'email' => 'aldhi@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    session(['auth.password_confirmed_at' => now()->timestamp]);

    return $login;
}

test('profile update dispatches starter toast', function () {
    $login = starterToastLogin();
    $login->forceFill(['profile_photo' => 'assets/starter/images/avatar.png'])->save();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->set('accountForm.name', 'Aldhi Updated')
        ->set('accountForm.email', 'aldhi.updated@example.test')
        ->call('saveAccount')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Profil berhasil disimpan.';
        });

    $this->assertDatabaseHas('starter_client_logins', [
        'id' => $login->id,
        'name' => 'Aldhi Updated',
        'email' => 'aldhi.updated@example.test',
        'profile_photo' => 'assets/starter/images/avatar.png',
    ]);
});

test('profile page renders settings sections for admin', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->assertSee('Pengaturan Akun')
        ->assertSee('Detail Akun')
        ->assertSee('Keamanan')
        ->assertSee('Ganti Foto Profil')
        ->assertSee('Hapus Foto Profil')
        ->assertSee('Hapus foto profil?')
        ->assertSee('Foto profil saat ini akan diganti dengan foto default.')
        ->assertSeeHtml('for="profile-current-password"')
        ->assertSeeHtml('id="profile-current-password"')
        ->assertSeeHtml('aria-label="visible ? \'Sembunyikan Password Saat Ini\' : \'Tampilkan Password Saat Ini\'"')
        ->assertSeeHtml('aria-label="visible ? \'Sembunyikan Password Baru\' : \'Tampilkan Password Baru\'"')
        ->assertSeeHtml('aria-label="visible ? \'Sembunyikan Konfirmasi Password\' : \'Tampilkan Konfirmasi Password\'"')
        ->assertSeeHtml('icon-tabler-eye')
        ->assertSeeHtml('icon-tabler-eye-off')
        ->assertDontSeeHtml('btn-ghost-secondary')
        ->assertDontSeeHtml('title="Tampilkan atau sembunyikan')
        ->assertSee('Minimal 10 karakter dengan huruf besar, huruf kecil, dan angka.')
        ->assertDontSee('Change avatar')
        ->assertDontSee('Delete avatar')
        ->assertDontSee('Image URL / Path')
        ->assertDontSee('Avatar Google')
        ->assertDontSee('Client Profile')
        ->assertDontSee('Admin Control')
        ->assertDontSee('Payment Reference')
        ->assertDontSee('Save Control');
});

test('temporary password profile starts on security with clear instructions', function () {
    $login = starterToastLogin();
    $login->forceFill(['must_change_password' => true])->save();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->assertSet('activeTab', 'security')
        ->assertSee('Password sementara harus diganti')
        ->assertSee('Masukkan password sementara yang diberikan admin')
        ->assertSee('Wajib')
        ->assertSeeHtml('x-data="{ activeTab: \'security\' }"')
        ->assertSeeHtml('x-bind:class="{ \'show active\': activeTab === \'security\' }"')
        ->assertDontSeeHtml('wire:click="showTab');
});

test('profile password change keeps security tab active and validates credentials', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->assertSeeHtml('x-on:click.prevent="activeTab = \'security\'"')
        ->assertDontSeeHtml('wire:click="showTab')
        ->call('changePassword')
        ->assertHasErrors([
            'passwordForm.current_password',
            'passwordForm.password',
            'passwordForm.password_confirmation',
        ])
        ->assertSee('Password saat ini wajib diisi.')
        ->assertSee('Password baru wajib diisi.')
        ->assertSee('Konfirmasi password wajib diisi.')
        ->assertDontSee('Current password wajib diisi.')
        ->set('passwordForm.current_password', 'WrongPassword123')
        ->set('passwordForm.password', 'ChangedPassword123')
        ->set('passwordForm.password_confirmation', 'ChangedPassword123')
        ->call('changePassword')
        ->assertSet('activeTab', 'security')
        ->assertHasErrors(['passwordForm.current_password'])
        ->assertSee('Password saat ini tidak sesuai.')
        ->assertSet('passwordForm.current_password', '')
        ->assertSet('passwordForm.password', '')
        ->assertSet('passwordForm.password_confirmation', '')
        ->set('passwordForm.current_password', 'Secret12345')
        ->set('passwordForm.password', 'ChangedPassword123')
        ->set('passwordForm.password_confirmation', 'DifferentPassword123')
        ->call('changePassword')
        ->assertSet('activeTab', 'security')
        ->assertHasErrors(['passwordForm.password'])
        ->assertSet('passwordForm.current_password', '')
        ->assertSet('passwordForm.password', '')
        ->assertSet('passwordForm.password_confirmation', '')
        ->set('passwordForm.current_password', 'Secret12345')
        ->set('passwordForm.password', 'ChangedPassword123')
        ->set('passwordForm.password_confirmation', 'ChangedPassword123')
        ->call('changePassword')
        ->assertHasNoErrors()
        ->assertSet('activeTab', 'security')
        ->assertSet('passwordForm.current_password', '')
        ->assertSet('passwordForm.password', '')
        ->assertSet('passwordForm.password_confirmation', '')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Password berhasil diubah.';
        });

    expect(Hash::check('ChangedPassword123', $login->fresh()->password))->toBeTrue()
        ->and(Hash::check('Secret12345', $login->fresh()->password))->toBeFalse();

    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.password_change_failed',
        'event' => 'security',
        'auditable_id' => (string) $login->id,
    ]);
    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.password_changed',
        'event' => 'security',
        'auditable_id' => (string) $login->id,
    ]);
});

test('required password change returns the user to an authorized app', function () {
    $login = starterToastLogin();
    $login->forceFill(['must_change_password' => true])->save();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->set('passwordForm.current_password', 'Secret12345')
        ->set('passwordForm.password', 'ChangedPassword123')
        ->set('passwordForm.password_confirmation', 'ChangedPassword123')
        ->call('changePassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('app1.dashboard'))
        ->assertSessionHas('starter-toast', function (array $toast): bool {
            return ($toast['type'] ?? null) === 'success'
                && ($toast['message'] ?? null) === 'Password berhasil diubah. Anda sudah dapat menggunakan aplikasi.';
        });

    expect($login->fresh()->must_change_password)->toBeFalse();
});

test('new regular user enters the assigned app before the required password form', function () {
    $superuserLogin = starterToastLogin();
    $app = App::query()->create([
        'name' => 'Operations App',
        'subdomain' => 'app1',
    ]);
    $module = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'operations_dashboard',
        'name' => 'Operations Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $module->id,
        'name' => 'app1.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $menu = AppMenu::query()->create([
        'app_mod_id' => $module->id,
        'app_route_id' => $route->id,
        'label' => 'Dashboard Operasional',
        'is_landing_candidate' => true,
    ]);
    $role = ClientRole::query()->create([
        'code' => 'new_operator',
        'name' => 'New Operator',
    ]);
    $role->mods()->attach($module);
    ClientRoleAppLanding::query()->create([
        'client_role_id' => $role->id,
        'app_id' => $app->id,
        'app_menu_id' => $menu->id,
    ]);
    ClientLogin::query()->create([
        'client_role_id' => $role->id,
        'name' => 'New Operator Login',
        'username' => 'new-operator-login',
        'email' => 'new-operator-login@example.test',
        'password' => 'Temporary123',
        'status' => 'active',
        'must_change_password' => true,
    ]);

    Livewire::test(Login::class)
        ->set('form.username', 'new-operator-login')
        ->set('form.password', 'Temporary123')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('app1.dashboard'));

    $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http';

    $this->get(route('app1.dashboard'))
        ->assertRedirect($scheme.'://app1.'.config('app.domain').'/profile/edit?tab=security');
});

test('application layout remains fluid through full hd width', function () {
    $login = starterToastLogin();

    $response = $this->actingAs($login)->get(route('starter.profile.edit'));

    $response
        ->assertOk()
        ->assertSee('.starter-content-container', false)
        ->assertSee('max-width: 1680px', false)
        ->assertSee('container-fluid starter-content-container', false)
        ->assertSee('Edit Profil Saya')
        ->assertSee('Pengaturan')
        ->assertSeeHtml('icon-tabler-settings')
        ->assertDontSee('Manajemen User')
        ->assertDontSee('Profil Perusahaan');
});

test('dashboard module uses dashboard parent with two summary children', function () {
    foreach (['app1', 'app2'] as $appKey) {
        $module = config("apps.{$appKey}.mods.dashboard");
        $menu = config("apps.{$appKey}.mods.dashboard.menus.0");

        expect($module['name'])->toBe('Dashboard')
            ->and($menu)
            ->toMatchArray([
                'label' => 'Dashboard',
                'icon' => 'layout-dashboard',
            ])
            ->and($menu['children'])->toHaveCount(2)
            ->and($menu['children'][0])->toMatchArray([
                'label' => 'Summary 1',
                'route' => "{$appKey}.dashboard",
                'landing' => true,
            ])
            ->and($menu['children'][1])->toMatchArray([
                'label' => 'Summary 2',
                'route' => "{$appKey}.dashboard.summary2",
            ])
            ->and(Route::has("{$appKey}.dashboard"))->toBeTrue()
            ->and(Route::has("{$appKey}.dashboard.summary2"))->toBeTrue();
    }
});

test('starter apps expose the same minimal two module structure', function () {
    $appOneModules = config('apps.app1.mods');
    $appTwoModules = config('apps.app2.mods');

    expect($appOneModules)->toHaveCount(2)
        ->and($appTwoModules)->toHaveCount(2)
        ->and(array_keys($appOneModules))->toBe(['dashboard', 'module1'])
        ->and(array_keys($appTwoModules))->toBe(['dashboard', 'module1'])
        ->and(Route::has('app1.mahasiswa_full.index'))->toBeFalse();
});

test('settings center combines roles users and company profile', function () {
    $login = starterToastLogin();

    $this->actingAs($login)
        ->get(route('starter.settings'))
        ->assertOk()
        ->assertSee('Pusat Pengaturan')
        ->assertSee('Roles')
        ->assertSee('Users')
        ->assertSee('Profil Perusahaan')
        ->assertSee('Total Aplikasi')
        ->assertSee('aplikasi tersedia')
        ->assertSee('Daftar Role')
        ->assertSee('Tambah Role')
        ->assertSeeHtml('data-role-create-location="content"')
        ->assertSeeHtml('data-role-access-summary')
        ->assertSeeHtml('data-role-module-count')
        ->assertDontSee('Starter / Manajemen User');

    $this->get(route('starter.settings', ['section' => 'users']))
        ->assertOk()
        ->assertSee('Tambah User')
        ->assertSeeHtml('data-user-create-location="content"')
        ->assertSee('Akun dikelola oleh Superuser')
        ->assertSee('Semua status');

    $this->get(route('starter.settings', ['section' => 'company']))
        ->assertOk()
        ->assertSee('Pengaturan Perusahaan')
        ->assertSee('Simpan Profil Perusahaan');

    $this->get(route('starter.user-management.roles'))
        ->assertRedirect(route('starter.settings', ['section' => 'roles']));

    $this->get(route('starter.user-management.users'))
        ->assertRedirect(route('starter.settings', ['section' => 'users']));
});

test('role create and edit use dedicated pages', function () {
    $login = starterToastLogin();

    $this->actingAs($login)
        ->get(route('starter.settings.roles.create'))
        ->assertOk()
        ->assertSee('Tambah Role')
        ->assertSee('Identitas Role')
        ->assertSee('Akses Module dan Halaman Awal')
        ->assertSeeHtml('data-role-form-layout="split"')
        ->assertSeeHtml('data-role-identity-panel')
        ->assertSeeHtml('data-role-access-panel')
        ->assertSeeHtml('data-role-form-summary')
        ->assertSeeHtml('data-role-system-access')
        ->assertSeeHtml('id="role-can-manage-settings"')
        ->assertSeeHtml('id="role-can-view-logs"')
        ->assertSee('Akses Pengaturan')
        ->assertSee('Lihat Log Aktivitas')
        ->assertSee('mengelola role, user, dan profil perusahaan')
        ->assertSee('Batal dan Kembali')
        ->assertSeeHtml('icon-tabler-arrow-left')
        ->assertSeeHtml('wire:model.defer="roleForm.code"')
        ->assertSeeHtml('wire:model.defer="roleForm.name"')
        ->assertDontSeeHtml('wire:model.live="roleForm.code"')
        ->assertDontSeeHtml('wire:model.live="roleForm.name"')
        ->assertDontSee('Kembali ke daftar role')
        ->assertDontSee('Daftar Role');

    $this->get(route('starter.settings.roles.edit', $login->client_role_id))
        ->assertOk()
        ->assertSee('Detail Role')
        ->assertSee('Role bawaan Superuser memiliki akses penuh')
        ->assertDontSee('Daftar Role');
});

test('role and user lists paginate for larger datasets', function () {
    $login = starterToastLogin();

    foreach (range(1, 12) as $index) {
        $role = ClientRole::query()->create([
            'code' => 'role_'.$index,
            'name' => 'Role '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        ]);

        ClientLogin::query()->create([
            'client_role_id' => $role->id,
            'name' => 'User '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'username' => 'user-'.$index,
            'email' => 'user-'.$index.'@example.test',
            'password' => 'Secret12345',
            'status' => 'active',
        ]);
    }

    $this->actingAs($login);

    Livewire::test(Roles::class, ['embedded' => true])
        ->assertSee('Menampilkan 1–10 dari 13 role')
        ->call('setPage', 2, 'rolesPage')
        ->assertSee('Menampilkan 11–13 dari 13 role');

    Livewire::test(Users::class, ['embedded' => true])
        ->assertSee('Menampilkan 1–10 dari 13 user')
        ->call('setPage', 2, 'usersPage')
        ->assertSee('Menampilkan 11–13 dari 13 user');
});

test('role and user pagination fetches only one database page with bounded queries', function () {
    $login = starterToastLogin()->load('role');

    foreach (range(1, 25) as $index) {
        $role = ClientRole::query()->create([
            'code' => 'paged_role_'.$index,
            'name' => 'Paged Role '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        ]);

        ClientLogin::query()->create([
            'client_role_id' => $role->id,
            'name' => 'Paged User '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'username' => 'paged-user-'.$index,
            'email' => 'paged-user-'.$index.'@example.test',
            'password' => 'Secret12345',
            'status' => 'active',
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();
    $userPage = app(UserManagementUserService::class)->paginateUsers($login, '', '');
    $userQueries = DB::getQueryLog();

    expect($userPage->count())->toBe(10)
        ->and($userPage->total())->toBe(26)
        ->and(count($userQueries))->toBeLessThanOrEqual(3)
        ->and(collect($userQueries)->contains(
            fn (array $query): bool => str_contains(strtolower($query['query']), 'limit 10'),
        ))->toBeTrue();

    DB::flushQueryLog();
    $rolePage = app(UserManagementRoleService::class)->paginateRoles($login, '');
    $roleQueries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($rolePage->count())->toBe(10)
        ->and($rolePage->total())->toBe(26)
        ->and(count($roleQueries))->toBeLessThanOrEqual(3)
        ->and(collect($roleQueries)->contains(
            fn (array $query): bool => str_contains(strtolower($query['query']), 'limit 10'),
        ))->toBeTrue();
});

test('role list pins superuser first and hides it from non superuser logins', function () {
    $superuserLogin = starterToastLogin();
    $zuluRole = ClientRole::query()->create([
        'code' => 'zulu',
        'name' => 'Zulu Role',
    ]);
    $alphaRole = ClientRole::query()->create([
        'code' => 'alpha',
        'name' => 'Alpha Role',
        'can_manage_settings' => true,
    ]);
    $regularLogin = ClientLogin::query()->create([
        'client_role_id' => $alphaRole->id,
        'name' => 'Regular Admin',
        'username' => 'regular-admin',
        'email' => 'regular-admin@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    $this->actingAs($superuserLogin);

    Livewire::test(Roles::class, ['embedded' => true])
        ->assertSeeInOrder(['Superuser', 'Alpha Role', 'Zulu Role'])
        ->assertSee('Role Default')
        ->assertSee('Tidak dapat diedit, hanya dapat dilihat oleh Superuser.')
        ->assertSeeHtml('data-default-role')
        ->assertSeeHtml('data-role-avatar');

    $this->actingAs($regularLogin);

    Livewire::test(Roles::class, ['embedded' => true])
        ->assertDontSee('Superuser')
        ->assertDontSee('Role Default')
        ->assertSeeInOrder([$alphaRole->name, $zuluRole->name]);
});

test('role access summary opens an app and module detail modal', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'Operations App',
        'subdomain' => 'operations',
    ]);
    $module = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'employee_view',
        'name' => 'Employee View',
        'desc' => 'Read employee records.',
    ]);
    $role = ClientRole::query()->create([
        'code' => 'viewer',
        'name' => 'Viewer',
    ]);
    $role->mods()->attach($module);

    $this->actingAs($login);

    Livewire::test(Roles::class, ['embedded' => true])
        ->assertSeeHtml('data-role-access-trigger')
        ->assertSeeHtml('starter-role-access-trigger')
        ->assertDontSeeHtml('btn btn-outline-secondary btn-sm w-100')
        ->call('showRoleAccess', $role->id)
        ->assertSet('roleAccessModalOpen', true)
        ->assertSet('roleAccessRoleName', 'Viewer')
        ->assertSet('roleAccessAppCount', 1)
        ->assertSet('roleAccessModuleCount', 1)
        ->assertSet('roleAccessCanManageSettings', false)
        ->assertSee('Detail Akses Role')
        ->assertSee('Tidak dapat mengakses Pengaturan')
        ->assertSee('Operations App')
        ->assertSee('Employee View')
        ->assertSee('employee_view')
        ->assertSee('Read employee records.')
        ->assertSeeHtml('data-role-access-detail')
        ->call('closeRoleAccessModal')
        ->assertSet('roleAccessModalOpen', false)
        ->call('showRoleAccess', $login->client_role_id)
        ->assertSet('roleAccessIsFull', true)
        ->assertSet('roleAccessCanManageSettings', true)
        ->assertSee('Akses penuh role default');
});

test('global profile keeps an authorized app context and remembers the last app', function () {
    $login = starterToastLogin();
    $appOne = App::query()->create([
        'name' => 'App One',
        'subdomain' => 'app1',
    ]);
    $appTwo = App::query()->create([
        'name' => 'App Two',
        'subdomain' => 'app2',
    ]);
    $modOne = AppMod::query()->create([
        'app_id' => $appOne->id,
        'code' => 'app_one_dashboard',
        'name' => 'App One Dashboard',
    ]);
    $modTwo = AppMod::query()->create([
        'app_id' => $appTwo->id,
        'code' => 'app_two_dashboard',
        'name' => 'App Two Dashboard',
    ]);
    $routeOne = AppRoute::query()->create([
        'app_mod_id' => $modOne->id,
        'name' => 'app1.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $routeTwo = AppRoute::query()->create([
        'app_mod_id' => $modTwo->id,
        'name' => 'app2.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    AppMenu::query()->create([
        'app_mod_id' => $modOne->id,
        'app_route_id' => $routeOne->id,
        'label' => 'Menu App One',
        'icon' => 'layout-dashboard',
    ]);
    AppMenu::query()->create([
        'app_mod_id' => $modTwo->id,
        'app_route_id' => $routeTwo->id,
        'label' => 'Menu App Two',
        'icon' => 'layout-dashboard',
    ]);

    $this->actingAs($login)
        ->get('http://'.config('app.domain').'/profile/edit')
        ->assertOk()
        ->assertSee('App One')
        ->assertSee('Menu App One')
        ->assertDontSee('Belum ada menu');

    $this->get(route('app2.dashboard'))
        ->assertOk()
        ->assertSee('App Two')
        ->assertSee('Menu App Two');

    $this->get('http://'.config('app.domain').'/profile/edit')
        ->assertOk()
        ->assertSee('App Two')
        ->assertSee('Menu App Two')
        ->assertDontSee('Menu App One')
        ->assertDontSee('Belum ada menu');
});

test('global profile selects the app granted to a regular role', function () {
    $superuserLogin = starterToastLogin();
    $app = App::query()->create([
        'name' => 'Operations App',
        'subdomain' => 'app1',
    ]);
    $module = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'operations_dashboard',
        'name' => 'Operations Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $module->id,
        'name' => 'app1.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    AppMenu::query()->create([
        'app_mod_id' => $module->id,
        'app_route_id' => $route->id,
        'label' => 'Dashboard Operasional',
        'icon' => 'layout-dashboard',
    ]);
    $role = ClientRole::query()->create([
        'code' => 'operator',
        'name' => 'Operator',
    ]);
    $role->mods()->attach($module);
    $operator = ClientLogin::query()->create([
        'client_role_id' => $role->id,
        'name' => 'Operator Login',
        'username' => 'operator-context',
        'email' => 'operator-context@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    $this->actingAs($operator)
        ->get('http://'.config('app.domain').'/profile/edit')
        ->assertOk()
        ->assertSee('Operations App')
        ->assertSee('Dashboard Operasional')
        ->assertDontSee('Belum ada menu');
});

test('settings access can be delegated while system role and users stay private', function () {
    $superuserLogin = starterToastLogin();
    $managerRole = ClientRole::query()->create([
        'code' => 'settings_manager',
        'name' => 'Settings Manager',
        'can_manage_settings' => true,
    ]);
    $managerLogin = ClientLogin::query()->create([
        'client_role_id' => $managerRole->id,
        'name' => 'Settings Manager Login',
        'username' => 'settings-manager',
        'email' => 'settings-manager@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);
    $operatorRole = ClientRole::query()->create([
        'code' => 'operator',
        'name' => 'Operator',
    ]);
    ClientLogin::query()->create([
        'client_role_id' => $operatorRole->id,
        'name' => 'Operator Login',
        'username' => 'operator-login',
        'email' => 'operator-login@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    expect($managerRole->canManageSettings())->toBeTrue()
        ->and($operatorRole->canManageSettings())->toBeFalse()
        ->and($superuserLogin->role->canManageSettings())->toBeTrue();

    $this->actingAs($managerLogin)
        ->get(route('starter.profile.edit'))
        ->assertOk()
        ->assertSee('Pengaturan');

    $this->get(route('starter.settings'))
        ->assertOk()
        ->assertSee('Settings Manager')
        ->assertSee('Operator')
        ->assertDontSee('Superuser')
        ->assertDontSee($superuserLogin->name)
        ->assertDontSee($superuserLogin->email);

    $this->get(route('starter.settings', ['section' => 'users']))
        ->assertOk()
        ->assertSee('Settings Manager Login')
        ->assertSee('Operator Login')
        ->assertDontSee($superuserLogin->name)
        ->assertDontSee($superuserLogin->username)
        ->assertDontSee($superuserLogin->email);

    $this->get(route('starter.settings', ['section' => 'company']))
        ->assertOk()
        ->assertSee('Pengaturan Perusahaan');

    $this->get(route('starter.settings.roles.create'))
        ->assertOk()
        ->assertSee('Akses Pengaturan');

    Livewire::test(Roles::class, ['embedded' => true])
        ->call('showRoleAccess', $managerRole->id)
        ->assertSet('roleAccessCanManageSettings', true)
        ->assertSee('Dapat mengakses Pengaturan');

    $this->get(route('starter.settings.roles.edit', $superuserLogin->client_role_id))
        ->assertNotFound();

    $this->get(route('starter.user-management.users.edit', $superuserLogin->id))
        ->assertNotFound();
});

test('user list pins the superuser account before alphabetically sorted users', function () {
    $superuserLogin = starterToastLogin();
    $superuserLogin->forceFill(['name' => 'Zulu Superuser'])->save();
    $regularRole = ClientRole::query()->create([
        'code' => 'operator',
        'name' => 'Operator',
    ]);

    foreach (['Beta User', 'Alpha User'] as $index => $name) {
        ClientLogin::query()->create([
            'client_role_id' => $regularRole->id,
            'name' => $name,
            'username' => 'operator-'.$index,
            'email' => 'operator-'.$index.'@example.test',
            'password' => 'Secret12345',
            'status' => 'active',
        ]);
    }

    $this->actingAs($superuserLogin);

    Livewire::test(Users::class, ['embedded' => true])
        ->assertSeeInOrder(['Zulu Superuser', 'Alpha User', 'Beta User'])
        ->assertSee('Akun Default')
        ->assertSeeHtml('data-default-user');
});

test('starter navigation keeps settings query string changes', function () {
    $runtime = file_get_contents(public_path('assets/starter/js/starter-runtime.js'));

    expect($runtime)
        ->toContain('isSameNavigationUrl(target.href, current.href)')
        ->toContain('normalizeNavigationUrl(url)')
        ->toContain("parsed.hash = '';");
});

test('livewire ajax requests use the global blur loader', function () {
    $login = starterToastLogin();
    $runtime = file_get_contents(public_path('assets/starter/js/starter-runtime.js'));
    $appLayout = file_get_contents(resource_path('views/starter/templates/layouts/app.blade.php'));
    $authLayout = file_get_contents(resource_path('views/starter/templates/layouts/auth.blade.php'));

    $this->actingAs($login)
        ->get(route('starter.settings'))
        ->assertOk()
        ->assertSeeHtml('data-starter-livewire-loader')
        ->assertSee('Memproses...')
        ->assertSee('starter-livewire-is-loading', false)
        ->assertSee('data-starter-livewire-loading', false)
        ->assertSee('z-index: 1045', false);

    expect($runtime)
        ->toContain('showLivewireLoader(components = [])')
        ->toContain('window.Livewire.interceptRequest(({ request, onSend, onError, onFinish })')
        ->toContain("component.el?.setAttribute('data-starter-livewire-loading', '')")
        ->toContain('const actionMessages = messages.filter((message) => Array.isArray(message.calls) && message.calls.length > 0)')
        ->toContain('if (actionMessages.length === 0)')
        ->toContain('this.hideLivewireLoader()')
        ->toContain('clearTimeout(this.livewireLoaderTimer);')
        ->toContain('this.clearLivewireLoader();');

    expect($runtime)->not->toContain('this.livewireLoaderTimer = setTimeout');

    expect($appLayout)
        ->toContain('z-index: 1045')
        ->not->toContain('z-index: 1060');

    expect($authLayout)
        ->toContain('z-index: 1045')
        ->not->toContain('z-index: 1060');
});

test('profile route stays on current app subdomain', function () {
    $login = starterToastLogin();
    $domain = (string) config('app.domain');

    App::query()->create([
        'name' => 'App 1',
        'subdomain' => 'app1',
    ]);

    $this->actingAs($login)
        ->get("http://app1.{$domain}/profile/edit")
        ->assertOk()
        ->assertSee('Edit Profil Saya')
        ->assertSee("http://app1.{$domain}/profile/edit", false)
        ->assertDontSee("http://{$domain}/profile/edit", false);
});

test('admin can update client profile from settings page', function () {
    $login = starterToastLogin();
    Client::query()->firstOrFail()->forceFill(['logo' => 'assets/starter/images/client-logo.png'])->save();

    $this->actingAs($login);

    Livewire::test(ClientProfile::class)
        ->assertSee('Profil Perusahaan')
        ->assertSee('Pengaturan Perusahaan')
        ->assertSee('Ganti Logo')
        ->assertSee('Hapus Logo')
        ->assertSee('Nama Perusahaan')
        ->assertDontSee('Account Status')
        ->assertDontSee('Subscription')
        ->assertDontSee('Payment')
        ->set('clientForm.name', 'Updated Client')
        ->set('clientForm.email', 'updated-client@example.test')
        ->set('clientForm.phone', '08123456789')
        ->set('clientForm.pic_name', 'Aldhi PIC')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('starter-client-branding-updated', function (string $event, array $params): bool {
            return $event === 'starter-client-branding-updated'
                && array_key_exists('logoUrl', $params)
                && $params['logoUrl'] === null
                && ($params['clientName'] ?? null) === 'Updated Client';
        })
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Profil perusahaan berhasil disimpan.';
        });

    $this->assertDatabaseHas('starter_clients', [
        'name' => 'Updated Client',
        'email' => 'updated-client@example.test',
        'phone' => '08123456789',
        'pic_name' => 'Aldhi PIC',
        'logo' => 'assets/starter/images/client-logo.png',
        'account_status' => 'approved',
    ]);
});

test('saas lifecycle and payment columns are removed from private client schema', function () {
    $login = starterToastLogin();

    expect(Schema::hasColumns('starter_clients', [
        'subscription_status',
        'payment_method',
        'payment_reference',
        'trial_ends_at',
    ]))->toBeFalse();

    expect(Schema::hasColumns('starter_client_roles', [
        'can_manage_settings',
        'can_view_logs',
    ]))->toBeTrue()
        ->and(Schema::hasTable('starter_logs'))->toBeTrue()
        ->and(Schema::hasTable('starter_audit_logs'))->toBeFalse();

    $this->actingAs($login);

    Livewire::test(ClientProfile::class)
        ->assertDontSee('Account Status')
        ->assertDontSee('Subscription')
        ->assertDontSee('Payment')
        ->assertDontSee('wire:model=&quot;clientForm.account_status&quot;', false)
        ->assertDontSee('wire:model=&quot;clientForm.subscription_status&quot;', false)
        ->assertDontSee('wire:model=&quot;clientForm.payment_method&quot;', false)
        ->assertDontSee('wire:model=&quot;clientForm.payment_reference&quot;', false);
});

test('client profile photo upload and reset are supported', function () {
    Storage::fake('public');

    $login = starterToastLogin();

    $this->actingAs($login);

    $component = Livewire::test(ClientProfile::class)
        ->set('clientPhotoUpload', UploadedFile::fake()->image('pertamina-wide.png', 1200, 300))
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('starter-client-branding-updated', function (string $event, array $params): bool {
            return $event === 'starter-client-branding-updated'
                && str_contains((string) ($params['logoUrl'] ?? ''), '/storage/starter/client-photos/')
                && ($params['clientName'] ?? null) === 'Acme Client';
        });

    $client = Client::query()->firstOrFail();
    $logo = (string) $client->logo;

    expect($logo)->toStartWith("storage/starter/client-photos/{$client->id}/");

    Storage::disk('public')->assertExists(str($logo)->after('storage/')->toString());

    $component
        ->call('resetClientPhoto')
        ->assertHasNoErrors()
        ->assertDispatched('starter-client-branding-updated', function (string $event, array $params): bool {
            return $event === 'starter-client-branding-updated'
                && array_key_exists('logoUrl', $params)
                && $params['logoUrl'] === null
                && ($params['clientName'] ?? null) === 'Acme Client';
        })
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Logo berhasil dikembalikan ke default.';
        });

    $this->assertDatabaseHas('starter_clients', [
        'id' => $client->id,
        'logo' => null,
    ]);

    Storage::disk('public')->assertMissing(str($logo)->after('storage/')->toString());
});

test('client logo brands the sidebar and keeps arbitrary image proportions contained', function () {
    $login = starterToastLogin();

    Client::query()->firstOrFail()->update([
        'name' => 'Pertamina Patra Niaga',
        'logo' => 'storage/starter/client-photos/1/pertamina-wide.png',
    ]);

    $this->actingAs($login)
        ->get(route('starter.settings', ['section' => 'company']))
        ->assertOk()
        ->assertSeeHtml('data-starter-brand-logo')
        ->assertSeeHtml('data-company-logo="true"')
        ->assertSee('storage/starter/client-photos/1/pertamina-wide.png', false)
        ->assertSee('Pertamina Patra Niaga')
        ->assertSee('object-fit: contain', false)
        ->assertSee('object-position: center', false)
        ->assertSeeHtml('data-client-logo-preview');

    $runtime = file_get_contents(public_path('assets/starter/js/starter-runtime.js'));

    expect($runtime)
        ->toContain("document.addEventListener('starter-client-branding-updated'")
        ->toContain('updateClientBranding(detail = {})')
        ->toContain('prepareClientBranding()')
        ->toContain("image.addEventListener('error'")
        ->toContain('image.complete && image.naturalWidth === 0')
        ->toContain("image.removeAttribute('data-company-logo')");
});

test('client profile settings route is visible to admin only', function () {
    $login = starterToastLogin();

    $this->actingAs($login)
        ->get(route('starter.client-profile'))
        ->assertRedirect(route('starter.settings', ['section' => 'company']));

    $this->get(route('starter.settings', ['section' => 'company']))
        ->assertOk()
        ->assertSee('Profil Perusahaan')
        ->assertSee('Simpan Profil Perusahaan');

    $operator = ClientLogin::query()->create([
        'client_role_id' => ClientRole::query()->create([
            'code' => 'operator',
            'name' => 'Operator',
        ])->id,
        'name' => 'Operator Login',
        'username' => 'operator',
        'email' => 'operator@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    $this->actingAs($operator)
        ->get(route('starter.client-profile'))
        ->assertForbidden();

    $this->get(route('starter.settings'))
        ->assertForbidden();
});

test('profile photo upload stores image path', function () {
    Storage::fake('public');

    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->set('profilePhotoUpload', UploadedFile::fake()->image('avatar.jpg', 160, 160))
        ->call('saveAccount')
        ->assertHasNoErrors()
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Profil berhasil disimpan.';
        });

    $profilePhoto = (string) $login->fresh()->profile_photo;

    expect($profilePhoto)->toStartWith("storage/starter/profile-photos/{$login->id}/");

    Storage::disk('public')->assertExists(str($profilePhoto)->after('storage/')->toString());
});

test('profile photo reset clears stored image path', function () {
    Storage::fake('public');

    $login = starterToastLogin();
    $oldPath = "starter/profile-photos/{$login->id}/old-avatar.jpg";

    Storage::disk('public')->put($oldPath, 'old avatar');
    $login->forceFill(['profile_photo' => 'storage/'.$oldPath])->save();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->call('resetProfilePhoto')
        ->assertHasNoErrors()
        ->assertDispatched('starter-account-updated')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Foto profil berhasil dikembalikan ke default.';
        });

    $this->assertDatabaseHas('starter_client_logins', [
        'id' => $login->id,
        'profile_photo' => 'assets/starter/images/avatar.png',
    ]);

    Storage::disk('public')->assertMissing($oldPath);
});

test('role save flashes starter toast for the redirected role list', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(RoleForm::class)
        ->set('roleForm.code', 'operator')
        ->set('roleForm.name', 'Operator')
        ->set('roleForm.desc', 'Operasional')
        ->set('roleForm.can_manage_settings', true)
        ->set('roleForm.module_ids', [])
        ->call('save')
        ->assertSessionHas('starter-toast', function (array $toast): bool {
            return ($toast['type'] ?? null) === 'success'
                && ($toast['message'] ?? null) === 'Role berhasil disimpan.';
        });

    $this->get(route('starter.settings', ['section' => 'roles']))
        ->assertOk()
        ->assertSeeHtml('data-starter-flash-toast')
        ->assertSeeHtml('data-type="success"')
        ->assertSeeHtml('data-message="Role berhasil disimpan."');

    $this->assertDatabaseHas('starter_client_roles', [
        'code' => 'operator',
        'can_manage_settings' => true,
    ]);
});

test('role save requires default page for selected app modules', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'App 1',
        'subdomain' => 'app1',
    ]);
    $mod = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'dashboard',
        'name' => 'Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'app1.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $alternateRoute = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'app1.dashboard.summary',
        'uri' => 'dashboard/summary',
        'method' => 'GET',
    ]);
    AppMenu::query()->create([
        'app_mod_id' => $mod->id,
        'app_route_id' => $route->id,
        'label' => 'Dashboard',
        'is_landing_candidate' => true,
    ]);
    AppMenu::query()->create([
        'app_mod_id' => $mod->id,
        'app_route_id' => $alternateRoute->id,
        'label' => 'Summary',
        'is_landing_candidate' => true,
    ]);

    $this->actingAs($login);

    Livewire::test(RoleForm::class)
        ->set('roleForm.code', 'operator')
        ->set('roleForm.name', 'Operator')
        ->set('roleForm.module_ids', [(string) $mod->id])
        ->call('save')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'danger'
                && ($params['message'] ?? null) === 'Halaman awal wajib dipilih untuk App 1.';
        });
});

test('role save stores default page menu for selected app modules', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'App 1',
        'subdomain' => 'app1',
    ]);
    $mod = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'dashboard',
        'name' => 'Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'app1.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $formRoute = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'app1.dashboard.create',
        'uri' => 'dashboard/create',
        'method' => 'GET',
    ]);
    AppMenu::query()->create([
        'app_mod_id' => $mod->id,
        'app_route_id' => $route->id,
        'label' => 'Dashboard',
        'is_landing_candidate' => true,
    ]);
    $menu = AppMenu::query()->create([
        'app_mod_id' => $mod->id,
        'app_route_id' => $formRoute->id,
        'label' => 'Form',
        'is_landing_candidate' => false,
    ]);

    $this->actingAs($login);

    Livewire::test(RoleForm::class)
        ->set('roleForm.code', 'operator')
        ->set('roleForm.name', 'Operator')
        ->set('roleForm.module_ids', [(string) $mod->id])
        ->set("roleForm.landing_menu_ids.{$app->id}", (string) $menu->id)
        ->call('save')
        ->assertSessionHas('starter-toast', function (array $toast): bool {
            return ($toast['type'] ?? null) === 'success'
                && ($toast['message'] ?? null) === 'Role berhasil disimpan.';
        });

    $role = ClientRole::query()->where('code', 'operator')->firstOrFail();

    $this->assertDatabaseHas('pivot_client_roles_app_landings', [
        'client_role_id' => $role->id,
        'app_id' => $app->id,
        'app_menu_id' => $menu->id,
    ]);
});

test('app anchor redirects to role default page', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'App 1',
        'subdomain' => 'app1',
    ]);
    $mod = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'dashboard',
        'name' => 'Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'app1.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $menu = AppMenu::query()->create([
        'app_mod_id' => $mod->id,
        'app_route_id' => $route->id,
        'label' => 'Dashboard',
        'is_landing_candidate' => true,
    ]);
    $role = ClientRole::query()->create([
        'code' => 'operator',
        'name' => 'Operator',
    ]);
    $role->mods()->attach($mod);
    ClientRoleAppLanding::query()->create([
        'client_role_id' => $role->id,
        'app_id' => $app->id,
        'app_menu_id' => $menu->id,
    ]);
    $login->forceFill(['client_role_id' => $role->id])->save();

    $target = app(NavigationAuthorizedRedirectService::class)->forAppAnchor($login->fresh('role'), 'app1');

    expect($target)->toBe(route('app1.dashboard'));
});

test('role delete validation dispatches danger starter toast', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(RoleForm::class, ['roleId' => $login->client_role_id])
        ->call('prepareRoleDeletion')
        ->call('deleteSelectedRole')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'danger'
                && ($params['message'] ?? null) === 'Role bawaan Superuser tidak dapat dihapus.';
        });
});

test('default admin role is read only', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(RoleForm::class, ['roleId' => $login->client_role_id])
        ->set('roleForm.name', 'Changed Admin')
        ->call('save')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'danger'
                && ($params['message'] ?? null) === 'Role bawaan Superuser hanya dapat dilihat.';
        });

    $this->assertDatabaseHas('starter_client_roles', [
        'id' => $login->client_role_id,
        'name' => 'Superuser',
    ]);
});

test('role users modal lists assigned users', function () {
    $login = starterToastLogin();

    ClientLogin::query()->create([
        'client_role_id' => $login->client_role_id,
        'name' => 'Second Admin',
        'username' => 'second-admin',
        'email' => 'second-admin@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    $this->actingAs($login);

    Livewire::test(Roles::class)
        ->assertSeeHtml('data-role-users-trigger')
        ->assertSeeHtml('starter-table-action-link')
        ->assertDontSeeHtml('class="btn btn-link p-0"')
        ->call('showRoleUsers', $login->client_role_id)
        ->assertSet('roleUsersModalOpen', true)
        ->assertSet('roleUsersRoleName', 'Superuser')
        ->assertSee('Aldhi Admin')
        ->assertSee('Second Admin')
        ->assertSee('second-admin@example.test');
});

test('superuser password reset is unavailable from user management', function () {
    $login = starterToastLogin();
    $operatorRole = ClientRole::query()->create([
        'code' => 'operator_resettable',
        'name' => 'Operator Resettable',
    ]);
    $operator = ClientLogin::query()->create([
        'client_role_id' => $operatorRole->id,
        'name' => 'Operator Resettable',
        'username' => 'operator-resettable',
        'email' => 'operator-resettable@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    $this->actingAs($login);

    Livewire::test(Users::class)
        ->assertDontSeeHtml('wire:click="preparePasswordReset('.$login->id.')"')
        ->assertSeeHtml('wire:click="preparePasswordReset('.$operator->id.')"');

    Livewire::test(Users::class)
        ->call('preparePasswordReset', $login->id)
        ->assertForbidden();

    expect(Hash::check('Secret12345', $login->fresh()->password))->toBeTrue();
});

test('user management shows private account metadata and can reset password', function () {
    $login = starterToastLogin();
    $operatorRole = ClientRole::query()->create([
        'code' => 'operator_password_reset',
        'name' => 'Operator',
    ]);

    Client::query()->firstOrFail()->update([
        'phone' => '08123456789',
        'pic_name' => 'Aldhi PIC',
        'account_status' => 'approved',
    ]);

    $operator = ClientLogin::query()->create([
        'client_role_id' => $operatorRole->id,
        'name' => 'Operator Login',
        'username' => 'operator-reset',
        'email' => 'operator@example.test',
        'email_verified_at' => now(),
        'password' => 'Secret12345',
        'status' => 'active',
        'last_login_ip' => '127.0.0.1',
    ]);

    $this->actingAs($login);

    Livewire::test(Users::class)
        ->assertSee('Manajemen User')
        ->assertSee('Operator Login')
        ->assertSee('operator-reset')
        ->assertSee('operator@example.test')
        ->assertSeeHtml('id="reset-user-password-modal"')
        ->assertDontSeeHtml('wire:confirm')
        ->call('preparePasswordReset', $operator->id)
        ->assertSet('passwordResetUserId', $operator->id)
        ->assertSet('passwordResetUserName', 'Operator Login')
        ->assertSet('passwordResetModalOpen', true)
        ->assertSeeHtml('modal modal-blur fade show d-block')
        ->call('cancelPasswordReset')
        ->assertSet('passwordResetUserId', null)
        ->assertSet('passwordResetModalOpen', false)
        ->call('preparePasswordReset', $operator->id)
        ->call('resetSelectedPassword')
        ->assertSet('passwordResetModalOpen', false)
        ->assertSet('temporaryPasswordUsername', 'operator-reset')
        ->assertSet('temporaryPassword', fn (?string $password): bool => filled($password))
        ->assertSeeHtml('alert alert-warning alert-dismissible')
        ->assertSeeHtml('data-temporary-credentials-dismiss');

    expect($operator->fresh()->must_change_password)->toBeTrue();

    $this->assertDatabaseHas('starter_logs', [
        'action_key' => 'auth.password_reset_by_admin',
        'event' => 'security',
        'client_login_id' => $login->id,
        'auditable_id' => (string) $operator->id,
    ]);
});

test('add user uses a dedicated page and previews selected role module access', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'Operations App',
        'subdomain' => 'operations',
    ]);
    $module = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'employee_view',
        'name' => 'Employee View',
        'desc' => 'Read employee records.',
    ]);
    $role = ClientRole::query()->create([
        'code' => 'viewer',
        'name' => 'Viewer',
    ]);
    $role->mods()->attach($module);

    $this->actingAs($login)
        ->get(route('starter.user-management.users.create'))
        ->assertOk()
        ->assertSee('Tambah User')
        ->assertSee('Akses Role');

    Livewire::test(Users::class)
        ->assertSeeHtml('href="'.route('starter.user-management.users.create').'"')
        ->assertSeeHtml('icon-tabler-user-plus')
        ->assertDontSee('Display Name');

    Livewire::test(UserForm::class)
        ->assertSeeHtml('icon-tabler-shield-lock')
        ->set('userForm.role_id', (string) $role->id)
        ->assertSee('Operations App')
        ->assertSee('Employee View')
        ->assertSee('employee_view')
        ->assertSee('Read employee records.');
});

test('dedicated user page creates account with temporary password', function () {
    $login = starterToastLogin();
    $role = ClientRole::query()->create([
        'code' => 'operator',
        'name' => 'Operator',
    ]);

    $this->actingAs($login);

    Livewire::test(UserForm::class)
        ->set('userForm.name', 'New Operator')
        ->set('userForm.username', 'new-operator')
        ->set('userForm.email', 'new-operator@example.test')
        ->set('userForm.role_id', (string) $role->id)
        ->set('userForm.status', 'active')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('temporaryPasswordUsername', 'new-operator')
        ->assertSet('temporaryPassword', fn (?string $password): bool => filled($password))
        ->assertSeeHtml('alert alert-warning alert-dismissible')
        ->assertSeeHtml('data-temporary-credentials-dismiss');

    $this->assertDatabaseHas('starter_client_logins', [
        'client_role_id' => $role->id,
        'name' => 'New Operator',
        'username' => 'new-operator',
        'email' => 'new-operator@example.test',
        'must_change_password' => true,
    ]);
});

test('role deletion uses the shared confirmation modal', function () {
    $login = starterToastLogin();
    $role = ClientRole::query()->create([
        'code' => 'temporary',
        'name' => 'Temporary Role',
    ]);

    $this->actingAs($login);

    Livewire::test(RoleForm::class, ['roleId' => $role->id])
        ->assertSeeHtml('id="delete-role-modal"')
        ->call('prepareRoleDeletion')
        ->assertSet('deleteRoleId', $role->id)
        ->assertSet('deleteRoleName', 'Temporary Role')
        ->assertSet('deleteRoleModalOpen', true)
        ->call('cancelRoleDeletion')
        ->assertSet('deleteRoleId', null)
        ->assertSet('deleteRoleModalOpen', false)
        ->call('prepareRoleDeletion')
        ->call('deleteSelectedRole')
        ->assertSet('deleteRoleId', null)
        ->assertSet('deleteRoleModalOpen', false)
        ->assertSessionHas('starter-toast', function (array $toast): bool {
            return ($toast['type'] ?? null) === 'success'
                && ($toast['message'] ?? null) === 'Role berhasil dihapus.';
        });

    $this->assertDatabaseMissing('starter_client_roles', ['id' => $role->id]);
});

test('module accordion presentation state stays in alpine when module access changes', function () {
    $login = starterToastLogin();
    $appOne = App::query()->create([
        'name' => 'App One',
        'subdomain' => 'app-one',
    ]);
    $appTwo = App::query()->create([
        'name' => 'App Two',
        'subdomain' => 'app-two',
    ]);
    $modOne = AppMod::query()->create([
        'app_id' => $appOne->id,
        'code' => 'app_one_module',
        'name' => 'Shared Module',
        'desc' => 'Module for app one.',
    ]);
    AppMod::query()->create([
        'app_id' => $appTwo->id,
        'code' => 'app_two_module',
        'name' => 'Shared Module',
        'desc' => 'Module for app two.',
    ]);

    $this->actingAs($login);

    Livewire::test(RoleForm::class)
        ->assertSeeHtml('x-data="{')
        ->assertSee('toggleModuleApp(appKey)', false)
        ->set('roleForm.module_ids', [(string) $modOne->id])
        ->assertSeeHtml('id="role-app-modules-app-'.$appOne->id.'"')
        ->assertSeeHtml('id="role-app-modules-app-'.$appTwo->id.'"')
        ->assertDontSeeHtml('wire:click="toggleModuleApp');
});

test('module accordion toggles individual apps from alpine state only', function () {
    $login = starterToastLogin();
    $appOne = App::query()->create(['name' => 'App One', 'subdomain' => 'app-one']);
    $appTwo = App::query()->create(['name' => 'App Two', 'subdomain' => 'app-two']);
    AppMod::query()->create(['app_id' => $appOne->id, 'code' => 'module_one', 'name' => 'Module One']);
    AppMod::query()->create(['app_id' => $appTwo->id, 'code' => 'module_two', 'name' => 'Module Two']);

    $this->actingAs($login);

    Livewire::test(RoleForm::class)
        ->assertDontSeeHtml('data-bs-target="#role-app-modules-app-'.$appOne->id.'"')
        ->assertSee('x-on:click="toggleModuleApp(', false)
        ->assertSee('x-bind:class="{ collapsed: ! isModuleAppExpanded(', false)
        ->assertSee('x-bind:class="{ show: isModuleAppExpanded(', false)
        ->assertSeeHtml('id="role-app-modules-app-'.$appOne->id.'"')
        ->assertSeeHtml('id="role-app-modules-app-'.$appTwo->id.'"')
        ->assertDontSeeHtml('wire:click="toggleModuleApp');
});

test('module accordion exposes every app key to alpine expand all state', function () {
    $login = starterToastLogin();
    $appOne = App::query()->create([
        'name' => 'App One',
        'subdomain' => 'app-one',
    ]);
    $appTwo = App::query()->create([
        'name' => 'App Two',
        'subdomain' => 'app-two',
    ]);
    AppMod::query()->create([
        'app_id' => $appOne->id,
        'code' => 'app_one_module',
        'name' => 'App One Module',
    ]);
    AppMod::query()->create([
        'app_id' => $appTwo->id,
        'code' => 'app_two_module',
        'name' => 'App Two Module',
    ]);

    $this->actingAs($login);

    Livewire::test(RoleForm::class)
        ->assertSee('moduleAppKeys:', false)
        ->assertSee('app-'.$appOne->id)
        ->assertSee('app-'.$appTwo->id)
        ->assertSee('this.expandedModuleApps = this.allModuleAppsExpanded()', false);
});

test('module accordion toggles all apps with alpine without a livewire request', function () {
    $login = starterToastLogin();
    $appOne = App::query()->create([
        'name' => 'App One',
        'subdomain' => 'app-one',
    ]);
    $appTwo = App::query()->create([
        'name' => 'App Two',
        'subdomain' => 'app-two',
    ]);
    AppMod::query()->create([
        'app_id' => $appOne->id,
        'code' => 'app_one_module',
        'name' => 'App One Module',
    ]);
    AppMod::query()->create([
        'app_id' => $appTwo->id,
        'code' => 'app_two_module',
        'name' => 'App Two Module',
    ]);

    $this->actingAs($login);

    Livewire::test(RoleForm::class)
        ->assertSeeHtml('x-on:click="toggleAllModuleApps()"')
        ->assertSee('Buka semua app')
        ->assertSee("allModuleAppsExpanded() ? 'Tutup semua app' : 'Buka semua app'", false)
        ->assertDontSeeHtml('wire:click="toggleAllModuleApps"');
});
