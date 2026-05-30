<?php

use App\Livewire\Starter\Profile\EditMyProfile;
use App\Livewire\Starter\Settings\ClientProfile;
use App\Livewire\Starter\UserManagement\Roles;
use App\Models\Starter\App;
use App\Models\Starter\AppMenu;
use App\Models\Starter\AppMod;
use App\Models\Starter\AppRoute;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Models\Starter\UserRole;
use App\Models\Starter\UserRoleAppLanding;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        ->set('accountForm.profile_photo', 'assets/mine/avatar.png')
        ->call('saveAccount')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Profile saved successfully.';
        });

    $this->assertDatabaseHas('user_logins', [
        'id' => $login->id,
        'name' => 'Aldhi Updated',
        'username' => 'aldhi-updated',
        'email' => 'aldhi.updated@example.test',
        'profile_photo' => 'assets/mine/avatar.png',
    ]);
});

test('profile page renders settings sections for admin', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(EditMyProfile::class)
        ->assertSee('Account Settings')
        ->assertSee('Account Detail')
        ->assertSee('Security')
        ->assertSee('Change profile photo')
        ->assertSee('Delete profile photo')
        ->assertSee('Delete profile photo?')
        ->assertSee('Your current profile photo will be replaced with the default photo.')
        ->assertDontSee('Change avatar')
        ->assertDontSee('Delete avatar')
        ->assertDontSee('Image URL / Path')
        ->assertDontSee('Avatar Google')
        ->assertDontSee('Client Profile')
        ->assertDontSee('Admin Control')
        ->assertDontSee('Payment Reference')
        ->assertDontSee('Save Control');
});

test('admin can update client profile from settings page', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(ClientProfile::class)
        ->assertSee('Client Profile')
        ->assertSee('Client Settings')
        ->assertSee('Change Logo')
        ->assertSee('Delete Logo')
        ->assertSee('Account Status')
        ->assertSee('Subscription')
        ->assertSee('Payment')
        ->assertSee('Client Name')
        ->set('clientForm.name', 'Updated Client')
        ->set('clientForm.email', 'updated-client@example.test')
        ->set('clientForm.phone', '08123456789')
        ->set('clientForm.pic_name', 'Aldhi PIC')
        ->set('clientForm.logo', 'assets/mine/client-logo.png')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Client profile saved successfully.';
        });

    $this->assertDatabaseHas('users', [
        'id' => $login->user_id,
        'name' => 'Updated Client',
        'email' => 'updated-client@example.test',
        'phone' => '08123456789',
        'pic_name' => 'Aldhi PIC',
        'logo' => 'assets/mine/client-logo.png',
        'account_status' => 'pending',
        'subscription_status' => 'none',
        'payment_method' => null,
        'payment_reference' => null,
    ]);
});

test('client lifecycle and payment fields are read only in client profile settings', function () {
    $login = starterToastLogin();

    User::query()->whereKey($login->user_id)->update([
        'account_status' => 'approved',
        'approved_at' => '2026-05-01 10:00:00',
        'subscription_status' => 'active',
        'payment_method' => 'bank-transfer',
        'payment_reference' => 'INV-001',
        'trial_ends_at' => '2026-06-01 10:00:00',
        'subscribed_at' => '2026-05-02 10:00:00',
        'subscription_ends_at' => '2027-05-02 10:00:00',
        'payment_approved_at' => '2026-05-03 10:00:00',
    ]);

    $this->actingAs($login);

    Livewire::test(ClientProfile::class)
        ->assertSee('Approved')
        ->assertSee('Active')
        ->assertSee('bank-transfer')
        ->assertSee('INV-001')
        ->assertDontSee('wire:model=&quot;clientForm.account_status&quot;', false)
        ->assertDontSee('wire:model=&quot;clientForm.subscription_status&quot;', false)
        ->assertDontSee('wire:model=&quot;clientForm.payment_method&quot;', false)
        ->assertDontSee('wire:model=&quot;clientForm.payment_reference&quot;', false);
});

