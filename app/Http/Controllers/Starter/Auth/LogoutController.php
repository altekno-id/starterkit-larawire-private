<?php

namespace App\Http\Controllers\Starter\Auth;

use App\Http\Controllers\Controller;
use App\Support\Starter\StarterNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = $request->input('redirect', url('/'));

        return redirect(StarterNavigation::authLoginUrl(
            StarterNavigation::isSafeRedirect($redirect) ? $redirect : url('/')
        ));
    }
}
