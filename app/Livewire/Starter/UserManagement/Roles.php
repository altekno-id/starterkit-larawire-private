<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\UserLogin;
use App\Services\Starter\UserManagement\RoleService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Roles extends Component
{
    public ?int $selectedRoleId = null;

    /**
     * @var array{code: string, name: string, desc: string, module_ids: array<int, string>}
     */
    public array $roleForm = [
        'code' => '',
        'name' => '',
        'desc' => '',
        'module_ids' => [],
    ];

    public function mount(): void
    {
        $this->newRole();
    }

    public function newRole(): void
    {
        $this->reset(['selectedRoleId', 'roleForm']);
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
        ];
        $this->resetValidation();
    }

    public function updatedRoleFormCode(string $value): void
    {
        $this->roleForm['code'] = Str::of($value)->lower()->slug('_')->toString();
    }

    public function save(): void
    {
        $clientId = $this->login()->user_id;

        $validated = $this->validate([
            'roleForm.code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('user_roles', 'code')
                    ->where(fn ($query) => $query->where('user_id', $clientId))
                    ->ignore($this->selectedRoleId),
            ],
            'roleForm.name' => ['required', 'string', 'max:255'],
            'roleForm.desc' => ['nullable', 'string', 'max:2000'],
            'roleForm.module_ids' => ['array'],
            'roleForm.module_ids.*' => ['integer', 'exists:app_mods,id'],
        ], [], [
            'roleForm.code' => 'kode',
            'roleForm.name' => 'nama',
            'roleForm.desc' => 'deskripsi',
            'roleForm.module_ids' => 'module access',
            'roleForm.module_ids.*' => 'module access',
        ])['roleForm'];

        $role = $this->roles()->saveRole($this->login(), $this->selectedRoleId, $validated, $validated['module_ids']);

        $this->selectedRoleId = $role->id;
        $this->roleForm['module_ids'] = $role->mods->pluck('id')->map(fn (int $id): string => (string) $id)->values()->all();

        $this->dispatch('starter-toast', type: 'success', message: 'Role berhasil disimpan.');
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

        $this->dispatch('starter-toast', type: 'success', message: 'Role berhasil dihapus.');
    }

    public function render()
    {
        $roles = $this->roles()->roles($this->login());
        $modules = $this->roles()->availableModules()
            ->groupBy(fn ($mod): string => $mod->app?->name ?? 'Tanpa Aplikasi');

        return view('starter.user-management.roles', [
            'roles' => $roles,
            'modules' => $modules,
        ])->title('Roles');
    }

    private function roles(): RoleService
    {
        return app(RoleService::class);
    }

    private function login(): UserLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof UserLogin, 403);

        return $login->loadMissing('user');
    }

    private function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? 'Data tidak valid.';
    }
}
