<?php

use App\Livewire\Apps\Web\Dashboard\WebDashboardIndex;
use App\Livewire\Apps\Web\Module1\WebModule1Create;
use App\Livewire\Apps\Web\Module1\WebModule1Edit;
use App\Livewire\Apps\Web\Module1\WebModule1Index;
use App\Livewire\Apps\Web\Module1\WebModule1Show;
use App\Models\Starter\UserLogin;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    $redirect = request()->query('redirect', url('/'));

    return redirect(StarterNavigation::authLoginUrl(StarterNavigation::isSafeRedirect($redirect) ? $redirect : url('/')));
});

Route::name('web.')->group(function () {
    Route::middleware('auth:web')->group(function () {
        Route::get('/', function (NavigationAuthorizedRedirectService $redirects) {
            $login = auth()->user();
            $appKey = str((string) request()->route()?->getName())->before('.anchor')->toString();

            return $login instanceof UserLogin
                ? redirect($redirects->forAppAnchor($login, $appKey))
                : redirect()->route('auth.login');
        })->name('anchor');

        Route::middleware('starter.authorize')->group(function () {
            Route::livewire('/dashboard/index', WebDashboardIndex::class)->name('dashboard');

            Route::prefix('module-1')->name('module1.')->group(function () {
                Route::livewire('/data', WebModule1Index::class)->name('index');

                Route::livewire('/create', WebModule1Create::class)->name('create');

                Route::livewire('/{id}', WebModule1Show::class)->name('show');

                Route::livewire('/{id}/edit', WebModule1Edit::class)->name('edit');
            });
        });
    });
});
