<?php

namespace App\Http\Controllers\Starter\Auth;

use App\Http\Controllers\Controller;
use App\Models\Starter\ClientLogin;
use App\Services\Starter\AuditLogService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request, AuditLogService $auditLogs): RedirectResponse
    {
        $login = $request->user();

        if ($login instanceof ClientLogin) {
            $auditLogs->recordSecurityEvent(
                'auth.logout',
                'Logout',
                target: $login,
                actor: $login,
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = $request->input('redirect', url('/'));

        return redirect(StarterNavigation::authLoginUrl(
            StarterNavigation::isSafeRedirect($redirect) ? $redirect : url('/')
        ));
    }
}
