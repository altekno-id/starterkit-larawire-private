<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\ClientRoleInterface;
use App\Models\Starter\AppMenu;
use App\Models\Starter\Client;
use App\Models\Starter\ClientRole;
use App\Models\Starter\ClientRoleAppLanding;
use Illuminate\Support\Collection;

class ClientRoleRepository implements ClientRoleInterface
{
    public function forClient(Client $client, array $with = [], array $withCount = [], string $orderBy = 'name'): Collection
    {
        return ClientRole::query()
            ->with($with)
            ->withCount($withCount)
            ->whereBelongsTo($client)
            ->orderBy($orderBy)
            ->get();
    }

    public function findForClient(Client $client, int $id, array $with = [], array $withCount = []): ?ClientRole
    {
        return ClientRole::query()
            ->with($with)
            ->withCount($withCount)
            ->whereBelongsTo($client)
            ->whereKey($id)
            ->first();
    }

    public function createForClient(Client $client, array $data): ClientRole
    {
        return $client->roles()->create($data);
    }

    public function update(ClientRole $role, array $data): ClientRole
    {
        $role->forceFill($data)->save();

        return $role->refresh();
    }

    public function syncMods(ClientRole $role, array $moduleIds): void
    {
        $role->mods()->sync($moduleIds);
    }

    public function syncLandings(ClientRole $role, array $landings): void
    {
        $appIds = collect($landings)->keys()->map(fn (int|string $id): int => (int) $id)->all();

        $staleLandings = ClientRoleAppLanding::query()->where('client_role_id', $role->id);

        $appIds === []
            ? $staleLandings->delete()
            : $staleLandings->whereNotIn('app_id', $appIds)->delete();

        foreach ($landings as $appId => $menuId) {
            ClientRoleAppLanding::query()->updateOrCreate([
                'client_role_id' => $role->id,
                'app_id' => (int) $appId,
            ], [
                'app_menu_id' => (int) $menuId,
            ]);
        }
    }

    public function detachMods(ClientRole $role): void
    {
        $role->mods()->detach();
    }

    public function detachLandings(ClientRole $role): void
    {
        $role->landings()->delete();
    }

    public function modIds(ClientRole $role): Collection
    {
        return $role->mods()->pluck('starter_app_mods.id');
    }

    public function landingMenuForApp(ClientRole $role, string $appSubdomain): ?AppMenu
    {
        return ClientRoleAppLanding::query()
            ->where('client_role_id', $role->id)
            ->whereHas('app', function ($query) use ($appSubdomain): void {
                $query->where('subdomain', $appSubdomain);
            })
            ->with('menu.route', 'menu.mod.app')
            ->first()
            ?->menu;
    }

    public function hasClientLogins(ClientRole $role): bool
    {
        return $role->clientLogins()->exists();
    }

    public function delete(ClientRole $role): void
    {
        $role->delete();
    }
}
