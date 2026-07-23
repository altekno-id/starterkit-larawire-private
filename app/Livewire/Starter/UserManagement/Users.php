<?php

namespace App\Livewire\Starter\UserManagement;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\UserManagementUserService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
class Users extends Component
{
    use WithPagination;

    public bool $embedded = false;

    public string $search = '';

    public string $statusFilter = '';

    public ?string $temporaryPassword = null;

    public ?string $temporaryPasswordUsername = null;

    public ?int $passwordResetUserId = null;

    public string $passwordResetUserName = '';

    public bool $passwordResetModalOpen = false;

    public function mount(bool $embedded = false): void
    {
        $this->embedded = $embedded;
    }

    public function updatedSearch(): void
    {
        $this->resetPage('usersPage');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage('usersPage');
    }

    public function preparePasswordReset(int $id): void
    {
        $login = $this->users()->findUser($this->login(), $id);
        $this->passwordResetUserId = $login->id;
        $this->passwordResetUserName = $login->name;
        $this->passwordResetModalOpen = true;
    }

    public function cancelPasswordReset(): void
    {
        $this->passwordResetUserId = null;
        $this->passwordResetUserName = '';
        $this->passwordResetModalOpen = false;
    }

    public function resetSelectedPassword(): void
    {
        if ($this->passwordResetUserId === null) {
            return;
        }

        $login = $this->users()->findUser($this->login(), $this->passwordResetUserId);
        $this->showTemporaryPassword($login, $this->users()->resetPassword($this->login(), $login->id));
        $this->passwordResetUserId = null;
        $this->passwordResetUserName = '';
        $this->passwordResetModalOpen = false;
        $this->dispatch('starter-toast', type: 'success', message: 'Password sementara baru berhasil dibuat.');
    }

    public function dismissTemporaryPassword(): void
    {
        $this->temporaryPassword = null;
        $this->temporaryPasswordUsername = null;
    }

    public function render()
    {
        $login = $this->login();
        $search = Str::lower(trim($this->search));
        $visibleUsers = $this->users()
            ->users($login)
            ->when(
                ! $login->role?->isSuperuser(),
                fn ($users) => $users->reject(fn (ClientLogin $user): bool => $user->role?->isSuperuser() ?? false),
            );
        $filteredUsers = $visibleUsers
            ->when($search !== '', fn ($users) => $users->filter(fn (ClientLogin $user): bool => Str::contains(Str::lower($user->name.' '.$user->username.' '.$user->email.' '.$user->role?->name), $search)
            ))
            ->when($this->statusFilter !== '', fn ($users) => $users->where('status', $this->statusFilter));
        $superuserLogins = $filteredUsers
            ->filter(fn (ClientLogin $user): bool => $user->role?->isSuperuser() ?? false)
            ->sortBy(fn (ClientLogin $user): string => $user->name, SORT_NATURAL | SORT_FLAG_CASE);
        $regularLogins = $filteredUsers
            ->reject(fn (ClientLogin $user): bool => $user->role?->isSuperuser() ?? false)
            ->sortBy(fn (ClientLogin $user): string => $user->name, SORT_NATURAL | SORT_FLAG_CASE);
        $filteredUsers = $superuserLogins
            ->concat($regularLogins)
            ->values();
        $currentPage = $this->getPage(pageName: 'usersPage');
        $perPage = 10;
        $users = new LengthAwarePaginator(
            $filteredUsers->forPage($currentPage, $perPage)->values(),
            $filteredUsers->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'usersPage',
            ],
        );

        return view('starter.user-management.users', [
            'users' => $users,
            'appCount' => $this->users()->appCount(),
        ])->title('Manajemen User');
    }

    private function showTemporaryPassword(ClientLogin $login, string $password): void
    {
        $this->temporaryPasswordUsername = $login->username;
        $this->temporaryPassword = $password;
    }

    private function users(): UserManagementUserService
    {
        return app(UserManagementUserService::class);
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
