<div align="center">

<img src="resources/brand/mark.svg" width="88" alt="Nexo Short isotype">

# Nexo Short

**Short links on your own domain — the open-source URL shortener of the Nexo ecosystem.**
Cookieless click metrics, privacy by design (no raw IPs), self-hostable.

</div>

---

Status: **Live (dark) in production** — Phases 1–3 (core shortener, click metrics, anti-abuse) + Nexo ID SSO client + SEO landing, all built and deployed. Redirects on **nxo.li**, panel on **nexoshort.alvarocdev.com** (Hostinger + Cloudflare, dark: registration closed, SSO off). 121 tests, Gates 0–3 signed, ADRs 001–008 Accepted. Remaining: Gate 4 ops (uptime monitors, backup-restore test), then Phase 5 launch (flip to SSO + open-source). See [docs/PLAN.md](docs/PLAN.md), [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md), [DEPLOYMENT.md](DEPLOYMENT.md).

## Nexo ecosystem

Nexo is a family of open-source, self-hostable tools that share one visual identity
([nexo-brand](https://github.com/nexo-tools)), one optional account
([Nexo ID](https://github.com/nexo-tools/nexo-id) SSO) and one set of engineering
standards. Every tool runs **fully standalone** — the ecosystem is opt-in.

| Tool | What it is | Repo |
| --- | --- | --- |
| **Nexo Tools** | Ecosystem hub — discover the tools and hop between them with one account | [nexo-tools](https://github.com/nexo-tools/nexo-tools) |
| **Nexo Links** | Link-in-bio you host yourself (Linktree alternative) | [nexo-links](https://github.com/nexo-tools/nexo-links) |
| **Nexo Agenda** | Bookings for service businesses (AgendaPro / Fresha / Booksy alternative) | [nexo-agenda](https://github.com/nexo-tools/nexo-agenda) |
| **Nexo Short** | Self-hosted URL shortener | — you are here |
| **Nexo Events** | Event tickets and passes | [nexo-events](https://github.com/nexo-tools/nexo-events) |
| **Nexo ID** | One account for every tool — OAuth 2.0 / OIDC SSO | [nexo-id](https://github.com/nexo-tools/nexo-id) |

New to Nexo? Start at **[nexotools.alvarocdev.com](https://nexotools.alvarocdev.com)**.
Built by **[alvarocdev.com](https://alvarocdev.com)** — the tech behind Nexo.
