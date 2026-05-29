<?php

namespace App\Services\Starter;

use App\Contracts\Starter\UserLoginInterface;
use App\Contracts\Starter\UserRoleInterface;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Models\Starter\UserRole;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class UserManagementUserService
{
    public function __construct(
        private readonly UserLoginInterface $userLogins,
        private readonly UserRoleInterface $userRoles
    ) {}

    /**
     * @return Collection<int, UserLogin>
     */
    public function users(UserLogin $login): Collection
    {
        return $this->userLogins->forUser($this->client($login), ['role']);
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function roles(UserLogin $login): Collection
    {
        return $this->userRoles->forUser($this->client($login));
    }

    public function findUser(UserLogin $currentLogin, int $id): UserLogin
    {
        $login = $this->userLogins->findForUser($this->client($currentLogin), $id, ['role']);

        abort_unless($login instanceof UserLogin, 404);

        return $login;
    }

    /**
     * @param  array{name: string, username: string, email: string, user_role_id: int|string, password?: ?string}  $data
     */
    public function saveUser(UserLogin $currentLogin, ?int $userLoginId, array $data): UserLogin
    {
        $client = $this->client($currentLogin);
        $role = $this->userRoles->findForUser($client, (int) $data['user_role_id']);

        if (! $role instanceof UserRole) {
            throw ValidationException::withMessages([
                'roleId' => 'Invalid role.',
            ]);
        }

        $login = $userLoginId ? $this->userLogins->findForUser($client, $userLoginId) : null;

        if ($userLoginId && ! $login instanceof UserLogin) {
            abort(404);
        }

        if ($login?->is($currentLogin) && (int) $data['user_role_id'] !== $currentLogin->user_role_id) {
            throw ValidationException::withMessages([
                'roleId' => 'The current login role cannot be changed from this page.',
            ]);
        }

        $payload = [
            'name' => trim($data['name']),
            'username' => str($data['username'])->lower()->trim()->toString(),
            'email' => str($data['email'])->lower()->trim()->toString(),
            'user_role_id' => $role->id,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        }

        return $login instanceof UserLogin
            ? $this->userLogins->update($login, $payload)
            : $this->userLogins->createForUser($client, $payload);
    }

    public function deleteUser(UserLogin $currentLogin, int $userLoginId): void
    {
        $login = $this->findUser($currentLogin, $userLoginId);

        if ($login->is($currentLogin)) {
            throw ValidationException::withMessages([
                'user' => 'The current login account cannot be deleted.',
            ]);
        }

        $this->userLogins->delete($login);
    }

    private function client(UserLogin $login): User
    {
        $client = $login->user;

        abort_unless($client instanceof User, 403);

        return $client;
    }
}
