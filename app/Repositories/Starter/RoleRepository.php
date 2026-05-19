<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\RoleInterface;
use App\Models\Starter\AppMod;
use App\Models\Starter\User;
use App\Models\Starter\UserRole;
use Illuminate\Support\Collection;

class RoleRepository implements RoleInterface
{
    public function forUser(User $user): Collection
    {
        return UserRole::query()
            ->with(['mods.app'])
            ->withCount('userLogins')
            ->whereBelongsTo($user)
            ->orderBy('name')
            ->get();
    }

    public function findForUser(User $user, int $id): ?UserRole
    {
        return UserRole::query()
            ->with(['mods.app'])
            ->withCount('userLogins')
            ->whereBelongsTo($user)
            ->whereKey($id)
            ->first();
    }

    public function create(User $user, array $data): UserRole
    {
        return $user->roles()->create($data);
    }

    public function update(UserRole $role, array $data): UserRole
    {
        $role->forceFill($data)->save();

        return $role->refresh();
    }

    public function syncModules(UserRole $role, array $moduleIds): void
    {
        $role->mods()->sync($moduleIds);
    }

    public function delete(UserRole $role): void
    {
        $role->mods()->detach();
        $role->delete();
    }

    public function availableModules(): Collection
    {
        return AppMod::query()
            ->with('app')
            ->orderBy('app_id')
            ->orderBy('name')
            ->get();
    }
}
