<?php

namespace App\Contracts\Starter;

use App\Models\Starter\App;
use App\Models\Starter\AppMod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

interface AppModInterface
{
    /**
     * @param  array<int, string>  $with
     * @param  array<int, string>  $orderBy
     * @return Collection<int, AppMod>
     */
    public function all(array $with = [], array $orderBy = ['id']): Collection;

    /**
     * @param  array<int, string>  $with
     * @param  array<int, int>  $onlyIds
     * @return EloquentCollection<int, AppMod>
     */
    public function forApp(App $app, array $with = [], array $onlyIds = []): EloquentCollection;
}
