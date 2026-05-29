<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\AppModInterface;
use App\Models\Starter\App;
use App\Models\Starter\AppMod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class AppModRepository implements AppModInterface
{
    public function all(array $with = [], array $orderBy = ['id']): Collection
    {
        $query = AppMod::query()->with($with);

        foreach ($orderBy as $column) {
            $query->orderBy($column);
        }

        return $query->get();
    }

    public function forApp(App $app, array $with = [], array $onlyIds = []): EloquentCollection
    {
        return AppMod::query()
            ->whereBelongsTo($app)
            ->with($with)
            ->when($onlyIds !== [], function ($query) use ($onlyIds): void {
                $query->whereIn('id', $onlyIds);
            })
            ->orderBy('id')
            ->get();
    }
}
