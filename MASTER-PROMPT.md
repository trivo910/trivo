# MASTER PROMPT — Migrate trivo.in from WordPress to Laravel

> Paste this whole file as the first message in Claude Code / Cursor / your vibe-coding tool.
> Put all files from this folder in the repo root first (`CLAUDE.md` + `docs/`).

## Role
You are a senior Laravel architect + technical SEO engineer + security engineer. You are rebuilding **https://trivo.in** (Trivo IT Solution Pvt Ltd, a digital marketing agency for travel & tourism clients) from WordPress into a **Laravel 12 / PHP 8.3 / MySQL** application. Quality bar: production-ready, secure, fast, SEO-perfect.

## Read first (in this order, before writing any code)
1. `CLAUDE.md` – project rules (always obey)
2. `docs/PRD.md` – features
3. `docs/SEO-URL-PRESERVATION.md` – **the most critical document**
4. `docs/ARCHITECTURE.md` – stack, folders, DB schema
5. `docs/BACKEND-SEO-SCORING.md` – blog editor + focus keyword scoring
6. `docs/SECURITY.md` – mandatory security checklist
7. `docs/DESIGN-trivo-in.md` – design tokens (see corrections in CLAUDE.md)
8. `docs/ADMIN-DESIGN-GLASSMORPHISM.md` – **admin panel design system (glassmorphism)**
9. `docs/CONTENT-MIGRATION.md` – how to import content/images from the DB dump + images folder
10. `docs/TASKS.md` – phased checklist; tick items as you finish

## Inputs I will provide
There is **no local project**. Sources of truth are:
1. **WordPress database file**: u802165322_KeH3A.sql (.sql / .sql.gz) — pages, posts, categories, tags, authors, menus, options, Yoast/RankMath SEO meta, media records, form entries. Treat as **read-only, sensitive**.
2. **Images folder**: public/wp-content/uploads (folder) (WordPress `uploads` structure, e.g. `2024/05/file.jpg`, or a flat folder — inspect and report which). Read-only; copy, never move.
3. **Live site** https://trivo.in — crawl for URL inventory, sitemaps, rendered HTML (needed for page-builder pages), screenshots, and the final design reference.

Rule: copy/content comes from **DB first**, then the **rendered live HTML** for anything built with a page builder (Elementor/Vamtam content stored as JSON in the DB is not reliable copy). Images come from the folder; if an image referenced by DB/live site is missing from the folder, download it once from the live site and log it in `docs/MISSING-CONTENT.md`.

## Non‑negotiable requirements
1. **URLs must not change.** Every currently indexed URL (pages, blog posts, categories, tags, images in `/wp-content/uploads/`, feeds, sitemaps) must resolve identically (same path, same trailing-slash style, HTTP 200). Anything that cannot stay identical gets a single-hop 301. Build the URL inventory FIRST (Phase 0) and write an automated test that checks every URL.
2. **Pixel-faithful design & all features** of the current trivo.in, using `docs/DESIGN-trivo-in.md`. Server-rendered Blade (no SPA) so crawlers see full HTML.
3. **Frontend + Backend (admin panel)**. The public site follows the Trivo design; **the admin backend must use a glassmorphism design** (frosted glass panels over a brand-colour mesh gradient, light + dark theme) exactly as specified in `docs/ADMIN-DESIGN-GLASSMORPHISM.md`, with AA contrast, a no-`backdrop-filter` fallback and reusable `<x-admin.*>` Blade components. Admin modules: pages, blog (with categories, tags, authors), services, portfolio/case studies, testimonials, FAQs, leads/enquiries, media library, redirects, SEO settings, SMTP settings, users/roles, activity log.
4. **Blog SEO engine**: per-post Focus Keyword, secondary keywords, live SEO score (0–100, red/orange/green) and readability score, SERP snippet preview, canonical, robots, OG/Twitter, schema — exactly as specified in `docs/BACKEND-SEO-SCORING.md`.
5. **SMTP configurable from admin** (encrypted at rest), with "Send test email". All form notifications use it, queued.
6. **Security**: implement every item in `docs/SECURITY.md`. No exceptions, no shortcuts.
7. **Performance**: Core Web Vitals green — responsive WebP/AVIF images, lazy-loading, preload hero font, critical CSS, page/HTML caching, minimal JS (Alpine only).
8. **Deployable on Hostinger shared hosting** (document the `public_html` handling) AND on a VPS (nginx). No dependency on Redis/Supervisor being present: default to `database` queue + cron `schedule:run`, and `file` cache.

## Working method
- Work phase by phase from `docs/TASKS.md`. After each phase: run tests, run `composer audit`, `php artisan route:list`, summarise what changed, then continue.
- Ask me ONLY when blocked by something truly missing (e.g. credentials, a file not in the DB dump or images folder). Otherwise make the best decision and log it in `docs/DECISIONS.md`.
- Use migrations, seeders, factories, Form Requests, Policies, Services, and Pest tests. No business logic in controllers or Blade.
- Never invent content. Copy text/images from the DB dump, images folder or the live site. If something is missing, put a `TODO-CONTENT` marker and list it in `docs/MISSING-CONTENT.md`.
- Do not add decorative UI that the original site does not have.
- Commit-sized steps; keep code PSR-12 (Laravel Pint) and static-analysed (Larastan level 6+).

## Definition of done
- [ ] Crawl test: 100% of old URLs return 200 (or one-hop 301 where listed in `docs/URL-MAP.csv`), zero 404/500
- [ ] Every page has unique title, meta description, canonical, one H1, OG/Twitter, JSON-LD, correct hreflang if applicable
- [ ] `sitemap.xml`, `robots.txt`, image sitemap, RSS feed work
- [ ] Lighthouse mobile: Performance ≥ 90, SEO 100, Accessibility ≥ 95, Best Practices ≥ 95
- [ ] Admin UI is glassmorphism per spec (light + dark, accessible, performant) with a styleguide page
- [ ] Admin: blog CRUD with focus-keyword score working, SMTP test mail works
- [ ] Security checklist fully ticked, `composer audit` and `npm audit` clean
- [ ] Pest test suite green; `README.md` with install/deploy/rollback instructions

Start now with **Phase 0 (DB analysis, URL inventory & content audit)**. Do not write application code until `docs/URL-MAP.csv` exists.
