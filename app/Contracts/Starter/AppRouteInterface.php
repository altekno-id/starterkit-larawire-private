<?php

namespace App\Contracts\Starter;

use App\Models\Starter\AppRoute;
use Illuminate\Support\Collection;

interface AppRouteInterface
{
    public function nameForGetUriAndAppSubdomain(string $uri, string $appSubdomain): ?string;

    /**
     * @param  array<int, int>  $modIds
     * @return Collection<int, AppRoute>
     */
    public function getRoutesForModIds(array $modIds, ?string $appSubdomain = null): Collection;
}
