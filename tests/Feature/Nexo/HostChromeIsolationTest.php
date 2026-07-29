<?php

use App\Models\Link;
use App\Models\User;

// Guardian (critical, ADR-001): the Nexo chrome (header/app-switcher/footer),
// the theme-init and the built frontend live ONLY on the panel host. The short
// host serves cookieless redirects/report/404 with none of it, and keeps the
// tightest CSP. This locks in the two-host isolation while carrying the brand.

$chromeMarkers = ['nexo-header', 'nexo-footer', 'nexo-menu', '/ecosystem/', 'data-theme', 'nexoTheme'];

it('keeps the short-host report free of panel chrome and theme-init', function () use ($chromeMarkers) {
    $html = $this->get(shortUrl('/report'))->assertOk()->getContent();

    foreach ($chromeMarkers as $marker) {
        expect($html)->not->toContain($marker);
    }
});

it('keeps the short-host 404 free of panel chrome and theme-init', function () use ($chromeMarkers) {
    $html = $this->get(shortUrl('/no-such-slug'))->assertNotFound()->getContent();

    foreach ($chromeMarkers as $marker) {
        expect($html)->not->toContain($marker);
    }
});

it('keeps every OTHER short-host error page chrome-free too, not just the 404', function () use ($chromeMarkers) {
    // The report form is the one short-host surface a user can rate-limit, so a
    // 429 renders errors/429 ON the cookieless domain. Every error view shares
    // components/error-layout, which is where the host branch lives — without it
    // the panel shell (chrome + theme-init + Vite bundle) leaks onto nxo.li.
    config()->set('nexo.report_rate.per_ip', 1);

    $payload = ['slug' => 'abc123', 'reason' => 'spam'];
    $this->post(shortUrl('/report'), $payload)->assertOk();

    $html = $this->post(shortUrl('/report'), $payload)->assertStatus(429)->getContent();

    foreach ($chromeMarkers as $marker) {
        expect($html)->not->toContain($marker);
    }
});

it('keeps chrome off the redirect itself (no body leak)', function () {
    Link::factory()->for(User::factory())->create(['slug' => 'iso1', 'is_active' => true, 'target_url' => 'https://example.com']);

    $html = $this->get(shortUrl('/iso1'))->assertStatus(302)->getContent();

    expect($html)->not->toContain('nexo-header');
});

it('renders the full chrome on the panel host', function () {
    $html = $this->get(panelUrl('/'))->assertOk()->getContent();

    expect($html)->toContain('nexo-header')
        ->and($html)->toContain('nexo-footer')
        ->and($html)->toContain('nexo-menu')
        ->and($html)->toContain('/ecosystem/nexoshort.svg')
        ->and($html)->toContain('data-theme');
});

it('keeps the short-host CSP tighter than the panel (no eval, no inline-script hash)', function () {
    $shortCsp = (string) $this->get(shortUrl('/report'))->headers->get('Content-Security-Policy');
    $panelCsp = (string) $this->get(panelUrl('/'))->headers->get('Content-Security-Policy');

    // Short host: script-src stays 'self' only.
    expect($shortCsp)->toContain("script-src 'self'")
        ->and($shortCsp)->not->toContain('unsafe-eval')
        ->and($shortCsp)->not->toContain('sha256-');

    // Panel host: Alpine (unsafe-eval) + the hash-allow-listed theme-init.
    expect($panelCsp)->toContain('unsafe-eval')
        ->and($panelCsp)->toContain('sha256-');
});
