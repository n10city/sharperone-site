# TEMPLEFORGE™ REGISTRY
## The Sharper ONE™ · Sovereign Repository Index
**Maintained by:** BiMKA™ · The SomeBody™ Company  
**Last Updated:** 2026-04-02  
**Status:** Living document — update on every repo change

---

## THE SIX

| Repo | What It Is | Deploy Target | Branch |
|---|---|---|---|
| `sharperone-site` | Main site (`sharper.one`) | `public_html/index.html` | `main` |
| `sharperone-start-lp` | `/start` QR landing page | `public_html/start/` | `main` |
| `sharperone-wall` | Wall of Edge™ (`sharper.one/wall/`) | `public_html/wall/` | `main` |
| `sharperone-soil` | SOiL™ intake system | `public_html/intake/` + `intake-c/` | `main` |
| `sharperone-oan` | OAN™ notification system | `public_html/start/` (status.json + admin/) | `main` |
| `sharperone-trade` | Trade pipeline dashboard | `ops.sharper.one/` | `main` |

---

## SERVER

- **Host:** i2i.HOST · Vultr · IP: `155.138.200.125`
- **Control Panel:** `engine.i2i.host` (Enhance)
- **Web Root:** `/var/www/e1508a19-43fd-42c4-97a1-958e8b5e6763/public_html/`
- **SSH Alias:** `sharper-one` (user `sharper_1`) · root via `i2i-prime`
- **Key:** `~/.ssh/sharper_one_deploy`

**PROTECTED PATHS — NEVER OVERWRITE DURING UNRELATED DEPLOYS:**
- `public_html/start/` — QR landing page; own deploy lane
- `public_html/wall/data/entries.json` — live Wall data; never SCP over
- `public_html/intake/sessions/` — live session data; never in Git

---

## NAMING CANON

**Pattern:** `sharperone-[what-it-is][-type-suffix-if-ambiguous]`

Suffix appears only when slug alone is ambiguous.
`wall` is self-evident. `soil` is self-evident. `start` is not — hence `-lp`.

**Future landing pages:** `sharperone-[segment]-lp`
Examples: `sharperone-barber-lp` · `sharperone-trade-lp` · `sharperone-seasonal-lp`

---

## EST™ INSTANCE PROTOCOL

| Instance | Role |
|---|---|
| **Claude.ai (chat)** | Strategy · brand judgment · QA · CTS™/CQT™ artifacts · project context |
| **Claude Code (WSL)** | File reads · patches · SSH/SCP deploys · git ops · server execution |

**The rule:** Server belongs to Claude Code. Context belongs to this chat. Never reverse without a reason.

---

## LOL™ ARCHIVE — KEY LEARNINGS

- `sharper_one_website_v2.html` is **server-only** — never tracked in any repo
- No `stable` branch exists in any local repo — `main` is the only branch in play
- Claude Code never writes to `/mnt/d/` — builds in container, SCPs to server
- Loop-in-bash: `cd` failure doesn't halt loop — init repos individually, confirm `pwd`
- Read live file before writing patch specs — never assume clean slate
- `git config --global init.defaultBranch main` — set globally
- `chown sharper_1:sharper_1 [dir]` required after any `mkdir` as root before SCP
- Google Fonts blocks WSL curl — pull woff2 files server-side
- `unset GIT_DIR && unset GIT_WORK_TREE` before any git op (Logseq contamination)

---

*TEMPLEFORGE™ · THAT S O M E B O D Y™ COMPANY · BiMKA™*
*Every LiFE™ has a ONE™. 🔱*
