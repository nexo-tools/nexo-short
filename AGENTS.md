# Nexo Short

> Entry point for any AI/agent working on this project. It follows Alvaro's standards system (repo `alvaro`, alvarocdev.com). Keep this file updated: persist here the important context that comes up during work sessions.
> This repo will be public: no secrets, credentials, or sensitive infrastructure details here.

## What this project is

Open source URL shortener of the Nexo ecosystem (Nexo Links, Nexo Agenda, Nexo ID, upcoming Nexo Events): short links on a dedicated short domain, cookieless click metrics, privacy by design, self-hostable. Alvaro's hosted instance: redirects on **nxo.li** (redirects ONLY — reputational fuse), panel/landing on **nexoshort.alvarocdev.com**. **Current state: Phase 0 (planning) — no product code yet.** Start at [docs/PLAN.md](docs/PLAN.md).

## Stack

Decided in ADR-002 (pending Gate 0 acceptance): Laravel (latest) + MySQL on Hostinger shared hosting, one app serving both hosts via host-scoped routes; Cloudflare free (proxied) in front of the short domain. Local dev via Sail (`laravel-bootstrap-docker-only` skill — no local PHP) on the shared **`dev-environment`** standard (`~/dev-environment`, already installed — never reinstall): database `nexo_short` in the shared MySQL (3306, `dev`/`dev`), app compose ships only the app runtime, `APP_PORT`/`VITE_PORT`/`WWWUSER`/`WWWGROUP` pinned in `.env`, tests on SQLite `:memory:`.

## How to run it

Nothing to run yet — Phase 1 scaffolds the app.

## Production

Not deployed. Planned (Phase 4): Hostinger shared via the `deploy-laravel-hostinger` skill; nxo.li as addon domain (Cloudflare proxy, SSL Full, IP Geolocation on). Until then, nxo.li carries a temporary Cloudflare redirect rule to alvarocdev.com.

## Project conventions

- This project runs on the `planning-by-stages` skill: [docs/PLAN.md](docs/PLAN.md) is the governing doc — one numbered task at a time, gate per phase with owner sign-off, ADRs in [docs/adr/](docs/adr/), SPEC before code with AC↔test traceability.
- Docs in English (public repo). Communication with Alvaro in Spanish.
- Nexo product conventions apply (siblings nexo-links/nexo-agenda/nexo-id as reference): zero external requests at runtime on the browser surface, i18n en/es/pt, `NEXO_ATTRIBUTION_*` footer, strict CSP + sync test, Pest/Pint/Larastan + CI with dependency audit.
- Canonical pieces to copy/adapt (per CATALOG rule, note origin here when copied): VisitorHash + reserved-usernames list + brand assets generator (nexo-links), i18n generator + CI workflow (nexo-agenda), SecurityHeaders/CSP sync test (nexo-agenda/nexo-links).
- **Hard product rules** (ADRs 001/004): the short domain is instance config (env), never hardcoded; redirects are 302 + `no-store`, never 301; no raw IPs at rest; the short domain serves redirects only.

## Key decisions

- **2026-07-19** — Foundational decisions taken by Alvaro at Phase 0 planning (ADRs 001–007, Proposed, pending Gate 0): hosted launch waits for Nexo ID (SSO-only instance; standalone auth still ships for self-hosters, per nexo-id ADR-004); Laravel + MySQL on Hostinger over the TS/Vercel direction; open source MIT multi-instance; no public API in MVP. See [docs/adr/](docs/adr/).
- **2026-07-19** — Domain nxo.li purchased (Dynadot, $8.54/yr, auto-renew on; .li admits no WHOIS privacy — accepted). DNS on Cloudflare free.
- **2026-07-19** — AGENTS.md created (setup-project-docs skill).

## Accumulated context

- **2026-07-19** — Coordination note from **Nexo Events** (Phase 0 planned, `/Users/alvarocarrizales/nexoevents`, its ADR-006): post-MVP it wants an auto short link per event, which requires **programmatic link creation by a trusted client** (HTTP API, env-configured — no DB coupling). Nexo Short's MVP has no public API (its ADRs) — this is a candidate for a later phase; non-blocking for both sides.
- **2026-07-20** — nexo-id state updated (its Phases 1–2 closed, audited): the OIDC provider is **live** at nexoid.alvarocdev.com; integration contract = nexo-id `docs/INTEGRATION.md`. Launch condition re-anchored (ADR-003 Update): no real users until **nexo-id T4 (verified backups + uptime)** is done. The reusable OIDC client pattern does not exist yet — nexo-id Phase 3 builds it with this project as first consumer, coordinated with Alvaro (never implement in that repo unilaterally).
- **2026-07-20** — ADR-008 added (short domain noindex); ADR-005 gained the target-scheme whitelist. CATALOG now lists both nexo-links source patterns (reserved names: `app/Rules/Username.php` + `config/nexo.php`; scheme whitelist: `app/Rules/LinkUrl.php`).
- **2026-07-19** — External dependency context: Nexo ID's accepted ADR-003 (OAuth2+PKCE/OIDC, no parent-domain cookie) and ADR-004 (tools ship standalone auth; SSO by env) supersede the auth sketches in the original evaluation docs (`nexoshort.md`, `nexo-id.md` §Auth).
- **2026-07-19** — The original evaluation brief lives at `nexoshort.md` (repo root, kept as historical input); where it conflicts with ADRs, the ADRs win.
- **2026-07-19** — Pending ops (Alvaro, Cloudflare panel): temporary redirect rule nxo.li → alvarocdev.com (PLAN task 0.8).
