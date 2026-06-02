<?php

namespace App\Services\Starter;

use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\ClientLoginInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class UserManagementUserService
{
    public function __construct(
        private readonly ClientLoginInterface $clientLogins,
        private readonly ClientRoleInterface $clientRoles,
        private readonly AppModInterface $appMods
    ) {}

    /**
     * @return Collection<int, ClientLogin>
     */
    public function users(ClientLogin $login): Collection
    {
        return $this->clientLogins->forClient($this->client($login), [
            'role.mods.app',
            'role.landings.menu.mod.app',
            'client',
        ]);
    }

    /**
     * @return Collection<int, ClientRole>
     */
    public function roles(ClientLogin $login): Collection
    {
        return $this->clientRoles->forClient($this->client($login), ['mods.app']);
    }

    public function findUser(ClientLogin $currentLogin, int $id): ClientLogin
    {
        $login = $this->clientLogins->findForClient($this->client($currentLogin), $id, [
            'role.mods.app',
            'role.landings.menu.mod.app',
            'client',
        ]);

        abort_unless($login instanceof ClientLogin, 404);

        return $login;
    }

    /**
     * @param  array{name: string, username: string, email: string, client_role_id: int|string, password?: ?string}  $data
     */
    public function saveUser(ClientLogin $currentLogin, ?int $userLoginId, array $data): ClientLogin
    {
        $client = $this->client($currentLogin);
        $role = $this->clientRoles->findForClient($client, (int) $data['client_role_id']);

        if (! $role instanceof ClientRole) {
            throw ValidationException::withMessages([
                'roleId' => 'Invalid role.',
            ]);
        }

        $login = $userLoginId ? $this->clientLogins->findForClient($client, $userLoginId) : null;

        if ($userLoginId && ! $login instanceof ClientLogin) {
            abort(404);
        }

        if ($login?->is($currentLogin) && (int) $data['client_role_id'] !== $currentLogin->client_role_id) {
            throw ValidationException::withMessages([
                'roleId' => 'The current login role cannot be changed from this page.',
            ]);
        }

        $payload = [
            'name' => trim($data['name']),
            'username' => str($data['username'])->lower()->trim()->toString(),
            'email' => str($data['email'])->lower()->trim()->toString(),
            'client_role_id' => $role->id,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        }

        return $login instanceof ClientLogin
            ? $this->clientLogins->update($login, $payload)
            : $this->clientLogins->createForClient($client, $payload);
    }

    public function deleteUser(ClientLogin $currentLogin, int $userLoginId): void
    {
        $login = $this->findUser($currentLogin, $userLoginId);

        if ($login->is($currentLogin)) {
            throw ValidationException::withMessages([
                'user' => 'The current login account cannot be deleted.',
            ]);
        }

        $this->clientLogins->delete($login);
    }

    public function appCount(): int
    {
        return $this->appMods
            ->all(['app'])
            ->pluck('app_id')
            ->filter()
            ->unique()
            ->count();
    }

    private function client(ClientLogin $login): Client
    {
        $client = $login->client;

        abort_unless($client instanceof Client, 403);

        return $client;
    }
}
