# ADR-008 — Short domain and SEO: nxo.li is noindex; all SEO lives on the landing

- **Date:** 2026-07-20
- **Status:** Accepted (accepted at Gate 0 — 2026-07-21, Alvaro)

## Context

The two surfaces have opposite SEO needs. The landing/panel domain is a public product surface and gets the standards' full SEO base (title/description, OG, canonical, JSON-LD, sitemap, hreflang — `validate-generated-site` checklist). The short domain must be the opposite: short links are user content pointing elsewhere — having search engines index `nxo.li/{slug}` pages would create thin/duplicate results, leak link slugs, and associate the domain's reputation with whatever users shorten. Redirects themselves (302) pass no meaningful equity and shouldn't be crawl targets.

## Decision

1. **The short domain is entirely non-indexable**: `X-Robots-Tag: noindex` on all short-domain responses (redirects, 404, `/report`), plus a restrictive `robots.txt` on the short host. Belt and suspenders, same style as ADR-004.
2. **`robots.txt` disallows everything on the short domain** except `robots.txt` itself. Crawlers that ignore robots still hit the `X-Robots-Tag` header.
3. **All SEO investment goes to the landing domain** (Phase 5 landing work): the standards' SEO base applies there and only there.
4. **Mechanical enforcement**: tests assert the `X-Robots-Tag` header on short-domain responses (redirect, 404, report) and that the landing responses do NOT carry it.
5. Multi-instance: this is host-based behavior, not domain-hardcoded — any self-hoster's short domain gets the same treatment automatically.

## Alternatives considered

- **Let slugs be indexed ("free backlinks")** — rejected: a shortener's slugs are not content; indexation creates spam-looking thin pages under the domain whose reputation is the product's single point of failure.
- **robots.txt only, no header** — rejected: robots.txt prevents crawling but not indexing of already-known URLs; the header is the authoritative signal, the file reduces crawl noise.

## Consequences

- The short domain contributes nothing to discoverability by design; the landing carries that job (and the attribution/branding surface).
- The `/report` page is noindex but must remain reachable by direct link from 404s, the landing and the terms page — findability comes from those links, not from search.
