<?php

use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use App\Support\ClickRecorder;
use Illuminate\Http\Request;

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

it('AC-1: a Referer host over 255 chars still 302s and stores a host truncated to the column width', function () {
    $link = Link::factory()->for(User::factory())->create([
        'slug' => 'longref',
        'target_url' => 'https://example.com/landing',
        'is_active' => true,
    ]);

    // Host far beyond the referrer_host column (255). The redirect must survive.
    $host = str_repeat('a', 300).'.com';
    $response = $this->withHeaders(['referer' => 'https://'.$host.'/path'])
        ->get(shortUrl('/'.$link->slug));

    $response->assertStatus(302);
    $response->assertHeader('Location', 'https://example.com/landing');

    $stored = (string) Click::where('link_id', $link->id)->value('referrer_host');
    expect(mb_strlen($stored))->toBeLessThanOrEqual(255);
});

it('AC-1: a click-logging failure never breaks the 302 (metrics are best-effort)', function () {
    $link = Link::factory()->for(User::factory())->create([
        'slug' => 'safe302',
        'target_url' => 'https://example.com/x',
        'is_active' => true,
    ]);

    // A recorder that always throws stands in for any metrics/DB failure on the
    // hot path; the controller must still emit the redirect.
    $this->app->instance(ClickRecorder::class, new class extends ClickRecorder
    {
        public function record(Request $request, Link $link): void
        {
            throw new RuntimeException('metrics down');
        }
    });

    $this->get(shortUrl('/'.$link->slug))
        ->assertStatus(302)
        ->assertHeader('Location', 'https://example.com/x');
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
