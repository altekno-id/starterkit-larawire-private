<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

if (! function_exists('starterAppKeys')) {
    /**
     * Discover runnable starter apps from config/apps/*.php.
     *
     * A file is runnable only when the filename is domain-safe, the route file
     * exists, and at least one module is configured.
     *
     * @return array<int, string>
     */
    function starterAppKeys(): array
    {
        return collect(glob(config_path('apps/*.php')) ?: [])
            ->map(function (string $path): string {
                return pathinfo($path, PATHINFO_FILENAME);
            })
            ->filter(function (string $key): bool {
                if (preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $key) !== 1) {
                    return false;
                }

                if (! is_file(base_path("routes/{$key}.php"))) {
                    return false;
                }

                $config = require config_path("apps/{$key}.php");

                return is_array($config)
                    && ! empty($config['mods'])
                    && is_array($config['mods']);
            })
            ->sort()
            ->values()
            ->all();
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {

            foreach (starterAppKeys() as $appKey) {
                if ($appKey === 'web') {
                    Route::middleware('web')
                        ->domain(config('app.domain'))
                        ->group(base_path('routes/web.php'));

                    continue;
                }

                Route::middleware('web')
                    ->domain($appKey.'.'.config('app.domain'))
                    ->group(base_path("routes/{$appKey}.php"));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
