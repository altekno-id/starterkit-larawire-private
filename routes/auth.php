<?php

use App\Http\Controllers\Starter\Auth\LogoutController;
use App\Livewire\Starter\Auth\Login;
use App\Models\Starter\UserLogin;
use App\Services\Starter\Navigation\AuthorizedRedirectService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('auth.')->group(function () {
    Route::get('/', function (Request $request, AuthorizedRedirectService $redirects) {
        $redirect = $request->query('redirect');

        $login = auth()->user();

        if ($login instanceof UserLogin) {
            return redirect($redirects->forLogin($login, $redirect));
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
