<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\UserManagementRoleService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Roles extends Component
{
    public ?int $selectedRoleId = null;

    public string $search = '';

    /**
     * @var array<int, string>
     */
    public array $expandedModuleAppKeys = [];

    public bool $roleUsersModalOpen = false;

    public string $roleUsersRoleName = '';

    /**
     * @var array<int, array{name: string, username: string, email: string}>
     */
    public array $roleUsers = [];

    /**
     * @var array{code: string, name: string, desc: string, module_ids: array<int, string>, landing_menu_ids: array<int|string, string>}
     */
    public array $roleForm = [
        'code' => '',
        'name' => '',
        'desc' => '',
        'module_ids' => [],
        'landing_menu_ids' => [],
    ];

    public function mount(): void
    {
        $this->newRole();
    }

    public function newRole(): void
    {
        $this->reset(['selectedRoleId', 'roleForm']);
        $this->expandedModuleAppKeys = [];
        $this->resetValidation();
    }

    public function editRole(int $id): void
    {
        $role = $this->roles()->findRole($this->login(), $id);

        $this->selectedRoleId = $role->id;
        $this->roleForm = [
            'code' => $role->code,
            'name' => $role->name,
            'desc' => (string) $role->desc,
            'module_ids' => $role->mods->pluck('id')->map(fn (int $id): string => (string) $id)->values()->all(),
            'landing_menu_ids' => $role->landings->mapWithKeys(fn ($landing): array => [
                $landing->app_id => (string) $landing->app_menu_id,
            ])->all(),
        ];
        $this->expandedModuleAppKeys = [];
        $this->resetValidation();
    }

    public function toggleModuleApp(string $appKey): void
    {
        $this->expandedModuleAppKeys = in_array($appKey, $this->expandedModuleAppKeys, true)
            ? array_values(array_diff($this->expandedModuleAppKeys, [$appKey]))
            : [...$this->expandedModuleAppKeys, $appKey];
    }

    public function expandAllModuleApps(): void
    {
        $this->expandedModuleAppKeys = $this->roles()
            ->availableModules()
            ->groupBy(fn ($mod): string => 'app-'.($mod->app_id ?? 'none'))
            ->keys()
            ->values()
            ->all();
    }

    public function toggleAllModuleApps(): void
    {
        $moduleAppKeys = $this->roles()
            ->availableModules()
            ->groupBy(fn ($mod): string => 'app-'.($mod->app_id ?? 'none'))
            ->keys()
            ->values()
            ->all();

        $this->expandedModuleAppKeys = array_diff($moduleAppKeys, $this->expandedModuleAppKeys) === []
            ? []
            : $moduleAppKeys;
    }

    public function updatedRoleFormCode(string $value): void
    {
        $this->roleForm['code'] = Str::of($value)->lower()->slug('_')->toString();
    }

    public function updatedRoleFormModuleIds(): void
    {
        $this->roleForm['landing_menu_ids'] = $this->normalizedLandingMenuIds(
            $this->roleForm['module_ids'],
            $this->roleForm['landing_menu_ids'],
        );
    }

    public function save(): void
    {
        $this->roleForm['landing_menu_ids'] = $this->normalizedLandingMenuIds(
            $this->roleForm['module_ids'],
            $this->roleForm['landing_menu_ids'],
        );

        $clientId = $this->login()->client_id;

        $validated = $this->validate([
            'roleForm.code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('client_roles', 'code')
                    ->where(fn ($query) => $query->where('client_id', $clientId))
                    ->ignore($this->selectedRoleId),
            ],
            'roleForm.name' => ['required', 'string', 'max:255'],
            'roleForm.desc' => ['nullable', 'string', 'max:2000'],
            'roleForm.module_ids' => ['array'],
            'roleForm.module_ids.*' => ['integer', 'exists:app_mods,id'],
            'roleForm.landing_menu_ids' => ['array'],
            'roleForm.landing_menu_ids.*' => ['nullable', 'integer', 'exists:app_menus,id'],
        ], [], [
            'roleForm.code' => 'code',
            'roleForm.name' => 'name',
            'roleForm.desc' => 'description',
            'roleForm.module_ids' => 'module access',
            'roleForm.module_ids.*' => 'module access',
            'roleForm.landing_menu_ids' => 'default page',
            'roleForm.landing_menu_ids.*' => 'default page',
        ])['roleForm'];

        try {
            $role = $this->roles()->saveRole($this->login(), $this->selectedRoleId, $validated, $validated['module_ids'], $validated['landing_menu_ids'] ?? []);
        } catch (ValidationException $exception) {
            $this->dispatch('starter-toast', type: 'danger', message: $this->firstValidationMessage($exception));

            return;
        }

        $this->selectedRoleId = $role->id;
        $this->roleForm['module_ids'] = $role->mods->pluck('id')->map(fn (int $id): string => (string) $id)->values()->all();
        $this->roleForm['landing_menu_ids'] = $role->landings->mapWithKeys(fn ($landing): array => [
            $landing->app_id => (string) $landing->app_menu_id,
        ])->all();

        $this->dispatch('starter-toast', type: 'success', message: 'Role saved successfully.');
    }

    public function deleteRole(int $id): void
    {
        try {
            $this->roles()->deleteRole($this->login(), $id);
        } catch (ValidationException $exception) {
            $this->dispatch('starter-toast', type: 'danger', message: $this->firstValidationMessage($exception));

            return;
        }

        $this->newRole();

        $this->dispatch('starter-toast', type: 'success', message: 'Role deleted successfully.');
    }

    public function showRoleUsers(int $id): void
    {
        $role = $this->roles()->findRole($this->login(), $id)->load('clientLogins');

        $this->roleUsersRoleName = $role->name;
        $this->roleUsers = $role->clientLogins
            ->map(fn (ClientLogin $login): array => [
                'name' => $login->name,
                'username' => $login->username,
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

    public function render()
    {
        $allRoles = $this->roles()->roles($this->login());
        $search = Str::of($this->search)->trim()->lower()->toString();
        $roles = $search === ''
            ? $allRoles
            : $allRoles->filter(function ($role) use ($search): bool {
                return Str::of($role->name)->lower()->contains($search)
                    || Str::of($role->code)->lower()->contains($search)
                    || Str::of((string) $role->desc)->lower()->contains($search);
            });
        $modules = $this->roles()->availableModules()
            ->groupBy(fn ($mod): string => $mod->app?->name ?? 'No App');

        return view('starter.user-management.roles', [
            'roles' => $roles,
            'roleCount' => $allRoles->count(),
            'selectedRole' => $allRoles->firstWhere('id', $this->selectedRoleId),
            'modules' => $modules,
        ])->title('Role Management');
    }

    private function roles(): UserManagementRoleService
    {
        return app(UserManagementRoleService::class);
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof ClientLogin, 403);

        return $login->loadMissing('client');
    }

    private function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? 'Invalid data.';
    }

    /**
     * @param  array<int, int|string>  $moduleIds
     * @param  array<int|string, int|string|null>  $landingMenuIds
     * @return array<int, string>
     */
    private function normalizedLandingMenuIds(array $moduleIds, array $landingMenuIds): array
    {
        $selectedModuleIds = collect($moduleIds)
            ->map(fn (int|string $id): string => (string) $id)
            ->filter()
            ->values();

        if ($selectedModuleIds->isEmpty()) {
            return [];
        }

        $nextLandingMenuIds = [];

        $this->roles()
            ->availableModules()
            ->filter(fn ($module): bool => $selectedModuleIds->contains((string) $module->id))
            ->groupBy('app_id')
            ->each(function ($appModules, int|string $appId) use (&$nextLandingMenuIds, $landingMenuIds): void {
                $candidateMenuIds = $appModules
                    ->flatMap(fn ($module) => $module->menus)
                    ->pluck('id')
                    ->map(fn (int|string $id): string => (string) $id)
                    ->unique()
                    ->values();

                $currentMenuId = (string) ($landingMenuIds[$appId] ?? '');

                if ($candidateMenuIds->contains($currentMenuId)) {
                    $nextLandingMenuIds[$appId] = $currentMenuId;

                    return;
                }

                if ($candidateMenuIds->count() === 1) {
                    $nextLandingMenuIds[$appId] = $candidateMenuIds->first();
                }
            });

        return $nextLandingMenuIds;
    }
}
