<?php

namespace App\Services\Starter\UserManagement;

use App\Contracts\Starter\RoleInterface;
use App\Models\Starter\AppMod;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Models\Starter\UserRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function __construct(
        private readonly RoleInterface $roles
    ) {}

    /**
     * @return Collection<int, UserRole>
     */
    public function roles(UserLogin $login): Collection
    {
        return $this->roles->forUser($this->client($login));
    }

    public function findRole(UserLogin $login, int $id): UserRole
    {
        $role = $this->roles->findForUser($this->client($login), $id);

        abort_unless($role instanceof UserRole, 404);

        return $role;
    }

    /**
     * @return Collection<int, AppMod>
     */
    public function availableModules(): Collection
    {
        return $this->roles->availableModules();
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
            $role = $roleId ? $this->roles->findForUser($client, $roleId) : null;

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
                ? $this->roles->update($role, $payload)
                : $this->roles->create($client, $payload);

            $this->roles->syncModules($role, $role->isAdmin() ? [] : $this->moduleIds($moduleIds));

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

        if ($role->userLogins()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Role masih dipakai oleh user login.',
            ]);
        }

        $this->roles->delete($role);
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
