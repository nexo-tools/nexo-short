<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ShortHostHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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
        ]);

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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
