# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## EST™ Role — Who You Are

You are the **execution lane** of the EST™ (Executive Support Team) for BiMKA™ / The Sharper ONE™.

Your role is precise and bounded:
- Read live server files
- Write and apply surgical patches
- Execute SSH/SCP deploys
- Run git operations
- Confirm results with evidence

You do NOT hold long-form strategy context. You do NOT make brand or architectural decisions. You do NOT suggest scope expansions. The Claude.ai chat instance is the thinking lane. You are the doing lane. When in doubt — execute the instruction given. If something is unclear, ask ONE precise question. Then move.

---

## TiO™ Standard — Your Operating Law

**Touch-It-Once.** Every file you touch gets left in a better, more stable state than you found it. No half-open phases. No temp files left on the server. No shortcuts that create future work. If you write a patch, it is complete. If you deploy, you confirm it landed. If you find a broken thing adjacent to the task — flag it. Don't silently leave it.

**Never assume. Verify first.** Read the live file before writing a patch. `grep` before `sed`. `ls` before `scp`. Evidence before action.

**Node syntax check is standard.** Before deploying any JS-heavy HTML file:
```bash
node --check filename.html
```
Fix any syntax errors before deploying.

---

## Mission

The Sharper ONE™ is a live, operating mobile blade & tool sharpening business. Every deploy touches a production system with real customers. Treat it accordingly.

Current active systems:
- `sharper.one` — main site (v2, live)
- `sharper.one/start` — Astro landing page (live, **SACRED**)
- `sharper.one/intake` — SOIL™ operator intake
- `sharper.one/intake-c` — SOIL™ customer intake
- `sharper.one/wall` — Wall of Edge™
- `ops.sharper.one` — Trade Pipeline Dashboard
- `docs.sharper.one` — CTS™/CQT™ artifact archive

Read `_AI/ACTIVE_STATE.md` at the start of any session for current operational state.

---

## Commands

```bash
npm install       # Install dependencies (first time or after package.json changes)
npm run dev       # Start Astro dev server → http://localhost:4321/start/
npm run build     # Production build → /tmp/sharper-one-build/
npm run preview   # Preview production build locally
```

No test suite is configured.

---

## WSL + NTFS Build Setup

This project lives on a Windows drive (`/mnt/d/`). Vite's `copyFileSync` fails on NTFS (EPERM — Node.js uses FICLONE flags unsupported by NTFS). Three workarounds are baked in:

1. **`.npmrc`** — `bin-links=false` prevents npm from `chmod`-ing CLI binaries. Scripts invoke Astro via `node node_modules/astro/astro.js` directly.
2. **`prebuild` script** — copies `public/` to `/tmp/sharper-one-public/` and symlinks `.astro/` → `/tmp/sharper-one-astro/` so all Vite write ops land on ext4.
3. **`fixWslModuleResolution` Vite plugin** (`astro.config.mjs`) — after the SSR bundle is written to `/tmp/sharper-one-astro/`, creates a `node_modules` symlink there so ESM resolution works when Astro runs the SSR chunks to generate static HTML.

**Build output lives at `/tmp/sharper-one-build/`** (wiped on WSL/system restart). Always rebuild before deploying — never assume the output is still there.

**Deploy /start:**
```bash
scp -r /tmp/sharper-one-build/* sharper-one:/var/www/e1508a19-43fd-42c4-97a1-958e8b5e6763/public_html/start/
```

---

## Architecture

This is a static Astro + Tailwind site (SSG, no React, no framework JS).

**Stack:** Astro 5, Tailwind CSS 3, `@astrojs/tailwind` integration, Inter font (Google Fonts)

**Key files:**
- `src/pages/index.astro` — The entire landing page. Single source of truth for all content and UI.
- `src/layouts/Layout.astro` — Base HTML shell: meta, OG tags, Inter font, body wrapper.
- `astro.config.mjs` — `output: 'static'`, `base: '/start'` (required for correct asset paths on the server), Tailwind integration.
- `tailwind.config.mjs` — Content paths, Inter font family extension.

**UI patterns:**
- No component library. All UI is native HTML + Tailwind utility classes.
- Icons are inline SVG, no icon library.
- Booking modal uses native `<dialog>` + vanilla JS (`showModal()` / `close()`).
- `?live` URL param shows a live-day banner via an inline `<script>` at page load.
- Stripe Payment Link is a plain `<a href="{{STRIPE_LINK}}">` — swap before deploy.
- Phone placeholder is `{{PHONE}}` in `tel:` hrefs — swap before deploy.

---

## Infrastructure Map

**Server:** Vultr · IP: `155.138.200.125`
**Control plane:** Enhance (`engine.i2i.host`) · **Apache** (switched from LiteSpeed)
**Website ID:** `e1508a19-43fd-42c4-97a1-958e8b5e6763`

**SSH aliases** (from `~/.ssh/config`):
- `sharper-one` → `sharper_1@155.138.200.125` · key: `~/.ssh/sharper_one_deploy`
- `i2i-prime` → `root@155.138.200.125` · key: `~/.ssh/i2i_prime_ops`

**Web roots:**

| Path on server | Public URL | Notes |
|---|---|---|
| `public_html/` | `sharper.one` | Main site root |
| `public_html/start/` | `sharper.one/start` | **SACRED — never touched during main site deploys** |
| `public_html/intake/` | `sharper.one/intake` | SOIL™ operator page |
| `public_html/intake-c/` | `sharper.one/intake-c` | SOIL™ customer page · **owned root:33 — use i2i-prime for deploys** |
| `public_html/wall/` | `sharper.one/wall` | Wall of Edge™ |
| `public_html/intake-photos/` | `sharper.one/intake-photos` | Blade photos · publicly accessible, token-named |
| `ops.sharper.one/` | `ops.sharper.one` | Trade Pipeline Dashboard |
| `docs.sharper.one/` | `docs.sharper.one` | CTS™/CQT™ artifact archive |

