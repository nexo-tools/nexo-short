<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Target-URL scheme whitelist (ADR-005 §3): a short link may only point at
 * http/https. javascript:, data:, file:, mailto:, tel:, etc. are rejected at
 * validation, before any external check. Adapted from the nexo-links LinkUrl
 * rule (CATALOG), narrowed to http/https — a redirect target is a web page.
 */
class LinkTargetUrl implements ValidationRule
{
    /** @var list<string> */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid URL.')->translate();

            return;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        if (! in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            $fail('The :attribute must start with http:// or https://.')->translate();

            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $fail('The :attribute must be a valid URL.')->translate();
        }
    }
}
