<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    // Feature tests render Blade without a built frontend; skip the Vite manifest
    // so panel views that @vite the chrome bundle render (assets aren't asserted).
    ->beforeEach(fn () => $this->withoutVite())
    ->in('Feature');

/** Build a URL on the configured short host (redirect host). */
function shortUrl(string $path = '/'): string
{
    return 'http://'.config('nexo.short_host').'/'.ltrim($path, '/');
}

/** Build a URL on the configured panel host (landing/auth/panel). */
function panelUrl(string $path = '/'): string
{
    return 'http://'.config('nexo.panel_host').'/'.ltrim($path, '/');
}
