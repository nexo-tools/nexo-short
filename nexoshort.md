# NexoShort — Acortador de links del ecosistema Nexo

> Documento de evaluación y diseño. Síntesis de la sesión del 19/07/2026.
> Estado: idea validada, dominio elegido, pendiente de desarrollo.

## 1. Qué es

Acortador de links público y open source, parte del ecosistema de herramientas Nexo (nexolinks, nexoagenda, nexoevents) del portafolio de alvarocdev.com. Métricas básicas incluidas. Como el resto de las tools: parte de una idea conocida (Bitly) pero con extras que mejoran o complementan la experiencia.

## 2. Decisiones cerradas

| Decisión | Elección | Razón |
|---|---|---|
| Nombre del producto | **NexoShort** | Sigue el patrón `nexo + algo`; distinción obvia frente a nexolinks |
| Dominio de redirects | **nxo.li** | 6 caracteres, disponible sin marca premium, $8.54/año parejo (registro = renovación) |
| Registrador | **Dynadot** | Más barato que Hostinger (~$14) para .li; WHOIS privacy gratis; panel simple; Porkbun no vende .li |
| Panel / landing / API | `nexoshort.alvarocdev.com` | Mantiene el patrón de las demás tools y apunta a la marca personal |
| Stack | **PHP + MySQL en Hostinger compartido** | Coherente con el resto del ecosistema; un redirect con lookup indexado es trivial en carga; hosting ya pago hasta ~2029 |
| DNS / protección | **Cloudflare free delante de nxo.li** | DNS rápido, DDoS, analytics básicos gratis |
| Auth | **Nexo ID** (proyecto hermano, ver `nexoid/nexo-id.md`) | Registro obligatorio = primera barrera anti-abuso, sin construir otro login |

### Checklist post-compra del dominio

- [x] Dominio comprado en Dynadot (19/07/2026, $8.54, renueva Jul 2027 por $8.54)
- [x] Auto-renew activado (dominio vencido = todos los links rotos + riesgo de squatting)
- [x] WHOIS privacy: **no aplica** — el registry de .li no admite servicios de privacidad (política del TLD, no de Dynadot). Datos reales obligatorios; el nombre ya es marca pública, no-problema
- [ ] Si llega email de verificación de contacto, confirmarlo (siendo ccTLD puede no llegar; si no llega, todo bien)
- [ ] Agregar nxo.li a Cloudflare (plan free) y cambiar los nameservers en Dynadot
- [ ] Mientras no exista el MVP: redirect rule en Cloudflare de nxo.li → alvarocdev.com (dominio útil desde el día 1, sin hosting)
- [ ] Apuntar a Hostinger como dominio adicional cuando exista el MVP (SSL: proxy de Cloudflare, modo Full)

## 3. Separación clave: dominio corto vs producto

- **nxo.li** sirve *solo* los redirects. Es un "fusible": barato, reemplazable, y aísla el riesgo.
- **nexoshort.alvarocdev.com** aloja landing, panel y API.
- Nunca servir redirects públicos de terceros desde `*.alvarocdev.com`: si un usuario sube phishing, el que entra a las listas negras (Google Safe Browsing, filtros de email, bloqueos de Instagram/WhatsApp) sería el dominio de la marca personal completo.

## 4. Arquitectura técnica

### Modelo de datos (mínimo)

```sql
CREATE TABLE links (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(32) NOT NULL UNIQUE,      -- índice único: el lookup del redirect
  target_url TEXT NOT NULL,
  user_id    INT UNSIGNED NOT NULL,            -- FK a Nexo ID
  is_active  TINYINT(1) NOT NULL DEFAULT 1,    -- kill-switch por link (abuso)
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clicks (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  link_id    INT UNSIGNED NOT NULL,
  clicked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  referrer   VARCHAR(255) NULL,
  device     VARCHAR(32) NULL,                 -- parseado del user-agent (mobile/desktop/bot)
  country    VARCHAR(2) NULL,                  -- opcional: header CF-IPCountry de Cloudflare, gratis
  INDEX idx_link_fecha (link_id, clicked_at)
);
```

Sin IPs crudas: para métricas básicas no hacen falta y evitan problemas de privacidad. Si algún día se necesita anti-fraude, guardar hash con sal.

### El redirect: 302, no 301

**La decisión técnica más importante del proyecto.** Un 301 (permanente) queda cacheado por el navegador: después del primer click el usuario ya no pasa por el servidor y las métricas mueren. Usar **302** (o 307). Flujo:

