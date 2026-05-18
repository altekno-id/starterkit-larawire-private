<?php

use App\Livewire\Apps\Dashboard;
use App\Livewire\Apps\Starter\Placeholder;
use App\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    $redirect = request()->query('redirect', url('/'));

    return redirect(StarterNavigation::authLoginUrl(StarterNavigation::isSafeRedirect($redirect) ? $redirect : url('/')));
});

Route::name('subdomain2.')->group(function () {
    Route::middleware(['auth:web', 'starter.authorize'])->group(function () {
        Route::livewire('/', Dashboard::class)->name('dashboard');

        Route::prefix('module-1')->name('module1.')->group(function () {
            Route::livewire('/', Placeholder::class)->name('index');

            Route::livewire('/create', Placeholder::class)->name('create');

            Route::livewire('/{id}', Placeholder::class)->name('show');

            Route::livewire('/{id}/edit', Placeholder::class)->name('edit');
        });
    });
});
