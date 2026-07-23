<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\UserManagementRoleService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
class Roles extends Component
{
    use WithPagination;

    public bool $embedded = false;

    public string $search = '';

    public bool $roleUsersModalOpen = false;

    public string $roleUsersRoleName = '';

    /**
     * @var array<int, array{name: string, email: string}>
     */
    public array $roleUsers = [];

    public bool $roleAccessModalOpen = false;

    public string $roleAccessRoleName = '';

    public string $roleAccessRoleCode = '';

    public bool $roleAccessIsFull = false;

    public bool $roleAccessCanManageSettings = false;

    public bool $roleAccessCanViewLogs = false;

    public int $roleAccessAppCount = 0;

    public int $roleAccessModuleCount = 0;

    /**
     * @var array<int, array{name: string, modules: array<int, array{name: string, code: string, desc: string}>}>
     */
    public array $roleAccessApps = [];

    public function mount(bool $embedded = false): void
    {
        $this->embedded = $embedded;
    }

    public function updatedSearch(): void
    {
        $this->resetPage('rolesPage');
    }

    public function showRoleUsers(int $id): void
    {
        $role = $this->roles()->findRole($this->login(), $id)->load('clientLogins');

        $this->roleUsersRoleName = $role->name;
        $this->roleUsers = $role->clientLogins
            ->map(fn (ClientLogin $login): array => [
                'name' => $login->name,
                'email' => $login->email,
            ])
            ->values()
            ->all();
        $this->roleUsersModalOpen = true;
    }

    public function closeRoleUsersModal(): void
    {
        $this->roleUsersModalOpen = false;
        $this->roleUsersRoleName = '';
        $this->roleUsers = [];
    }

    public function showRoleAccess(int $id): void
    {
        $role = $this->roles()->findRole($this->login(), $id);
        $modules = $role->isSuperuser()
            ? $this->roles()->availableModules()
            : $role->mods;

        $this->roleAccessRoleName = $role->name;
        $this->roleAccessRoleCode = $role->code;
        $this->roleAccessIsFull = $role->isSuperuser();
        $this->roleAccessCanManageSettings = $role->canManageSettings();
        $this->roleAccessCanViewLogs = $role->canViewLogs();
        $this->roleAccessModuleCount = $modules->count();
        $this->roleAccessApps = $modules
            ->groupBy(fn ($module): string => $module->app?->name ?? 'Tanpa App')
            ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE)
            ->map(function ($appModules, string $appName): array {
                return [
                    'name' => $appName,
                    'modules' => $appModules
                        ->sortBy(fn ($module): string => $module->name, SORT_NATURAL | SORT_FLAG_CASE)
                        ->map(fn ($module): array => [
                            'name' => $module->name,
                            'code' => $module->code,
                            'desc' => (string) $module->desc,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
        $this->roleAccessAppCount = count($this->roleAccessApps);
        $this->roleAccessModalOpen = true;
    }

    public function closeRoleAccessModal(): void
    {
        $this->roleAccessModalOpen = false;
        $this->roleAccessRoleName = '';
        $this->roleAccessRoleCode = '';
        $this->roleAccessIsFull = false;
        $this->roleAccessCanManageSettings = false;
        $this->roleAccessCanViewLogs = false;
        $this->roleAccessAppCount = 0;
        $this->roleAccessModuleCount = 0;
        $this->roleAccessApps = [];
    }

    public function render()
    {
        $login = $this->login();
        $visibleRoles = $this->roles()->roles($login);
        $superuserRole = $visibleRoles->first(fn ($role): bool => $role->isSuperuser());
        $allRoles = $visibleRoles
            ->reject(fn ($role): bool => $role->isSuperuser())
            ->sortBy(fn ($role): string => $role->name, SORT_NATURAL | SORT_FLAG_CASE)
            ->when($superuserRole, fn ($roles) => $roles->prepend($superuserRole))
            ->values();
        $search = Str::of($this->search)->trim()->lower()->toString();
        $filteredRoles = $search === ''
            ? $allRoles
            : $allRoles->filter(function ($role) use ($search): bool {
                return Str::of($role->name)->lower()->contains($search)
                    || Str::of($role->code)->lower()->contains($search)
                    || Str::of((string) $role->desc)->lower()->contains($search);
            });
        $currentPage = $this->getPage(pageName: 'rolesPage');
        $perPage = 10;
        $roles = new LengthAwarePaginator(
            $filteredRoles->forPage($currentPage, $perPage)->values(),
            $filteredRoles->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'rolesPage',
            ],
        );

        return view('starter.user-management.roles', [
            'roles' => $roles,
            'roleCount' => $allRoles->count(),
            'totalAppCount' => $this->roles()->availableModules()->pluck('app_id')->filter()->unique()->count(),
            'totalModuleCount' => $this->roles()->availableModules()->count(),
        ])->title('Manajemen Role');
    }

    private function roles(): UserManagementRoleService
    {
        return app(UserManagementRoleService::class);
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();
        abort_unless(
            $login instanceof ClientLogin
                && ($login->loadMissing('role')->role?->canManageSettings() ?? false),
            403,
        );

        return $login->loadMissing('client');
    }
}