test('client profile photo upload and reset are supported', function () {
    Storage::fake('public');

    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(ClientProfile::class)
        ->set('clientPhotoUpload', UploadedFile::fake()->image('client.jpg', 180, 180))
        ->call('save')
        ->assertHasNoErrors();

    $client = $login->user->fresh();
    $logo = (string) $client->logo;

    expect($logo)->toStartWith("storage/starter/client-photos/{$client->id}/");

    Storage::disk('public')->assertExists(str($logo)->after('storage/')->toString());

    Livewire::test(ClientProfile::class)
        ->call('resetClientPhoto')
        ->assertHasNoErrors()
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Logo reset to default.';
        });

    $this->assertDatabaseHas('users', [
        'id' => $client->id,
        'logo' => null,
    ]);

    Storage::disk('public')->assertMissing(str($logo)->after('storage/')->toString());
});

test('client profile settings route is visible to admin only', function () {
    $login = starterToastLogin();

    $this->actingAs($login)
        ->get(route('starter.client-profile'))
        ->assertOk()
        ->assertSee('Client Profile')
        ->assertSee('Save Client Profile');

    $operator = UserLogin::query()->create([
        'user_id' => $login->user_id,
        'user_role_id' => UserRole::query()->create([
            'user_id' => $login->user_id,
            'code' => 'operator',
            'name' => 'Operator',
        ])->id,
        'name' => 'Operator Login',
        'username' => 'operator',
        'email' => 'operator@example.test',
        'password' => 'secret',
    ]);

    $this->actingAs($operator)
        ->get(route('starter.client-profile'))
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
                && ($params['message'] ?? null) === 'Profile saved successfully.';
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
                && ($params['message'] ?? null) === 'Profile photo reset to default.';
        });

    $this->assertDatabaseHas('user_logins', [
        'id' => $login->id,
        'profile_photo' => 'assets/mine/avatar.png',
    ]);

    Storage::disk('public')->assertMissing($oldPath);
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
                && ($params['message'] ?? null) === 'Role saved successfully.';
        });
});

test('role save requires default page for selected app modules', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'Web',
        'subdomain' => 'web',
    ]);
    $mod = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'dashboard',
        'name' => 'Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'web.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $alternateRoute = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'web.dashboard.summary',
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

    Livewire::test(Roles::class)
        ->set('roleForm.code', 'operator')
        ->set('roleForm.name', 'Operator')
        ->set('roleForm.module_ids', [(string) $mod->id])
        ->call('save')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'danger'
                && ($params['message'] ?? null) === 'Default page is required for Web.';
        });
});

test('role save stores default page menu for selected app modules', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'Web',
        'subdomain' => 'web',
    ]);
    $mod = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'dashboard',
        'name' => 'Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'web.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $formRoute = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'web.dashboard.create',
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

    Livewire::test(Roles::class)
        ->set('roleForm.code', 'operator')
        ->set('roleForm.name', 'Operator')
        ->set('roleForm.module_ids', [(string) $mod->id])
        ->set("roleForm.landing_menu_ids.{$app->id}", (string) $menu->id)
        ->call('save')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'success'
                && ($params['message'] ?? null) === 'Role saved successfully.';
        });

    $role = UserRole::query()->where('code', 'operator')->firstOrFail();

    $this->assertDatabaseHas('rel_user_roles_app_landings', [
        'user_role_id' => $role->id,
        'app_id' => $app->id,
        'app_menu_id' => $menu->id,
    ]);
});

