<?php

namespace App\Http\Middleware;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StarterEnsureActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $login = $request->user();

        if ($login instanceof ClientLogin && ! $login->isActive()) {
            app(AuditLogService::class)->recordSecurityEvent(
                'auth.session_revoked',
                'Session dihentikan karena akun tidak aktif',
                target: $login,
                actor: $login,
                metadata: ['reason' => 'account_inactive'],
            );

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')->withErrors([
                'username' => 'Akun tidak aktif atau sedang dikunci. Hubungi administrator.',
            ]);
        }

        return $next($request);
    }
}
