<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Is this request on the isolated short host (ADR-001)? The apex and its www.
 * both count — www is canonicalized with a 301 that is itself served by the
 * cookieless short stack. Single source of truth for the two places that must
 * branch on the host: the CSP (the short host keeps the tightest script-src)
 * and the error views (the short host loads no chrome, tokens or build).
 */
class ShortHost
{
    public static function matches(Request $request): bool
    {
        $short = (string) config('nexo.short_host');

        if ($short === '') {
            return false;
        }

        $host = $request->getHost();

        return $host === $short || $host === 'www.'.$short;
    }
}
