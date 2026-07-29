<?php

// Guardian: dark mode is a first-class theme, not an afterthought on the landing.
//
// Two things are checked, because they fail in different ways:
//   1. The theme plumbing: <html> gets data-theme before paint, and the tokens
//      file declares both palettes. Without this the page flashes the wrong theme.
//   2. Coverage: the views do not paint surfaces with theme-blind utilities.
//      A view that hardcodes `bg-white` looks fine in light and breaks in dark —
//      exactly the drift NoHardcodedColorsTest cannot see (it only scans hex).
//
// Pest note: toContain() is variadic — a second argument is another needle, not
// a failure message — so human-readable messages go through toBeTrue()/toBe().

use RecursiveDirectoryIterator as Dir;
use RecursiveIteratorIterator as Walk;

it('stamps the theme before paint and ships both palettes on the panel host', function () {
    // Panel host only: the short host is deliberately theme-less (ADR-001) — it
    // ships a fixed dark shell with no theme-init, see HostChromeIsolationTest.
    $html = $this->get(panelUrl('/'))->assertOk()->getContent();

    expect(str_contains($html, 'data-theme'))->toBeTrue('The theme-init snippet must stamp data-theme on <html> before paint.');

    $tokens = resource_path('css/nexo-tokens.css');
    expect(is_file($tokens))->toBeTrue('nexo-tokens.css is missing — the tool is not on the brand tokens.');

    $css = (string) file_get_contents($tokens);
    expect(str_contains($css, '--nexo-'))->toBeTrue('Token variables missing from nexo-tokens.css.');
    expect(str_contains($css, 'dark'))->toBeTrue('nexo-tokens.css declares no dark palette.');
});

it('paints every view through the token layer, so they work in both themes', function () {
    // Utilities that hardcode a light-only surface, bypassing the tokens.
    $themeBlind = ['bg-white', 'bg-gray-50', 'bg-gray-100', 'text-black'];

    // The whole view tree, not a hand-picked list of key pages: the surface that
    // rots in dark mode is always the one nobody thought to put on the list. The
    // short-host shells need no carve-out here — they use no Tailwind at all.
    $offenders = [];

    /** @var SplFileInfo $file */
    foreach (new Walk(new Dir(resource_path('views'), FilesystemIterator::SKIP_DOTS)) as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());

        foreach ($themeBlind as $utility) {
            // Allowed when paired with a dark: variant on the same element, so a
            // surface that must stay light in both themes says so out loud.
            if (preg_match('/\b'.preg_quote($utility, '/').'\b(?![^"\']*dark:)/', $contents)) {
                $offenders[] = $file->getPathname().' -> '.$utility;
            }
        }
    }

    expect($offenders)->toBe([], "Theme-blind utilities found (use token classes like bg-bg/bg-surface, or pair with dark:):\n".implode("\n", $offenders));
});
