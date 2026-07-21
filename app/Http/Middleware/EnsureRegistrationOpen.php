<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the local registration surface (ADR-003 §4): when
 * NEXO_ALLOW_REGISTRATION is false the routes 404 — no public signup exists
 * (the hosted instance runs SSO-only; self-hosters open it if they want).
 */
class EnsureRegistrationOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) config('nexo.allow_registration'), 404);

        return $next($request);
    }
}
