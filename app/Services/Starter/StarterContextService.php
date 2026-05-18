<?php

namespace App\Services\Starter;

use App\Models\Starter\App;
use App\Models\Starter\AppMenu;
use App\Models\Starter\AppMod;
use App\Models\Starter\UserLogin;
use App\Support\Starter\StarterNavigation;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class StarterContextService
{
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
        $login = $login instanceof UserLogin ? $login : null;

        $currentAppKey = $this->currentAppKey();
        $currentApp = $this->currentApp($currentAppKey);
        $accessibleApps = $this->accessibleApps($login);
        $sidebarMods = $this->sidebarMods($login, $currentApp);

        return [
            'login' => $login,
            'loginName' => $login?->name,
            'loginEmail' => $login?->email,
            'loginRoleName' => $login?->role?->name,
            'currentApp' => $currentApp,
            'currentAppKey' => $currentAppKey,
            'currentAppName' => $currentApp?->name,
            'currentAppIcon' => $currentApp?->icon,
            'currentDashboardUrl' => $this->dashboardUrl($currentAppKey),
            'appOptions' => $this->appOptions($accessibleApps, $currentApp),
            'sidebarMods' => $this->sidebarPayload($sidebarMods),
            'accessibleAppCount' => $accessibleApps->count(),
            'sidebarModCount' => $sidebarMods->count(),
        ];
    }

    private function currentAppKey(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.') && ! str_starts_with($routeName, 'default-livewire.')) {
            return explode('.', $routeName)[0];
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
        return App::query()
            ->where('subdomain', $currentAppKey)
            ->first();
    }

    /**
     * @return EloquentCollection<int, App>
     */
    private function accessibleApps(?UserLogin $login): EloquentCollection
    {
        if (! $login) {
            return new EloquentCollection;
        }

        $query = App::query()->orderBy('name');

        if ($login->role?->hasFullAccess()) {
            return $query->get();
        }

        $modIds = $login->role?->mods()->pluck('app_mods.id') ?? collect();

        if ($modIds->isEmpty()) {
            return new EloquentCollection;
        }

        return $query
            ->whereHas('mods', function ($query) use ($modIds): void {
                $query->whereIn('app_mods.id', $modIds);
            })
            ->get();
    }

    /**
     * @return EloquentCollection<int, AppMod>
     */
    private function sidebarMods(?UserLogin $login, ?App $currentApp): EloquentCollection
    {
        if (! $login || ! $currentApp) {
            return new EloquentCollection;
        }

        $query = AppMod::query()
            ->where('app_id', $currentApp->id)
            ->with([
                'menus' => function ($query): void {
                    $query
                        ->whereNull('parent_id')
                        ->with(['route', 'childrenRecursive.route'])
                        ->orderBy('order');
                },
            ])
            ->orderBy('id');

        if (! $login->role?->hasFullAccess()) {
            $modIds = $login->role?->mods()->pluck('app_mods.id') ?? collect();
            $query->whereIn('id', $modIds);
        }

        return $query->get();
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
                'icon' => $app->icon,
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

        return [
            'label' => $menu->label,
            'icon' => $menu->icon,
            'url' => $this->menuUrl($menu),
            'children' => $children
                ->map(fn (AppMenu $child): array => $this->menuPayload($child))
                ->values(),
            'hasChildren' => $children->isNotEmpty(),
        ];
    }

    private function dashboardUrl(string $appKey): string
    {
        $routeName = $appKey.'.dashboard';

        return Route::has($routeName) ? route($routeName) : url('/');
    }

    private function appUrl(App $app): string
    {
        $routeName = $app->subdomain.'.dashboard';

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

        if ($routeName && Route::has($routeName)) {
            return route($routeName);
        }

        return 'javascript:void(0);';
    }
}
