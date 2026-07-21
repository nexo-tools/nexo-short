<?php

use App\Models\Link;
use App\Models\User;
use App\Rules\LinkTargetUrl;
use App\Rules\ReservedSlug;
use App\Support\SlugGenerator;
use Illuminate\Support\Facades\Validator;

it('AC-7: generates unique base62 slugs within the configured length range', function () {
    config(['nexo.slug.min_length' => 6, 'nexo.slug.max_length' => 7]);
    $user = User::factory()->create();
    $generator = new SlugGenerator;

    $slugs = [];
    for ($i = 0; $i < 50; $i++) {
        $slug = $generator->generate();
        expect($slug)->toMatch('/^[0-9A-Za-z]{6,7}$/');
        Link::factory()->for($user)->create(['slug' => $slug]);
        $slugs[] = $slug;
    }

    expect(array_unique($slugs))->toHaveCount(50);
});

it('AC-8: retries on collision and still returns a free slug', function () {
    $user = User::factory()->create();
    Link::factory()->for($user)->create(['slug' => 'TAKEN1']);

    // A generator that first produces the taken slug, then a free one.
    $generator = new class extends SlugGenerator
    {
        private int $calls = 0;

        protected function randomString(int $length): string
        {
            return $this->calls++ === 0 ? 'TAKEN1' : 'FREE99';
        }
    };

    expect($generator->generate())->toBe('FREE99');
});

it('AC-9: rejects custom slugs outside the allowed format', function (string $slug) {
    $result = Validator::make(['slug' => $slug], ['slug' => new ReservedSlug]);

    expect($result->fails())->toBeTrue();
})->with(['ab', 'has space', 'inv@lid', 'toooooooooooooooooooooooooooooo-long-slug-value']);

it('AC-9: accepts a well-formed custom slug', function () {
    $result = Validator::make(['slug' => 'my_cool-Link1'], ['slug' => new ReservedSlug]);

    expect($result->passes())->toBeTrue();
});

it('AC-10: rejects reserved slugs (case-insensitive)', function (string $slug) {
    $result = Validator::make(['slug' => $slug], ['slug' => new ReservedSlug]);

    expect($result->fails())->toBeTrue();
})->with(['admin', 'Admin', 'API', 'report', 'nexo']);

it('AC-11: rejects non-http/https target schemes', function (string $url) {
    $result = Validator::make(['url' => $url], ['url' => new LinkTargetUrl]);

    expect($result->fails())->toBeTrue();
})->with([
    'javascript:alert(1)',
    'data:text/html;base64,PHNjcmlwdD4=',
    'file:///etc/passwd',
    'ftp://example.com/file',
    'not-a-url',
]);

it('AC-11: accepts http and https targets', function (string $url) {
    $result = Validator::make(['url' => $url], ['url' => new LinkTargetUrl]);

    expect($result->passes())->toBeTrue();
})->with(['http://example.com', 'https://example.com/path?q=1']);
