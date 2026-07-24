<?php

// Guardian: brand colors come from nexo-brand tokens (var(--nexo-*)), never raw
// hex in Blade views or app CSS. SVGs under public/ are not scanned.

use RecursiveDirectoryIterator as Dir;
use RecursiveIteratorIterator as Walk;

it('has no hardcoded hex colors in blade views or app css (use --nexo-* tokens)', function () {
    $roots = array_filter([resource_path('views'), resource_path('css')], 'is_dir');

    // Filenames allowed to contain literal hex:
    //  - the generated brand tokens and the shared chrome layer;
    //  - head.blade.php (the PWA <meta name="theme-color"> can't reference a CSS var);
    //  - the short-host shells (layout.blade.php = report, errors/404.blade.php).
    //    These are deliberately isolated from the panel bundle — they never load the
    //    token stylesheet (ADR-001 cookieless short host), so they self-contain their
    //    minimal dark styles. All PANEL surfaces must still use var(--nexo-*).
    $allowed = ['nexo-tokens.css', 'nexo-ui.css', 'head.blade.php', 'layout.blade.php', '404.blade.php'];

    $offenders = [];
    foreach ($roots as $root) {
        foreach (new Walk(new Dir($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if (! preg_match('/\.(blade\.php|css)$/', $file->getFilename())) {
                continue;
            }
            if (in_array($file->getFilename(), $allowed, true)) {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            if (preg_match_all('/#[0-9a-fA-F]{3,8}\b/', $contents, $m)) {
                $offenders[] = $file->getPathname().' -> '.implode(', ', array_unique($m[0]));
            }
        }
    }

    expect($offenders)->toBe([], "Hardcoded hex colors found (use var(--nexo-*)):\n".implode("\n", $offenders));
});
