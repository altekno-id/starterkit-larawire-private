<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\UserLogin;
use App\Services\Starter\UserManagement\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Users extends Component
{
    public ?int $selectedUserId = null;

    /**
     * @var array{name: string, username: string, email: string, role_id: string, password: string, password_confirmation: string}
     */
    public array $userForm = [
        'name' => '',
        'username' => '',
        'email' => '',
        'role_id' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function mount(): void
    {
        $this->newUser();
    }

    public function newUser(): void
    {
        $this->reset(['selectedUserId', 'userForm']);
        $this->userForm['role_id'] = (string) ($this->users()->roles($this->login())->first()?->id ?? '');
        $this->resetValidation();
    }

    public function editUser(int $id): void
    {
        $login = $this->users()->findUser($this->login(), $id);

        $this->selectedUserId = $login->id;
        $this->userForm = [
            'name' => $login->name,
            'username' => $login->username,
            'email' => $login->email,
            'role_id' => (string) $login->user_role_id,
            'password' => '',
            'password_confirmation' => '',
        ];
        $this->resetValidation();
    }

    public function save(): void
    {
        $clientId = $this->login()->user_id;

        $validated = $this->validate([
            'userForm.name' => ['required', 'string', 'max:255'],
            'userForm.username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('user_logins', 'username')->ignore($this->selectedUserId),
            ],
            'userForm.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user_logins', 'email')->ignore($this->selectedUserId),
            ],
            'userForm.role_id' => [
                'required',
                'integer',
                Rule::exists('user_roles', 'id')->where(fn ($query) => $query->where('user_id', $clientId)),
            ],
            'userForm.password' => [$this->selectedUserId ? 'nullable' : 'required', 'string', 'min:5', 'same:userForm.password_confirmation'],
            'userForm.password_confirmation' => [$this->selectedUserId ? 'nullable' : 'required', 'string'],
        ], [], [
            'userForm.name' => 'name',
            'userForm.username' => 'username',
            'userForm.email' => 'email',
            'userForm.role_id' => 'role',
            'userForm.password' => 'password',
            'userForm.password_confirmation' => 'password confirmation',
        ])['userForm'];

        $login = $this->users()->saveUser($this->login(), $this->selectedUserId, [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'user_role_id' => $validated['role_id'],
            'password' => $validated['password'] ?? null,
        ]);

        $this->selectedUserId = $login->id;
        $this->userForm['password'] = '';
        $this->userForm['password_confirmation'] = '';

        $this->dispatch('starter-toast', type: 'success', message: 'User login saved successfully.');
    }

    public function deleteUser(int $id): void
    {
        try {
            $this->users()->deleteUser($this->login(), $id);
        } catch (ValidationException $exception) {
            $this->dispatch('starter-toast', type: 'danger', message: $this->firstValidationMessage($exception));

            return;
        }

        $this->newUser();

        $this->dispatch('starter-toast', type: 'success', message: 'User login deleted successfully.');
    }

    public function render()
    {
        return view('starter.user-management.users', [
            'users' => $this->users()->users($this->login()),
            'roles' => $this->users()->roles($this->login()),
        ])->title('User');
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

    private function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? 'Invalid data.';
    }
}
