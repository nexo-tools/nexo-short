# ADR-002 — Stack and hosting: Laravel + MySQL on the existing Hostinger shared hosting, Cloudflare in front of the short domain

- **Date:** 2026-07-19
- **Status:** Accepted (core decision taken by Alvaro during Phase 0 planning, 2026-07-19; accepted at Gate 0 — 2026-07-21, Alvaro)

## Context

The evaluation document assumed PHP + MySQL on the current shared hosting, written before the standards system existed. The standards declare a strategic direction of **TypeScript end-to-end** (starter-master; la-herreria on Vercel + Neon as reference), while keeping **Laravel + MySQL as the pragmatic stack** for what deploys on Hostinger shared (PHP-only, prepaid until ~2029). Nexo ID resolved the same tension the same way (its ADR-002).

What is specific to a shortener: it lives off redirect latency, and every click MUST reach the origin (302, never cached — ADR-004), so no CDN cache can hide a slow origin. Cloudflare free already fronts `nxo.li` (DNS delegated; TLS, DDoS protection, `CF-IPCountry` geo header for free), but it cannot cache the redirect itself.

## Decision

1. **Laravel (latest) + MySQL on the existing Hostinger shared hosting** — same stack, host, DB engine and deploy playbook (`deploy-laravel-hostinger`) as the sibling tools it will share an ecosystem (and an SSO provider) with. $0 new cost.
2. **One Laravel app serves both hosts**: the short domain (redirect routes only) and the panel domain, as host-scoped route groups over one database. Hostinger serves both domains from the same docroot (addon domain). Single deploy, single schema, no cross-app sync.
3. **Cloudflare stays in front of nxo.li** (proxied, SSL mode Full): TLS for the short domain, DDoS protection, and the `CF-IPCountry` header for country metrics. Explicitly **no** Cloudflare features that intercept the redirect (no caching of 3xx, no Workers in v1) — the origin is the single source of truth for lookups, kill-switch and click logging.
4. The redirect path is treated as the app's hot path: minimal middleware, indexed unique lookup, and a latency measurement at the production gate (perf audit per standards). If shared-hosting latency ever proves unacceptable, that triggers a revisit (edge/Worker accelerator or VPS), recorded as a new ADR — the multi-instance env contract already keeps the domain decoupled from the runtime.

## Alternatives considered

- **Next.js on Vercel + Neon (strategic TS direction)** — better global edge latency and where the ecosystem is heading, but: fragments the Nexo products right as they consolidate on one host and one SSO provider; the brief already rejected it for free-tier limits; and the sibling Laravel client pattern for Nexo ID (nexo-id Phase 3) would not be reusable. Rejected *for now*; the revisit trigger is real latency data or the future VPS, not preference.
- **Cloudflare Worker + KV doing the redirect at the edge** — lowest possible latency, but splits the source of truth: kill-switch and click metrics would depend on state replication to the edge, precisely the two things that must never lag. Rejected for v1; viable later as an accelerator that syncs from the origin.
- **VPS** — doesn't exist yet; a redirect with an indexed lookup does not justify new infrastructure (brief §10). The VPS decision belongs to other projects' growth.
- **Two separate apps (redirect microservice + panel)** — rejected: two deploys and schema coupling for zero benefit at this scale.

## Consequences

- Deploy, CSP/.htaccess (LiteSpeed), SMTP and cron patterns are already solved in the siblings; CI reference is nexo-agenda's workflow.
- Single-region origin: redirect latency for far-away visitors is bounded by Hostinger's region + Cloudflare routing. Measured, not assumed — production gate includes it.
- Local dev without PHP installed: scaffold via the `laravel-bootstrap-docker-only` skill (Sail), like nexo-id plans.
- Knowingly diverges from the strategic TS direction; recorded so a future migration ADR has its context (mirrors nexo-id ADR-002).
