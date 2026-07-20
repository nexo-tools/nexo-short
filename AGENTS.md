# Nexo Short

> Entry point for any AI/agent working on this project. It follows Alvaro's standards system (repo `alvaro`, alvarocdev.com). Keep this file updated: persist here the important context that comes up during work sessions.
> This repo will be public: no secrets, credentials, or sensitive infrastructure details here.

## What this project is

Open source URL shortener of the Nexo ecosystem (Nexo Links, Nexo Agenda, Nexo ID, upcoming Nexo Events): short links on a dedicated short domain, cookieless click metrics, privacy by design, self-hostable. Alvaro's hosted instance: redirects on **nxo.li** (redirects ONLY — reputational fuse), panel/landing on **nexoshort.alvarocdev.com**. **Current state: Phase 0 (planning) — no product code yet.** Start at [docs/PLAN.md](docs/PLAN.md).

## Stack

Decided in ADR-002 (pending Gate 0 acceptance): Laravel (latest) + MySQL on Hostinger shared hosting, one app serving both hosts via host-scoped routes; Cloudflare free (proxied) in front of the short domain. Local dev via Sail (`laravel-bootstrap-docker-only` skill — no local PHP).

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

- **2026-07-19** — External dependency: public launch gates on nexo-id Phase 2 (provider live); Nexo Short is nexo-id's Phase 3 first client — coordinate both plans. Nexo ID's accepted ADR-003 (OAuth2+PKCE/OIDC, no parent-domain cookie) and ADR-004 (tools ship standalone auth; SSO by env) supersede the auth sketches in the original evaluation docs (`nexoshort.md`, `nexo-id.md` §Auth).
- **2026-07-19** — The original evaluation brief lives at `nexoshort.md` (repo root, kept as historical input); where it conflicts with ADRs, the ADRs win.
- **2026-07-19** — Pending ops (Alvaro, Cloudflare panel): temporary redirect rule nxo.li → alvarocdev.com (task 0.7).
