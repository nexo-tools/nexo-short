# Nexo Short

> Entry point for any AI/agent working on this project. It follows Alvaro's standards system (repo `alvaro`, alvarocdev.com). Keep this file updated: persist here the important context that comes up during work sessions.
> This repo will be public: no secrets, credentials, or sensitive infrastructure details here.

## What this project is

Open source URL shortener of the Nexo ecosystem (Nexo Links, Nexo Agenda, Nexo ID, upcoming Nexo Events): short links on a dedicated short domain, cookieless click metrics, privacy by design, self-hostable. Alvaro's hosted instance: redirects on **nxo.li** (redirects ONLY — reputational fuse), panel/landing on **nexoshort.alvarocdev.com**. **Current state: Phases 1–3 implemented — core shortener + click metrics + the ADR-005 anti-abuse package (creation rate limits per user/IP, env-optional Safe Browsing, public `/report`, terms, operator moderation commands). 40 ACs green (AC-1…AC-40); ADRs 001–008 Accepted and Gates 0–3 signed off (Alvaro, 2026-07-21); security review done (trusted-proxies fix). Next is Phase 4 (production deploy) — owner infra, not autonomous.** Start at [docs/PLAN.md](docs/PLAN.md); architecture map in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Stack

Decided in ADR-002 (Accepted, Gate 0 2026-07-21): Laravel (latest) + MySQL on Hostinger shared hosting, one app serving both hosts via host-scoped routes; Cloudflare free (proxied) in front of the short domain. Local dev via Sail (`laravel-bootstrap-docker-only` skill — no local PHP) on the shared **`dev-environment`** standard (`~/dev-environment`, already installed — never reinstall): database `nexo_short` in the shared MySQL (3306, `dev`/`dev`), app compose ships only the app runtime, `APP_PORT`/`VITE_PORT`/`WWWUSER`/`WWWGROUP` pinned in `.env`, tests on SQLite `:memory:`.

## How to run it

Docker only (no local PHP). The shared `dev-environment` must be up (`cd ~/dev-environment && docker compose up -d mysql`); database `nexo_short` already created there.

- **First time:** `docker compose build` (Sail 8.5 image).
- **Migrate:** `docker compose run --rm laravel.test php artisan migrate`
- **Serve:** `docker compose up -d` → app on `http://localhost:8102`. For the two hosts, add to `/etc/hosts`: `127.0.0.1 nxo.test nexoshort.test` and browse `http://nxo.test:8102` (redirects) / `http://nexoshort.test:8102` (panel).
- **Checks (CI equivalent):** run via the composer image —
  `docker run --rm -v "$PWD":/app -w /app composer:latest bash -c 'vendor/bin/pint --test && vendor/bin/phpstan analyse && vendor/bin/pest'`
  and translations `docker run --rm -v "$PWD":/app -w /app node:22 node scripts/generate-translations.mjs --check`.
- Tests use SQLite `:memory:` (no MySQL needed). `pdo_mysql` is absent from the `composer` image, so run artisan/migrate through Sail (`docker compose run --rm laravel.test …`), not the composer image.

## Production

Not deployed. **Phase 4 runbook prepared: [DEPLOYMENT.md](DEPLOYMENT.md)** + [.env.production.example](.env.production.example) — two-host deploy (nxo.li addon domain + nexoshort.alvarocdev.com subdomain, one app), Cloudflare (proxy, SSL Full, IP Geolocation on, no cache on redirects), `TRUSTED_PROXIES` required, verified backups + redirect canary. Needs Alvaro's Hostinger/Cloudflare access to execute. Until then, nxo.li carries a temporary Cloudflare redirect rule to alvarocdev.com (task 0.8).

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

- **2026-07-20** — **Phase 1 implemented** (tasks 1.1–1.8, one commit each `"1,N …"`, CI green per task). Stack live: Laravel 13.8 + Sail 8.5 on shared `dev-environment` MySQL, Pest/Pint/Larastan (level 6), CI (audit + Pint + Larastan + translations `--check` + Pest). 53 tests, all 20 SPEC ACs name-traced. **CATALOG pieces adapted (origins):** `SecurityHeaders` + `.htaccess` CSP sync ← nexo-id/nexo-agenda; `generate-translations.mjs` + guardian ← nexo-id; `LinkTargetUrl` ← nexolinks `app/Rules/LinkUrl.php` (narrowed to http/https per ADR-005 §3); `ReservedSlug` ← nexolinks `app/Rules/Username.php` + `config/nexo.php`; `LoginRequest`/auth ← Laravel Breeze via nexolinks; `generate-brand-assets.mjs` ← nexolinks (binaries deferred to Phase 5). Deviations logged in [docs/SPEC-phase-1-core.md](docs/SPEC-phase-1-core.md) § Reconciliation (brand binaries + Vite build deferred; password reset deferred).
- **2026-07-21** — **Phases 2–3 implemented + Gates 0–3 signed off** (Alvaro): click metrics (ADR-006) and the ADR-005 anti-abuse package. ADRs 001–008 → Accepted. Safe Browsing default = **fail-open** (configurable). Redirect-latency measurement deferred to Phase 4. **Security review done:** fixed trusted-proxies (`TRUSTED_PROXIES`) so per-IP rate limits don't collapse behind Cloudflare. Phase 4 deploy runbook prepared (DEPLOYMENT.md). 90 tests, AC-1…AC-40 traced, CI green.
- **2026-07-19** — Coordination note from **Nexo Events** (Phase 0 planned, `/Users/alvarocarrizales/nexoevents`, its ADR-006): post-MVP it wants an auto short link per event, which requires **programmatic link creation by a trusted client** (HTTP API, env-configured — no DB coupling). Nexo Short's MVP has no public API (its ADRs) — this is a candidate for a later phase; non-blocking for both sides.
- **2026-07-20** — nexo-id state updated (its Phases 1–2 closed, audited): the OIDC provider is **live** at nexoid.alvarocdev.com; integration contract = nexo-id `docs/INTEGRATION.md`. Launch condition re-anchored (ADR-003 Update): no real users until **nexo-id T4 (verified backups + uptime)** is done. The reusable OIDC client pattern does not exist yet — nexo-id Phase 3 builds it with this project as first consumer, coordinated with Alvaro (never implement in that repo unilaterally).
- **2026-07-20** — ADR-008 added (short domain noindex); ADR-005 gained the target-scheme whitelist. CATALOG now lists both nexo-links source patterns (reserved names: `app/Rules/Username.php` + `config/nexo.php`; scheme whitelist: `app/Rules/LinkUrl.php`).
- **2026-07-19** — External dependency context: Nexo ID's accepted ADR-003 (OAuth2+PKCE/OIDC, no parent-domain cookie) and ADR-004 (tools ship standalone auth; SSO by env) supersede the auth sketches in the original evaluation docs (`nexoshort.md`, `nexo-id.md` §Auth).
- **2026-07-19** — The original evaluation brief lives at `nexoshort.md` (repo root, kept as historical input); where it conflicts with ADRs, the ADRs win.
- **2026-07-19** — Pending ops (Alvaro, Cloudflare panel): temporary redirect rule nxo.li → alvarocdev.com (PLAN task 0.8).
