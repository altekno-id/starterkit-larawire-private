<?php

use App\Http\Controllers\Starter\Auth\LogoutController;
use App\Livewire\Starter\Auth\Login;
use App\Support\Starter\StarterNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('auth.')->group(function () {
    Route::get('/', function (Request $request) {
        $redirect = $request->query('redirect');

        if (auth()->check()) {
            return redirect(StarterNavigation::isSafeRedirect($redirect) ? $redirect : route('web.dashboard'));
        }

        return redirect(StarterNavigation::authLoginUrl(StarterNavigation::isSafeRedirect($redirect) ? $redirect : null));
    })->name('home');

    Route::livewire('/login', Login::class)
        ->middleware('guest')
        ->name('login');

    Route::post('/logout', LogoutController::class)
        ->middleware('auth:web')
        ->name('logout');
});
