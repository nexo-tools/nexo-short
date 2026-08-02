<?php

// Guardian: brand colors come from nexo-brand tokens (var(--nexo-*)), never raw
// hex in Blade views or app CSS. SVGs under public/ are not scanned.

use RecursiveDirectoryIterator as Dir;
use RecursiveIteratorIterator as Walk;

it('has no hardcoded hex colors in blade views or app css (use --nexo-* tokens)', function () {
    $roots = array_filter([resource_path('views'), resource_path('css')], 'is_dir');

    // Filenames allowed to contain literal hex:
    //  - the generated brand tokens and the shared chrome layer;
    //  - nexo-seo.blade.php (the PWA <meta name="theme-color"> can't reference a CSS var);
    //  - the two short-host shells (layout = report form, short-error-layout = 404 and
    //    any other short-host error). These are deliberately isolated from the panel
    //    bundle — they never load the token stylesheet (ADR-001 cookieless short host),
    //    so they self-contain their minimal dark styles, in the brand violet. All
    //    PANEL surfaces, error pages included, must still use var(--nexo-*).
    $allowed = ['nexo-tokens.css', 'nexo-ui.css', 'nexo-seo.blade.php', 'layout.blade.php', 'short-error-layout.blade.php'];

    // Directories allowed to contain literal hex, relative to resource_path():
    // mail clients strip <style> and know nothing about the tokens, so the
    // family mail template inlines hex on purpose (templates/nexo-mail/README).
    $allowedPrefixes = ['views/emails/'];

    $offenders = [];
    foreach ($roots as $root) {
        foreach (new Walk(new Dir($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if (! preg_match('/\.(blade\.php|css)$/', $file->getFilename())) {
                continue;
            }
            if (in_array($file->getFilename(), $allowed, true)) {
                continue;
            }
            $relative = str_replace(resource_path().'/', '', $file->getPathname());
            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($relative, $prefix)) {
                    continue 2;
                }
            }
            $contents = file_get_contents($file->getPathname());
            if (preg_match_all('/#[0-9a-fA-F]{3,8}\b/', $contents, $m)) {
                $offenders[] = $file->getPathname().' -> '.implode(', ', array_unique($m[0]));
            }
        }
    }

    expect($offenders)->toBe([], "Hardcoded hex colors found (use var(--nexo-*)):\n".implode("\n", $offenders));
});
