# ADR-003 — Auth and launch: hosted instance is SSO-only via Nexo ID; public launch waits for it; standalone auth still ships in the code

- **Date:** 2026-07-19 (amended 2026-07-20 — see "Update" below)
- **Status:** Proposed (core decision taken by Alvaro during Phase 0 planning, 2026-07-19; formal acceptance at Gate 0)

## Context

Anti-abuse is a hard MVP requirement (brief §5): link creation must sit behind an account gate — "no auth, open to everyone" is off the table. Nexo ID exists but is in Phase 0→1 (planning signed off, no code yet). Its accepted decisions already constrain this project:

- **nexo-id ADR-004 §1**: every Nexo tool ships standalone local auth (single-tool self-host works with zero extra services). §5: *"Nexo Short launches SSO-only against Alvaro's Nexo ID on the hosted instance (registration barrier is the point), while still shipping the standalone mode for self-hosters."*
- **nexo-id PLAN Phase 3** is literally "First client: Nexo Short".

The open decision was the launch strategy for the hosted instance: couple to Nexo ID, or launch earlier with provisional/own auth. Coupling needed explicit justification (it ties this project's launch to an unbuilt sibling).

## Decision

1. **Alvaro's hosted instance launches SSO-only against Nexo ID** — Nexo Short is Nexo ID's first client, as both projects' plans state. The hosted instance never exposes a public registration/password surface of its own in v1: registration, email verification and credentials live in Nexo ID.
2. **The public launch of the hosted instance gates on Nexo ID's provider being live** (nexo-id Phase 2) and on the Phase 3 client integration. Chosen by Alvaro over a provisional local-auth beta, with this justification: there is no launch pressure, and a provisional public auth would be throwaway work plus a user migration for zero time gain. This satisfies the rule that coupling to nexo-id must be justified in an ADR on both sides (nexo-id side: its ADR-004 §5 and PLAN Phase 3).
3. **Development does not wait.** Phases 1–3 (core shortener, metrics, anti-abuse) build against the **standalone local auth** that the code must ship anyway per nexo-id ADR-004 — used by self-hosters and by dev/tests. Only the *public hosted launch* is coupled.
4. **Auth mode is instance configuration** (env), exactly per nexo-id ADR-004: local-only (self-host default), SSO-only (Alvaro's hosted instance), or both. The SPEC must define behavior for each mode and for Nexo ID being unreachable (graceful degradation: active local sessions keep working; only new logins block).
5. **Anti-abuse coupling**: the account gate on the hosted instance is Nexo ID's registration with verified email (nexo-id SCOPE security minimums). Rate limiting and the rest of the package (ADR-005) apply regardless of auth mode — a self-hosted instance with local auth gets the same protections.

## Alternatives considered

- **Minimal provisional local auth + invite-only closed beta, migrate to Nexo ID later** — viable and initially recommended (fastest decoupled launch), but rejected by owner: builds a public-facing auth surface destined for deletion, plus an account migration, to win time nobody needs.
- **Own auth with open public registration from day 1** — rejected: duplicates exactly what Nexo ID is being built to provide, and puts the weakest version of the anti-abuse gate (fresh unproven registration flow) in front of the riskiest surface.
- **Hard dependency on Nexo ID (no standalone mode at all)** — rejected: contradicts nexo-id ADR-004 (accepted at its Gate 0), kills the single-tool self-host story, and would leave this project with no auth to develop against until nexo-id Phase 2.

## Consequences

- Launch timeline is coupled to nexo-id Phases 1–2. Accepted trade-off; if nexo-id stalls badly, the fallback (invite-only beta on the already-shipped standalone auth) is a Gate decision, not a rewrite.
- Nexo Short's SSO integration reuses the client pattern nexo-id builds in its Phase 3 (OIDC client + `NEXO_SSO_*` env + local session + graceful degradation) — coordination point between both plans.
- Until launch, the deployed instance (Phase 4 dark deploy) runs with registration closed: local auth exists but no public signup.
- Standalone auth is real product surface (self-hosters), not scaffolding: it gets the same SPEC/AC/test discipline (rate-limited login, secure sessions), reusing sibling patterns.

## Update — 2026-07-20 (nexo-id state changed; launch condition re-anchored)

nexo-id closed its Phases 1–2 (audited "yes with conditions"): the OIDC provider is **live in production** at `nexoid.alvarocdev.com` (46/46 ACs), with the integration contract published in its `docs/INTEGRATION.md`. Consequences for this ADR:

- The "provider live" precondition of Decision §2 is **already satisfied** — it no longer gates anything.
- The launch condition inherited from nexo-id's audit replaces it: **the hosted instance does not launch to real users until nexo-id has verified backups + uptime monitoring** (its deferred T4, planned as a cross-tool ops pass). Recorded as an external condition of this project's launch gate (PLAN Gate 5).
- The reusable OIDC client pattern still does **not** exist: nexo-id's Phase 3 builds it *with Nexo Short as first consumer*. Anything touching the nexo-id side is planned with Alvaro — this project does not implement in that repo on its own.
