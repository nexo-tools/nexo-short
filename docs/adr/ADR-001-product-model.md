# ADR-001 — Product model: standalone open source multi-instance tool; the short domain belongs to the instance

- **Date:** 2026-07-19
- **Status:** Accepted (core decision taken by Alvaro during Phase 0 planning, 2026-07-19; accepted at Gate 0 — 2026-07-21, Alvaro)

## Context

The evaluation document (nexoshort.md, 2026-07-19) frames Nexo Short as a public, open source shortener in the Nexo ecosystem. The siblings (Nexo Links, Nexo Agenda, Nexo ID per its ADR-001) are all MIT, multi-instance, self-hostable, with docs in English. Two facts from the brief are fixed and shape the model:

- **nxo.li serves redirects only.** It is a cheap, replaceable reputational fuse: if a user uploads phishing, the blacklists (Safe Browsing, mail filters, messenger blocks) hit the short domain, not the personal-brand domain. Panel/landing/API live on `nexoshort.alvarocdev.com`.
- The shortener is a portfolio piece; the README documents the architecture.

Open question resolved here: open source multi-instance vs closed.

## Decision

1. Nexo Short is **its own product and repo**, sibling to nexo-links/nexo-agenda/nexo-id: **MIT license**, documentation in **English**, repo private during development and public after the `audit-open-source` skill passes.
2. **Multi-instance: the short domain belongs to the instance, not the code.** Every self-hoster configures their own short domain (and panel domain) via env; `nxo.li` is the domain of Alvaro's instance, never hardcoded. Attribution follows the multi-instance pattern (`NEXO_ATTRIBUTION_*` env, as in the siblings).
3. **Two-host architecture is part of the product**, not an ops detail: the app distinguishes the redirect host (redirects + branded 404 + report entry only) from the panel host (landing, auth, panel). Self-hosters get the same fuse pattern for their own domains.
4. It inherits the Nexo product conventions proven in production: zero external requests at runtime on the browser surface, i18n en/es/pt, strict CSP with sync test, CI with dependency audit.
5. GitHub repo slug: **`nexo-short`** (sibling pattern: `nexo-links`, `nexo-agenda`, `nexo-id`) — to confirm at Gate 0.

## Alternatives considered

- **Closed source** — rejected by owner: loses the portfolio effect the brief explicitly wants, and breaks the ecosystem's open source promise (all siblings are MIT).
- **Serving redirects from `*.alvarocdev.com`** — rejected: a single abusive link would put the whole personal-brand domain into blacklists (brief §3, hard fact).
- **Single-host product (panel and redirects on one domain)** — rejected: the fuse only works if the short domain is isolated and disposable; self-hosters need the same separation.

## Consequences

- The repo must be publishable: no secrets, no internal infra details in tracked files; `audit-open-source` before flipping public.
- Docs, identifiers and commit messages in English from the first commit (public-repo language standard); communication with Alvaro stays in Spanish.
- Env contract must carry the instance identity: short domain, panel domain, attribution — defined in the Phase 1 SPEC.
- Being public makes the instance a target: ADR-005's anti-abuse package is a launch gate.
