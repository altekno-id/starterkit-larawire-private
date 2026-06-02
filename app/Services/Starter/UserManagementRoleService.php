<?php

namespace App\Services\Starter;

use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Models\Starter\AppMod;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserManagementRoleService
{
    public function __construct(
        private readonly ClientRoleInterface $clientRoles,
        private readonly AppModInterface $appMods
    ) {}

    /**
     * @return Collection<int, ClientRole>
     */
    public function roles(ClientLogin $login): Collection
    {
        return $this->clientRoles->forClient($this->client($login), ['mods.app', 'landings.menu.route', 'landings.menu.mod.app'], ['clientLogins']);
    }

    public function findRole(ClientLogin $login, int $id): ClientRole
    {
        $role = $this->clientRoles->findForClient($this->client($login), $id, ['mods.app', 'landings.menu.route', 'landings.menu.mod.app'], ['clientLogins']);

        abort_unless($role instanceof ClientRole, 404);

        return $role;
    }

    /**
     * @return Collection<int, AppMod>
     */
    public function availableModules(): Collection
    {
        return $this->appMods->all([
            'app',
            'menus' => function ($query): void {
                $query
                    ->with('route')
                    ->whereNotNull('app_route_id')
                    ->orderBy('order');
            },
        ], ['app_id', 'name']);
    }

    /**
     * @param  array{name: string, code: string, desc?: ?string}  $data
     * @param  array<int, int|string>  $moduleIds
     * @param  array<int|string, int|string|null>  $landingMenuIds
     */
    public function saveRole(ClientLogin $login, ?int $roleId, array $data, array $moduleIds, array $landingMenuIds = []): ClientRole
    {
        $client = $this->client($login);
        $code = $this->normalizeCode($data['code']);
        $moduleIds = $this->moduleIds($moduleIds);
        $landings = $this->landingMenuIds($moduleIds, $landingMenuIds);

        return DB::transaction(function () use ($client, $roleId, $data, $moduleIds, $landings, $code): ClientRole {
            $role = $roleId ? $this->clientRoles->findForClient($client, $roleId) : null;

            if ($roleId && ! $role instanceof ClientRole) {
                abort(404);
            }

            if ($role?->isAdmin()) {
                throw ValidationException::withMessages([
                    'role' => 'The default admin role is read only.',
                ]);
            }

            $payload = [
                'code' => $code,
                'name' => trim($data['name']),
                'desc' => filled($data['desc'] ?? null) ? trim((string) $data['desc']) : null,
            ];

            $role = $role instanceof ClientRole
                ? $this->clientRoles->update($role, $payload)
                : $this->clientRoles->createForClient($client, $payload);

            $this->clientRoles->syncMods($role, $role->isAdmin() ? [] : $moduleIds);
            $this->clientRoles->syncLandings($role, $role->isAdmin() ? [] : $landings);

            return $role->load('mods.app', 'landings.menu.route', 'landings.menu.mod.app')->loadCount('clientLogins');
        });
    }

    public function deleteRole(ClientLogin $login, int $roleId): void
    {
        $role = $this->findRole($login, $roleId);

        if ($role->isAdmin()) {
            throw ValidationException::withMessages([
                'role' => 'The default admin role cannot be deleted.',
            ]);
        }

        if ($this->clientRoles->hasClientLogins($role)) {
            throw ValidationException::withMessages([
                'role' => 'Role is still assigned to one or more users.',
            ]);
        }

        DB::transaction(function () use ($role): void {
            $this->clientRoles->detachLandings($role);
            $this->clientRoles->detachMods($role);
            $this->clientRoles->delete($role);
        });
    }

    private function client(ClientLogin $login): Client
    {
        $client = $login->client;

        abort_unless($client instanceof Client, 403);

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

    /**
     * @param  array<int, int>  $moduleIds
     * @param  array<int|string, int|string|null>  $landingMenuIds
     * @return array<int, int>
     */
    private function landingMenuIds(array $moduleIds, array $landingMenuIds): array
    {
        if ($moduleIds === []) {
            return [];
        }

        $mods = AppMod::query()
            ->with([
                'app',
                'menus' => function ($query): void {
                    $query
                        ->with('route')
                        ->whereNotNull('app_route_id')
                        ->orderBy('order');
                },
            ])
            ->whereIn('id', $moduleIds)
            ->get();

        $landings = [];

        foreach ($mods->groupBy('app_id') as $appId => $appMods) {
            $appName = $appMods->first()?->app?->name ?? 'selected app';
            $candidateMenuIds = $appMods
                ->flatMap(fn (AppMod $mod): Collection => $mod->menus)
                ->pluck('id')
                ->map(fn (int|string $id): int => (int) $id)
                ->unique()
                ->values();

            if ($candidateMenuIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'roleForm.landing_menu_ids' => "Selected modules for {$appName} do not provide a default page.",
                ]);
            }

            $menuId = (int) ($landingMenuIds[$appId] ?? 0);

            if ($menuId === 0) {
                throw ValidationException::withMessages([
                    'roleForm.landing_menu_ids' => "Default page is required for {$appName}.",
                ]);
            }

            if (! $candidateMenuIds->contains($menuId)) {
                throw ValidationException::withMessages([
                    'roleForm.landing_menu_ids' => "Default page for {$appName} must belong to selected modules.",
                ]);
            }

            $landings[(int) $appId] = $menuId;
        }

        return $landings;
    }
}
