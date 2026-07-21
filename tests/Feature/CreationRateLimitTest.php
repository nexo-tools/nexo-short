<?php

use App\Models\Link;
use App\Models\User;

it('AC-32: rate-limits link creation per user', function () {
    config(['nexo.create_rate.per_user' => 2, 'nexo.create_rate.per_ip' => 100]);
    $user = User::factory()->create();

    $this->actingAs($user)->post(panelUrl('links'), ['target_url' => 'https://a.example'])->assertRedirect();
    $this->actingAs($user)->post(panelUrl('links'), ['target_url' => 'https://b.example'])->assertRedirect();

    $this->actingAs($user)->post(panelUrl('links'), ['target_url' => 'https://c.example'])->assertStatus(429);

    expect(Link::count())->toBe(2);
});

it('AC-33: rate-limits link creation per IP', function () {
    config(['nexo.create_rate.per_user' => 100, 'nexo.create_rate.per_ip' => 2]);
    $user = User::factory()->create();

    $this->actingAs($user)->post(panelUrl('links'), ['target_url' => 'https://a.example'])->assertRedirect();
    $this->actingAs($user)->post(panelUrl('links'), ['target_url' => 'https://b.example'])->assertRedirect();

    $this->actingAs($user)->post(panelUrl('links'), ['target_url' => 'https://c.example'])->assertStatus(429);

    expect(Link::count())->toBe(2);
});
