<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every short-host response is non-indexable (ADR-008) and uncacheable
 * (ADR-004): redirects, the branded 404 and robots.txt alike. Belt (this header)
 * and suspenders (robots.txt) — nothing on the short domain should be crawled,
 * and no redirect may be cached (the kill-switch depends on it).
 */
class ShortHostHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex');

        // robots.txt is fine to cache; everything else must never be.
        if (! $request->is('robots.txt')) {
            $response->headers->set('Cache-Control', 'no-store');
        }

        return $response;
    }
}
