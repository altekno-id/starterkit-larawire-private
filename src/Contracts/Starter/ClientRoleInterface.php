<?php

namespace Altekno\StarterKit\Contracts\Starter;

use Altekno\StarterKit\Models\Starter\AppMenu;
use Altekno\StarterKit\Models\Starter\ClientLogin;
use Altekno\StarterKit\Models\Starter\ClientRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ClientRoleInterface
{
    /**
     * @return Collection<int, ClientRole>
     */
    public function allAssignableForViewer(ClientLogin $viewer): Collection;

    /**
     * @return LengthAwarePaginator<int, ClientRole>
     */
    public function paginateForViewer(
        ClientLogin $viewer,
        string $search,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator;

    public function findBasicById(int $id): ?ClientRole;

    public function findForManagement(int $id): ?ClientRole;

    /**
     * @return Collection<int, ClientLogin>
     */
    public function clientLogins(ClientRole $role): Collection;

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRole(array $data): ClientRole;

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRole(ClientRole $role, array $data): ClientRole;

    public function countForViewer(ClientLogin $viewer): int;

    /**
     * @return array{
     *     module_ids: list<int>,
     *     landing_menu_ids: array<int, int>,
     *     can_manage_settings: bool,
     *     can_view_logs: bool
     * }
     */
    public function accessSnapshot(ClientRole $role): array;

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

    public function deleteRole(ClientRole $role): void;
}
