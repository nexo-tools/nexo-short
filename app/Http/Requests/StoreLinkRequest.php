<?php

namespace App\Http\Requests;

use App\Rules\LinkTargetUrl;
use App\Rules\NotFlaggedTargetUrl;
use App\Rules\ReservedSlug;
use Illuminate\Foundation\Http\FormRequest;

class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'target_url' => ['required', 'string', 'max:2048', new LinkTargetUrl, new NotFlaggedTargetUrl],
            // Optional custom slug: format + reserved-word check + uniqueness.
            'custom_slug' => ['nullable', 'string', new ReservedSlug, 'unique:links,slug'],
        ];
    }
}
