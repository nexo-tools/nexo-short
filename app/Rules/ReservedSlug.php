<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a user-supplied custom slug: the allowed character set and length,
 * and that it is not a reserved word (ADR-005 §5). Adapted from the nexo-links
 * Username rule + config/nexo.php reserved list (CATALOG).
 */
class ReservedSlug implements ValidationRule
{
    /** Letters, digits, hyphens and underscores; 3–32 chars (SPEC Phase 1). */
    private const FORMAT = '/^[A-Za-z0-9_-]{3,32}$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match(self::FORMAT, $value) !== 1) {
            $fail('The :attribute may only contain letters, numbers, hyphens and underscores (3–32 characters).')->translate();

            return;
        }

        if (self::isReserved($value)) {
            $fail('The :attribute ":input" is reserved.')->translate();
        }
    }

    /**
     * Whether a slug collides with the reserved list (config/nexo.php),
     * compared case-insensitively. Shared with SlugGenerator so generated and
     * custom slugs are judged by the exact same criterion (ADR-005 §5).
     */
    public static function isReserved(string $slug): bool
    {
        $reserved = array_map('strtolower', config('nexo.reserved_slugs'));

        return in_array(strtolower($slug), $reserved, true);
    }
}
