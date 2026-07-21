<?php

use App\Models\Link;
use App\Models\User;
use App\Services\LinkService;

it('AC-12: an unauthenticated user cannot create a link', function () {
    $response = $this->post(panelUrl('links'), ['target_url' => 'https://example.com']);

    $response->assertRedirect(route('login'));
    expect(Link::count())->toBe(0);
});

it('AC-15: an authenticated user creates a link that redirects on the short host', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(panelUrl('links'), [
        'target_url' => 'https://example.com/dest',
    ])->assertRedirect(route('panel'));

    $link = Link::sole();
    expect($link->user_id)->toBe($user->id);

    $this->get(shortUrl($link->slug))
        ->assertStatus(302)
        ->assertHeader('Location', 'https://example.com/dest');
});

it('AC-15: a custom slug is used when provided', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(panelUrl('links'), [
        'target_url' => 'https://example.com',
        'custom_slug' => 'promo-2026',
    ]);

    expect(Link::where('slug', 'promo-2026')->exists())->toBeTrue();
});

it('AC-15: a reserved custom slug is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(panelUrl('links'), [
        'target_url' => 'https://example.com',
        'custom_slug' => 'admin',
    ])->assertSessionHasErrors('custom_slug');

    expect(Link::count())->toBe(0);
});

it('AC-16: a user lists only their own links', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Link::factory()->for($me)->create(['slug' => 'mine1']);
    Link::factory()->for($other)->create(['slug' => 'theirs1']);

    $this->actingAs($me)->get(panelUrl('panel'))
        ->assertSee('mine1')
        ->assertDontSee('theirs1');
});

it('AC-16: deactivating a link makes it 404 on the short host', function () {
    $user = User::factory()->create();
    $link = Link::factory()->for($user)->create(['slug' => 'live1', 'is_active' => true]);

    $this->get(shortUrl('live1'))->assertStatus(302);

    $this->actingAs($user)->patch(panelUrl("links/{$link->id}/deactivate"))
        ->assertRedirect(route('panel'));

    expect($link->fresh()->is_active)->toBeFalse();
    $this->get(shortUrl('live1'))->assertStatus(404);
});

it('AC-16: a user cannot deactivate another user\'s link', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $link = Link::factory()->for($other)->create();

    $this->actingAs($me)->patch(panelUrl("links/{$link->id}/deactivate"))->assertForbidden();

    expect($link->fresh()->is_active)->toBeTrue();
});

it('AC-17: the LinkService creates and deactivates without any HTTP context', function () {
    $user = User::factory()->create();
    $service = app(LinkService::class);

    $link = $service->create($user, 'https://example.com/headless');
    expect($link->user_id)->toBe($user->id);
    expect($link->is_active)->toBeTrue();
    expect($link->slug)->toMatch('/^[0-9A-Za-z]{6,7}$/');

    $service->deactivate($link);
    expect($link->fresh()->is_active)->toBeFalse();

    expect($service->forUser($user))->toHaveCount(1);
});
