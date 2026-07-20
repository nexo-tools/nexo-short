# ADR-004 — Redirect semantics: 302 + no-store, never 301; origin sees every click

- **Date:** 2026-07-19
- **Status:** Proposed (hard requirement fixed in the brief; formal acceptance at Gate 0)

## Context

The brief calls this "the most important technical decision of the project", and it is a fixed fact, not up for re-evaluation: a `301 Moved Permanently` gets cached by browsers, so after the first click the visitor never reaches the server again. That kills, in one stroke:

- **Metrics** — the entire analytics feature (ADR-006) depends on every click hitting the origin.
- **The kill-switch** — a deactivated (abusive) link would keep redirecting from browser cache after moderation. This turns a metrics bug into a security/abuse bug.

Cloudflare sits in front of the short domain (proxied), which adds a second place where caching could silently break this.

## Decision

1. Redirects respond **`302 Found`** with an explicit **`Cache-Control: no-store`** header. Never `301`/`308`. (`307` is an acceptable variant if method preservation ever matters; the default is 302.)
2. **Unknown or deactivated slugs return the branded 404 page** (Nexo Short branding, link to the panel/landing and to the report channel), also uncacheable.
3. **Redirect flow** (hot path, kept minimal): indexed unique lookup on slug → active check → click log (ADR-006) → `Location` + 302. Click logging is synchronous in v1 (trivial at this scale; queueing is a scale response in the backlog).
4. **Cloudflare must stay pass-through for this path**: no cache rules on the redirect host, no Workers, no "always online"-style features that could serve a stale redirect. The `no-store` header is the belt; not configuring interception is the suspenders.
5. **Mechanical enforcement** (standards rule: a convention without a check is a suggestion): the test suite asserts that redirect responses are 302 + `no-store`, and a guard test greps the redirect controller/routes for `301`/`permanentRedirect` usage. Deliberate-violation check at the gate: flipping a link to inactive must make the very next request 404.

## Alternatives considered

- **301 for "SEO/performance"** — rejected: the cache behavior that makes 301 fast is exactly what breaks metrics and moderation. There is no SEO equity to preserve on a shortener's slugs.
- **Cacheable 302 (short TTL)** — rejected: any TTL > 0 delays the kill-switch by that TTL on already-visited browsers; not worth the microseconds at this scale.
- **Meta-refresh/JS interstitial page** (some shorteners do this to run analytics scripts) — rejected: slower for every user, breaks non-browser clients, and server-side logging already captures what v1 needs without any client-side code.

## Consequences

- Every click costs one origin request + one DB read + one insert, forever, by design. Scale responses (queued ingestion, aggregation tables) go to the backlog and must never change the response semantics.
- Redirect latency is fully visible to users (nothing is cached), which is why ADR-002 treats this route as the hot path and the production gate measures it.
- The 404 page is public surface on the short domain: it carries attribution and the report entry point, and must respect the CSP/zero-external-requests conventions.
