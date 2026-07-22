<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the local credential surface by auth mode (ADR-003 §4). When
 * NEXO_AUTH_MODE is `sso`, the hosted instance has no local password login or
 * registration — those endpoints 404. `local` and `both` keep them. The login
 * page itself stays reachable (it hosts the "Continue with Nexo ID" button).
 */
class EnsureLocalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(config('nexo.auth_mode') === 'sso', 404);

        return $next($request);
    }
}
