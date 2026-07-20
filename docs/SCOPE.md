# SCOPE — Nexo Short

<!-- Living record: every new idea lands here (docs: commit) BEFORE being implemented. -->

## Value proposition

Open source URL shortener of the Nexo ecosystem: short links on a dedicated short domain, basic click metrics without cookies or third parties, privacy by design (no raw IPs stored), self-hostable like its siblings (Nexo Links, Nexo Agenda, Nexo ID). Like the rest of the Nexo tools, it starts from a known idea (Bitly) and adds the ecosystem angle: one account (Nexo ID), consistent conventions, future integrations with the sibling tools.

Alvaro's hosted instance: redirects on **nxo.li** (and only redirects — reputational fuse, see ADR-001), panel/landing on **nexoshort.alvarocdev.com**.

## MVP

### In

- **Redirect service** on the short domain: `302` + `no-store` (never `301` — ADR-004), active links only, branded 404 for unknown/inactive slugs with a report link.
- **Slugs**: random base62 (6–7 chars, collision retry), optional custom slugs (`[a-zA-Z0-9_-]{3,32}`), reserved-slug list (ADR-005).
- **Link management panel**: create, list, deactivate (kill-switch), per-link stats.
- **Auth**: standalone local auth ships in the code (self-host default, per nexo-id ADR-004); Alvaro's hosted instance runs **SSO-only via Nexo ID** — public launch waits for it (ADR-003). No public registration surface of its own on the hosted instance.
- **Basic metrics**, cookieless and without raw IPs: clicks total and per day, referrer, device class, country (Cloudflare header); simple per-day chart in the panel (ADR-006).
- **Anti-abuse package** (launch requirement, not a feature — ADR-005): account-gated creation, Google Safe Browsing check at creation, rate limiting, reserved slugs, kill-switch, report channel, published terms of use.
- **Nexo product conventions**: i18n en/es/pt, instance-configurable attribution (`NEXO_ATTRIBUTION_*`), strict CSP + sync test, zero external requests at runtime (browser surface), CI with lint + static analysis + tests + dependency audit.
- **Multi-instance by design**: the short domain belongs to the instance (env), not the code (ADR-001).

### Out (with the why)

- **Public REST API + tokens** — no real consumer exists yet (Nexo Events is not built); the service layer keeps it additive later (ADR-007).
- **Ecosystem integrations** (Nexo Events auto-links, Nexo Links bio shortening, Nexo Agenda share links) — depend on the API; same reason.
- **QR codes, link expiration, UTM builder** — brief roadmap extras; none is needed to validate the core loop.
- **Periodic Safe Browsing re-check of existing targets** — v1 checks at creation; the re-check job is hardening, not launch-blocking.
- **Queued/aggregated click ingestion** — synchronous insert is trivial at current scale; queueing is a scale response, not a v1 need.
- **Custom domains per user, bulk import/export** — not validated needs; large surface.

## Product principles

- **Domain/product separation**: the short domain serves redirects only; panel/landing/API live elsewhere. Never serve third-party redirects from `*.alvarocdev.com` — the short domain is a cheap, replaceable reputational fuse.
- **Metrics must survive the redirect**: any mechanism that stops clicks from reaching the origin (301, caching, edge shortcuts) is a bug by definition.
- **Privacy by design**: no cookies on the redirect, no raw IPs at rest, no third-party requests from the browser.
- **Anti-abuse is a launch gate**: a public shortener is a phishing magnet; the cost of failure is domain blacklisting.
- **Ecosystem-consistent**: conventions, look and repo shape match the sibling Nexo tools.

## Backlog post-v1

<!-- Each item with the why it was deferred. -->

- REST API with per-user tokens (create/list/deactivate, stats) — deferred until a consumer exists (ADR-007).
- Nexo Events / Nexo Links / Nexo Agenda integrations — depend on the API.
- QR code per link, link expiration, UTM helpers — nice-to-haves after the core loop is proven.
- Periodic Safe Browsing re-check + domain-level blocklist — anti-abuse hardening beyond launch minimum.
- Queued click ingestion / daily aggregation tables — when click volume makes synchronous inserts or raw-row queries heavy.
- Back-channel logout propagation — tracked in nexo-id (its known limitation, post-v1 there).
