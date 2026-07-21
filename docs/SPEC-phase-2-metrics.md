# SPEC — Phase 2: Click metrics

> SPEC-first (planning-by-stages). Governs Phase 2. ACs continue the global numbering
> (AC-21+) so grep traceability stays unambiguous across phases. Deviations get dated notes
> in [§ Reconciliation](#reconciliation).

## Purpose

Server-side, cookieless click logging on the redirect (ADR-006) and per-link stats in the
panel. Every click already reaches the origin (302, ADR-004), which is the only measurement
point — there is no page under our control after the redirect. Privacy by design: no cookies,
no third parties, **no raw IPs or User-Agents at rest** — uniques come from the daily-rotating
VisitorHash.

Governing ADR: 006 (click analytics). Depends on Phase 1 redirect flow (ADR-004).

## Scope

### In
- `clicks` table + logging inside the redirect flow (synchronous insert, ADR-004 §3).
- Per click: `link_id`, `created_at`, `visitor_hash` (daily-rotating), `referrer_host`
  (external host only, nullable), `device` (`mobile`/`desktop`/`bot`), `country`
  (`CF-IPCountry`, 2 letters, nullable).
- VisitorHash (adapted from nexolinks, CATALOG) for uniques without identity.
- Panel per-link stats: totals (clicks + unique visitors), per-day series (inline SVG chart,
  locally bundled), breakdowns by device / country / referrer; bot filter.
- Privacy page (panel host) documenting exactly what is stored per click.

### Out (later / backlog)
- Aggregation tables / queued ingestion (SCOPE backlog; synchronous insert is fine at scale).
- Cross-day unique visitors (VisitorHash rotates daily by design).
- Terms of use + report page (Phase 3).

## Data model

`clicks`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `link_id` | FK → links, cascade | |
| `visitor_hash` | string(64) | SHA-256(app key + date + IP + UA); nothing identifying persisted |
| `referrer_host` | string, nullable | external host only; null for direct/self |
| `device` | string(7) | `mobile` \| `desktop` \| `bot` (coarse, from UA) |
| `country` | char(2), nullable | `CF-IPCountry`; null without Cloudflare |
| `created_at` | timestamp | |

Indexes: `(link_id, created_at)` for time series, `(link_id, visitor_hash)` for uniques.
**No `ip` or `user_agent` column exists** — enforced by test (AC-22).

## Logging flow (hot path, ADR-004/006)

Inside `RedirectController`, after the active check and before the 302: insert one click with
`visitor_hash`, `referrer_host`, `device`, `country`, `created_at`. The redirect semantics do
not change (302 + `no-store` + `noindex`). Inactive/unknown slugs 404 **before** any insert.

- **VisitorHash** (`app/Support/VisitorHash.php`) — daily-rotating SHA-256 of app key + date +
  IP + UA. IP/UA used transiently to compute the hash, never stored.
- **Device** (`app/Support/DeviceClassifier.php`) — coarse UA parse: bot keywords → `bot`,
  mobile tokens → `mobile`, else `desktop`.
- **Country** — `CF-IPCountry` header, uppercased 2 letters, else null.
- **Referrer** — `Referer` host, `www.` stripped, null if empty or same host.

## Stats (panel)

`ClickStats` service computes, for a link owned by the user: total clicks, unique visitors
(distinct `visitor_hash`), per-day counts (last N days), and breakdowns by device / country /
referrer. A bot filter excludes `device = bot` from the figures. The chart is inline SVG
(zero external requests, CSP-clean). Route: `GET /links/{link}/stats` (auth, owner-only).

## Acceptance criteria

- **AC-21** A click on an active link inserts exactly one `clicks` row for that link.
- **AC-22** The `clicks` table has no `ip`/`user_agent` column; the visitor is stored only as a
  hash (no-raw-IP/UA verified by schema test).
- **AC-23** `visitor_hash` is stable for the same visitor within a day and differs across days
  (daily rotation).
- **AC-24** `device` is derived from the UA: a known bot UA → `bot`, a mobile UA → `mobile`,
  a desktop UA → `desktop`.
- **AC-25** `country` is taken from `CF-IPCountry` (2 letters) and is null when the header is
  absent.
- **AC-26** `referrer_host` stores the external host only; a same-host or missing referrer
  stores null.
- **AC-27** Logging does not change redirect semantics: still 302 + `no-store` + `noindex`;
  an inactive/unknown slug 404s and logs **no** click.
- **AC-28** The stats page shows totals, unique visitors, a per-day series and device/country/
  referrer breakdowns for the owner's link.
- **AC-29** Bot clicks are recorded but the bot filter excludes them from the totals.
- **AC-30** The stats page issues no external requests (inline SVG chart; CSP `default-src
  'self'` holds — no `http(s)://` asset hosts).
- **AC-31** The privacy page lists what is stored per click and states no raw IPs are kept.

## Definition of done (Gate 2)

ACs traced (grep); no raw IP/UA at rest verified by test (AC-22); redirect still 302/no-store/
noindex with logging (AC-27); stats correct; CI green; ARCHITECTURE updated; owner sign-off.
Redirect-latency-not-degraded is a production measurement (ADR-002, Phase 4) — noted, not a
unit test.

## Reconciliation

- _(none yet)_
