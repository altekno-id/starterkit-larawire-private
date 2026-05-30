<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\UserRoleInterface;
use App\Models\Starter\AppMenu;
use App\Models\Starter\User;
use App\Models\Starter\UserRole;
use App\Models\Starter\UserRoleAppLanding;
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

    public function syncLandings(UserRole $role, array $landings): void
    {
        $appIds = collect($landings)->keys()->map(fn (int|string $id): int => (int) $id)->all();

        $staleLandings = UserRoleAppLanding::query()->where('user_role_id', $role->id);

        $appIds === []
            ? $staleLandings->delete()
            : $staleLandings->whereNotIn('app_id', $appIds)->delete();

        foreach ($landings as $appId => $menuId) {
            UserRoleAppLanding::query()->updateOrCreate([
                'user_role_id' => $role->id,
                'app_id' => (int) $appId,
            ], [
                'app_menu_id' => (int) $menuId,
            ]);
        }
    }

    public function detachMods(UserRole $role): void
    {
        $role->mods()->detach();
    }

    public function detachLandings(UserRole $role): void
    {
        $role->landings()->delete();
    }

    public function modIds(UserRole $role): Collection
    {
        return $role->mods()->pluck('app_mods.id');
    }

    public function landingMenuForApp(UserRole $role, string $appSubdomain): ?AppMenu
    {
        return UserRoleAppLanding::query()
            ->where('user_role_id', $role->id)
            ->whereHas('app', function ($query) use ($appSubdomain): void {
                $query->where('subdomain', $appSubdomain);
            })
            ->with('menu.route', 'menu.mod.app')
            ->first()
            ?->menu;
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