test('app anchor redirects to role default page', function () {
    $login = starterToastLogin();
    $app = App::query()->create([
        'name' => 'Web',
        'subdomain' => 'web',
    ]);
    $mod = AppMod::query()->create([
        'app_id' => $app->id,
        'code' => 'dashboard',
        'name' => 'Dashboard',
    ]);
    $route = AppRoute::query()->create([
        'app_mod_id' => $mod->id,
        'name' => 'web.dashboard',
        'uri' => 'dashboard/index',
        'method' => 'GET',
    ]);
    $menu = AppMenu::query()->create([
        'app_mod_id' => $mod->id,
        'app_route_id' => $route->id,
        'label' => 'Dashboard',
        'is_landing_candidate' => true,
    ]);
    $role = UserRole::query()->create([
        'user_id' => $login->user_id,
        'code' => 'operator',
        'name' => 'Operator',
    ]);
    $role->mods()->attach($mod);
    UserRoleAppLanding::query()->create([
        'user_role_id' => $role->id,
        'app_id' => $app->id,
        'app_menu_id' => $menu->id,
    ]);
    $login->forceFill(['user_role_id' => $role->id])->save();

    $target = app(NavigationAuthorizedRedirectService::class)->forAppAnchor($login->fresh('role'), 'web');

    expect($target)->toBe(route('web.dashboard'));
});

test('role delete validation dispatches danger starter toast', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(Roles::class)
        ->call('deleteRole', $login->user_role_id)
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'danger'
                && ($params['message'] ?? null) === 'The default admin role cannot be deleted.';
        });
});

test('default admin role is read only', function () {
    $login = starterToastLogin();

    $this->actingAs($login);

    Livewire::test(Roles::class)
        ->call('editRole', $login->user_role_id)
        ->set('roleForm.name', 'Changed Admin')
        ->call('save')
        ->assertDispatched('starter-toast', function (string $event, array $params): bool {
            return $event === 'starter-toast'
                && ($params['type'] ?? null) === 'danger'
                && ($params['message'] ?? null) === 'The default admin role is read only.';
        });

    $this->assertDatabaseHas('user_roles', [
        'id' => $login->user_role_id,
        'name' => 'Administrator',
    ]);
});

test('role users modal lists assigned users', function () {
    $login = starterToastLogin();

    UserLogin::query()->create([
        'user_id' => $login->user_id,
        'user_role_id' => $login->user_role_id,
        'name' => 'Second Admin',
        'username' => 'second-admin',
        'email' => 'second-admin@example.test',
        'password' => 'secret',
    ]);

    $this->actingAs($login);

    Livewire::test(Roles::class)
        ->call('showRoleUsers', $login->user_role_id)
        ->assertSet('roleUsersModalOpen', true)
        ->assertSet('roleUsersRoleName', 'Administrator')
        ->assertSee('Aldhi Admin')
        ->assertSee('Second Admin')
        ->assertSee('second-admin@example.test');
});

test('module accordion keeps expanded app when module access changes', function () {
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

    Livewire::test(Roles::class)
        ->call('toggleModuleApp', 'app-'.$appOne->id)
        ->assertSet('expandedModuleAppKeys', ['app-'.$appOne->id])
        ->set('roleForm.module_ids', [(string) $modOne->id])
        ->assertSet('expandedModuleAppKeys', ['app-'.$appOne->id])
        ->assertSeeHtml('id="role-app-modules-app-'.$appOne->id.'" class="accordion-collapse collapse show"')
        ->assertDontSeeHtml('id="role-app-modules-app-'.$appTwo->id.'" class="accordion-collapse collapse show"');
});

test('module accordion can expand all apps', function () {
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

    Livewire::test(Roles::class)
        ->call('expandAllModuleApps')
        ->assertSet('expandedModuleAppKeys', ['app-'.$appOne->id, 'app-'.$appTwo->id])
        ->assertSeeHtml('id="role-app-modules-app-'.$appOne->id.'" class="accordion-collapse collapse show"')
        ->assertSeeHtml('id="role-app-modules-app-'.$appTwo->id.'" class="accordion-collapse collapse show"');
});

test('module accordion toggles all apps', function () {
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

    Livewire::test(Roles::class)
        ->assertSee('Expand all')
        ->call('toggleAllModuleApps')
        ->assertSet('expandedModuleAppKeys', ['app-'.$appOne->id, 'app-'.$appTwo->id])
        ->assertSee('Collapse all')
        ->call('toggleAllModuleApps')
        ->assertSet('expandedModuleAppKeys', [])
        ->assertSee('Expand all');
});
