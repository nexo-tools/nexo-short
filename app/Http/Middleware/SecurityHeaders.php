<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach security headers and a self-contained Content-Security-Policy to
     * every web response. The policy allows only same-origin resources (no CDNs,
     * no external fonts) — matching the project's zero-external-requests rule.
     *
     * Adapted from the nexo-agenda / nexo-links canonical SecurityHeaders (CATALOG).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ($this->headers($request) as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    /** @return array<string, string> */
    private function headers(Request $request): array
    {
        $headers = [
            'Content-Security-Policy' => $this->contentSecurityPolicy($request),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'X-XSS-Protection' => '0',
        ];

        // Instruct browsers to stay on HTTPS once the site is served securely.
        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        return $headers;
    }

    private function contentSecurityPolicy(Request $request): string
    {
        // Host-scoped script policy (two-host isolation, ADR-001):
        //  - Panel host: runs Alpine (needs 'unsafe-eval') and exactly one inline
        //    <script>, the FOUC-free theme-init, allow-listed by its sha256 hash —
        //    never 'unsafe-inline' for scripts. Recompute the hash if the snippet
        //    in partials/theme-init.blade.php changes.
        //  - Short host: cookieless redirects/report/404 with no JS at all, so it
        //    keeps the tightest script-src ('self' only) — no eval, no hash.
        // Inline styles cover the odd style attribute; fonts are self-hosted, so
        // every source is same-origin. Zero external requests on the browser.
        $script = "'self'";
        $style = "'self' 'unsafe-inline'";
        $connect = "'self'";

        if (! $this->isShortHost($request)) {
            $script .= " 'unsafe-eval' 'sha256-QY4re+NFw+ChK0c8H/EaTpktoUisSWU0fL7V6J43umM='";
            // Panel host only: permit the Nexo Tools hub so the opt-in cookieless
            // pageview beacon (navigator.sendBeacon) is not blocked. It only fires
            // when NEXO_BEACON_ENABLED renders the beacon metas. The short host is
            // never widened — it stays connect-src 'self'. Mirror in .htaccess.
            $connect .= ' https://nexotools.alvarocdev.com';
        }

        // Allow the Vite dev server (and its websocket) while running HMR locally.
        if ($dev = $this->viteDevServer()) {
            $script .= " {$dev}";
            $style .= " {$dev}";
            $connect .= " {$dev} ".preg_replace('#^http#', 'ws', $dev);
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "img-src 'self' data:",
            "font-src 'self'",
            "script-src {$script}",
            "style-src {$style}",
            "connect-src {$connect}",
        ]);
    }

    /** The cookieless short host (apex or www) — kept on the tightest script-src. */
    private function isShortHost(Request $request): bool
    {
        $short = (string) config('nexo.short_host');

        if ($short === '') {
            return false;
        }

        $host = $request->getHost();

        return $host === $short || $host === 'www.'.$short;
    }

    private function viteDevServer(): ?string
    {
        $hotFile = public_path('hot');

        if (! app()->environment('local') || ! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) file_get_contents($hotFile));

        return $url === '' ? null : rtrim($url, '/');
    }
}
