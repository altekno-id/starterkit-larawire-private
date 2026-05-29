<?php

namespace App\Contracts\Starter;

use App\Models\Starter\App;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface AppInterface
{
    public function findBySubdomain(string $subdomain): ?App;

    /**
     * @return EloquentCollection<int, App>
     */
    public function allOrderedByName(): EloquentCollection;

    /**
     * @param  array<int, int>  $modIds
     * @return EloquentCollection<int, App>
     */
    public function whereHasModIds(array $modIds): EloquentCollection;
}
