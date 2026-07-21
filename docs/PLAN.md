# PLAN — Nexo Short

> Execution follows the `planning-by-stages` skill (alvaro standards repo): one numbered task at a time, checklist marked at the moment, SPEC before code, AC ↔ test traceability by name, one commit per task (`"N,M description"`), CI green before the next task, gate per phase with owner sign-off.
>
> Phase 1 is pre-broken into tasks by explicit owner instruction; they get reconciled against the Phase 1 SPEC's acceptance criteria when the phase opens (any divergence is amended in this file, dated). Later phases list objective, key work and gate criteria only.
>
> **External dependency** (re-anchored 2026-07-20, ADR-003 Update): nexo-id's provider is already **live in production** (its Phases 1–2 closed; contract in its `docs/INTEGRATION.md`). What still gates the public launch is **nexo-id T4: verified backups + uptime monitoring** (deferred there to a cross-tool ops pass) — hard condition on Gate 5. The reusable OIDC client pattern is built in nexo-id Phase 3 *with this project as first consumer*, coordinated with Alvaro. Development here does not wait — Phases 1–3 build on standalone auth.

## Phase 0 — Planning & foundations (current)

**Objective:** decisions made and recorded, scope fixed, project formalized. Zero product code.

- [x] 0.1 Read the standards system + evaluation document (nexoshort.md) + nexo-id's accepted ADRs/plan; separate brief facts from assumptions to re-evaluate.
- [x] 0.2 Foundational decisions consulted with Alvaro (2026-07-19): launch waits for Nexo ID; Laravel + MySQL on Hostinger; open source MIT multi-instance; API post-MVP.
- [x] 0.3 `docs/SCOPE.md` — value proposition, MVP in/out, product principles, backlog.
- [x] 0.4 Foundational ADRs 001–007, status Proposed.
- [x] 0.5 `docs/PLAN.md` (this file) with phases and gates.
- [x] 0.6 Project formalization: `AGENTS.md` (EN), `CLAUDE.md` → AGENTS, `CLAUDE.local.md` (gitignored) with standards briefing, `README.md` with Status line, `.gitignore`, MIT `LICENSE`, git init, private GitHub repo (final destination: `nexo-tools` org).
- [x] 0.7 Re-validation against the updated planning prompt (2026-07-20): ADR-003 amended (nexo-id provider live; launch condition re-anchored to its T4), ADR-005 gains the target-scheme whitelist, ADR-008 added (short domain noindex), task 1.2 switched to the shared `dev-environment` standard, AGENTS.md updated.
- [ ] 0.8 Ops (Alvaro, Cloudflare panel — pending from the brief's post-purchase checklist): temporary redirect rule `nxo.li/*` → `alvarocdev.com` so the domain is useful from day 1.
- [ ] 0.9 Present plan + ADRs to Alvaro; resolve open points; stamp sign-off.

**Gate 0 (owner sign-off required):**
- [ ] ADRs 001–008 reviewed and accepted (or amended here, dated).
- [ ] SCOPE MVP in/out approved.
- [ ] Open points confirmed: Safe Browsing fail-open vs fail-closed default (ADR-005, can also be settled in the Phase 1/3 SPEC); whether redirect latency gets an early measurement spike in Phase 1 or waits for the Phase 4 production measurement (ADR-002).
- [ ] Sign-off: pending.

## Phase 1 — Core shortener (standalone)

**Objective:** working shortener with panel on local auth — redirect, slugs, CRUD, kill-switch — SPEC-first, sibling conventions installed from the start. No public exposure.

- [ ] 1.1 `SPEC.md` for the core: env contract (short/panel domain, auth mode, attribution), data model, redirect flow, slug rules, auth modes, service-layer boundary (ADR-007), numbered ACs mapped to tests.
- [ ] 1.2 Scaffold: Laravel latest via `laravel-bootstrap-docker-only` (Sail, no local PHP) on the shared **`dev-environment`** standard (already installed at `~/dev-environment` — do NOT reinstall): create database `nexo_short` in the shared MySQL (standard port 3306, `dev`/`dev`), app compose ships ONLY the app runtime (no DB/mail services), pin `APP_PORT`/`VITE_PORT`/`WWWUSER`/`WWWGROUP` in `.env`, tests on SQLite `:memory:`; Pest/Pint/Larastan; CI per nexo-agenda reference (lint + static analysis + tests + `composer audit`).
- [ ] 1.3 Canonical ecosystem pieces (CATALOG sources noted in AGENTS.md): i18n generator en/es/pt + guardian test, SecurityHeaders/CSP + `.htaccess` sync test, brand assets generator, `NEXO_ATTRIBUTION_*` footer.
- [ ] 1.4 Migrations: `links` (unique slug index, `is_active`, user FK), users/sessions per Laravel + auth-mode groundwork.
- [ ] 1.5 Slug engine: base62 random 6–7 with collision retry, custom slug validation (`[a-zA-Z0-9_-]{3,32}`), reserved list + target scheme whitelist (both adapted from nexo-links per CATALOG) — with tests exercising the list, the whitelist and collisions.
- [ ] 1.6 Redirect host: host-scoped routes, 302 + `no-store`, inactive/unknown → branded 404 with report link, `X-Robots-Tag: noindex` + restrictive robots.txt (ADR-008); guard tests from ADR-004/008 (grep for 301, status/header assertions, landing NOT noindex).
- [ ] 1.7 Standalone local auth (self-host default; registration closable by env), rate-limited, secure sessions — reusing sibling patterns.
- [ ] 1.8 Panel: create/list/deactivate links over the service layer; i18n'd UI; attribution footer.
- [ ] 1.9 Phase reconciliation: SPEC ↔ implementation, `docs/ARCHITECTURE.md` first version, AGENTS.md updated.

**Gate 1:** all ACs green with name-traced tests (`grep` pass); deliberate violations caught (reserved slug rejected, 301 guard trips, deactivated link 404s on next request); CSP sync test green; CI green; ARCHITECTURE matches reality; owner sign-off.

## Phase 2 — Click metrics

**Objective:** ADR-006 live: server-side click logging on the redirect and per-link stats in the panel.

Key work: SPEC; `clicks` migration (`link_id, clicked_at` index); logging inside the redirect flow (referrer, device class, `CF-IPCountry`, VisitorHash adapted from nexo-links); panel stats (totals, per-day chart with locally-bundled assets, breakdowns); bot flagging; privacy-page content for what's stored.

**Gate 2:** ACs traced; redirect latency not measurably degraded by logging (compare against Gate 1 baseline); no raw IP/UA at rest verified by test; owner sign-off.

## Phase 3 — Anti-abuse & policies

**Objective:** the full ADR-005 package — the mandatory gate before any public exposure.

Key work: SPEC (including Safe Browsing fail-open/fail-closed decision); Safe Browsing check at creation (env-optional); creation rate limits (user + IP); report page at `/report` (rate-limited, no auth); terms of use + privacy pages; moderation kill-switch flow in panel.

**Gate 3 (blocks any public exposure):** deliberate-violation evidence — rate limit actually blocks, Safe Browsing test URL rejected, reserved slugs unregistrable, deactivated link dead immediately; security audit exercised (not theoretical) per standards; owner sign-off.

## Phase 4 — Production deploy (dark) + ops baseline

**Objective:** the app live on real infrastructure with registration closed — no public users yet.

Key work: deploy via `deploy-laravel-hostinger` playbook (no clean-slate rule); nxo.li as addon domain replacing the temporary Cloudflare redirect rule (proxy on, SSL Full, IP Geolocation enabled for `CF-IPCountry`); panel at `nexoshort.alvarocdev.com`; **verified backups** (restore tested once, DB + env); **uptime monitoring with a redirect canary** — a monitor hitting a known slug on nxo.li expecting 302 (if nxo.li falls, every published link dies — this monitor is the product's heartbeat) plus a panel health check; redirect latency measured from a realistic location (perf audit, ADR-002 revisit trigger).

**Gate 4:** real E2E on production (create link → click → 302 → stats recorded); backup restored for real once; monitors alerting verified (kill the canary deliberately); owner sign-off.

## Phase 5 — Nexo ID SSO, launch & open-sourcing

**Objective:** hosted instance goes SSO-only and public; repo goes public.

**Depends on:** nexo-id Phase 3 (the reusable OIDC client pattern is built there *with this project as first consumer* — coordinated with Alvaro; nothing gets implemented in the nexo-id repo unilaterally from here) and **nexo-id T4 (verified backups + uptime)** before real users. The provider itself is already live; integration follows nexo-id `docs/INTEGRATION.md` (discovery at runtime, client registered via `nexo:sso-client`, code + PKCE).

Key work: SSO integration (`NEXO_SSO_*` env contract, account linking, graceful degradation per ADR-003); hosted instance switched to SSO-only with registration via Nexo ID; landing with SEO base (validate-generated-site checklist) on the panel domain; `audit-open-source` pass → repo public; README as portfolio piece (architecture documented); launch.

**Gate 5:** mirror of nexo-id Gate 3 — real signup→login→create→click flow via Nexo ID from nxo.li; degradation verified (Nexo ID down → active sessions and redirects keep working); standalone mode still green in the suite (self-host story intact); audit passed and repo public; **external hard condition: nexo-id T4 done (verified backups + uptime on the identity provider)** — no real users before that; owner sign-off = **public launch**.

## Post-v1 backlog

Prioritized from SCOPE when v1 is live: REST API + tokens (ADR-007, first consumer likely Nexo Events), ecosystem integrations, QR per link, link expiration, UTM helpers, periodic Safe Browsing re-check, queued click ingestion.
