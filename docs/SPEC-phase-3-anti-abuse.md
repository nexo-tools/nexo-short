# SPEC — Phase 3: Anti-abuse & policies

> SPEC-first. The full ADR-005 package — the mandatory gate before any public exposure.
> ACs continue global numbering (AC-32+). Deviations → dated notes in
> [§ Reconciliation](#reconciliation).

## Purpose

A public shortener is a phishing/spam magnet; the cost of failure is the short domain being
blacklisted (and every published link dying with it). This phase adds the defenses that gate
public exposure (ADR-005): creation rate limits, a Safe Browsing check at creation, a public
report channel, published terms, and an operator moderation kill-switch. The account gate
(Phase 1 auth), target-scheme whitelist (Phase 1 `LinkTargetUrl`, AC-11), reserved slugs
(Phase 1 `ReservedSlug`, AC-10) and the per-link kill-switch (Phase 1 `is_active`, AC-4/AC-16)
are already in place.

Governing ADR: 005. Also 003 (account gate), 008 (short host noindex — `/report` is noindex).

## Open decision resolved here — Safe Browsing failure mode

ADR-005 §Consequences left "fail-open vs fail-closed on API timeout/error" to the SPEC with
Alvaro. **Default chosen (pending owner confirmation): fail-OPEN, configurable.** On a Safe
Browsing API error/timeout the link is **created** (the request is not blocked on Google's
uptime), because the account gate + creation rate limits + scheme whitelist + reserved slugs
already stand between an attacker and abuse, and blocking legitimate users on a third-party
outage is the worse failure for a self-hostable tool. Instances that prefer the stricter
posture set `NEXO_SAFE_BROWSING_FAIL_CLOSED=true` to reject on API error. A *positive* match
(URL is flagged) always rejects, regardless of this flag. **Flagged for owner review.**

## Env contract (additions)

| Key | Meaning | Default |
|---|---|---|
| `NEXO_SAFE_BROWSING_KEY` | Google Safe Browsing Lookup API key; empty → check disabled (self-host default) | `` (empty) |
| `NEXO_SAFE_BROWSING_FAIL_CLOSED` | reject creation when the API errors/times out | `false` (fail-open) |
| `NEXO_CREATE_RATE_PER_USER` | link creations per user per minute | `10` |
| `NEXO_CREATE_RATE_PER_IP` | link creations per IP per minute | `20` |
| `NEXO_REPORT_RATE_PER_IP` | reports per IP per minute | `5` |

## Scope

### In
- **Creation rate limiting** (per user AND per IP) on `POST /links` via a named limiter.
- **Safe Browsing check at creation**: env-optional; disabled cleanly with no key; a flagged
  URL is rejected; API error honors the fail-open/closed flag. Server-side only (not the
  browser surface — ADR-005 §4 privacy note).
- **Report channel** `{short-host}/report`: no-auth, rate-limited (per IP), stores a `reports`
  row (slug + reason + optional note). Reachable from the branded 404 and the landing. noindex.
- **Terms of use** page (panel host), published. (Privacy page shipped in Phase 2.)
- **Operator moderation kill-switch**: `nexo:link-deactivate {slug}` / `nexo:link-activate
  {slug}` artisan commands — the instance operator can kill/restore ANY link without deleting
  it (evidence preserved). A moderation *dashboard* is backlog (ADR-005).

### Out (backlog, ADR-005/SCOPE)
- Periodic Safe Browsing re-check of existing targets; domain-level blocklist; submission
  heuristics; moderation dashboard beyond the CLI kill-switch; report → email delivery.

## Data model (addition)

`reports`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `slug` | string(32) | reported slug (link may or may not exist) |
| `reason` | string(32) | one of a fixed set (malicious/spam/abusive/broken/other) |
| `note` | string(500), nullable | optional free text |
| `created_at` | timestamp | |

No reporter identity stored (no auth, no raw IP at rest — consistent with ADR-006).

## Acceptance criteria

- **AC-32** `POST /links` is rate-limited per user: after `NEXO_CREATE_RATE_PER_USER` creations
  in the window the next is blocked (429).
- **AC-33** `POST /links` is rate-limited per IP.
- **AC-34** A URL flagged by Safe Browsing is rejected at creation (check enabled).
- **AC-35** With no `NEXO_SAFE_BROWSING_KEY`, creation proceeds (check disabled, graceful).
- **AC-36** On a Safe Browsing API error, fail-open (default) creates the link; fail-closed
  (`NEXO_SAFE_BROWSING_FAIL_CLOSED=true`) rejects it.
- **AC-37** `GET /report` on the short host loads without auth; `POST /report` stores a report.
- **AC-38** `POST /report` is rate-limited per IP (blocks after the configured count).
- **AC-39** The terms page is published and reachable on the panel host.
- **AC-40** `nexo:link-deactivate {slug}` deactivates any link (operator kill-switch); the link
  then 404s on the short host, and `nexo:link-activate` restores it.

## Definition of done (Gate 3 — blocks any public exposure)

Deliberate-violation evidence: rate limit actually blocks (AC-32/33/38); a Safe Browsing test
URL rejected (AC-34); reserved slugs unregistrable (AC-10); a `javascript:`/`data:` target
rejected (AC-11); a deactivated link dead immediately (AC-4/AC-40). CI green; ARCHITECTURE
updated; security review exercised per standards; owner sign-off.

## Reconciliation

- _(none yet)_
