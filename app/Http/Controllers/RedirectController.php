<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Symfony\Component\HttpFoundation\Response;

/**
 * The redirect hot path (ADR-004): indexed unique lookup on slug → active check
 * → 302 Found to the target. Unknown or deactivated slugs fall through to the
 * branded 404. NEVER a permanent redirect — that would be cached and break both
 * click metrics (Phase 2) and the kill-switch. The guard test greps this file.
 */
class RedirectController extends Controller
{
    public function __invoke(string $slug): Response
    {
        $link = Link::query()->where('slug', $slug)->first();

        abort_if($link === null || ! $link->is_active, 404);

        // 302 + no-store (the no-store header is added by ShortHostHeaders).
        return redirect()->away($link->target_url, 302);
    }
}
