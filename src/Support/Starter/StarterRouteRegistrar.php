<?php

namespace Altekno\StarterKit\Support\Starter;

use Illuminate\Support\Facades\Route;

class StarterRouteRegistrar
{
    public static function registerAll(): void
    {
        foreach (StarterAppRegistry::keys() as $appKey) {
            self::register($appKey);
        }
    }

    public static function register(string $appKey): void
    {
        Route::middleware('web')
            ->domain($appKey.'.'.config('app.domain'))
            ->group(base_path("routes/apps/{$appKey}.php"));

        Route::getRoutes()->refreshNameLookups();
        Route::getRoutes()->refreshActionLookups();
    }
}
