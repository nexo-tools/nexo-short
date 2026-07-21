# ADR-007 — API surface: no public API in the MVP; service layer keeps it additive

- **Date:** 2026-07-19
- **Status:** Accepted (core decision taken by Alvaro during Phase 0 planning, 2026-07-19; accepted at Gate 0 — 2026-07-21, Alvaro)

## Context

The brief sketches a REST API with per-user tokens (create/list/deactivate links, read stats) and names the ecosystem integrations as the differentiator: Nexo Events generating a short link per event, Nexo Links shortening bio links, Nexo Agenda sharing links. But today none of those consumers exists (Nexo Events is unbuilt; the others have no shortening feature yet), and a public API is real attack/abuse surface (token auth, its own rate limiting, its own audit) that would land inside the already-loaded launch gate.

## Decision

1. **The MVP ships no public API.** The API and the ecosystem integrations move to post-v1 backlog (SCOPE), to be specced when the first real consumer exists — most likely Nexo Events at its birth.
2. **The architecture pays the (cheap) insurance now**: link creation/deactivation/stats live in a service layer that both the panel controllers and a future API controller call. The Phase 1 SPEC states this boundary; no HTTP concerns leak into it.
3. When the API phase opens, it gets its own SPEC and ADR (token mechanism, scopes, rate limits, versioning) — not inherited assumptions from the brief.

## Alternatives considered

- **API in the MVP** — rejected by owner: more surface to secure and audit before launch, for zero current consumers; the integrations it enables are themselves post-v1.
- **Internal-only API (shared-secret between Nexo tools) as a stopgap** — rejected: the future consumers are the same Laravel/host ecosystem, but a bespoke internal protocol would be thrown away when the real token API arrives; better to wait and build it once.

## Consequences

- Nexo Events' "short link per event" feature has a declared dependency: it needs this API first. That belongs in Nexo Events' planning when it starts (mirror of this note), not silently assumed.
- The service-layer boundary is testable from Phase 1 (panel tests exercise it), so the future API phase starts from proven core operations.
