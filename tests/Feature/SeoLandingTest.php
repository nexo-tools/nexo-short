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
        ->toContain(route('legal.privacy'))
        ->toContain(route('legal.terms'))
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

it('emits JSON-LD that actually parses', function () {
    // The block used to render compiled Blade internals instead of JSON: keys
    // like `@context` are Blade directives (Laravel 11 added `@context`), so the
    // template was compiling them away and shipping broken structured data on
    // every page. Asserting the tag exists is not enough — it has to parse.
    $html = $this->get('/')->assertOk()->getContent();

    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    expect($matches[1])->not->toBeEmpty('No JSON-LD block was rendered.');

    foreach ($matches[1] as $block) {
        $decoded = json_decode($block, true);
        expect(json_last_error())->toBe(JSON_ERROR_NONE, 'JSON-LD is not valid JSON: '.substr($block, 0, 200));
        expect($decoded['@context'] ?? null)->toBe('https://schema.org');
        expect($decoded['@type'] ?? null)->not->toBeNull('JSON-LD has no @type.');
    }
});
