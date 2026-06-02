<?php

use App\Livewire\Apps\Subdomain1\Dashboard\Subdomain1DashboardIndex;
use App\Livewire\Apps\Subdomain1\Module1\Subdomain1Module1Create;
use App\Livewire\Apps\Subdomain1\Module1\Subdomain1Module1Edit;
use App\Livewire\Apps\Subdomain1\Module1\Subdomain1Module1Index;
use App\Livewire\Apps\Subdomain1\Module1\Subdomain1Module1Show;
use App\Models\Starter\ClientLogin;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    $redirect = request()->query('redirect', url('/'));

    return redirect(StarterNavigation::authLoginUrl(StarterNavigation::isSafeRedirect($redirect) ? $redirect : url('/')));
});

Route::name('subdomain1.')->group(function () {
    Route::middleware('auth:web')->group(function () {
        Route::get('/', function (NavigationAuthorizedRedirectService $redirects) {
            $login = auth()->user();
            $appKey = str((string) request()->route()?->getName())->before('.anchor')->toString();

            return $login instanceof ClientLogin
                ? redirect($redirects->forAppAnchor($login, $appKey))
                : redirect()->route('auth.login');
        })->name('anchor');

        Route::middleware('starter.authorize')->group(function () {
            Route::livewire('/dashboard/index', Subdomain1DashboardIndex::class)->name('dashboard');

            Route::prefix('module-1')->name('module1.')->group(function () {
                Route::livewire('/data', Subdomain1Module1Index::class)->name('index');

                Route::livewire('/create', Subdomain1Module1Create::class)->name('create');

                Route::livewire('/{id}', Subdomain1Module1Show::class)->name('show');

                Route::livewire('/{id}/edit', Subdomain1Module1Edit::class)->name('edit');
            });
        });
    });
});