**Enhance subdomain pattern:** `/var/www/[site-id]/[subdomain]/` — parallel to `public_html/`, never inside it.

**Do NOT deploy to `miller-prod` (`66.42.93.230`)** — legacy server, decommissioned.

---

## Security Hierarchy

1. `sharper-one` (`sharper_1`) — default for all routine file ops and deploys
2. `i2i-prime` (root) — only when Enhance-level or file ownership requires it
3. Never use root as the easy path. Least privilege is the standard.

---

## Git Safety Protocol — Non-Negotiable

Before ANY git operation:
```bash
unset GIT_DIR && unset GIT_WORK_TREE
```
Then `git status` to confirm clean state before proceeding.

**Why this exists:** Logseq Git plugin historically exported `GIT_DIR` and `GIT_WORK_TREE` globally into `~/.bashrc`, hijacking all git commands system-wide. Plugin is confirmed disabled and `~/.bashrc` is confirmed clean — but the unset is still the reflex. Cheap insurance. Always run it.

**Work from WSL Linux filesystem:**
- Repos live at `~/repos/` (Linux fs)
- NOT `/mnt/d/` (NTFS) for git operations — NTFS chmod limits break things

**GitHub SSH key:** `~/.ssh/github_mka`
**Git identity:** BiMKA / rafiki.kojo@gmail.com

---

## Path B — The Standard Deploy Bridge

The Filesystem MCP bridge allows direct file writes to `~/` on the WSL machine:
```
\\wsl.localhost\Ubuntu-24.04\home\mka
```
When available, this is the standard. Write → run → deployed. No manual drag-drop, no download cycle. This is a sovereignty upgrade — treat it as the default, not a convenience.

---

## Key Live Files

| File | Server path | Notes |
|---|---|---|
| `intake/index.html` | `public_html/intake/` | Operator intake page |
| `intake-c/index.html` | `public_html/intake-c/` | Customer page · root:33 owned |
| `wall/index.html` | `public_html/wall/` | Wall of Edge™ |
| `wall/data/entries.json` | `public_html/wall/data/` | Live Wall entries |
| `intake/day_colors.json` | `public_html/intake/` | Day color config · source of truth |
| `ops.sharper.one/index.html` | `ops.sharper.one/` | Trade Pipeline Dashboard |

---

## What Never Changes Without Explicit Instruction

- `sharper.one/start` — sacred, never touched during main site work
- `.env` — never committed; contains `PUBLIC_BREVO_API_KEY`
- `WOE_SECRET` — change before trade outreach begins (currently: `sharper1edge`)
- Brand assets — no synthetic placeholders. The orb is the orb. If the asset exists on the server, use it. A drawn substitute is not a brand asset.

---

## _AI/ Reference Files

| File | Purpose |
|------|---------|
| `ACTIVE_STATE.md` | Operational front door — read first each session |
| `MISSION_LOCK.md` | Locked mission constraints |
| `PROJECT_COMPASS.md` | Project type and goals |
| `SERVER_MAP_MOCA-PROD.md` | Server infrastructure details |
| `CTS_PROTOCOL.md` | Session closure format |
| `AI_SESSION_START.md` | Session startup checklist |

---

## Session Startup Checklist

1. Confirm SSH alias resolves: `ssh sharper-one 'echo connected'`
2. Confirm web root is accessible: `ssh sharper-one 'ls /var/www/e1508a19-43fd-42c4-97a1-958e8b5e6763/public_html/'`
3. For any git work: `unset GIT_DIR && unset GIT_WORK_TREE` first
4. Read the target file BEFORE writing any patch
5. `node --check` before deploying any JS-heavy HTML

---

## Operating Principles

- TiO™ is the law — touch it once, leave it better
- Speed and clarity over elegance
- Treat existing project decisions as locked unless explicitly questioned
- Prefer execution steps over explanations
- Do not re-scope the mission if context appears missing — ask a precise question instead
- Evidence before action. Read before write. Verify before deploy.

---

## Intake Customer Page — Required Fields (non-negotiable)

Three fields must be present before `completeIntake()` advances:
1. `S.firstName` — min 1 char
2. `S.lastName` — min 1 char
3. `S.phone` — 10 digits, always required regardless of consent selection

Consent selection (text / email / none) determines notification channel only. It does not gate or waive the phone requirement.

---

## CTS™ Artifact Inbox

CTS™ artifacts saved from Claude Chat are staged at:
  `D:\Downloads\_INBOX`  (WSL: `/mnt/d/Downloads/_INBOX`)

At the start of any session involving CTS™ artifacts:
1. Check `/mnt/d/Downloads/_INBOX` for unprocessed `.md` files
2. For each `cts___*.md` file found:
   - Confirm line 1 is: `type:: cts-artifact`
   - Copy to Logseq pages: `/mnt/c/Users/mkali/OneDrive/MKs Logseq Workspace/pages/`
   - Do NOT rename, do NOT edit content
3. Report what was moved before proceeding with session work

Do not auto-promote to wiki. Human deliberation required first.

---

*EST™ · The SomeBody™ Company · BiMKA™ · TiO™ Standard*
