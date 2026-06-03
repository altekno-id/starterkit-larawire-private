<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\UserManagementUserService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Users extends Component
{
    public ?int $selectedUserId = null;

    public ?int $deleteUserId = null;

    public ?int $detailUserId = null;

    public string $deleteUserName = '';

    public string $search = '';

    public string $roleFilter = '';

    public string $emailStatusFilter = '';

    public string $orderBy = 'name_asc';

    public bool $userModalOpen = false;

    public bool $deleteUserModalOpen = false;

    public bool $detailUserModalOpen = false;

    /**
     * @var array{name: string, email: string, role_id: string, password: string, password_confirmation: string}
     */
    public array $userForm = [
        'name' => '',
        'email' => '',
        'role_id' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function mount(): void
    {
        $this->newUser(false);
    }

    public function newUser(bool $openModal = true): void
    {
        $this->reset(['selectedUserId', 'userForm']);
        $this->userForm['role_id'] = (string) ($this->users()->roles($this->login())->first()?->id ?? '');
        $this->resetValidation();
        $this->userModalOpen = $openModal;
    }

    public function editUser(int $id): void
    {
        $login = $this->users()->findUser($this->login(), $id);

        $this->selectedUserId = $login->id;
        $this->userForm = [
            'name' => $login->name,
            'email' => $login->email,
            'role_id' => (string) $login->client_role_id,
            'password' => '',
            'password_confirmation' => '',
        ];
        $this->resetValidation();
        $this->userModalOpen = true;
        $this->detailUserModalOpen = false;
        $this->detailUserId = null;
    }

    public function closeUserModal(): void
    {
        $this->userModalOpen = false;
        $this->resetValidation();
    }

    public function showUserDetail(int $id): void
    {
        $login = $this->users()->findUser($this->login(), $id);

        $this->detailUserId = $login->id;
        $this->detailUserModalOpen = true;
    }

    public function closeUserDetail(): void
    {
        $this->detailUserModalOpen = false;
        $this->detailUserId = null;
    }

    public function resetUserFilters(): void
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->emailStatusFilter = '';
        $this->orderBy = 'name_asc';
    }

    public function save(): void
    {
        $clientId = $this->login()->client_id;

        $validated = $this->validate([
            'userForm.name' => ['required', 'string', 'max:255'],
            'userForm.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('starter_client_logins', 'email')->ignore($this->selectedUserId),
            ],
            'userForm.role_id' => [
                'required',
                'integer',
                Rule::exists('starter_client_roles', 'id')->where(fn ($query) => $query->where('client_id', $clientId)),
            ],
            'userForm.password' => [$this->selectedUserId ? 'nullable' : 'required', 'string', 'min:5', 'same:userForm.password_confirmation'],
            'userForm.password_confirmation' => [$this->selectedUserId ? 'nullable' : 'required', 'string'],
        ], [], [
            'userForm.name' => 'name',
            'userForm.email' => 'email',
            'userForm.role_id' => 'role',
            'userForm.password' => 'password',
            'userForm.password_confirmation' => 'password confirmation',
        ])['userForm'];

        $login = $this->users()->saveUser($this->login(), $this->selectedUserId, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'client_role_id' => $validated['role_id'],
            'password' => $validated['password'] ?? null,
        ]);

        $this->selectedUserId = $login->id;
        $this->userForm['password'] = '';
        $this->userForm['password_confirmation'] = '';
        $this->userModalOpen = false;

        $this->dispatch('starter-toast', type: 'success', message: 'User login saved successfully.');
    }

    public function confirmDeleteUser(int $id): void
    {
        $login = $this->users()->findUser($this->login(), $id);

        $this->deleteUserId = $login->id;
        $this->deleteUserName = $login->name;
        $this->userModalOpen = false;
        $this->deleteUserModalOpen = true;
    }

    public function closeDeleteUserModal(): void
    {
        $this->deleteUserModalOpen = false;
        $this->deleteUserId = null;
        $this->deleteUserName = '';
    }

    public function deleteSelectedUser(): void
    {
        if (! $this->deleteUserId) {
            $this->closeDeleteUserModal();

            return;
        }

        $this->deleteUser($this->deleteUserId);
    }

    public function deleteUser(int $id): void
    {
        try {
            $this->users()->deleteUser($this->login(), $id);
        } catch (ValidationException $exception) {
            $this->dispatch('starter-toast', type: 'danger', message: $this->firstValidationMessage($exception));
            $this->closeDeleteUserModal();

            return;
        }

        $this->newUser(false);
        $this->closeDeleteUserModal();

        $this->dispatch('starter-toast', type: 'success', message: 'User login deleted successfully.');
    }

    public function render()
    {
        $allUsers = $this->users()->users($this->login());
        $roles = $this->users()->roles($this->login());
        $search = Str::of($this->search)->trim()->lower()->toString();
        $users = $search === ''
            ? $allUsers
            : $allUsers->filter(function (ClientLogin $user) use ($search): bool {
                return Str::of($user->name)->lower()->contains($search)
                    || Str::of($user->email)->lower()->contains($search)
                    || Str::of((string) $user->role?->name)->lower()->contains($search)
                    || Str::of((string) $user->role?->code)->lower()->contains($search);
            });
        $users = $users
            ->when($this->roleFilter !== '', fn ($users) => $users->filter(
                fn (ClientLogin $user): bool => (string) $user->client_role_id === $this->roleFilter
            ))
            ->when($this->emailStatusFilter === 'verified', fn ($users) => $users->filter(
                fn (ClientLogin $user): bool => $user->email_verified_at !== null
            ))
            ->when($this->emailStatusFilter === 'unverified', fn ($users) => $users->filter(
                fn (ClientLogin $user): bool => $user->email_verified_at === null
            ));
        $users = match ($this->orderBy) {
            'name_desc' => $users->sortByDesc(fn (ClientLogin $user): string => Str::lower($user->name)),
            'role_asc' => $users->sortBy(fn (ClientLogin $user): string => Str::lower((string) $user->role?->name)),
            'created_desc' => $users->sortByDesc(fn (ClientLogin $user): int => $user->created_at?->timestamp ?? 0),
            'created_asc' => $users->sortBy(fn (ClientLogin $user): int => $user->created_at?->timestamp ?? 0),
            'last_login_desc' => $users->sortByDesc(fn (ClientLogin $user): int => $user->last_login_at?->timestamp ?? 0),
            default => $users->sortBy(fn (ClientLogin $user): string => Str::lower($user->name)),
        };
        $users = $users->values();
        $selectedUser = $this->selectedUserId
            ? $allUsers->firstWhere('id', $this->selectedUserId)
            : null;
        $selectedDetailUser = $this->detailUserId
            ? $allUsers->firstWhere('id', $this->detailUserId)
            : null;

        return view('starter.user-management.users', [
            'users' => $users,
            'userCount' => $allUsers->count(),
            'verifiedUserCount' => $allUsers->filter(fn (ClientLogin $user): bool => $user->email_verified_at !== null)->count(),
            'activeUserCount' => $allUsers->filter(fn (ClientLogin $user): bool => $user->last_login_at?->greaterThanOrEqualTo(now()->subDays(30)) ?? false)->count(),
            'googleUserCount' => $allUsers->filter(fn (ClientLogin $user): bool => filled($user->google_id))->count(),
            'roles' => $roles,
            'roleCount' => $roles->count(),
            'appCount' => $this->users()->appCount(),
            'client' => $this->login()->client,
            'selectedUser' => $selectedUser,
            'selectedDetailUser' => $selectedDetailUser,
        ])->title('User Management');
    }

    private function users(): UserManagementUserService
    {
        return app(UserManagementUserService::class);
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
}
