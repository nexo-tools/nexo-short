<?php

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

it('has the expected links columns', function () {
    expect(Schema::hasColumns('links', [
        'id', 'user_id', 'slug', 'target_url', 'is_active', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

it('enforces a unique slug (the redirect lookup key)', function () {
    Link::factory()->create(['slug' => 'dup-slug']);

    expect(fn () => Link::factory()->create(['slug' => 'dup-slug']))
        ->toThrow(QueryException::class);
});

it('defaults is_active to true', function () {
    $link = Link::factory()->create();

    expect($link->fresh()->is_active)->toBeTrue();
});

it('belongs to a user and cascades on user delete', function () {
    $user = User::factory()->create();
    $link = Link::factory()->for($user)->create();

    expect($link->user->is($user))->toBeTrue();

    $user->delete();
    expect(Link::find($link->id))->toBeNull();
});
