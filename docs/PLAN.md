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

> **Execution note (2026-07-20):** Alvaro authorized autonomous execution of Phases 1–3 (the code phases) in a single session, with commit+push per task, and asked for open decisions to be defaulted-with-a-flag and reported together at the end rather than paused on. Gate 0's formal sign-off box stays unchecked (owner-only); the open Gate 0 points are carried as defaults noted in the relevant SPEC and surfaced for review. Phases 4–5 are not executed here (production infra + external nexo-id coordination).

- [x] 1.1 `SPEC.md` for the core → [docs/SPEC-phase-1-core.md](SPEC-phase-1-core.md): env contract (short/panel domain, auth mode, attribution), data model, redirect flow, slug rules, auth modes, service-layer boundary (ADR-007), numbered ACs (AC-1…AC-20) mapped to tests.
- [x] 1.2 Scaffold: Laravel 13.8 via `laravel-bootstrap-docker-only` (Sail 8.5, no local PHP) on the shared **`dev-environment`** standard: database `nexo_short` created in the shared MySQL (3306, `dev`/`dev`), `compose.yaml` ships only the app runtime (reaches MySQL via `host.docker.internal`), `APP_PORT=8102`/`VITE_PORT=5177`/`WWWUSER`/`WWWGROUP` pinned in `.env`, tests on SQLite `:memory:`; Pest/Pint/Larastan green; CI `.github/workflows/ci.yml` (audit + Pint + Larastan + Pest). E2E verified: Sail app container migrates against shared MySQL.
- [x] 1.3 Canonical ecosystem pieces (adapted from siblings, CATALOG origins noted in AGENTS.md at 1.9): i18n generator en/es/pt + guardian test (AC-19), SecurityHeaders/CSP + `.htaccess` sync test (AC-18), brand assets generator + master mark (binaries deferred to Phase 5 — SPEC note), `NEXO_ATTRIBUTION_*` footer component (AC-20). Pint/Larastan/Pest green.
- [x] 1.4 Migrations: `links` (unique slug index, `is_active` indexed, `user_id` FK cascade), `Link` model + factory, `User->links()`; users/sessions per Laravel default; auth-mode groundwork (config `auth_mode`; SSO columns deferred to Phase 5). Schema tests green.
- [x] 1.5 Slug engine: `SlugGenerator` base62 6–7 with collision retry + widening (AC-7/AC-8), `ReservedSlug` rule (`[A-Za-z0-9_-]{3,32}` + case-insensitive reserved list, AC-9/AC-10), `LinkTargetUrl` http/https whitelist (AC-11). Both rules adapted from nexo-links (CATALOG). 19 tests green.
- [x] 1.6 Redirect host: `then:`-registered host-scoped routes (short host cookieless), `RedirectController` 302 + `no-store` (AC-1), `ShortHostHeaders` X-Robots-Tag noindex + no-store (AC-2), branded `errors/404` with panel + report links (AC-3), kill-switch 404s next request (AC-4), anti-301 guard grep (AC-5), `RobotsController` Disallow all + panel NOT noindex (AC-6). 38 tests green.
- [x] 1.7 Standalone local auth: LoginRequest (per-email+IP rate limit, AC-13), AuthenticatedSessionController + RegisteredUserController, `EnsureRegistrationOpen` env gate (AC-14), secure http-only sessions, self-contained auth views + `<x-layout>`. Password reset/email-verification deferred (SPEC note). Adapted from Breeze/nexo-links (CATALOG). 45 tests green.
- [x] 1.8 Panel: `LinkService` boundary (create/forUser/deactivate, AC-17), `LinkController` + `StoreLinkRequest`, auth-gated create (AC-12/AC-15), reserved-slug rejection, own-links listing + owner-only deactivate → 404 on short host (AC-16); i18n'd panel UI + attribution footer. 53 tests green.
- [x] 1.9 Phase reconciliation: SPEC ↔ implementation reconciled (dated notes in the SPEC), `docs/ARCHITECTURE.md` first version, AGENTS.md updated (state, run instructions, CATALOG origins). AC↔test grep verified (all 20 ACs cited).

**Gate 1:** all ACs green with name-traced tests (`grep` pass); deliberate violations caught (reserved slug rejected, 301 guard trips, deactivated link 404s on next request); CSP sync test green; CI green; ARCHITECTURE matches reality; owner sign-off.

**Gate 1 status (2026-07-20):** technical criteria met — 20/20 ACs name-traced (grep pass), deliberate-violation tests green (reserved slug rejected AC-10, anti-301 guard AC-5, kill-switch 404 AC-4/AC-16), CSP sync green (AC-18), local CI-equivalent green (audit + Pint + Larastan + translations `--check` + Pest, 53 tests), ARCHITECTURE matches reality. **Owner sign-off: pending** (Alvaro). Open Gate 0/1 points still needing an owner call are listed for review (Safe Browsing fail-open/closed → decided in Phase 3 SPEC; redirect-latency spike → deferred to Phase 4, ADR-002).

## Phase 2 — Click metrics

**Objective:** ADR-006 live: server-side click logging on the redirect and per-link stats in the panel. SPEC: [docs/SPEC-phase-2-metrics.md](SPEC-phase-2-metrics.md) (AC-21…AC-31).

- [x] 2.1 SPEC: clicks data model, logging flow, VisitorHash, device/country/referrer parsing, stats, privacy note; ACs AC-21…AC-31.
- [x] 2.2 `clicks` migration (`link_id, created_at` + `link_id, visitor_hash` indexes) + `Click` model + factory; schema test (no raw IP/UA column, AC-22).
- [x] 2.3 VisitorHash (adapt nexolinks) + `DeviceClassifier` + referrer/country parsing; log inside the redirect flow (AC-21/AC-23/AC-24/AC-25/AC-26/AC-27).
- [x] 2.4 Panel per-link stats over a `ClickStats` service: totals, unique visitors, per-day inline-SVG chart (local assets), breakdowns, bot filter (AC-28/AC-29/AC-30).
- [ ] 2.5 Privacy page (panel host) documenting what is stored per click (AC-31).
- [ ] 2.6 Reconciliation: SPEC ↔ impl, ARCHITECTURE + AGENTS updated.

**Gate 2:** ACs traced; redirect latency not measurably degraded by logging (compare against Gate 1 baseline — production measurement, Phase 4/ADR-002); no raw IP/UA at rest verified by test; owner sign-off.

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
