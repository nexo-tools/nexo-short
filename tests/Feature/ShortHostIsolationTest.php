<?php

use App\Models\Link;
use App\Models\User;

/** Build a URL on www.<short host>. */
function wwwShortUrl(string $path = '/'): string
{
    return 'http://www.'.config('nexo.short_host').'/'.ltrim($path, '/');
}

it('AC-49: non-GET verbs on a short-host slug are 405 and never leak into the panel session stack', function (string $method) {
    // Only the GET redirect route is registered on the short host. Any other
    // verb must be Method-Not-Allowed — it may NOT fall through to a panel POST
    // handler (which would carry a session cookie) on the cookieless domain.
    Link::factory()->for(User::factory())->create([
        'slug' => 'novget',
        'is_active' => true,
        'target_url' => 'https://example.com',
    ]);

    $response = $this->{$method}(shortUrl('/novget'));

    $response->assertStatus(405);
    // Cookieless: no panel session cookie is set on the short host.
    expect($response->headers->getCookies())->toBeEmpty();
})->with(['post', 'put', 'delete']);

it('AC-47: a dotted/panel path on the short host 404s through the cookieless noindex stack (no leak to the panel)', function () {
    $response = $this->get(shortUrl('/sitemap.xml'));

    $response->assertStatus(404);
    // Short-host invariants hold: noindex + no-store, and NOT the panel sitemap.
    $response->assertHeader('X-Robots-Tag', 'noindex');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    expect($response->getContent())->not->toContain('<urlset');
    // Cookieless: the short stack sets no session cookie.
    expect($response->headers->getCookies())->toBeEmpty();
});

it('AC-47: an active link still redirects (catch-all did not break real slugs)', function () {
    Link::factory()->for(User::factory())->create(['slug' => 'still-works', 'is_active' => true, 'target_url' => 'https://example.com']);

    $this->get(shortUrl('/still-works'))
        ->assertStatus(302)
        ->assertHeader('Location', 'https://example.com');
});

it('AC-48: www.<short-host> canonicalizes to the apex short host (301), cookieless', function () {
    $response = $this->get(wwwShortUrl('/hb'));

    $response->assertStatus(301);
    $response->assertHeader('Location', 'https://'.config('nexo.short_host').'/hb');
    $response->assertHeader('X-Robots-Tag', 'noindex');
    expect($response->headers->getCookies())->toBeEmpty();
});

it('AC-48: www.<short-host> root also 301s to the apex', function () {
    $this->get(wwwShortUrl('/'))
        ->assertStatus(301)
        ->assertHeader('Location', 'https://'.config('nexo.short_host').'/');
});
