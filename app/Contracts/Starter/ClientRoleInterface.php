<?php

namespace App\Contracts\Starter;

use App\Models\Starter\AppMenu;
use App\Models\Starter\ClientRole;
use Illuminate\Support\Collection;

interface ClientRoleInterface
{
    /**
     * @param  array<int, string>  $with
     * @param  array<int, string>  $withCount
     * @return Collection<int, ClientRole>
     */
    public function all(array $with = [], array $withCount = [], string $orderBy = 'name'): Collection;

    /**
     * @param  array<int, string>  $with
     * @param  array<int, string>  $withCount
     */
    public function find(int $id, array $with = [], array $withCount = []): ?ClientRole;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ClientRole;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ClientRole $role, array $data): ClientRole;

    /**
     * @param  array<int, int>  $moduleIds
     */
    public function syncMods(ClientRole $role, array $moduleIds): void;

    /**
     * @param  array<int, int>  $landings
     */
    public function syncLandings(ClientRole $role, array $landings): void;

    public function detachMods(ClientRole $role): void;

    public function detachLandings(ClientRole $role): void;

    /**
     * @return Collection<int, int>
     */
    public function modIds(ClientRole $role): Collection;

    public function landingMenuForApp(ClientRole $role, string $appSubdomain): ?AppMenu;

    public function hasClientLogins(ClientRole $role): bool;

    public function delete(ClientRole $role): void;
}
