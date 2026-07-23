<?php

namespace App\Http\Middleware;

use App\Models\Starter\ClientLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StarterLogAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $login = $request->user();

        if (! $login instanceof ClientLogin || ! $login->loadMissing('role')->role?->canViewLogs()) {
            abort(403);
        }

        return $next($request);
    }
}
