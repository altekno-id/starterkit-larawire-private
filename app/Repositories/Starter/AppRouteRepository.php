<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\AppRouteInterface;
use App\Models\Starter\AppRoute;
use Illuminate\Support\Collection;

class AppRouteRepository implements AppRouteInterface
{
    public function nameForGetUriAndAppSubdomain(string $uri, string $appSubdomain): ?string
    {
        return AppRoute::query()
            ->where('method', 'GET')
            ->where('uri', $uri)
            ->whereHas('mod.app', function ($query) use ($appSubdomain): void {
                $query->where('subdomain', $appSubdomain);
            })
            ->value('name');
    }

    public function getRoutesForModIds(array $modIds, ?string $appSubdomain = null): Collection
    {
        return AppRoute::query()
            ->with('mod.app')
            ->whereIn('app_mod_id', $modIds)
            ->where('method', 'GET')
            ->when($appSubdomain, function ($query) use ($appSubdomain): void {
                $query->whereHas('mod.app', function ($query) use ($appSubdomain): void {
                    $query->where('subdomain', $appSubdomain);
                });
            })
            ->get();
    }
}
