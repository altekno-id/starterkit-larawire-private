<?php

namespace App\Http\Middleware;

use App\Models\Starter\AppRoute;
use App\Models\Starter\UserLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StarterAuthorize
{
    /**
     * Authorize access to starter page routes synced into app_routes.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $login = $request->user();
        $routeName = $request->route()?->getName();

        if ($routeName && str_starts_with($routeName, 'starter.')) {
            return $next($request);
        }

        if (! $login instanceof UserLogin || ! $routeName) {
            abort(403);
        }

        $route = AppRoute::query()
            ->where('name', $routeName)
            ->first();

        if (! $route || ! $login->canAccessRoute($route)) {
            abort(403);
        }

        return $next($request);
    }
}
