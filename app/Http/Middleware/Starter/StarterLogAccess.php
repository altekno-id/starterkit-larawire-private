<?php

namespace App\Http\Middleware\Starter;

use App\Services\Starter\AuthenticatedLoginService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StarterLogAccess
{
    public function __construct(
        private readonly AuthenticatedLoginService $authenticatedLogins,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->authenticatedLogins->logViewer($request->user());

        return $next($request);
    }
}
