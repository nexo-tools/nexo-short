<?php

use App\Http\Middleware\NexoSsoSilentLogin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ShortHostHeaders;
use App\Mail\OperatorAlert;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            $short = (string) config('nexo.short_host');

            // Two-host product (ADR-001/002). The short host is registered FIRST
            // and domain-scoped, so it always wins for redirect traffic; it runs a
            // cookieless stack (no session/CSRF) — just security + noindex/no-store.
            Route::middleware('short')
                ->domain($short)
                ->group(base_path('routes/short.php'));

            // Canonicalize www.<short-host> to the apex, still on the cookieless
            // noindex stack — so the short domain never serves the indexable panel
            // app (a host-detection leak) and short links resolve on www too.
            Route::middleware('short')
                ->domain('www.'.$short)
                ->group(function () use ($short): void {
                    Route::get('/{path?}', fn (string $path = '') => redirect()->away('https://'.$short.'/'.$path, 301))
                        ->where('path', '.*');
                });

            // The panel host answers on every other host (panel domain, and
            // localhost in dev/tests) with the full web stack.
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Panel (web) responses carry the security headers. SetLocale needs a
        // session, so it is aliased and applied to panel routes only.
        $middleware->web(append: [
            SecurityHeaders::class,
            // Silent SSO trigger (prompt=none) — pass-through unless NEXO_SSO_ENABLED.
            // Panel host only by construction: the short host never uses the web group.
            NexoSsoSilentLogin::class,
        ]);

        // Shared preference cookies (theme + language) are scoped to the parent
        // domain so they cross every ecosystem tool. Each tool has its own APP_KEY,
        // so they must stay UNencrypted to be readable across tools. Only the panel
        // (web) stack has EncryptCookies; the short host stays cookieless.
        $middleware->encryptCookies(except: ['nexo-lang', 'nexo-theme']);

        // Short host: cookieless. Security headers + X-Robots-Tag noindex + no-store
        // on every response (ADR-004/008). No session, no CSRF, no cookies.
        $middleware->group('short', [
            SecurityHeaders::class,
            ShortHostHeaders::class,
        ]);

        $middleware->alias([
            'setlocale' => SetLocale::class,
        ]);

        // Behind Cloudflare, the real client IP arrives in proxy headers. Without
        // trusting the proxy, $request->ip() is the edge IP and every per-IP rate
        // limit (login, creation, report) + the VisitorHash collapse to one bucket.
        // Set TRUSTED_PROXIES in production (Cloudflare ranges, or '*' when the
        // origin is reachable ONLY through Cloudflare). Empty in local/dev.
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(
                at: $proxies === '*' ? '*' : array_map('trim', explode(',', (string) $proxies)),
            );
        }

        // TrustHosts allowlist (defense in depth). When enabled, the app answers
        // ONLY on its own hosts — the short host and the panel host, each with an
        // optional www. — so a spoofed Host header can't drive absolute-URL
        // generation or cache poisoning. Derived from the two-host contract
        // (ADR-001) so nothing is hardcoded. Off by default (same conditional
        // shape as trustProxies) so local/dev and self-host setups keep working;
        // the framework also no-ops it in the local env and under tests. Turn on
        // in production (behind Cloudflare) with TRUST_HOSTS_ENABLED=true.
        if (env('TRUST_HOSTS_ENABLED', false)) {
            $hosts = array_values(array_filter(
                [config('nexo.short_host'), config('nexo.panel_host')],
                fn ($host): bool => is_string($host) && $host !== '',
            ));

            $middleware->trustHosts(
                at: array_map(fn (string $host): string => '^(www\.)?'.preg_quote($host).'$', $hosts),
                subdomains: false,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Something broke and nobody is watching: this ecosystem has no error
        // tracker by design (a third party observing users contradicts the
        // product), so the operator hears about a 500 by mail. Deduped by
        // exception identity for 15 minutes — a loop must not flood an inbox
        // until its owner stops reading it. See templates/nexo-ops/README.md.
        $exceptions->report(function (Throwable $e): void {
            // Off unless the operator turned it on — which is also what keeps
            // a suite quiet, since the flag is false in the testing env.
            if (! config('nexo.ops_mail', false)) {
                return;
            }

            $recipient = (string) config('nexo.support_email');
            if ($recipient === '') {
                return;
            }

            $key = 'ops-mail:'.sha1($e::class.'|'.$e->getFile().'|'.$e->getLine());
            if (! Cache::add($key, true, now()->addMinutes(15))) {
                return;
            }

            Mail::to($recipient)->queue(OperatorAlert::fromThrowable($e, request()?->fullUrl()));
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
