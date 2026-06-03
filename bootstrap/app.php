<?php

use App\Http\Middleware\StarterAdmin;
use App\Http\Middleware\StarterAuthorize;
use App\Models\Starter\ClientLogin;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Support\Starter\StarterAppRegistry;
use App\Support\Starter\StarterNavigation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('web')
                ->group(base_path('routes/starter.php'));

            Route::middleware('web')
                ->domain(config('app.domain'))
                ->group(base_path('routes/web.php'));

            foreach (StarterAppRegistry::keys() as $appKey) {
                Route::middleware('web')
                    ->domain($appKey.'.'.config('app.domain'))
                    ->group(base_path("routes/apps/{$appKey}.php"));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'starter.admin' => StarterAdmin::class,
            'starter.authorize' => StarterAuthorize::class,
        ]);

        $middleware->redirectGuestsTo(fn ($request) => StarterNavigation::authLoginUrl($request->fullUrl()));
        $middleware->redirectUsersTo(function ($request): string {
            $login = $request->user();

            return $login instanceof ClientLogin
                ? app(NavigationAuthorizedRedirectService::class)->forLogin($login, $request->query('redirect'))
                : url('/');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            $loginUrl = StarterNavigation::authLoginUrl($request->fullUrl());

            if ($request->headers->get('X-Livewire-Navigate') === '1') {
                return response()
                    ->view('templates.session-expired', ['loginUrl' => $loginUrl])
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            }

            if ($request->headers->get('X-Livewire') === '1') {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'redirect' => $loginUrl,
                ], 401);
            }

            return null;
        });
    })->create();
