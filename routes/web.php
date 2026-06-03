<?php

use App\Http\Controllers\Starter\Auth\GoogleController;
use App\Http\Controllers\Starter\Auth\LogoutController;
use App\Livewire\Starter\Auth\ForgotPassword;
use App\Livewire\Starter\Auth\Login;
use App\Livewire\Starter\Auth\Register;
use App\Livewire\Starter\Auth\ResetPassword;
use App\Livewire\Starter\Landing\LandingIndex;
use App\Models\Starter\ClientLogin;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::livewire('/', LandingIndex::class)->name('landing');

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/', function (Request $request, NavigationAuthorizedRedirectService $redirects) {
        $redirect = $request->query('redirect');
        $login = auth()->user();

        if ($login instanceof ClientLogin) {
            return redirect($redirects->forLogin($login, $redirect));
        }

        return redirect(StarterNavigation::authLoginUrl(StarterNavigation::isSafeRedirect($redirect) ? $redirect : null));
    })->name('home');

    Route::livewire('/login', Login::class)
        ->middleware('guest')
        ->name('login');

    Route::livewire('/register', Register::class)
        ->middleware('guest')
        ->name('register');

    Route::livewire('/forgot-password', ForgotPassword::class)
        ->middleware('guest')
        ->name('password.request');

    Route::livewire('/reset-password/{token}', ResetPassword::class)
        ->middleware('guest')
        ->name('password.reset');

    Route::get('/google', [GoogleController::class, 'redirect'])
        ->middleware('guest')
        ->name('google.redirect');

    Route::get('/google/callback', [GoogleController::class, 'callback'])
        ->middleware('guest')
        ->name('google.callback');

    Route::post('/logout', LogoutController::class)
        ->middleware('auth:web')
        ->name('logout');
});
