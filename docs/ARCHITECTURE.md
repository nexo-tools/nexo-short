# ARCHITECTURE — Nexo Short

> What exists and where, as of Phase 1 (core shortener, standalone). Each gate includes
> "ARCHITECTURE matches reality" — update this in the same change as the code.

## Shape

One Laravel 13 app serving **two hosts** (ADR-001/002), on the shared `dev-environment`
MySQL locally (Sail 8.5, no local PHP). Tests run on SQLite `:memory:`.

- **Short host** (`NEXO_SHORT_HOST`, e.g. `nxo.li`): redirects, branded 404, robots.txt only.
  Cookieless, every response `noindex` + `no-store`.
- **Panel host** (`NEXO_PANEL_HOST`, e.g. `nexoshort.alvarocdev.com`): landing, standalone
  auth, panel. Full web stack (session, CSRF, locale).

Host routing is wired in [bootstrap/app.php](../bootstrap/app.php) via `withRouting(then:)`:
the short host is domain-scoped and registered first (so it wins for redirect traffic and
runs the cookieless `short` middleware group); the panel answers every other host — including
`localhost` in dev/tests.

## Request paths

| Host | Route | Handler |
|---|---|---|
| short | `GET /{slug}` | [RedirectController](../app/Http/Controllers/RedirectController.php) → 302 + `no-store`, unknown/inactive → 404 |
| short | `GET /robots.txt` | [RobotsController](../app/Http/Controllers/RobotsController.php) → `Disallow: /` |
| short | `GET/POST /report` | [ReportController](../app/Http/Controllers/ReportController.php) — public, no-auth, `throttle:report` |
| short | `GET /` | branded 404 (nothing to redirect) |
| panel | `GET /` | landing ([welcome.blade.php](../resources/views/welcome.blade.php)) |
| panel | `GET/POST /login`, `/logout` | [AuthenticatedSessionController](../app/Http/Controllers/Auth/AuthenticatedSessionController.php) |
| panel | `GET/POST /register` | [RegisteredUserController](../app/Http/Controllers/Auth/RegisteredUserController.php), gated by `EnsureRegistrationOpen` |
| panel | `GET /panel` | [LinkController@index](../app/Http/Controllers/LinkController.php) |
| panel | `POST /links`, `PATCH /links/{link}/deactivate` | LinkController@store / @deactivate |
| panel | `POST /links` | LinkController@store — `throttle:link-creation` (per user + IP) |
| panel | `GET /links/{link}/stats` | LinkController@stats (owner-only) |
| panel | `GET /privacy`, `GET /terms` | privacy (ADR-006) + terms of use (ADR-005 §8) |
| panel | `GET /auth/nexo/redirect`, `GET /auth/nexo/callback` | Nexo ID SSO (only when `NEXO_SSO_ENABLED`; [NexoSsoController](../app/Http/Controllers/Auth/NexoSsoController.php)) |

## Layers

- **Service layer** ([LinkService](../app/Services/LinkService.php)) — create / forUser /
  deactivate. No HTTP concerns (ADR-007). Panel controllers call it; a future API controller
  will call the same methods. [ClickStats](../app/Services/ClickStats.php) — aggregate read
  model (totals, uniques, per-day, breakdowns, bot filter).
- **Click logging** ([ClickRecorder](../app/Support/ClickRecorder.php)) — records one click on
  the redirect hot path via [VisitorHash](../app/Support/VisitorHash.php) (daily-rotating) and
  [DeviceClassifier](../app/Support/DeviceClassifier.php). No raw IP/UA at rest (ADR-006).
- **Slug engine** ([SlugGenerator](../app/Support/SlugGenerator.php)) — unique base62,
  collision retry + widening.
- **Anti-abuse (ADR-005)** — creation rate limiters (`link-creation` per user + IP, `report`
  per IP; defined in `AppServiceProvider`), [SafeBrowsing](../app/Support/SafeBrowsing.php)
  check at creation (env-optional, fail-open/closed), operator moderation commands
  `nexo:link-deactivate` / `nexo:link-activate`.
- **SSO (ADR-003)** — `App\Services\NexoSso\*` + `NexoSsoController`, the reusable
  `nexo-sso-client` template (copied unmodified; OAuth2+PKCE/OIDC against Nexo ID). Off by
  default (`NEXO_SSO_ENABLED=false`) → standalone auth intact; sessions are tool-owned
  (provider downtime never logs anyone out). Callback on the **panel host**.
- **Validation rules** — [ReservedSlug](../app/Rules/ReservedSlug.php) (format + reserved
  list), [LinkTargetUrl](../app/Rules/LinkTargetUrl.php) (http/https whitelist),
  [NotFlaggedTargetUrl](../app/Rules/NotFlaggedTargetUrl.php) (Safe Browsing).
- **Middleware** — `SecurityHeaders` (CSP, both hosts), `ShortHostHeaders` (noindex/no-store),
  `SetLocale` (panel only), `EnsureRegistrationOpen`.

## Data

`users` (Laravel default) · `links` (`user_id` FK cascade, unique `slug`, `target_url`,
`is_active` indexed) · `clicks` (`link_id` FK cascade, `visitor_hash`, `referrer_host`,
`device`, `country`, `created_at`; no raw IP/UA — ADR-006) · `reports` (`slug`, `reason`,
`note`, `created_at`; no reporter identity — ADR-005). See
[SPEC-phase-1-core.md](SPEC-phase-1-core.md), [SPEC-phase-2-metrics.md](SPEC-phase-2-metrics.md)
and [SPEC-phase-3-anti-abuse.md](SPEC-phase-3-anti-abuse.md).

## Conventions

- **CSP**: `SecurityHeaders` middleware + re-asserted in [public/.htaccess](../public/.htaccess);
  `SecurityHeadersHtaccessSyncTest` fails on drift.
- **i18n** en/es/pt: English strings via `__()`; `scripts/generate-translations.mjs` builds
  `lang/{es,pt}.json` from `scripts/translations/`; `TranslationsSyncTest` + CI `--check` guard it.
- **Attribution**: `<x-attribution>` from `NEXO_ATTRIBUTION_*` env.
- **Brand assets**: `scripts/generate-brand-assets.mjs` from `resources/brand/mark.svg`
  (binaries generated on demand; not committed in the dark phase).

## CI

[.github/workflows/ci.yml](../.github/workflows/ci.yml): composer audit · Pint · Larastan
(level 6) · translations `--check` · Pest.

## Not here yet

Production deploy (Phase 4) · SSO-only switch + SEO landing + open-sourcing + launch
(Phase 5 remainder) · public API (backlog, ADR-007) · periodic Safe Browsing re-check,
moderation dashboard, report→email (ADR-005 backlog).
