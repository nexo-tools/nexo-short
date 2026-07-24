<div align="center">

<img src="resources/brand/mark.svg" width="88" alt="Nexo Short isotype">

# Nexo Short

**Short links on your own domain — the open-source URL shortener of the Nexo ecosystem (a self-hosted Bitly / Dub alternative).**
Cookieless click metrics, anti-abuse built in, no raw IPs.

[![CI](https://github.com/nexo-tools/nexo-short/actions/workflows/ci.yml/badge.svg)](https://github.com/nexo-tools/nexo-short/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-7C3AED.svg)](LICENSE)
![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777bb4.svg)
![Laravel 13](https://img.shields.io/badge/Laravel-13-ff2d20.svg)

[Live instance](https://nexoshort.alvarocdev.com) ·
[Deployment guide](DEPLOYMENT.md) ·
[Architecture](docs/ARCHITECTURE.md) ·
[Scope](docs/SCOPE.md)

</div>

---

Nexo Short is a **self-hosted URL shortener** — a Bitly / Dub alternative you run on
**your own domain** — with cookieless click metrics and privacy by design. It's part
of the **Nexo** family, so it ships the shared Nexo chrome and the optional single
account ([Nexo ID](https://github.com/nexo-tools/nexo-id) SSO). It is **live in
production**: redirects on **nxo.li**, panel on
**[nexoshort.alvarocdev.com](https://nexoshort.alvarocdev.com)**.

## Why Nexo Short?

- **Your own domain, no lock-in** — short links live on a domain *you* control (e.g.
  `nxo.li`), not a shared branded host. No platform can paywall or reclaim them.
- **Cookieless click metrics** — per-link click totals and stats with **zero cookies
  and no raw IPs stored**. No consent banner needed.
- **Anti-abuse built in** — optional Google Safe Browsing checks on new links, an
  anonymous report system (malicious / spam / abusive / broken) with moderation, and
  per-user and per-IP rate limits on link creation.
- **Split-host by design** — a **cookieless redirect host** (fast, sessionless) is kept
  separate from the **panel host** (SEO landing, auth and dashboard), each with its own
  `robots` policy so only the right surface is indexed.
- **Flexible auth** — standalone local accounts out of the box, or **SSO-only** via
  Nexo ID; registration can be open or closed by env (self-host opens it; the hosted
  instance keeps it closed).
- **Private and fast** — server-rendered redirects, **zero external requests** at
  runtime (no CDNs, no font services, no trackers).
- **Multilingual** — English, Spanish and Portuguese (`en`/`es`/`pt`) with a visible
  switcher and a translatable `/help` center.
- **Self-hostable** — a standard Laravel app; run the whole shortener on your own
  infrastructure.

## Tech stack

PHP 8.3+ · Laravel 13 · Blade + Alpine.js + Tailwind CSS (Vite) · MySQL

Nexo ID id_token verification with
[firebase/php-jwt](https://github.com/firebase/php-jwt). Quality:
[Pest](https://pestphp.com) · [Pint](https://laravel.com/docs/pint) ·
[Larastan](https://github.com/larastan/larastan) · GitHub Actions CI.
Zero external runtime requests — system font stack, no CDNs.

## Quick start (local)

Requirements: Docker — everything else runs in containers via
[Laravel Sail](https://laravel.com/docs/sail).

```bash
git clone https://github.com/nexo-tools/nexo-short.git
cd nexo-short
cp .env.example .env
docker run --rm -v "$(pwd):/app" -w /app composer:latest composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install && ./vendor/bin/sail npm run build
```

Open [http://localhost](http://localhost). Local email inbox (Mailpit):
[http://localhost:8025](http://localhost:8025). Nexo Short serves two hosts — the short
(redirect) host and the panel host; set `NEXO_SHORT_HOST` / `NEXO_PANEL_HOST` to map
them (see [DEPLOYMENT.md](DEPLOYMENT.md)).

## Self-hosting

Nexo Short is a standard Laravel app — run it on your own domain. See
**[DEPLOYMENT.md](DEPLOYMENT.md)** for the full guide (short + panel hosts, SMTP,
anti-abuse). Key configuration (see `.env.example`):

| Env var | Purpose | Default |
| --- | --- | --- |
| `NEXO_SHORT_HOST` | Domain that serves the redirects | `nxo.test` |
| `NEXO_PANEL_HOST` | Domain that serves the landing, auth and panel | `nexoshort.test` |
| `NEXO_AUTH_MODE` | `local` accounts or `sso` (Nexo ID only) | `local` |
| `NEXO_ALLOW_REGISTRATION` | Allow local sign-ups | `true` |
| `NEXO_SSO_ENABLED` | Enable the Nexo ID SSO client | `false` |
| `NEXO_SAFE_BROWSING_KEY` | Google Safe Browsing API key (optional) | empty |
| `NEXO_ATTRIBUTION_TEXT` | "Powered by" footer text | `Powered by alvarocdev.com` |
| `NEXO_SUPPORT_EMAIL` | Contact address on the `/help` center | `hola@alvarocdev.com` |

Slug length, creation / report rate limits and reserved slugs live in
[`config/nexo.php`](config/nexo.php).

## Status

**Live (dark) in production.** Phases 1–3 (core shortener, cookieless click metrics,
anti-abuse) plus the Nexo ID SSO client and the SEO landing are built and deployed —
redirects on **nxo.li**, panel on
**[nexoshort.alvarocdev.com](https://nexoshort.alvarocdev.com)** (currently dark:
registration closed, SSO off). Gates 0–3 are signed and ADRs 001–008 accepted.
Remaining: Gate 4 ops (uptime monitors, backup-restore test), then the Phase 5 launch
(flip to SSO and open the source).

## Documentation

- [Scope](docs/SCOPE.md) · [Architecture](docs/ARCHITECTURE.md)
- Specs: [phase 1 — core](docs/SPEC-phase-1-core.md) · [phase 2 — metrics](docs/SPEC-phase-2-metrics.md) · [phase 3 — anti-abuse](docs/SPEC-phase-3-anti-abuse.md)
- [Plan & gates](docs/PLAN.md) · [Decisions (ADRs)](docs/adr/)
- [Deployment guide](DEPLOYMENT.md)

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

## License

[MIT](LICENSE) © [Álvaro Carrizales](https://alvarocdev.com) — the tech behind Nexo.
