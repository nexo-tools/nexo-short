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
| short | `GET /` | branded 404 (nothing to redirect) |
| panel | `GET /` | landing ([welcome.blade.php](../resources/views/welcome.blade.php)) |
| panel | `GET/POST /login`, `/logout` | [AuthenticatedSessionController](../app/Http/Controllers/Auth/AuthenticatedSessionController.php) |
| panel | `GET/POST /register` | [RegisteredUserController](../app/Http/Controllers/Auth/RegisteredUserController.php), gated by `EnsureRegistrationOpen` |
| panel | `GET /panel` | [LinkController@index](../app/Http/Controllers/LinkController.php) |
| panel | `POST /links`, `PATCH /links/{link}/deactivate` | LinkController@store / @deactivate |

## Layers

- **Service layer** ([LinkService](../app/Services/LinkService.php)) — create / forUser /
  deactivate. No HTTP concerns (ADR-007). Panel controllers call it; a future API controller
  will call the same methods.
- **Slug engine** ([SlugGenerator](../app/Support/SlugGenerator.php)) — unique base62,
  collision retry + widening.
- **Validation rules** — [ReservedSlug](../app/Rules/ReservedSlug.php) (format + reserved
  list), [LinkTargetUrl](../app/Rules/LinkTargetUrl.php) (http/https whitelist).
- **Middleware** — `SecurityHeaders` (CSP, both hosts), `ShortHostHeaders` (noindex/no-store),
  `SetLocale` (panel only), `EnsureRegistrationOpen`.

## Data

`users` (Laravel default) · `links` (`user_id` FK cascade, unique `slug`, `target_url`,
`is_active` indexed). See [SPEC-phase-1-core.md](SPEC-phase-1-core.md).

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

Click metrics (Phase 2) · anti-abuse package + `/report` + terms/privacy (Phase 3) ·
production deploy (Phase 4) · Nexo ID SSO (Phase 5) · public API (backlog, ADR-007).
