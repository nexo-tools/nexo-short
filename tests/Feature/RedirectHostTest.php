<?php

use App\Models\Link;
use App\Models\User;

/** Build a URL on the configured short host. */
function shortUrl(string $path = '/'): string
{
    return 'http://'.config('nexo.short_host').$path;
}

function panelUrl(string $path = '/'): string
{
    return 'http://'.config('nexo.panel_host').$path;
}

it('AC-1: active link returns 302 to the target with no-store', function () {
    $link = Link::factory()->for(User::factory())->create([
        'slug' => 'active1',
        'target_url' => 'https://example.com/landing',
        'is_active' => true,
    ]);

    $response = $this->get(shortUrl('/'.$link->slug));

    $response->assertStatus(302);
    $response->assertHeader('Location', 'https://example.com/landing');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('AC-2: redirect and 404 both carry X-Robots-Tag noindex', function () {
    $link = Link::factory()->for(User::factory())->create(['slug' => 'active2']);

    $this->get(shortUrl('/'.$link->slug))->assertHeader('X-Robots-Tag', 'noindex');
    $this->get(shortUrl('/does-not-exist'))->assertHeader('X-Robots-Tag', 'noindex');
});

it('AC-3: unknown slug returns the branded 404', function () {
    $response = $this->get(shortUrl('/nope404'));

    $response->assertStatus(404);
    $response->assertSee(__('Link not found'));
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('AC-4: a deactivated link 404s on the very next request (kill-switch)', function () {
    $link = Link::factory()->for(User::factory())->create([
        'slug' => 'killme',
        'is_active' => true,
    ]);

    $this->get(shortUrl('/killme'))->assertStatus(302);

    $link->update(['is_active' => false]);

    $this->get(shortUrl('/killme'))->assertStatus(404);
});

it('AC-5: the redirect controller contains no 301/permanent redirect', function () {
    $source = file_get_contents(app_path('Http/Controllers/RedirectController.php'));

    expect($source)
        ->not->toContain('301')
        ->not->toContain('permanentRedirect')
        ->not->toContain('Response::HTTP_MOVED_PERMANENTLY');
});

it('AC-6: short host robots.txt disallows everything', function () {
    $response = $this->get(shortUrl('/robots.txt'));

    $response->assertOk();
    expect($response->getContent())->toContain('Disallow: /');
    $response->assertHeader('X-Robots-Tag', 'noindex');
});

it('AC-6: panel responses do NOT carry X-Robots-Tag noindex', function () {
    $response = $this->get(panelUrl('/'));

    $response->assertOk();
    expect($response->headers->has('X-Robots-Tag'))->toBeFalse();
});
