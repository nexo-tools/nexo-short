<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PanelSeoController;
use App\Http\Middleware\EnsureLocalAuth;
use App\Http\Middleware\EnsureRegistrationOpen;
use Illuminate\Support\Facades\Route;

// Panel-host SEO surface (indexable — ADR-008). The short host's own robots.txt
// (Disallow all) is domain-scoped and registered first, so it wins for nxo.li.
Route::get('robots.txt', [PanelSeoController::class, 'robots']);
Route::get('sitemap.xml', [PanelSeoController::class, 'sitemap']);

// Panel host: landing, standalone local auth (ADR-003) and the panel. The short
// host lives in routes/short.php. All panel routes negotiate locale (setlocale).
Route::middleware('setlocale')->group(function () {
    Route::get('/', fn () => view('welcome'))->name('landing');
    Route::get('privacy', fn () => view('privacy'))->name('privacy');
    Route::get('terms', fn () => view('terms'))->name('terms');

    Route::middleware('guest')->group(function () {
        // The login page stays reachable in every mode (it hosts the SSO button);
        // the local credential POST is closed when the instance is SSO-only.
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware(EnsureLocalAuth::class);

        // Registration: off in SSO-only mode, and otherwise closable by env
        // (self-host default open; hosted instance keeps it closed).
        Route::middleware([EnsureLocalAuth::class, EnsureRegistrationOpen::class])->group(function () {
            Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
            Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1');
        });
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        // Panel: list / create / deactivate links over the LinkService (ADR-007).
        Route::get('panel', [LinkController::class, 'index'])->name('panel');
        Route::post('links', [LinkController::class, 'store'])->middleware('throttle:link-creation')->name('links.store');
        Route::patch('links/{link}/deactivate', [LinkController::class, 'deactivate'])->name('links.deactivate');
        Route::get('links/{link}/stats', [LinkController::class, 'stats'])->name('links.stats');
    });
});

// Nexo ID SSO (reusable client template, ADR-003). No-op when NEXO_SSO_ENABLED is
// false (default): standalone auth stays intact and no SSO routes are registered.
// These routes live on the panel host (they need a session — the short host is
// cookieless), so the provider's redirect URI must be on the panel host.
require __DIR__.'/nexo-sso.php';
