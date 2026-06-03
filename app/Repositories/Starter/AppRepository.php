<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\AppInterface;
use App\Models\Starter\App;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class AppRepository implements AppInterface
{
    public function findBySubdomain(string $subdomain): ?App
    {
        return App::query()
            ->where('subdomain', $subdomain)
            ->first();
    }

    public function allOrderedByName(): EloquentCollection
    {
        return App::query()
            ->orderBy('name')
            ->get();
    }

    public function whereHasModIds(array $modIds): EloquentCollection
    {
        return App::query()
            ->whereHas('mods', function ($query) use ($modIds): void {
                $query->whereIn('starter_app_mods.id', $modIds);
            })
            ->orderBy('name')
            ->get();
    }
}
