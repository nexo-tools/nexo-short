# SPEC — Phase 1: Core shortener (standalone)

> Written before code (planning-by-stages). Governs Phase 1 tasks 1.2–1.8. Acceptance
> criteria are numbered (AC-N) and each maps to ≥1 test whose name cites the id
> (`AC-N: …`), grep-verifiable at Gate 1. Deviations get a dated note in
> [§ Reconciliation](#reconciliation) — never silent divergence.

## Purpose

A working URL shortener with a management panel on **standalone local auth**: redirect
service on the short host, slug engine, link CRUD (create/list/deactivate), kill-switch.
No public exposure, no metrics yet (Phase 2), no anti-abuse package yet (Phase 3). One
Laravel app serving two hosts via host-scoped routes (ADR-002).

Governing ADRs: 001 (multi-instance, two-host), 002 (Laravel+MySQL, one app), 003 (auth
modes, standalone ships), 004 (302+no-store), 007 (service layer), 008 (short-domain
noindex).

## Scope

### In
- Env-driven instance identity: short host, panel host, auth mode, attribution.
- Host-scoped routing: short host → redirect + branded 404 + (reserved) report path only;
  panel host → landing, auth, panel.
- `links` data model + `users`/sessions; slug engine (random base62 + custom slugs +
  reserved list + target-scheme whitelist).
- Redirect flow: 302 + `no-store`, active-only, branded 404, `X-Robots-Tag: noindex`.
- Standalone local auth (login/logout/register, register closable by env), rate-limited.
- Panel: create / list / deactivate links over a service layer (no HTTP concerns inside).
- Nexo conventions: i18n en/es/pt, strict CSP + `.htaccess` sync test, `NEXO_ATTRIBUTION_*`
  footer, zero external browser requests, CI (Pint + Larastan + Pest + `composer audit`).

### Out (later phases / backlog)
- Click logging + stats (Phase 2). Slug schema carries no click coupling yet.
- Safe Browsing, creation rate limits, `/report` delivery, terms/privacy pages (Phase 3).
  The `report` slug is reserved now; the page itself is Phase 3.
- Nexo ID SSO (Phase 5). Auth-mode env keys are defined now; only `local` is implemented.
- Public API (ADR-007) — service layer is API-ready but no HTTP API ships.

## Env contract

Instance identity lives in env, never hardcoded (ADR-001). Keys (documented in
`.env.example`):

| Key | Meaning | Default (dev) |
|---|---|---|
| `NEXO_SHORT_HOST` | Host that serves redirects only | `nxo.test` |
| `NEXO_PANEL_HOST` | Host that serves landing/auth/panel | `nexoshort.test` |
| `NEXO_AUTH_MODE` | `local` \| `sso` \| `both` — Phase 1 implements `local` | `local` |
| `NEXO_ALLOW_REGISTRATION` | `true`\|`false` — self-host opens/closes local signup | `true` |
| `NEXO_ATTRIBUTION_ENABLED` | show "powered by" footer | `true` |
| `NEXO_ATTRIBUTION_TEXT` | footer text | `Powered by alvarocdev.com` |
| `NEXO_ATTRIBUTION_URL` | footer link | `https://alvarocdev.com` |
| `NEXO_SLUG_MIN_LENGTH` | random slug length lower bound | `6` |
| `NEXO_SLUG_MAX_LENGTH` | random slug length upper bound | `7` |

Host resolution is by request host, matched against `NEXO_SHORT_HOST` / `NEXO_PANEL_HOST`.
A request whose host matches neither is treated as the panel host in dev and returns 404 in
production for the short-only paths (multi-instance: behavior is host-based, ADR-008 §5).

## Data model

`users` — Laravel default (`id`, `name`, `email` unique, `password`, timestamps). Standalone
auth. SSO linkage columns are Phase 5.

`links`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users, cascade on delete | owner; creation is account-gated (ADR-005) |
| `slug` | string(32) | **unique index**; base62 random or custom |
| `target_url` | text | http/https only (scheme whitelist, ADR-005 §3) |
| `is_active` | boolean, default true, indexed | kill-switch (ADR-004/005) |
| `created_at`/`updated_at` | timestamps | |

Index: unique(`slug`) drives the hot-path lookup; index(`is_active`) supports panel filters.
No `clicks` table in Phase 1 (Phase 2).

## Redirect flow (hot path, ADR-004)

On the **short host**, `GET /{slug}`:
1. Indexed unique lookup on `slug`.
2. Not found **or** `is_active = false` → branded 404 (uncacheable, noindex).
3. Found & active → `302 Found`, `Location: target_url`, `Cache-Control: no-store`,
   `X-Robots-Tag: noindex`.

Never `301`/`308`/`permanentRedirect`. Click logging (step between 2 and 3) is Phase 2 and
must not change these semantics. Middleware on the short host is minimal.

`GET /robots.txt` on the short host → `Disallow: /` for all agents (ADR-008 §2). `/report`
is a reserved slug (Phase 3 page); in Phase 1 it 404s like any unknown slug but is never
registrable.

## Slug rules

- **Random**: base62 (`[0-9A-Za-z]`), length `NEXO_SLUG_MIN_LENGTH`–`NEXO_SLUG_MAX_LENGTH`,
  generated on create; on unique-collision, retry (bounded attempts) then widen length.
- **Custom** (optional at create): `^[A-Za-z0-9_-]{3,32}$`, case-sensitive stored,
  uniqueness enforced by DB + validation.
- **Reserved list** (ADR-005 §5): custom slug matching a reserved word is rejected. List in
  `config/nexo.php`, rule in `app/Rules/ReservedSlug.php` (adapted from nexolinks
  `app/Rules/Username.php` + `config/nexo.php` — CATALOG canonical source, noted in AGENTS.md).
- **Target scheme whitelist** (ADR-005 §3): only `http`/`https` targets accepted;
  `javascript:`, `data:`, `file:`, etc. rejected at validation. Rule in
  `app/Rules/LinkTargetUrl.php` (adapted from nexolinks `app/Rules/LinkUrl.php`; the
  canonical allows `mailto`/`tel` too — narrowed to http/https only, per ADR-005 §3, since a
  redirect target of `mailto:`/`tel:` is out of scope for a shortener).

## Auth modes (ADR-003)

`NEXO_AUTH_MODE` = `local` (implemented), `sso`, `both` (Phase 5). Phase 1:
- **local**: register (gated by `NEXO_ALLOW_REGISTRATION`), login, logout. Rate-limited
  (per email + per IP; IP transient, never persisted — ADR-005/006). Secure sessions
  (httponly, samesite, secure in prod). Link creation requires an authenticated user.

## Service-layer boundary (ADR-007)

`app/Services/LinkService.php` owns create / list-for-user / deactivate. No HTTP request,
response, redirect, or auth-session concern leaks in — inputs are plain values/DTOs, it
returns models/values. Panel controllers call it; a future API controller (backlog) will
call the same methods. Tests exercise the service directly.

## Acceptance criteria

Redirect & short host (ADR-004/008):
- **AC-1** Active link on short host returns `302` with `Location` = target and
  `Cache-Control: no-store`.
- **AC-2** Redirect responses (and 404) carry `X-Robots-Tag: noindex`.
- **AC-3** Unknown slug on short host returns the branded 404 (status 404), uncacheable.
- **AC-4** A link flipped to `is_active=false` returns 404 on the **very next** request
  (kill-switch, no caching).
- **AC-5** Guard: the redirect controller/routes contain no `301`/`permanentRedirect`
  (grep-style code assertion) — a deliberate `301` trips the test.
- **AC-6** Short host `robots.txt` disallows all; panel responses do **not** carry
  `X-Robots-Tag: noindex`.

Slugs (ADR-005 §3/§5):
- **AC-7** Random slug is base62 within the configured length range and unique.
- **AC-8** On slug collision the engine retries and still yields a unique slug.
- **AC-9** Custom slug not matching `^[A-Za-z0-9_-]{3,32}$` is rejected.
- **AC-10** A reserved slug (e.g. `admin`, `report`) is rejected on create.
- **AC-11** A non-http/https target (e.g. `javascript:alert(1)`, `data:...`) is rejected.

Auth (ADR-003):
- **AC-12** Unauthenticated user cannot create a link (redirected/denied).
- **AC-13** Login endpoint is rate-limited (blocks after the configured attempts).
- **AC-14** Registration is disabled when `NEXO_ALLOW_REGISTRATION=false`.

Panel & service layer (ADR-007):
- **AC-15** Authenticated user creates a link via the panel; it is owned by them and
  redirects on the short host.
- **AC-16** User lists only their own links; deactivate flips `is_active` and the link then
  404s on the short host.
- **AC-17** `LinkService` create/deactivate work when called directly (no HTTP), proving the
  boundary.

Conventions:
- **AC-18** CSP sync test: the middleware CSP and `public/.htaccess` copy match.
- **AC-19** i18n guardian: en/es/pt keysets are complete and in sync (no missing keys).
- **AC-20** Attribution footer renders on public surfaces when `NEXO_ATTRIBUTION_ENABLED`,
  hidden when disabled.

## Definition of done (Gate 1)

All ACs green with name-traced tests (grep pass); deliberate violations caught (reserved
slug rejected, `301` guard trips, deactivated link 404s next request); CSP sync test green;
CI green (Pint + Larastan + Pest + `composer audit`); `docs/ARCHITECTURE.md` matches
reality; owner sign-off.

## Reconciliation

<!-- Dated notes when implementation diverges from this SPEC. -->
- **2026-07-20 (1.3)** — Brand-assets generator (`scripts/generate-brand-assets.mjs`) and
  master `resources/brand/mark.svg` are installed, but the binary outputs
  (favicons/OG/PWA) are **not generated/committed in Phase 1**: they are public-surface
  assets consumed by the landing (Phase 5), and Phase 1 is dark. Run `npm run brand` to
  produce them. `sharp`/`png-to-ico` are `optionalDependencies` so CI stays lean.
- **2026-07-20 (1.3)** — Frontend build (`npm run build`, Vite/Tailwind) is not a CI step
  in Phase 1: no compiled assets are referenced yet (landing/panel use self-contained inline
  styles per the zero-external-requests rule). The Vite build step joins CI when the landing
  compiles assets (Phase 5).
