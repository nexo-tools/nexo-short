<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Anonymous, daily-rotating visitor fingerprint (ADR-006). Derived from the app
 * key, the current date, the IP and the User-Agent — none of which are stored.
 * The date in the payload makes it impossible to link the same visitor across
 * days, so "unique clicks per day" is possible while re-identification is not.
 * Adapted from the nexo-links canonical VisitorHash (CATALOG).
 */
class VisitorHash
{
    public static function make(Request $request): string
    {
        return hash('sha256', implode('|', [
            (string) config('app.key'),
            now()->toDateString(),
            (string) $request->ip(),
            (string) $request->userAgent(),
        ]));
    }
}
