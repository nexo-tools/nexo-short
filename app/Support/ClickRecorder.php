<?php

namespace App\Support;

use App\Models\Link;
use Illuminate\Http\Request;

/**
 * Records one click for a link (ADR-006), server-side, on the redirect hot path.
 * Cookieless and privacy-first: the visitor is a daily-rotating hash; only coarse,
 * non-identifying fields are persisted. IP and UA are read transiently and never
 * stored.
 */
class ClickRecorder
{
    public function record(Request $request, Link $link): void
    {
        $link->clicks()->create([
            'visitor_hash' => VisitorHash::make($request),
            'referrer_host' => $this->referrerHost($request),
            'device' => DeviceClassifier::classify($request->userAgent()),
            'country' => $this->country($request),
            'created_at' => now(),
        ]);
    }

    /** External host the visitor came from; direct/self/missing → null. */
    private function referrerHost(Request $request): ?string
    {
        $referer = $request->headers->get('referer');

        if (! is_string($referer)) {
            return null;
        }

        $host = strtolower((string) parse_url($referer, PHP_URL_HOST));
        $host = (string) preg_replace('/^www\./', '', $host);

        if ($host === '' || $host === $request->getHost()) {
            return null;
        }

        return $host;
    }

    /** 2-letter country from Cloudflare's CF-IPCountry; null without it. */
    private function country(Request $request): ?string
    {
        $country = strtoupper((string) $request->headers->get('CF-IPCountry'));

        return strlen($country) === 2 && ctype_alpha($country) ? $country : null;
    }
}
