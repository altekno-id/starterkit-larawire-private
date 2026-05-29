<?php

namespace App\Contracts\Starter;

use App\Models\Starter\User;
use App\Models\Starter\UserRole;
use Illuminate\Support\Collection;

interface UserRoleInterface
{
    /**
     * @param  array<int, string>  $with
     * @param  array<int, string>  $withCount
     * @return Collection<int, UserRole>
     */
    public function forUser(User $user, array $with = [], array $withCount = [], string $orderBy = 'name'): Collection;

    /**
     * @param  array<int, string>  $with
     * @param  array<int, string>  $withCount
     */
    public function findForUser(User $user, int $id, array $with = [], array $withCount = []): ?UserRole;

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForUser(User $user, array $data): UserRole;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(UserRole $role, array $data): UserRole;

    /**
     * @param  array<int, int>  $moduleIds
     */
    public function syncMods(UserRole $role, array $moduleIds): void;

    public function detachMods(UserRole $role): void;

    /**
     * @return Collection<int, int>
     */
    public function modIds(UserRole $role): Collection;

    public function hasUserLogins(UserRole $role): bool;

    public function delete(UserRole $role): void;
}
