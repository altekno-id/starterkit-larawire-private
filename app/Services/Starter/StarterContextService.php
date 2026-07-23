<?php

namespace App\Services\Starter;

use App\Contracts\Starter\AppInterface;
use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\ClientInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Models\Starter\App;
use App\Models\Starter\AppMenu;
use App\Models\Starter\AppMod;
use App\Models\Starter\ClientLogin;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class StarterContextService
{
    public function __construct(
        private readonly AppInterface $apps,
        private readonly AppModInterface $appMods,
        private readonly ClientRoleInterface $clientRoles,
        private readonly ClientInterface $clients,
        private readonly StarterConfigService $configs,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $attribute = 'starter.context.data';

        if (request()->attributes->has($attribute)) {
            return request()->attributes->get($attribute);
        }

        $data = $this->build();
        request()->attributes->set($attribute, $data);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $login = auth()->user();
        $login = $login instanceof ClientLogin ? $login : null;

        $login?->loadMissing('role');
        $client = $login ? $this->clients->current() : null;

        $accessibleApps = $this->accessibleApps($login);
        $currentApp = $this->resolveCurrentApp($accessibleApps);
        $currentAppKey = $currentApp?->subdomain ?? 'landing';
        $sidebarMods = $this->sidebarMods($login, $currentApp);
        $sidebarPayload = $this->sidebarPayload($sidebarMods);

        $lockScreenEnabled = $this->configs->boolean('security.lock_screen_enabled');
        $lockScreenTimeoutSeconds = max(
            60,
            min(86400, $this->configs->integer('security.lock_screen_timeout_minutes') * 60),
        );

        return [
            'login' => $login,
            'loginName' => $login?->name,
            'loginEmail' => $login?->email,
            'loginAvatarUrl' => $this->avatarUrl($login),
            'loginRoleName' => $login?->role?->name,
            'clientName' => $client?->name,
            'clientLogoUrl' => $this->clientLogoUrl($client?->logo),
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
            'lockScreenEnabled' => $lockScreenEnabled,
            'lockScreenTimeoutSeconds' => $lockScreenTimeoutSeconds,
            'lockScreenUrl' => route('starter.lock-screen'),
            'sessionActivityUrl' => route('starter.session.activity'),
        ];
    }

    private function requestedAppKey(): ?string
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

        if ($domain && $host !== $domain && str_ends_with($host, '.'.$domain)) {
            return str($host)->before('.'.$domain)->toString();
        }

        return null;
    }

    /**
     * Keep the sidebar attached to an authorized app on global starter pages.
     *
     * @param  EloquentCollection<int, App>  $accessibleApps
     */
    private function resolveCurrentApp(EloquentCollection $accessibleApps): ?App
    {
        $requestedAppKey = $this->requestedAppKey();
        $requestedApp = $requestedAppKey ? $this->apps->findBySubdomain($requestedAppKey) : null;

        if ($requestedApp instanceof App && $accessibleApps->contains(fn (App $app): bool => $app->is($requestedApp))) {
            $this->rememberCurrentApp($requestedApp);

            return $requestedApp;
        }

        $rememberedAppKey = request()->hasSession()
            ? request()->session()->get('starter.current_app_key')
            : null;
        $rememberedApp = filled($rememberedAppKey)
            ? $accessibleApps->firstWhere('subdomain', $rememberedAppKey)
            : null;

        if ($rememberedApp instanceof App) {
            return $rememberedApp;
        }

        $fallbackApp = $accessibleApps->first();

        if ($fallbackApp instanceof App) {
            $this->rememberCurrentApp($fallbackApp);
        }

        return $fallbackApp;
    }

    private function rememberCurrentApp(App $app): void
    {
        if (request()->hasSession()) {
            request()->session()->put('starter.current_app_key', $app->subdomain);
        }
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
        $photo = $login?->profile_photo;

        if (! $photo) {
            return asset('assets/mine/avatar.png');
        }

        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://') || str_starts_with($photo, '//')) {
            return $photo;
        }

        return asset(ltrim($photo, '/'));
    }

    private function clientLogoUrl(?string $logo): ?string
    {
        $logo = trim((string) $logo);

        if ($logo === '') {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '//')) {
            return $logo;
        }

        return asset(ltrim($logo, '/'));
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

        $host = $app->subdomain.'.'.config('app.domain');

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
