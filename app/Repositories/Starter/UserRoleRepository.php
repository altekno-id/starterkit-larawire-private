<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\UserRoleInterface;
use App\Models\Starter\User;
use App\Models\Starter\UserRole;
use Illuminate\Support\Collection;

class UserRoleRepository implements UserRoleInterface
{
    public function forUser(User $user, array $with = [], array $withCount = [], string $orderBy = 'name'): Collection
    {
        return UserRole::query()
            ->with($with)
            ->withCount($withCount)
            ->whereBelongsTo($user)
            ->orderBy($orderBy)
            ->get();
    }

    public function findForUser(User $user, int $id, array $with = [], array $withCount = []): ?UserRole
    {
        return UserRole::query()
            ->with($with)
            ->withCount($withCount)
            ->whereBelongsTo($user)
            ->whereKey($id)
            ->first();
    }

    public function createForUser(User $user, array $data): UserRole
    {
        return $user->roles()->create($data);
    }

    public function update(UserRole $role, array $data): UserRole
    {
        $role->forceFill($data)->save();

        return $role->refresh();
    }

    public function syncMods(UserRole $role, array $moduleIds): void
    {
        $role->mods()->sync($moduleIds);
    }

    public function detachMods(UserRole $role): void
    {
        $role->mods()->detach();
    }

    public function modIds(UserRole $role): Collection
    {
        return $role->mods()->pluck('app_mods.id');
    }

    public function hasUserLogins(UserRole $role): bool
    {
        return $role->userLogins()->exists();
    }

    public function delete(UserRole $role): void
    {
        $role->delete();
    }
}
