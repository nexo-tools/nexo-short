<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\RobotsController;
use Illuminate\Support\Facades\Route;

// Short host (ADR-001): redirects, branded 404 and robots.txt only. Cookieless
// stack (see the 'short' middleware group). All responses are noindex + no-store.

Route::get('/robots.txt', RobotsController::class);

// The short host serves redirects only — its root has nothing to redirect.
Route::get('/', fn () => abort(404));

Route::get('/{slug}', RedirectController::class)
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('short.redirect');
