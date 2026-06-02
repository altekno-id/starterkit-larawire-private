<?php

namespace App\Http\Middleware;

use App\Models\Starter\ClientLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StarterAdmin
{
    /**
     * Allow only the built-in admin role to access global starter management.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $login = $request->user();

        if (! $login instanceof ClientLogin || ! $login->role?->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
