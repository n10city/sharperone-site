# CTS™ — The Orb Found Its Home
**Date:** 2026-03-20 · **Thread:** Wall of Edge™ · Testimonial System · Full Deploy

---

## STEP 1 — CLOSE

- **Wall of Edge™ built and deployed** — live at `sharper.one/wall/` — public display, internal feed, AI-assisted entry form, segment filters, live stats, featured card system
- **Edge capture page built and deployed** — live at `sharper.one/edge/` — mobile-first, three-moment consent flow, before/after photo upload, feeds Wall via shared localStorage
- **BOSS™ entry sealed** — Featured, pole position, food processor moment preserved
- **Three-moment testimonial capture system** — architected, documented, flow diagram artifact sealed
- **Font sovereignty achieved** — Google Fonts dependency eliminated across both pages, self-hosted woff2 under `public/fonts/`, TiO™ font policy established for all future builds
- **The orb resolved** — real asset image (`Sharper-ONE_Logo-Symbol_app-icon_trans.png`) deployed to `public_html/icons/`, transparent fill, orange border trim, pulse radiates from rim — rendering correctly in incognito
- **Favicon deployed** — both pages, same icon asset, both tabs branded
- **`master → main`** — completed globally, local Git config updated, remote cleaned
- **CQT™ Protocol canonized** — branded HTML artifact + live Markdown mission card sealed; Markdown as canonical format locked into memory
- **Font policy** — baked into institutional knowledge, needs adding to `CLAUDE.md`

**Decisions locked:**
- Self-hosted fonts only — no Google Fonts CDN in any deployed file, ever
- Orb = real image asset, not CSS text or inline SVG — the WordPress-equivalent swap, sovereign version
- localStorage is session/browser-scoped — backend persistence is the next infrastructure priority
- Wall + Edge share origin at `sharper.one` — localStorage works across both on same browser only
- CTS™ and CQT™ artifacts = Markdown, dated, titled to the most poignant moment of the thread

---

## STEP 2 — TRANSITION

**New thread mission:** Upgrade Wall of Edge™ to persistent server-side storage — entries survive across all browsers, devices, and sessions

**Immediate objectives in sequence:**
1. Build `submit.php` — receives POST from `/edge/`, writes to JSON flat file on server
2. Build `entries.php` — serves stored entries as JSON to the Wall display
3. Update `edge.html` — swap localStorage write for POST to `submit.php`
4. Update `wall-of-edge.html` — fetch from `entries.php` on load, localStorage as fallback only
5. Test full cycle: submit on phone → appears on Wall on desktop
6. Migrate seed entries to server-side JSON

**Carried-forward context:**
- Server: Vultr · IP: `155.138.200.125` · Enhance: `engine.i2i.host` · Apache
- SSH alias: `sharper-one` · web root: `/var/www/e1508a19-43fd-42c4-97a1-958e8b5e6763/public_html/`
- `/start` — live and protected, never touched during deploys
- Wall entry schema: `id, name, segment, blade, quote, date, notes, featured, isPublic, before, after, source`
- Base64 photo storage hits localStorage limits fast — server persistence solves this too
- `ops.sharper.one` `.htaccess` Basic Auth pattern = reference for Wall admin protection

**Standing items for future threads:**
- Intake Cards — field-ready, printable, TiO™ standard
- Client Communications — transaction confirmations, onboarding series, nurture flow
- Payment process testing — Square walk-up + Stripe SEC™ membership verification
- VSCode `.code-workspace` file — create and commit to repo
- `CLAUDE.md` — font policy section needs to be added
- `go.sharper.one` — Shlink deployment
- Brevo integration — main site sticky bar + membership modal
- Wall of Edge™ admin protection — once backend is live

---

*CTS™ — Canmore Two-Step™ · The SomeBody™ Company · BiMKA™ · EST™*
*See yuh OTOS™* 🔱
