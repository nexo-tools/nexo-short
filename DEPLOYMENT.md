# DEPLOYMENT — Nexo Short (Phase 4)

> **Status: DONE — deployed (dark) 2026-07-22.** nxo.li serves redirects, panel on
> nexoshort.alvarocdev.com; registration closed, SSO off. This runbook records the exact
> steps taken (guided with Alvaro). Based on the `deploy-laravel-hostinger` skill, extended
> for the **two-host** shape (one app serves the short host *and* the panel host — ADR-001/002).
>
> **As-deployed facts** (differ slightly from the generic runbook below): DB host is
> `localhost` (the MySQL grant is `@localhost`, not `@127.0.0.1`); nxo.li is a *separate site*
> on the plan (`~/domains/nxo.li/public_html` symlinked cross-domain to
> `~/domains/alvarocdev.com/nexo-short/public` — works here, no `open_basedir` block); the new
> nxo.li site defaulted to an old PHP → bump it to 8.4+; **no** `npm run build` (inline styles);
> `route:cache` is skipped (closure routes) — only `config:cache` + `view:cache`; Cloudflare's
> "Managed robots.txt" must stay **off**. Remaining before launch: Gate 4 ops (UptimeRobot
> canary `nxo.li/hb`=302 + `/up`=200, backup-restore test) then the Phase 5 SSO flip.

## Running it locally

Before deploying anywhere, this is how to get Nexo Short up on your own machine. The README
points here on purpose: keeping the steps in one place is why they stopped drifting.

### Option A — everything in Docker (recommended if you just want it running)

`compose.yaml` in this repo runs the **app only**: the author's machine keeps a single
MySQL/Mailpit shared by every Nexo tool, so shipping another database per repo would be
waste. `compose.selfhost.yaml` is the overlay that fills the gap for everyone else.

```sh
cp .env.example .env
# in .env: DB_HOST=mysql  DB_PORT=3306  MAIL_HOST=mailpit  MAIL_PORT=1025
docker compose -f compose.yaml -f compose.selfhost.yaml up -d
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
npm install && npm run build
```

The app answers on **http://localhost:8102** and outgoing mail lands in Mailpit at
http://localhost:8025.

### Option B — your own MySQL

Keep `compose.yaml` alone (or no Docker at all) and point `.env` at your database:
`DB_HOST` / `DB_PORT` / `DB_DATABASE` (`nexo_short`) / `DB_USERNAME` / `DB_PASSWORD`. Everything
else is a stock Laravel app: `composer install`, `php artisan key:generate`,
`php artisan migrate`, `npm run build`, `php artisan serve`.

> The values committed in `.env.example` target the author's shared local stack
> (`host.docker.internal:3307`). Override them — they are a default, not a requirement.

Run the suite with `vendor/bin/pest` (SQLite in memory — it never touches your database).

---

## Topology (what makes this different from the sibling tools)

One Laravel app, **two docroots pointing at the same `public/`**:

- **`nxo.li`** → Hostinger **addon domain**, serves the **short host** (redirects, branded 404,
  `/report`, robots.txt). Cloudflare proxied in front.
- **`nexoshort.alvarocdev.com`** → **subdomain**, serves the **panel host** (landing, auth,
  panel, privacy, terms).

The app decides behavior by request host (`NEXO_SHORT_HOST` / `NEXO_PANEL_HOST`), so both
docroots symlink to the *same* app `public/`.

## Prerequisites (Alvaro)

- [ ] Hostinger SSH access (port **65002**; use the exact SSH host from the panel, not the
      A-record). PHP + Composer over SSH; **no Node** (build assets locally/CI, `scp` them up).
- [ ] `nxo.li` added as an **addon domain** in hPanel.
- [ ] `nexoshort` subdomain created under `alvarocdev.com`.
- [ ] A MySQL database + user created in hPanel (prod credentials — never the dev `dev/dev`).
- [ ] Cloudflare zone for `nxo.li` (already on Cloudflare free).

## Cloudflare config for nxo.li (critical — ADR-002/004/006)

- [ ] DNS record for `nxo.li` (and `www`) **proxied** (orange cloud) → Hostinger origin IP.
- [ ] SSL/TLS mode **Full** (not Flexible).
- [ ] **IP Geolocation ON** (Network settings) → sends `CF-IPCountry` for click metrics.
- [ ] **No cache rules on the redirect path, no Workers, no "Always Online"** — the origin is
      the single source of truth; a cached 3xx would break the kill-switch and metrics (ADR-004).
