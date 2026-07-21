<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'es', 'pt'];

    /**
     * Resolve the request locale from ?lang=, the session, or the Accept-Language
     * header, in that order. Applied on the panel host only (the short host serves
     * cookieless redirects with no session). Adapted from nexo-id (CATALOG).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang');

        if (is_string($requested) && in_array($requested, self::SUPPORTED, true)) {
            $request->session()->put('locale', $requested);
        }

        $locale = $request->session()->get('locale')
            ?? $request->getPreferredLanguage(self::SUPPORTED)
            ?? config('app.locale');

        app()->setLocale($locale);

        return $next($request);
    }
}
