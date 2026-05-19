<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\UserLogin;
use App\Services\Starter\UserManagement\UserService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Users extends Component
{
    public string $pageTitle = 'Users';

    public ?int $selectedUserId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $roleId = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function mount(): void
    {
        $this->newUser();
    }

    public function newUser(): void
    {
        $this->reset(['selectedUserId', 'name', 'username', 'email', 'password', 'passwordConfirmation']);
        $this->roleId = (string) ($this->users()->roles($this->login())->first()?->id ?? '');
        $this->resetValidation();
    }

    public function editUser(int $id): void
    {
        $login = $this->users()->findUser($this->login(), $id);

        $this->selectedUserId = $login->id;
        $this->name = $login->name;
        $this->username = $login->username;
        $this->email = $login->email;
        $this->roleId = (string) $login->user_role_id;
        $this->reset(['password', 'passwordConfirmation']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $clientId = $this->login()->user_id;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('user_logins', 'username')->ignore($this->selectedUserId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user_logins', 'email')->ignore($this->selectedUserId),
            ],
            'roleId' => [
                'required',
                'integer',
                Rule::exists('user_roles', 'id')->where(fn ($query) => $query->where('user_id', $clientId)),
            ],
            'password' => [$this->selectedUserId ? 'nullable' : 'required', 'string', 'min:5', 'same:passwordConfirmation'],
            'passwordConfirmation' => [$this->selectedUserId ? 'nullable' : 'required', 'string'],
        ]);

        $login = $this->users()->saveUser($this->login(), $this->selectedUserId, [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'user_role_id' => $validated['roleId'],
            'password' => $validated['password'] ?? null,
        ]);

        $this->selectedUserId = $login->id;
        $this->reset(['password', 'passwordConfirmation']);

        session()->flash('status', 'User login berhasil disimpan.');
    }

    public function deleteUser(int $id): void
    {
        $this->users()->deleteUser($this->login(), $id);
        $this->newUser();

        session()->flash('status', 'User login berhasil dihapus.');
    }

    public function render()
    {
        return view('starter.user-management.users', [
            'users' => $this->users()->users($this->login()),
            'roles' => $this->users()->roles($this->login()),
        ])->title($this->pageTitle);
    }

    private function users(): UserService
    {
        return app(UserService::class);
    }

    private function login(): UserLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof UserLogin, 403);

        return $login->loadMissing('user');
    }
}
