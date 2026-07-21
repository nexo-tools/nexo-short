<?php

use App\Models\Click;
use App\Models\Link;
use App\Models\User;

function activeLink(string $slug = 'log1'): Link
{
    return Link::factory()->for(User::factory())->create(['slug' => $slug, 'is_active' => true]);
}

it('AC-21: logs exactly one click for an active redirect', function () {
    $link = activeLink();

    $this->get(shortUrl($link->slug))->assertStatus(302);

    expect(Click::where('link_id', $link->id)->count())->toBe(1);
});

it('AC-27: logging does not change redirect semantics', function () {
    $link = activeLink('sem1');

    $response = $this->get(shortUrl('sem1'));

    $response->assertStatus(302)->assertHeader('X-Robots-Tag', 'noindex');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('AC-27: an inactive or unknown slug logs no click', function () {
    $link = Link::factory()->for(User::factory())->create(['slug' => 'off1', 'is_active' => false]);

    $this->get(shortUrl('off1'))->assertStatus(404);
    $this->get(shortUrl('missing'))->assertStatus(404);

    expect(Click::count())->toBe(0);
});

it('AC-23: the visitor hash is stable within a day and rotates across days', function () {
    $link = activeLink('rot1');

    $this->get(shortUrl('rot1'));
    $this->get(shortUrl('rot1'));
    $hashes = Click::where('link_id', $link->id)->pluck('visitor_hash');
    expect($hashes[0])->toBe($hashes[1]); // same visitor, same day

    $this->travel(1)->days();
    $this->get(shortUrl('rot1'));
    $tomorrow = Click::where('link_id', $link->id)->latest('id')->first()->visitor_hash;

    expect($tomorrow)->not->toBe($hashes[0]); // different day → different hash
});

it('AC-25: stores CF-IPCountry when present and null when absent', function () {
    activeLink('geo1');

    // No header first (withHeaders persists across requests in one test).
    $this->get(shortUrl('geo1'));
    expect(Click::latest('id')->first()->country)->toBeNull();

    $this->withHeaders(['CF-IPCountry' => 'es'])->get(shortUrl('geo1'));
    expect(Click::latest('id')->first()->country)->toBe('ES');
});

it('AC-26: stores the external referrer host only, null for same host or missing', function () {
    activeLink('ref1');

    $this->get(shortUrl('ref1'));
    expect(Click::latest('id')->first()->referrer_host)->toBeNull();

    $this->withHeaders(['referer' => 'https://www.twitter.com/some/post'])->get(shortUrl('ref1'));
    expect(Click::latest('id')->first()->referrer_host)->toBe('twitter.com');
});
