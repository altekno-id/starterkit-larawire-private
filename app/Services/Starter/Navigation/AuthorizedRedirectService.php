<?php

namespace App\Services\Starter\Navigation;

use App\Models\Starter\AppRoute;
use App\Models\Starter\UserLogin;
use App\Support\Starter\StarterNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Throwable;

class AuthorizedRedirectService
{
    public function forLogin(UserLogin $login, ?string $redirect = null, ?string $intended = null): string
    {
        if ($this->canVisitUrl($login, $redirect)) {
            return (string) $redirect;
        }

        if ($this->canVisitUrl($login, $intended)) {
            return (string) $intended;
        }

        return $this->firstAuthorizedUrl($login);
    }

    public function forAppAnchor(UserLogin $login, string $appKey): string
    {
        $dashboardRoute = "{$appKey}.dashboard";

        if ($this->canAccessRouteName($login, $dashboardRoute)) {
            return route($dashboardRoute);
        }

        $appRoute = $this->firstAuthorizedRoute($login, $appKey);

        if ($appRoute instanceof AppRoute && Route::has($appRoute->name)) {
            return route($appRoute->name);
        }

        return $this->firstAuthorizedUrl($login);
    }

    public function firstAuthorizedUrl(UserLogin $login): string
    {
        if ($login->role?->hasFullAccess() && Route::has('web.dashboard')) {
            return route('web.dashboard');
        }

        $route = $this->firstAuthorizedRoute($login);

        if ($route instanceof AppRoute && Route::has($route->name)) {
            return route($route->name);
        }

        return Route::has('starter.profile.edit')
            ? route('starter.profile.edit')
            : url('/');
    }

    public function canVisitUrl(UserLogin $login, ?string $url): bool
    {
        if (! StarterNavigation::isSafeRedirect($url)) {
            return false;
        }

        $routeName = $this->routeNameFromUrl((string) $url);

        return $routeName !== null && $this->canAccessRouteName($login, $routeName);
    }

    private function canAccessRouteName(UserLogin $login, string $routeName): bool
    {
        if (! Route::has($routeName)) {
            return false;
        }

        if ($routeName === 'starter.profile.edit') {
            return true;
        }

        if ($routeName === 'starter.client-profile') {
            return $login->role?->isAdmin() ?? false;
        }

        if (str_starts_with($routeName, 'starter.user-management.')) {
            return $login->role?->isAdmin() ?? false;
        }

        if (str_ends_with($routeName, '.anchor')) {
            $dashboardRoute = str($routeName)->replaceEnd('.anchor', '.dashboard')->toString();

            return $this->canAccessRouteName($login, $dashboardRoute);
        }

        return $login->canAccessRoute($routeName);
    }

    private function routeNameFromUrl(string $url): ?string
    {
        try {
            $routeName = app('router')
                ->getRoutes()
                ->match(Request::create($url, 'GET'))
                ->getName();

            if ($routeName) {
                return $routeName;
            }
        } catch (Throwable) {
            // Fall back to the synced route table for local/dev URL edge cases.
        }

        $host = parse_url($url, PHP_URL_HOST);
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $domain = config('app.domain');

        if (! $host || $host === StarterNavigation::authHost()) {
            return null;
        }

        $appKey = $host === $domain
            ? 'web'
            : str((string) $host)->before('.'.$domain)->toString();

        if ($host === $domain) {
            $starterRoute = match ($path) {
                'profile/edit' => 'starter.profile.edit',
                'client-profile' => 'starter.client-profile',
                'user-management/roles' => 'starter.user-management.roles',
                'user-management/users' => 'starter.user-management.users',
                default => null,
            };

            if ($starterRoute) {
                return $starterRoute;
            }
        }

        if ($path === '') {
            $anchorRoute = "{$appKey}.anchor";

            return Route::has($anchorRoute) ? $anchorRoute : null;
        }

        return AppRoute::query()
            ->where('method', 'GET')
            ->where('uri', $path)
            ->whereHas('mod.app', function ($query) use ($appKey): void {
                $query->where('subdomain', $appKey);
            })
            ->value('name');
    }

    private function firstAuthorizedRoute(UserLogin $login, ?string $appKey = null): ?AppRoute
    {
        $modIds = $login->role?->mods()->pluck('app_mods.id') ?? collect();

        if ($modIds->isEmpty()) {
            return null;
        }

        return AppRoute::query()
            ->with('mod.app')
            ->whereIn('app_mod_id', $modIds)
            ->where('method', 'GET')
            ->when($appKey, function ($query) use ($appKey): void {
                $query->whereHas('mod.app', function ($query) use ($appKey): void {
                    $query->where('subdomain', $appKey);
                });
            })
            ->get()
            ->filter(fn (AppRoute $route): bool => Route::has($route->name))
            ->sortBy(fn (AppRoute $route): array => [
                $this->routeScore($route->name),
                $route->mod?->app?->name ?? '',
                $route->mod?->name ?? '',
                $route->name,
            ])
            ->first();
    }

    private function routeScore(string $routeName): int
    {
        return match (true) {
            str_ends_with($routeName, '.dashboard') => 0,
            str_ends_with($routeName, '.index') => 1,
            str_ends_with($routeName, '.create') => 2,
            default => 3,
        };
    }
}
