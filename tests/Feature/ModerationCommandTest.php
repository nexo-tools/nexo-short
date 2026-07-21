<?php

use App\Models\Link;
use App\Models\User;

it('AC-40: the operator can deactivate and reactivate any link by slug', function () {
    $link = Link::factory()->for(User::factory())->create(['slug' => 'modme', 'is_active' => true]);

    $this->get(shortUrl('modme'))->assertStatus(302);

    $this->artisan('nexo:link-deactivate modme')->assertExitCode(0);
    expect($link->fresh()->is_active)->toBeFalse();
    $this->get(shortUrl('modme'))->assertStatus(404); // dead immediately

    $this->artisan('nexo:link-activate modme')->assertExitCode(0);
    expect($link->fresh()->is_active)->toBeTrue();
    $this->get(shortUrl('modme'))->assertStatus(302);
});

it('AC-40: the command fails cleanly for an unknown slug', function () {
    $this->artisan('nexo:link-deactivate ghost')->assertExitCode(1);
});
