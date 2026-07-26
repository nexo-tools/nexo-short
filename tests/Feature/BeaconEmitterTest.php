<?php

// The cookieless beacon emitter is wired into the PANEL host only. It fires when
// this instance opts in (NEXO_BEACON_ENABLED) and respects Do Not Track. The
// isolated short host (redirects/report/404) never emits it and its CSP is never
// widened — the two-host isolation (ADR-001) still holds.

it('renders the beacon metas on the panel host only when enabled', function () {
    config(['nexo.beacon.enabled' => true]);

    $this->get(panelUrl('/'))
        ->assertOk()
        ->assertSee('name="nexo:beacon-endpoint"', false)
        ->assertSee('name="nexo:beacon-origin" content="nexoshort"', false);
});

it('renders no beacon metas on the panel when the beacon is off (default)', function () {
    config(['nexo.beacon.enabled' => false]);

    $this->get(panelUrl('/'))
        ->assertOk()
        ->assertDontSee('nexo:beacon-endpoint', false)
        ->assertDontSee('nexo:beacon-origin', false);
});

it('never emits the beacon on the isolated short host, even when enabled', function () {
    config(['nexo.beacon.enabled' => true]);

    $report = $this->get(shortUrl('/report'));
    $notFound = $this->get(shortUrl('/no-such-slug'));

    foreach ([$report, $notFound] as $response) {
        expect($response->getContent())
            ->not->toContain('nexo:beacon-endpoint')
            ->not->toContain('nexo:beacon-origin');
    }

    // The short-host CSP is never widened for the hub — it stays connect-src 'self'.
    expect((string) $report->headers->get('Content-Security-Policy'))
        ->toContain("connect-src 'self'")
        ->not->toContain('nexotools.alvarocdev.com');
});

it('ships the shareable snippet in the app bundle and honours Do Not Track', function () {
    $source = file_get_contents(resource_path('js/nexo-beacon.js'));

    expect($source)
        ->toContain('doNotTrack')
        ->toContain('globalPrivacyControl')
        ->toContain('sendBeacon')
        ->toContain("event: 'pageview'");

    expect(file_get_contents(resource_path('js/app.js')))->toContain('nexo-beacon.js');
});
