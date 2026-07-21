<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
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
