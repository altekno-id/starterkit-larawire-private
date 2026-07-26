<?php

namespace App\Services\Starter;

use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\ClientLoginInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Models\Starter\AppMod;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserManagementUserService
{
    public function __construct(
        private readonly ClientLoginInterface $clientLogins,
        private readonly ClientRoleInterface $clientRoles,
        private readonly AppModInterface $appMods,
    ) {}

    /** @return Collection<int, ClientLogin> */
    public function users(ClientLogin $login): Collection
    {
        return $this->clientLogins
            ->all(['role.mods.app'])
            ->when(
                ! $login->role?->isSuperuser(),
                fn (Collection $users): Collection => $users
                    ->reject(fn (ClientLogin $user): bool => $user->role?->isSuperuser() ?? false)
                    ->values(),
            );
    }

    /** @return Collection<int, ClientRole> */
    public function roles(ClientLogin $login): Collection
    {
        return $this->clientRoles
            ->all(['mods.app'])
            ->when(
                ! $login->role?->isSuperuser(),
                fn (Collection $roles): Collection => $roles
                    ->reject(fn (ClientRole $role): bool => $role->isSuperuser())
                    ->values(),
            );
    }

    public function findUser(ClientLogin $currentLogin, int $id): ClientLogin
    {
        $login = $this->clientLogins->find($id, ['role.mods.app']);

        abort_unless($login instanceof ClientLogin, 404);
        abort_if($login->role?->isSuperuser() && ! $currentLogin->role?->isSuperuser(), 404);

        return $login;
    }

    public function findPasswordResetTarget(ClientLogin $currentLogin, int $id): ClientLogin
    {
        $login = $this->findUser($currentLogin, $id);

        abort_if(
            $login->role?->isSuperuser(),
            403,
            'Password Superuser hanya dapat diubah melalui Edit Profil Saya.',
        );

        return $login;
    }

    /**
     * @param  array{name: string, username: string, email: string, client_role_id: int|string, status: string, password?: string}  $data
     */
    public function saveUser(ClientLogin $currentLogin, ?int $userLoginId, array $data): ClientLogin
    {
        $role = $this->clientRoles->find((int) $data['client_role_id']);

        if (! $role instanceof ClientRole) {
            throw ValidationException::withMessages(['userForm.role_id' => 'Role tidak valid.']);
        }

        $login = $userLoginId ? $this->clientLogins->find($userLoginId) : null;

        if ($userLoginId && ! $login instanceof ClientLogin) {
            abort(404);
        }

        abort_if($login?->role?->isSuperuser() && ! $currentLogin->role?->isSuperuser(), 404);

        if ($role->isSuperuser() && ! $login?->role?->isSuperuser()) {
            throw ValidationException::withMessages(['userForm.role_id' => 'Role sistem Superuser tidak dapat diberikan ke akun lain.']);
        }

        if ($login?->role?->isSuperuser() && (
            (int) $data['client_role_id'] !== $login->client_role_id || $data['status'] !== 'active'
        )) {
            throw ValidationException::withMessages(['userForm.status' => 'Akun Superuser harus tetap aktif dengan role sistem.']);
        }

        $payload = [
            'name' => trim($data['name']),
            'username' => str($data['username'])->lower()->trim()->toString(),
            'email' => str($data['email'])->lower()->trim()->toString(),
            'client_role_id' => $role->id,
            'status' => $data['status'],
        ];

        if (! $login instanceof ClientLogin) {
            $payload += [
                'password' => $data['password'] ?? Str::password(16),
                'must_change_password' => true,
            ];
        }

        $actionKey = $login instanceof ClientLogin ? 'user.update' : 'user.create';
        $actionLabel = ($login instanceof ClientLogin ? 'Mengubah user ' : 'Membuat user ').$payload['name'];

        return app(AuditLogService::class)->withinAction(
            $actionKey,
            $actionLabel,
            fn (): ClientLogin => $login instanceof ClientLogin
                ? $this->clientLogins->update($login, $payload)
                : $this->clientLogins->create($payload),
        );
    }

    public function resetPassword(ClientLogin $currentLogin, int $userLoginId): string
    {
        $login = $this->findPasswordResetTarget($currentLogin, $userLoginId);
        $temporaryPassword = Str::password(16);

        app(AuditLogService::class)->withinAction('user.reset_password', 'Reset password user '.$login->name, function () use ($login, $temporaryPassword): void {
            $this->clientLogins->update($login, [
                'password' => $temporaryPassword,
                'must_change_password' => true,
                'password_changed_at' => now(),
                'failed_login_count' => 0,
                'locked_until' => null,
                'remember_token' => Str::random(60),
            ]);
        });

        app(AuditLogService::class)->recordSecurityEvent(
            'auth.password_reset_by_admin',
            'Password direset oleh administrator',
            target: $login,
            actor: $currentLogin,
            metadata: ['must_change_password' => true],
        );

        return $temporaryPassword;
    }

    public function appCount(): int
    {
        return $this->appMods->all(['app'])->pluck('app_id')->filter()->unique()->count();
    }

    /** @return Collection<int, AppMod> */
    public function availableModules(): Collection
    {
        return $this->appMods->all(['app'], ['app_id', 'name']);
    }
}
