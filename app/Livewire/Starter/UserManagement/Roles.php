<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\UserLogin;
use App\Services\Starter\UserManagement\RoleService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Roles extends Component
{
    public string $pageTitle = 'Roles';

    public ?int $selectedRoleId = null;

    public string $code = '';

    public string $name = '';

    public string $desc = '';

    /**
     * @var array<int, string>
     */
    public array $moduleIds = [];

    public function mount(): void
    {
        $this->newRole();
    }

    public function newRole(): void
    {
        $this->reset(['selectedRoleId', 'code', 'name', 'desc', 'moduleIds']);
        $this->resetValidation();
    }

    public function editRole(int $id): void
    {
        $role = $this->roles()->findRole($this->login(), $id);

        $this->selectedRoleId = $role->id;
        $this->code = $role->code;
        $this->name = $role->name;
        $this->desc = (string) $role->desc;
        $this->moduleIds = $role->mods->pluck('id')->map(fn (int $id): string => (string) $id)->values()->all();
        $this->resetValidation();
    }

    public function updatedCode(): void
    {
        $this->code = Str::of($this->code)->lower()->slug('_')->toString();
    }

    public function save(): void
    {
        $clientId = $this->login()->user_id;

        $validated = $this->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('user_roles', 'code')
                    ->where(fn ($query) => $query->where('user_id', $clientId))
                    ->ignore($this->selectedRoleId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'desc' => ['nullable', 'string', 'max:2000'],
            'moduleIds' => ['array'],
            'moduleIds.*' => ['integer', 'exists:app_mods,id'],
        ]);

        $role = $this->roles()->saveRole($this->login(), $this->selectedRoleId, $validated, $this->moduleIds);

        $this->selectedRoleId = $role->id;
        $this->moduleIds = $role->mods->pluck('id')->map(fn (int $id): string => (string) $id)->values()->all();

        session()->flash('status', 'Role berhasil disimpan.');
    }

    public function deleteRole(int $id): void
    {
        $this->roles()->deleteRole($this->login(), $id);
        $this->newRole();

        session()->flash('status', 'Role berhasil dihapus.');
    }

    public function render()
    {
        $roles = $this->roles()->roles($this->login());
        $modules = $this->roles()->availableModules()
            ->groupBy(fn ($mod): string => $mod->app?->name ?? 'Tanpa Aplikasi');

        return view('starter.user-management.roles', [
            'roles' => $roles,
            'modules' => $modules,
        ])->title($this->pageTitle);
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
}
