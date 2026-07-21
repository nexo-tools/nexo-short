<?php

namespace App\Rules;

use App\Support\SafeBrowsing;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects a target URL flagged by Google Safe Browsing at creation (ADR-005 §4).
 * Delegates to the SafeBrowsing service, which is env-optional and honors the
 * fail-open/closed policy. Applied after LinkTargetUrl (scheme whitelist).
 */
class NotFlaggedTargetUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return; // LinkTargetUrl already handles non-strings.
        }

        if (! app(SafeBrowsing::class)->allows($value)) {
            $fail('The :attribute was flagged as unsafe and cannot be shortened.')->translate();
        }
    }
}
