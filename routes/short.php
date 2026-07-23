<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RobotsController;
use Illuminate\Support\Facades\Route;

// Short host (ADR-001): redirects, branded 404, robots.txt and the report
// channel only. Cookieless stack (see the 'short' middleware group). All
// responses are noindex + no-store.

Route::get('/robots.txt', RobotsController::class);

// Public abuse report (ADR-005 §7): no auth, rate-limited per IP. Registered
// before /{slug} so `report` is never treated as a link slug.
Route::get('/report', [ReportController::class, 'show'])->name('short.report');
Route::post('/report', [ReportController::class, 'store'])->middleware('throttle:report');

// The short host serves redirects only — its root has nothing to redirect.
Route::get('/', fn () => abort(404));

// Catch EVERYTHING else on the short host so nothing leaks to the panel web
// routes (which carry sessions and are indexable). A real slug redirects; any
// other path (e.g. /sitemap.xml, multi-segment) 404s through the cookieless,
// noindex, no-store 'short' stack. Static files (favicons) are served by the
// web server before reaching here.
Route::get('/{slug}', RedirectController::class)
    ->where('slug', '.+')
    ->name('short.redirect');
