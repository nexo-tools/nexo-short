<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Support\ClickRecorder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * The redirect hot path (ADR-004/006): indexed unique lookup on slug → active
 * check → log the click → 302 Found to the target. Unknown or deactivated slugs
 * fall through to the branded 404 and log nothing. NEVER a permanent redirect —
 * that would be cached and break both click metrics and the kill-switch. The
 * guard test greps this file.
 */
class RedirectController extends Controller
{
    public function __invoke(Request $request, ClickRecorder $clicks, string $slug): Response
    {
        $link = Link::query()->where('slug', $slug)->first();

        abort_if($link === null || ! $link->is_active, 404);

        // Server-side click logging (ADR-006), synchronous on the hot path (v1).
        // Metrics are best-effort: NO logging failure may break the 302, so any
        // Throwable is reported and swallowed — the redirect always fires.
        try {
            $clicks->record($request, $link);
        } catch (Throwable $e) {
            report($e);
        }

        // 302 + no-store (the no-store header is added by ShortHostHeaders).
        return redirect()->away($link->target_url, 302);
    }
}
