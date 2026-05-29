<?php

namespace App\Services\Starter;

use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\UserRoleInterface;
use App\Models\Starter\AppMod;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Models\Starter\UserRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserManagementRoleService
{
    public function __construct(
        private readonly UserRoleInterface $userRoles,
        private readonly AppModInterface $appMods
    ) {}

    /**
     * @return Collection<int, UserRole>
     */
    public function roles(UserLogin $login): Collection
    {
        return $this->userRoles->forUser($this->client($login), ['mods.app'], ['userLogins']);
    }

    public function findRole(UserLogin $login, int $id): UserRole
    {
        $role = $this->userRoles->findForUser($this->client($login), $id, ['mods.app'], ['userLogins']);

        abort_unless($role instanceof UserRole, 404);

        return $role;
    }

    /**
     * @return Collection<int, AppMod>
     */
    public function availableModules(): Collection
    {
        return $this->appMods->all(['app'], ['app_id', 'name']);
    }

    /**
     * @param  array{name: string, code: string, desc?: ?string}  $data
     * @param  array<int, int|string>  $moduleIds
     */
    public function saveRole(UserLogin $login, ?int $roleId, array $data, array $moduleIds): UserRole
    {
        $client = $this->client($login);
        $code = $this->normalizeCode($data['code']);

        return DB::transaction(function () use ($client, $roleId, $data, $moduleIds, $code): UserRole {
            $role = $roleId ? $this->userRoles->findForUser($client, $roleId) : null;

            if ($roleId && ! $role instanceof UserRole) {
                abort(404);
            }

            if ($role?->isAdmin() && $code !== 'admin') {
                throw ValidationException::withMessages([
                    'code' => 'The admin role code cannot be changed.',
                ]);
            }

            $payload = [
                'code' => $code,
                'name' => trim($data['name']),
                'desc' => filled($data['desc'] ?? null) ? trim((string) $data['desc']) : null,
            ];

            $role = $role instanceof UserRole
                ? $this->userRoles->update($role, $payload)
                : $this->userRoles->createForUser($client, $payload);

            $this->userRoles->syncMods($role, $role->isAdmin() ? [] : $this->moduleIds($moduleIds));

            return $role->load('mods.app')->loadCount('userLogins');
        });
    }

    public function deleteRole(UserLogin $login, int $roleId): void
    {
        $role = $this->findRole($login, $roleId);

        if ($role->isAdmin()) {
            throw ValidationException::withMessages([
                'role' => 'The default admin role cannot be deleted.',
            ]);
        }

        if ($this->userRoles->hasUserLogins($role)) {
            throw ValidationException::withMessages([
                'role' => 'Role is still assigned to one or more users.',
            ]);
        }

        DB::transaction(function () use ($role): void {
            $this->userRoles->detachMods($role);
            $this->userRoles->delete($role);
        });
    }

    private function client(UserLogin $login): User
    {
        $client = $login->user;

        abort_unless($client instanceof User, 403);

        return $client;
    }

    private function normalizeCode(string $code): string
    {
        return Str::of($code)->lower()->slug('_')->toString();
    }

    /**
     * @param  array<int, int|string>  $moduleIds
     * @return array<int, int>
     */
    private function moduleIds(array $moduleIds): array
    {
        return collect($moduleIds)
            ->map(fn (int|string $id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
