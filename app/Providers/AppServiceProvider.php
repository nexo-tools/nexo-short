<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Link creation is rate-limited per user AND per IP (ADR-005 §2). The
        // stricter of the two wins for a given request. Limits are env-tunable.
        RateLimiter::for('link-creation', function (Request $request) {
            return [
                Limit::perMinute((int) config('nexo.create_rate.per_user'))
                    ->by('lc-user:'.$request->user()->id),
                Limit::perMinute((int) config('nexo.create_rate.per_ip'))
                    ->by('lc-ip:'.$request->ip()),
            ];
        });

        // The public report form is rate-limited per IP (ADR-005 §7).
        RateLimiter::for('report', function (Request $request) {
            return Limit::perMinute((int) config('nexo.report_rate.per_ip'))->by('rp-ip:'.$request->ip());
        });
    }
}
