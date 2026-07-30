<?php

namespace App\Services;

use App\Models\Link;
use App\Models\User;
use App\Support\SlugGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The core link operations (ADR-007 service-layer boundary): create / list /
 * deactivate. No HTTP concern (request, response, redirect, session) leaks in —
 * inputs are plain values, outputs are models. The panel controllers call this
 * today; a future API controller (backlog) calls the same methods.
 */
class LinkService
{
    public function __construct(private readonly SlugGenerator $slugs) {}

    /**
     * Create a link for a user. A custom slug is used as-is (already validated
     * by the caller); otherwise a unique random base62 slug is generated.
     */
    public function create(User $user, string $targetUrl, ?string $customSlug = null): Link
    {
        return $user->links()->create([
            'slug' => $customSlug ?? $this->slugs->generate(),
            'target_url' => $targetUrl,
            'is_active' => true,
        ]);
    }

    /** Kill-switch (ADR-004/005): deactivate without deleting. */
    public function deactivate(Link $link): void
    {
        $link->update(['is_active' => false]);
    }

    /** @return LengthAwarePaginator<int, Link> */
    public function forUser(User $user): LengthAwarePaginator
    {
        return $user->links()->latest()->paginate(25)->withQueryString();
    }
}
