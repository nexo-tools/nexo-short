<?php

use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use App\Services\ClickStats;

it('AC-28: shows totals, unique visitors and breakdowns for the owner link', function () {
    $user = User::factory()->create();
    $link = Link::factory()->for($user)->create(['slug' => 'stat1']);

    Click::factory()->for($link)->create(['visitor_hash' => 'aaa', 'device' => 'desktop', 'country' => 'ES']);
    Click::factory()->for($link)->create(['visitor_hash' => 'aaa', 'device' => 'desktop', 'country' => 'ES']);
    Click::factory()->for($link)->create(['visitor_hash' => 'bbb', 'device' => 'mobile', 'country' => 'PT']);

    $this->actingAs($user)->get(panelUrl("links/{$link->id}/stats"))
        ->assertOk()
        ->assertSee(__('Total clicks'))
        ->assertSee(__('Unique visitors'))
        ->assertSee('ES')
        ->assertSee(__('By device'));
});

it('AC-28: the ClickStats service computes totals, uniques and per-day series', function () {
    $link = Link::factory()->create();
    Click::factory()->for($link)->create(['visitor_hash' => 'x', 'device' => 'desktop']);
    Click::factory()->for($link)->create(['visitor_hash' => 'x', 'device' => 'desktop']);
    Click::factory()->for($link)->create(['visitor_hash' => 'y', 'device' => 'mobile']);

    $stats = app(ClickStats::class)->forLink($link);

    expect($stats['total'])->toBe(3);
    expect($stats['unique'])->toBe(2);
    expect($stats['by_device'])->toBe(['desktop' => 2, 'mobile' => 1]);
    expect(array_sum($stats['per_day']))->toBe(3);
});

it('AC-29: the bot filter excludes bot clicks from the totals', function () {
    $link = Link::factory()->create();
    Click::factory()->for($link)->count(2)->create(['device' => 'desktop']);
    Click::factory()->for($link)->count(3)->bot()->create();

    $service = app(ClickStats::class);

    expect($service->forLink($link, excludeBots: false)['total'])->toBe(5);
    expect($service->forLink($link, excludeBots: true)['total'])->toBe(2);
});

it('AC-30: the stats page issues no external requests (inline SVG, CSP clean)', function () {
    $user = User::factory()->create();
    $link = Link::factory()->for($user)->create(['slug' => 'ext1']);
    Click::factory()->for($link)->create();

    $response = $this->actingAs($user)->get(panelUrl("links/{$link->id}/stats"));

    $response->assertOk()->assertSee('<svg', false); // inline chart, not an external image/lib

    // Zero external requests. The shared panel chrome adds one inline,
    // hash-allow-listed theme-init <script> and same-origin <img src> marks —
    // both same-origin, so no network egress. What must never appear is an
    // external script or an off-origin resource src, and the CSP stays self-only.
    $body = (string) $response->getContent();
    expect($body)->not->toContain('<script src')
        ->and($body)->not->toContain('src="http')
        ->and($body)->not->toContain("src='http");

    $csp = $response->headers->get('Content-Security-Policy');
    expect($csp)->toContain("default-src 'self'")->not->toContain('://');
});

it('AC-28: a user cannot view another user\'s stats', function () {
    $me = User::factory()->create();
    $link = Link::factory()->for(User::factory())->create();

    $this->actingAs($me)->get(panelUrl("links/{$link->id}/stats"))->assertForbidden();
});
