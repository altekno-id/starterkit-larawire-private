<?php

namespace Altekno\StarterKit\Livewire\Starter\UserManagement;

use Altekno\StarterKit\Models\Starter\ClientLogin;
use Altekno\StarterKit\Services\Starter\AuthenticatedLoginService;
use Altekno\StarterKit\Services\Starter\UserManagementUserService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
class Users extends Component
{
    use WithPagination;

    private UserManagementUserService $userService;

    private AuthenticatedLoginService $authenticatedLogins;

    public bool $embedded = false;

    public string $search = '';

    public string $statusFilter = '';

    public ?string $temporaryPassword = null;

    public function boot(
        UserManagementUserService $userService,
        AuthenticatedLoginService $authenticatedLogins,
    ): void {
        $this->userService = $userService;
        $this->authenticatedLogins = $authenticatedLogins;
    }

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
        $login = $this->users()->findPasswordResetTarget($this->login(), $id);
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

        $login = $this->users()->findPasswordResetTarget($this->login(), $this->passwordResetUserId);
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
        $users = $this->users()->paginateUsers(
            $login,
            $this->search,
            $this->statusFilter,
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
        return $this->userService;
    }

    private function login(): ClientLogin
    {
        return $this->authenticatedLogins->settingsManager();
    }
}
