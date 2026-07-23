<?php

use App\Livewire\Apps\App2\Dashboard\App2DashboardIndex;
use App\Livewire\Apps\App2\Module1\App2Module1Create;
use App\Livewire\Apps\App2\Module1\App2Module1Edit;
use App\Livewire\Apps\App2\Module1\App2Module1Index;
use App\Livewire\Apps\App2\Module1\App2Module1Show;
use App\Models\Starter\ClientLogin;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    $redirect = request()->query('redirect', url('/'));

    return redirect(StarterNavigation::authLoginUrl(StarterNavigation::isSafeRedirect($redirect) ? $redirect : url('/')));
});

Route::name('app2.')->group(function () {
    Route::middleware(['auth:web', 'starter.active', 'starter.password-change', 'starter.lock'])->group(function () {
        Route::get('/', function (NavigationAuthorizedRedirectService $redirects) {
            $login = auth()->user();
            $appKey = str((string) request()->route()?->getName())->before('.anchor')->toString();

            return $login instanceof ClientLogin
                ? redirect($redirects->forAppAnchor($login, $appKey))
                : redirect()->route('auth.login');
        })->name('anchor');

        Route::middleware('starter.authorize')->group(function () {
            Route::livewire('/dashboard/index', App2DashboardIndex::class)->name('dashboard');
            Route::livewire('/dashboard/summary-2', App2DashboardIndex::class)->name('dashboard.summary2');

            Route::prefix('module-1')->name('module1.')->group(function () {
                Route::livewire('/data', App2Module1Index::class)->name('index');

                Route::livewire('/create', App2Module1Create::class)->name('create');

                Route::livewire('/{id}', App2Module1Show::class)->name('show');

                Route::livewire('/{id}/edit', App2Module1Edit::class)->name('edit');
            });
        });
    });
});
