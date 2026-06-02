<?php

namespace App\Services\Starter;

use App\Contracts\Starter\AppInterface;
use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Models\Starter\App;
use App\Models\Starter\AppMenu;
use App\Models\Starter\AppMod;
use App\Models\Starter\ClientLogin;
use App\Support\Starter\StarterNavigation;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class StarterContextService
{
    public function __construct(
        private readonly AppInterface $apps,
        private readonly AppModInterface $appMods,
        private readonly ClientRoleInterface $clientRoles
    ) {}

    /**
     * @var array<string, mixed>|null
     */
    private ?array $data = null;

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data ??= $this->build();
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $login = auth()->user();
        $login = $login instanceof ClientLogin ? $login : null;

        $currentAppKey = $this->currentAppKey();
        $currentApp = $this->currentApp($currentAppKey);
        $accessibleApps = $this->accessibleApps($login);
        $sidebarMods = $this->sidebarMods($login, $currentApp);
        $sidebarPayload = $this->sidebarPayload($sidebarMods);

        return [
            'login' => $login,
            'loginName' => $login?->name,
            'loginEmail' => $login?->email,
            'loginAvatarUrl' => $this->avatarUrl($login),
            'loginRoleName' => $login?->role?->name,
            'currentApp' => $currentApp,
            'currentAppKey' => $currentAppKey,
            'currentAppName' => $currentApp?->name,
            'currentAppIcon' => $this->normalizeIcon($currentApp?->icon, 'apps'),
            'currentDashboardUrl' => $this->dashboardUrl($currentAppKey),
            'currentProfileUrl' => $this->profileUrl(),
            'appOptions' => $this->appOptions($accessibleApps, $currentApp),
            'sidebarMods' => $sidebarPayload,
            'accessibleAppCount' => $accessibleApps->count(),
            'sidebarModCount' => $sidebarMods->count(),
        ];
    }

    private function currentAppKey(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.') && ! str_starts_with($routeName, 'default-livewire.')) {
            $routeAppKey = explode('.', $routeName)[0];

            if ($routeAppKey !== 'starter') {
                return $routeAppKey;
            }
        }

        $host = request()->getHost();
        $domain = config('app.domain');

        if ($domain && $host !== $domain && $host !== StarterNavigation::authHost() && str_ends_with($host, '.'.$domain)) {
            return str($host)->before('.'.$domain)->toString();
        }

        return 'web';
    }

    private function currentApp(string $currentAppKey): ?App
    {
        return $this->apps->findBySubdomain($currentAppKey);
    }

    /**
     * @return EloquentCollection<int, App>
     */
    private function accessibleApps(?ClientLogin $login): EloquentCollection
    {
        if (! $login) {
            return new EloquentCollection;
        }

        if ($login->role?->hasFullAccess()) {
            return $this->apps->allOrderedByName();
        }

        $modIds = $login->role
            ? $this->clientRoles->modIds($login->role)
            : collect();

        if ($modIds->isEmpty()) {
            return new EloquentCollection;
        }

        return $this->apps->whereHasModIds($modIds->all());
    }

    /**
     * @return EloquentCollection<int, AppMod>
     */
    private function sidebarMods(?ClientLogin $login, ?App $currentApp): EloquentCollection
    {
        if (! $login || ! $currentApp) {
            return new EloquentCollection;
        }

        $with = [
            'menus' => function ($query): void {
                $query
                    ->whereNull('parent_id')
                    ->with(['route', 'childrenRecursive.route'])
                    ->orderBy('order');
            },
        ];

        if ($login->role?->hasFullAccess()) {
            return $this->appMods->forApp($currentApp, $with);
        }

        $modIds = $login->role
            ? $this->clientRoles->modIds($login->role)
            : collect();

        return $modIds->isEmpty()
            ? new EloquentCollection
            : $this->appMods->forApp($currentApp, $with, $modIds->all());
    }

    /**
     * @param  EloquentCollection<int, App>  $apps
     * @return Collection<int, array<string, mixed>>
     */
    private function appOptions(EloquentCollection $apps, ?App $currentApp): Collection
    {
        return $apps
            ->map(fn (App $app): array => [
                'name' => $app->name,
                'subdomain' => $app->subdomain,
                'icon' => $this->normalizeIcon($app->icon, 'apps'),
                'url' => $this->appUrl($app),
                'active' => $currentApp?->is($app) ?? false,
            ])
            ->values();
    }

    /**
     * @param  EloquentCollection<int, AppMod>  $mods
     * @return Collection<int, array<string, mixed>>
     */
    private function sidebarPayload(EloquentCollection $mods): Collection
    {
        return $mods
            ->map(fn (AppMod $mod): array => [
                'name' => $mod->name,
                'menus' => $mod->menus
                    ->map(fn (AppMenu $menu): array => $this->menuPayload($menu))
                    ->values(),
                'menuLabels' => $mod->menus->pluck('label')->join(', '),
            ])
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function menuPayload(AppMenu $menu): array
    {
        $children = $menu->childrenRecursive;
        $childPayload = $children
            ->map(fn (AppMenu $child): array => $this->menuPayload($child))
            ->values();
        $isActive = $this->isCurrentUrl($this->menuUrl($menu));
        $isExpanded = $isActive || $childPayload->contains(fn (array $child): bool => $child['active'] || $child['expanded']);

        return [
            'label' => $menu->label,
            'icon' => $this->normalizeIcon($menu->icon, 'circle'),
            'url' => $this->menuUrl($menu),
            'children' => $childPayload,
            'hasChildren' => $children->isNotEmpty(),
            'active' => $isActive,
            'expanded' => $isExpanded,
        ];
    }

    private function isCurrentUrl(?string $url): bool
    {
        if (! $url || $url === 'javascript:void(0);') {
            return false;
        }

        return rtrim($url, '/') === rtrim(url()->current(), '/');
    }

    private function normalizeIcon(?string $icon, string $fallback): string
    {
        return match ($icon) {
            'ri-global-line' => 'world',
            'ri-apps-line', 'ri-apps-2-line' => 'apps',
            'ri-dashboard-line' => 'layout-dashboard',
            'ri-folder-line' => 'folder',
            'ri-user-settings-line', 'user-management' => 'users-group',
            null, '' => $fallback,
            default => str($icon)
                ->replaceStart('ri-', '')
                ->replaceEnd('-line', '')
                ->toString(),
        };
    }

    public function avatarUrl(?ClientLogin $login): string
    {
        $photo = $login?->profile_photo ?: $login?->google_avatar;

        if (! $photo) {
            return asset('assets/mine/avatar.png');
        }

        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://') || str_starts_with($photo, '//')) {
            return $photo;
        }

        return asset(ltrim($photo, '/'));
    }

    private function dashboardUrl(string $appKey): string
    {
        $routeName = $appKey.'.anchor';

        return Route::has($routeName) ? route($routeName) : url('/');
    }

    private function profileUrl(): string
    {
        return $this->routeUrl('starter.profile.edit', url('/profile/edit'));
    }

    private function appUrl(App $app): string
    {
        $routeName = $app->subdomain.'.anchor';

        if (Route::has($routeName)) {
            return route($routeName);
        }

        $host = $app->subdomain === 'web'
            ? config('app.domain')
            : $app->subdomain.'.'.config('app.domain');

        return request()->getScheme().'://'.$host;
    }

    private function menuUrl(AppMenu $menu): string
    {
        $routeName = $menu->route?->name;

        return $routeName ? $this->routeUrl($routeName) : 'javascript:void(0);';
    }

    private function routeUrl(string $routeName, string $fallback = 'javascript:void(0);'): string
    {
        return Route::has($routeName) ? route($routeName) : $fallback;
    }
}
