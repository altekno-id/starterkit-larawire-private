<?php

namespace App\Http\Middleware;

use App\Models\Starter\ClientLogin;
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
