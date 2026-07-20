# ADR-006 — Click analytics: server-side on the redirect, cookieless, no raw IPs; VisitorHash for uniques

- **Date:** 2026-07-19
- **Status:** Proposed (privacy constraints fixed in the brief; formal acceptance at Gate 0)

## Context

Metrics are part of the product's value ("basic metrics included"), and two brief facts bound the design: every click reaches the origin (302, ADR-004) and **no raw IPs are stored**. The standards add: cookieless, no third-party analytics (no Google Analytics), and the ecosystem already has a proven pattern for anonymous uniques — **VisitorHash** (canonical in nexo-links, per CATALOG): a daily-rotating SHA-256 of app key + date + IP + UA, where nothing identifying is persisted.

A shortener has one structural constraint most products don't: there is no page under our control after the click, so client-side beacons are impossible. The redirect request is the only measurement point.

## Decision

1. **Measurement happens server-side inside the redirect request** (ADR-004 flow step 3). No cookies, no JS, nothing on the destination page.
2. **Stored per click** (the brief's `clicks` shape): link id, timestamp, referrer (origin/host, truncated; may be null), device class parsed from the User-Agent (`mobile` / `desktop` / `bot` — coarse on purpose), country from Cloudflare's `CF-IPCountry` header (2 letters, free; null when absent, e.g. self-hosts without Cloudflare).
3. **Unique visitors via the VisitorHash pattern** copied/adapted from nexo-links (noted in AGENTS.md per CATALOG rule): daily-rotating hash for dedupe, so "unique clicks per day" is possible while re-identification across days is not. No raw IP or UA at rest.
4. **v1 reads are aggregate queries over the clicks table** (totals, per-day series for the chart, breakdowns by referrer/device/country), rendered with locally-bundled assets (zero external requests). Aggregation tables/queues only when volume demands them (backlog, SCOPE).
5. **Bot handling is display-level in v1**: bots are recorded (flagged by device class), filterable in the panel — not silently discarded (data you drop is data you can't reconsider).

## Alternatives considered

- **Third-party analytics (GA or similar)** — rejected by standards: cookies/third parties contradict the privacy pitch, and per-link metrics ARE the product, not telemetry.
- **Cloudflare Analytics as the metrics source** — rejected: domain-level only, no per-link dimension, and self-hosted instances may not use Cloudflare at all.
- **Storing raw IP + UA "for future flexibility"** — rejected: brief fixes no-raw-IPs; VisitorHash gives uniques without the liability.
- **Client-side beacon** — impossible here: no controlled page after the redirect.

## Consequences

- Metrics accuracy is bounded by what one server-side request exposes: no session depth, no post-click behavior — consistent with "basic metrics" scope; anything richer is a different product decision.
- `CF-IPCountry` requires the Cloudflare proxy (Alvaro's instance has it; self-hosts without it simply get null country) — the SPEC treats country as optional data, and IP Geolocation must be enabled in the Cloudflare zone (ops checklist, Phase 4).
- The clicks insert sits on the hot path; it must stay a single indexed insert (`link_id, clicked_at` index per the brief) — any enrichment that needs external calls at click time is disqualified by design.
- Privacy page documents exactly what is stored per click (and that IPs are used transiently for the daily hash and rate limiting only).