1. Request a `nxo.li/{slug}`
2. Lookup por índice único en `links` (activo → destino)
3. INSERT en `clicks` (o encolar/agregar si algún día pesa)
4. `Location: {target_url}` con 302
5. Slug inexistente → 404 con página propia (branding + link a nexoshort)

### Slugs

- Aleatorios: base62 (a-z, A-Z, 0-9), 6-7 caracteres. Colisión: regenerar (con índice UNIQUE es un retry barato).
- Personalizados: opcionales, validados (regex `[a-zA-Z0-9_-]{3,32}`).
- **Lista de reservados** para que nadie los registre: `admin`, `api`, `login`, `app`, `dashboard`, `help`, `terms`, `privacy`, `report`, `abuse`, nombres de las tools nexo, etc.

## 5. Anti-abuso (obligatorio antes de abrir al público)

Un acortador público es un imán de phishing/spam. Sin esto, el dominio termina en blacklists en semanas:

1. **Creación de links solo con cuenta** (Nexo ID).
2. **Rate limiting** por usuario y por IP en la creación.
3. **Google Safe Browsing API** (gratis): validar la URL destino al crear y opcionalmente re-chequear periódicamente.
4. **Mecanismo de reporte**: página o mailto en el 404/landing (`nxo.li/report` reservado).
5. **Kill-switch** (`is_active`) para desactivar links sin borrarlos.
6. Términos de uso simples publicados.

## 6. Métricas básicas (alcance v1)

Clicks totales y por día, referrer, tipo de dispositivo, país (via Cloudflare). Gráfico simple por día en el panel. Todo sale de la tabla `clicks` con queries agregadas; sin herramientas externas.

## 7. API e integraciones con el ecosistema

- API REST con token por usuario: crear/listar/desactivar links, leer stats.
- Integraciones internas (el diferencial "ecosistema"):
  - **nexoevents**: cada evento genera su link corto automáticamente.
  - **nexolinks**: opción de acortar los links de la bio.
  - **nexoagenda**: links cortos para compartir agenda/reservas.

## 8. Open source

Repo público con README que documente la arquitectura (el README es portafolio). Referencias para espec de features (no como base, el valor está en construirlo propio):

- **YOURLS** (yourls.org) — exactamente el mismo stack PHP+MySQL
- **Shlink** (shlink.io) — PHP moderno, API-first
- **Dub** (github.com/dubinc/dub) — la referencia de UX/features actual (Next.js)

## 9. Roadmap

1. ✅ Evaluación, nombre y dominio — **comprado** (nxo.li en Dynadot, $8.54/año)
2. Checklist post-compra (sección 2) + Cloudflare
3. MVP: schema, endpoint redirect 302, slugs (aleatorios + custom + reservados), 404 propio
4. Panel en `nexoshort.alvarocdev.com` (auth: Nexo ID, o login simple provisional si Nexo ID no está listo)
5. Métricas básicas + gráfico
6. Anti-abuso completo (sección 5) — gate obligatorio antes del lanzamiento público
7. API REST + primera integración interna
8. Repo open source + landing
9. Extras futuros: QR por link, expiración de links, UTMs

## 10. Alternativas evaluadas y descartadas

| Alternativa | Por qué no |
|---|---|
| nxo.cc | Tomado |
| nxo.sh | Libre pero ~$46-82/año de renovación según registrador; 6-10x el costo de .li por estética |
| nxo.to / nxo.me / nexo.link | Válidos pero .li ganó en largo + precio |
| Comprar dominio en Hostinger | ~$14 vs $8.54 primer año, y renovaciones más caras; comprar en registrador y apuntar DNS no complica nada |
| Vercel (Next.js + serverless) | Fragmenta el ecosistema justo cuando se quiere consolidar; free tiers con límites |
| VPS para esto | Innecesario: un redirect no lo exige y el compartido está pago hasta ~2029. El VPS es decisión aparte, disparada por el crecimiento del sistema del bar, no por NexoShort. Cuando llegue: Hostinger KVM 1 ~$5/mes promo (renovación +20-40%) + Coolify/Dokploy para consolidar lo de Vercel |
| Dominio nexotools.com ahora | Postergado: el patrón `nexo*.alvarocdev.com` apunta todo a la marca personal (objetivo actual del portafolio) y el SSO por cookie compartida depende del dominio padre común. Se reevalúa si "Nexo" se vuelve suite con identidad propia. Comprarlo solo como reserva defensiva (~$10/año) es opcional |

## 11. Costos

- Dominio nxo.li: **$8.54/año** (Dynadot, renovación igual)
- Hosting, SSL, Cloudflare, Safe Browsing API: **$0** (ya pago o gratis)
- **Total nuevo: ~$8.54/año**
