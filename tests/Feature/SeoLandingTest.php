<?php

it('AC-44: the landing carries a complete SEO head', function () {
    $response = $this->get(panelUrl('/'));
    $response->assertOk();
    $html = $response->getContent();

    expect($html)
        ->toContain('<title>')
        ->toContain('name="description"')
        ->toContain('rel="canonical"')
        ->toContain('property="og:image"')
        ->toContain('rel="alternate" hreflang="es"')
        ->toContain('rel="apple-touch-icon"');

    // JSON-LD block parses and describes the app.
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
    $jsonLd = json_decode($m[1] ?? '', true);
    expect($jsonLd)->toBeArray();
    expect($jsonLd['@type'])->toBe('SoftwareApplication');
    expect($jsonLd['url'])->toStartWith('http');
});

it('AC-44: the landing is indexable (no noindex) and self-contained', function () {
    $response = $this->get(panelUrl('/'));

    $response->assertOk()->assertHeaderMissing('X-Robots-Tag');
    expect($response->getContent())
        ->not->toContain('<script src=')
        ->not->toContain('name="robots" content="noindex"');
});

it('AC-45: the sitemap lists the public pages with language alternates', function () {
    $response = $this->get(panelUrl('sitemap.xml'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('xml');

    $xml = $response->getContent();
    expect($xml)
        ->toContain('<urlset')
        ->toContain(route('landing'))
        ->toContain(route('privacy'))
        ->toContain(route('terms'))
        ->toContain('hreflang="pt"');

    // Well-formed XML.
    expect(simplexml_load_string($xml))->not->toBeFalse();
});

it('AC-46: panel robots.txt allows crawling and points at the sitemap', function () {
    $response = $this->get(panelUrl('robots.txt'));

    $response->assertOk();
    expect($response->getContent())
        ->toContain('Allow: /')
        ->toContain('Sitemap:')
        ->not->toContain('Disallow: /');
});

it('AC-46: the short host robots.txt still disallows everything', function () {
    $response = $this->get(shortUrl('robots.txt'));

    $response->assertOk();
    expect($response->getContent())->toContain('Disallow: /');
});
