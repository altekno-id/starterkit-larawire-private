<?php

use App\Http\Middleware\StarterAuthorize;
use App\Support\Starter\StarterAppRegistry;
use App\Support\Starter\StarterNavigation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('web')
                ->domain('auth.'.config('app.domain'))
                ->group(base_path('routes/auth.php'));

            foreach (StarterAppRegistry::keys() as $appKey) {
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
        $middleware->alias([
            'starter.authorize' => StarterAuthorize::class,
        ]);

        $middleware->redirectGuestsTo(fn ($request) => StarterNavigation::authLoginUrl($request->fullUrl()));
        $middleware->redirectUsersTo(fn ($request) => StarterNavigation::isSafeRedirect($request->query('redirect'))
            ? $request->query('redirect')
            : route('web.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
