<?php

namespace App\Support;

use App\Models\Link;
use App\Rules\ReservedSlug;

/**
 * Generates a random base62 slug of the configured length and guarantees it is
 * unique against the links table, retrying on collision and widening the length
 * if collisions persist (ADR-004 hot-path lookup; SPEC Phase 1 AC-7/AC-8).
 */
class SlugGenerator
{
    private const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function generate(): string
    {
        $min = (int) config('nexo.slug.min_length');
        $max = (int) config('nexo.slug.max_length');
        $attempt = 0;

        do {
            $length = random_int($min, $max);
            $slug = $this->randomString($length);

            // Every few collisions, widen the space so we always converge.
            if (++$attempt % 5 === 0) {
                $max++;
            }

            // Reject anything a user could never register either: a base62 slug
            // may land on a reserved word (e.g. "report"), so apply the same
            // reserved-list criterion as the ReservedSlug rule (ADR-005 §5).
        } while ($this->exists($slug) || ReservedSlug::isReserved($slug));

        return $slug;
    }

    /** Overridable in tests to force a deterministic collision. */
    protected function randomString(int $length): string
    {
        $out = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $out .= self::ALPHABET[random_int(0, $max)];
        }

        return $out;
    }

    protected function exists(string $slug): bool
    {
        return Link::where('slug', $slug)->exists();
    }
}
