<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Restrictive robots.txt for the short host (ADR-008 §2): disallow everything.
 * The X-Robots-Tag header (ShortHostHeaders) is the authoritative signal; this
 * file just reduces crawl noise.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        return response("User-agent: *\nDisallow: /\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
