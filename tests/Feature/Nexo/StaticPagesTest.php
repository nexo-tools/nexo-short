<?php

use Illuminate\Support\Facades\Route;

// Guardian: the static surfaces every Nexo tool must have — error pages with the
// tool's identity, and legal pages — exist, answer, and are translated.
//
// Why it exists: 403/404/419/429/500/503 and privacy/terms are the pages nobody
// opens while building, so they rot silently. 419 and 429 in particular are the
// ones a real user hits (expired session, rate limit) and the ones most often
// left as Laravel's untranslated default.
//
// Two-host note (ADR-001): the error views are shared, and the branch that keeps
// the short host on its self-contained shell lives in components/error-layout.
// Everything below is asserted on the PANEL host; that the short host stays free
// of chrome is HostChromeIsolationTest's job.

$codes = [403, 404, 419, 429, 500, 503];

it('ships an error view for every code the standard requires', function () use ($codes) {
    $missing = array_values(array_filter(
        $codes,
        fn (int $code) => ! is_file(resource_path("views/errors/{$code}.blade.php"))
    ));

    expect($missing)->toBe([], 'Missing error views (copy from templates/nexo-ui/pages/errors/): '.implode(', ', $missing));
});

it('keeps every error page translatable and free of template placeholders', function () use ($codes) {
    foreach ($codes as $code) {
        $contents = (string) file_get_contents(resource_path("views/errors/{$code}.blade.php"));

        // Strings go through __() so the generator can translate them.
        expect(str_contains($contents, '__('))->toBeTrue("errors/{$code}.blade.php has hardcoded strings — wrap them in __().");
        expect(str_contains($contents, '[COMPLETAR'))->toBeFalse("errors/{$code}.blade.php still has a template placeholder.");
    }
});

it('serves a branded 404 on the panel host instead of the framework default', function () {
    $html = $this->get(panelUrl('/this-path-does-not-exist-'.uniqid()))
        ->assertNotFound()
        ->getContent();

    // The chrome renders, so the page belongs to the product.
    expect($html)->toContain('404')
        ->and($html)->toContain('nexo-header')
        ->and($html)->not->toContain('Whoops, looks like something went wrong');
});

it('serves the legal pages and links them from each other', function () {
    foreach (['legal.privacy', 'legal.terms'] as $route) {
        expect(Route::has($route))->toBeTrue("Route {$route} is not registered.");

        $html = $this->get(panelUrl(route($route, absolute: false)))->assertOk()->getContent();

        expect(str_contains($html, '[COMPLETAR'))->toBeFalse("The {$route} page still ships a template placeholder — write this tool's real content before shipping.");
        expect($html)->toContain(route('legal.privacy'))
            ->and($html)->toContain(route('legal.terms'));
    }
});

it('translates the legal content in every supported locale', function () {
    foreach (array_keys(config('nexo.locales')) as $locale) {
        $path = lang_path("{$locale}/legal.php");
        expect(is_file($path))->toBeTrue("lang/{$locale}/legal.php is missing.");

        $content = require $path;

        foreach (['privacy', 'terms'] as $page) {
            expect($content[$page]['title'] ?? null)->not->toBeNull("legal.{$page}.title missing in {$locale}.");
            expect($content[$page]['sections'] ?? [])->not->toBeEmpty("legal.{$page}.sections empty in {$locale}.");
        }

        expect(str_contains((string) json_encode($content), '[COMPLETAR'))->toBeFalse("lang/{$locale}/legal.php still has template placeholders.");
    }
});

it('links the legal pages from the footer and lists them in the sitemap', function () {
    $landing = $this->get(panelUrl('/'))->assertOk()->getContent();

    expect($landing)->toContain(route('legal.privacy'))
        ->and($landing)->toContain(route('legal.terms'));

    $sitemap = $this->get(panelUrl('sitemap.xml'))->assertOk()->getContent();

    expect($sitemap)->toContain(route('legal.privacy'))
        ->and($sitemap)->toContain(route('legal.terms'));
});
