<?php

use App\Models\Link;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function postLink(string $url = 'https://target.example'): array
{
    return ['target_url' => $url];
}

it('AC-34: rejects a URL flagged by Safe Browsing', function () {
    config(['nexo.safe_browsing.key' => 'test-key']);
    Http::fake(['safebrowsing.googleapis.com/*' => Http::response(['matches' => [['threatType' => 'MALWARE']]], 200)]);

    $this->actingAs(User::factory()->create())
        ->post(panelUrl('links'), postLink('https://evil.example'))
        ->assertSessionHasErrors('target_url');

    expect(Link::count())->toBe(0);
});

it('AC-34: allows a clean URL when the check is enabled', function () {
    config(['nexo.safe_browsing.key' => 'test-key']);
    Http::fake(['safebrowsing.googleapis.com/*' => Http::response([], 200)]);

    $this->actingAs(User::factory()->create())
        ->post(panelUrl('links'), postLink('https://clean.example'))
        ->assertRedirect(route('panel'));

    expect(Link::count())->toBe(1);
});

it('AC-35: proceeds without a Safe Browsing key (check disabled)', function () {
    config(['nexo.safe_browsing.key' => '']);
    Http::fake();

    $this->actingAs(User::factory()->create())
        ->post(panelUrl('links'), postLink())
        ->assertRedirect(route('panel'));

    expect(Link::count())->toBe(1);
    Http::assertNothingSent();
});

it('AC-36: fails open on API error by default (link created)', function () {
    config(['nexo.safe_browsing.key' => 'test-key', 'nexo.safe_browsing.fail_closed' => false]);
    Http::fake(['safebrowsing.googleapis.com/*' => Http::response('', 500)]);

    $this->actingAs(User::factory()->create())
        ->post(panelUrl('links'), postLink())
        ->assertRedirect(route('panel'));

    expect(Link::count())->toBe(1);
});

it('AC-36: fails closed on API error when configured (link rejected)', function () {
    config(['nexo.safe_browsing.key' => 'test-key', 'nexo.safe_browsing.fail_closed' => true]);
    Http::fake(['safebrowsing.googleapis.com/*' => Http::response('', 500)]);

    $this->actingAs(User::factory()->create())
        ->post(panelUrl('links'), postLink())
        ->assertSessionHasErrors('target_url');

    expect(Link::count())->toBe(0);
});
