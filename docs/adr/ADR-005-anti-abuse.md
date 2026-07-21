# ADR-005 — Anti-abuse: the launch-gating package (account gate, Safe Browsing, rate limits, reserved slugs, kill-switch, reporting)

- **Date:** 2026-07-19
- **Status:** Accepted (requirement level fixed in the brief; accepted at Gate 0 — 2026-07-21, Alvaro)

## Context

A public shortener is a phishing/spam magnet; without defenses the short domain lands in blacklists (Google Safe Browsing, mail filters, Instagram/WhatsApp blocks) within weeks, and every published link dies with it. The brief fixes anti-abuse as an MVP requirement — the open questions were only the concrete mechanisms and their v1/backlog split. Constraints from the standards: rate limiting always on public inputs; no third-party/cookie-based challenges (no reCAPTCHA); privacy by design (no raw IPs at rest).

## Decision

The v1 package — all of it is a **gate before any public exposure** of the hosted instance:

1. **Account-gated creation**: links are created only by authenticated users (ADR-003). On the hosted instance that means a Nexo ID account with verified email; on self-hosts, local accounts (registration open or closed is the self-hoster's choice via env).
2. **Rate limiting** on link creation (per user and per IP) and on auth endpoints (per account and per IP). IP is used in-memory/short-window for limiting only — never persisted raw (consistent with ADR-006).
3. **Target URL scheme whitelist at creation**: only `http`/`https` targets are accepted — `javascript:`, `data:`, `file:`, etc. are rejected at validation, before any external check. Pattern copied/adapted from nexo-links `app/Rules/LinkUrl.php` (CATALOG canonical source).
4. **Google Safe Browsing Lookup API check at creation**: a flagged target URL is rejected. Configured by env; if no API key is set (self-hosts), the feature degrades gracefully and the instance runs without it. Privacy note, recorded deliberately: this sends the *submitted target URL* (user content) to Google server-side at creation time — it involves no visitor data, no browser-side third party, and no cookies, so it does not breach the zero-third-parties browser principle. Documented in the privacy page.
5. **Reserved slugs**: `admin`, `api`, `app`, `dashboard`, `login`, `logout`, `register`, `help`, `terms`, `privacy`, `report`, `abuse`, `status`, plus the Nexo tool names and the set proven by nexo-links' reserved-usernames pattern (CATALOG canonical source: `app/Rules/Username.php` + `config/nexo.php`). Enforced in validation with a test that exercises the list.
6. **Kill-switch per link** (`is_active`): moderation deactivates without deleting (evidence preserved). Effective on the next click because redirects are never cached (ADR-004).
7. **Report channel**: `{short-domain}/report` (reserved slug) — a simple, rate-limited, no-auth report page reachable from the branded 404 and the landing; delivery mechanism (form → mail vs stored queue) is a SPEC detail. An abuse contact is published.
8. **Terms of use + privacy page**, simple and published at launch.

Backlog (hardening, not launch-blocking — see SCOPE): periodic Safe Browsing re-check of existing targets, domain-level blocklist/allowlist, submission heuristics, moderation dashboard beyond the kill-switch.

## Alternatives considered

- **Open creation + moderation after the fact** — rejected: blacklisting is faster than reactive moderation; the brief treats the account gate as the first barrier.
- **CAPTCHA (reCAPTCHA/hCaptcha)** — rejected by standards: third party + cookies contradict the Nexo privacy pitch. If a challenge is ever needed, it must be self-hosted and cookieless (ALTCHA-style) — backlog, only if rate limits + account gate prove insufficient.
- **Storing raw IPs for abuse forensics** — rejected: brief fixes no-raw-IPs; if anti-fraud ever needs it, salted hashes (brief §4), decided then in its own ADR.

## Consequences

- Safe Browsing adds an external dependency on the creation path (not the redirect path): the SPEC must define behavior on API timeout/error (fail-open with flag for review vs fail-closed — decided in SPEC with Alvaro).
- The gate for public exposure includes deliberate-violation evidence: rate limit actually blocks, a `javascript:`/`data:` target is rejected, a known Safe Browsing test URL is rejected, reserved slugs cannot be registered, a deactivated link 404s immediately.
- Multi-instance consequence: every mechanism is env-configurable and documented for self-hosters; none may assume Alvaro's infrastructure.