- [ ] Remove the **temporary redirect rule** `nxo.li/* → alvarocdev.com` (PLAN task 0.8) only
      when the app is verified live.
- [ ] Lock the Hostinger origin to Cloudflare (firewall/allowlist Cloudflare IP ranges) so the
      real client IP always arrives via the proxy — required for `TRUSTED_PROXIES` below.

## First deploy

```bash
cd ~/domains/nxo.li            # or the account's domains dir
git clone <repo> nexo-short && cd nexo-short
composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

cp .env.production.example .env
php artisan key:generate       # BEFORE setting APP_ENV=production
# edit .env: APP_ENV=production, APP_DEBUG=false,
#   NEXO_SHORT_HOST=nxo.li, NEXO_PANEL_HOST=nexoshort.alvarocdev.com,
#   NEXO_AUTH_MODE=local, NEXO_ALLOW_REGISTRATION=false   (dark deploy),
#   DB_* (prod), SESSION_SECURE_COOKIE=true,
#   TRUSTED_PROXIES=<cloudflare ranges, or * if origin is locked to CF>,
#   MAIL_MAILER=smtp, MAIL_SCHEME=smtps, MAIL_HOST=smtp.hostinger.com, MAIL_PORT=465,
#   NEXO_SAFE_BROWSING_KEY=<key>  (optional but recommended for the public instance),
#   NEXO_ATTRIBUTION_URL=https://alvarocdev.com/?utm_source=nexo-short&utm_medium=powered-by

php artisan migrate --force
ln -s "$PWD/storage/app/public" "$PWD/public/storage"   # storage:link may fail (exec disabled)
# assets from local/CI:  npm ci && npm run build  (locally)  ->  scp -P 65002 -r public/build user@host:~/domains/nxo.li/nexo-short/public/
php artisan package:discover
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Point BOTH docroots at the same public/ (rm the default.php first):
cd ~/domains/nxo.li && rm -rf public_html && ln -s ~/domains/nxo.li/nexo-short/public public_html
# panel subdomain docroot (path depends on how the subdomain was created in hPanel):
rm -rf ~/domains/alvarocdev.com/public_html/nexoshort && ln -s ~/domains/nxo.li/nexo-short/public ~/domains/alvarocdev.com/public_html/nexoshort

# cron (hPanel -> Cron Jobs), once:
# * * * * * cd ~/domains/nxo.li/nexo-short && php artisan schedule:run >> /dev/null 2>&1
```

## Updates

```bash
cd ~/domains/nxo.li/nexo-short
php artisan down && git pull
composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
php artisan package:discover
# scp the new public/build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## Backups + uptime (Gate 4)

- [ ] **Verified backups**: DB dump + `.env` backed up; **restore tested once** into a scratch DB.
- [ ] **Redirect canary** (the product's heartbeat): an uptime monitor hitting a known slug on
      `https://nxo.li/<slug>` expecting **302** (if nxo.li falls, every published link dies).
- [ ] **Panel health check**: monitor `https://nexoshort.alvarocdev.com/up` expecting 200.
- [ ] Kill the canary deliberately once to confirm the alert fires (Gate 4 evidence).

## Post-deploy verification

```bash
# Panel: 200 + the STRICT CSP (not LiteSpeed's upgrade-insecure-requests)
curl -sS -o /dev/null -w "%{http_code}\n" https://nexoshort.alvarocdev.com
curl -sS -D - -o /dev/null https://nexoshort.alvarocdev.com | grep -i content-security-policy

# Short host: create a link in the panel, then
curl -sS -D - -o /dev/null https://nxo.li/<slug> | grep -iE 'HTTP/|location|cache-control|x-robots-tag'
#   expect: 302, Location: <target>, Cache-Control: no-store, X-Robots-Tag: noindex
curl -sS https://nxo.li/robots.txt      # Disallow: /
```

Then: real E2E (create link → click → 302 → click recorded in stats), no CSP violations in the
browser console, and record the deploy date in [AGENTS.md](AGENTS.md) § Production.

## Latency (ADR-002 revisit trigger)

Measure redirect latency from a realistic location once live (perf audit). If shared-hosting
latency proves unacceptable, that triggers a new ADR (edge accelerator / VPS) — the env-decoupled
short domain already keeps that migration open.

**Measured 2026-07-22 (from South America):** `nxo.li/hb` warm TTFB ~108–117 ms (302), ~450 ms
cold on the first hit. Well within acceptable; the ADR-002 revisit trigger is **not** met.
