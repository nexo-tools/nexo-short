<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\LegalController;
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

    // Legal pages (nexo-ui standard): Spanish-first URLs, ecosystem-wide route
    // names. The old English paths were live and are in the published sitemap,
    // so they 301 instead of breaking.
    Route::get('privacidad', [LegalController::class, 'privacy'])->name('legal.privacy');
    Route::get('terminos', [LegalController::class, 'terms'])->name('legal.terms');
    Route::permanentRedirect('privacy', 'privacidad');
    Route::permanentRedirect('terms', 'terminos');

    // Help center (nexo-ui). Panel host only, before any catch-all (there is none
    // on this host; the short-host {slug} catch-all lives in routes/short.php).
    Route::get('help', HelpController::class)->name('help');

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

        // Password recovery, local accounts only. This tool shipped without it:
        // a self-hosted instance in local mode had no way back from a forgotten
        // password except the operator editing the database. On the hosted
        // instance the account lives in Nexo ID and recovery belongs there, so
        // the whole flow rides EnsureLocalAuth with the credential login.
        Route::middleware(EnsureLocalAuth::class)->group(function () {
            Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
            Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('throttle:5,1')->name('password.email');

            Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
            Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->middleware('throttle:5,1')->name('password.store');
        });
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        // Email verification. Not middleware on the panel: shortening a link is
        // the product, and gating it on an unread mail would be our problem,
        // not the person's. It is what makes the reset path trustworthy.
        Route::get('verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('verify-email/send', [EmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1')->name('verification.send');

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
