# TASKS — tick as you go

## Phase 0 — Audit (no app code)
- [ ] Crawl live site + sitemaps → `docs/URL-MAP.csv`
- [ ] Detect permalink/category/tag/author bases, trailing slash, www/non-www
- [ ] Read local project → `docs/SITE-INVENTORY.md`
- [ ] Capture screenshots of key pages (desktop/mobile) for visual diff → `docs/reference/`

## Phase 1 — Foundation
- [ ] Laravel 12 install, env, Pint, Larastan, Pest, Vite, Tailwind, Alpine
- [ ] Security middleware + headers, rate limiters, session/cookie config
- [ ] Migrations + models + seeders (schema in ARCHITECTURE.md)
- [ ] Self-hosted Montserrat, design tokens in Tailwind theme

## Phase 2 — Admin core
- [ ] Glassmorphism design system: tokens, `.glass` utilities, `<x-admin.*>` components, light/dark toggle, `_styleguide` page (see ADMIN-DESIGN-GLASSMORPHISM.md)
- [ ] Hidden admin path, auth, mandatory 2FA, roles/permissions, activity log
- [ ] Media library with WebP/AVIF/srcset
- [ ] Settings (site, social, analytics), **SMTP settings + test mail**, mail provider boot
- [ ] Redirects manager + 404 log

## Phase 3 — Content modules
- [ ] Pages + blocks builder, Services, Portfolio, Testimonials, FAQs, Clients, Menus
- [ ] Blog: posts, categories, tags, authors, Tiptap editor, sanitisation
- [ ] **SEO panel + SeoScorer + ReadabilityScorer (live + server)**, SERP preview, revisions, scheduling
- [ ] Leads module (list/filter/export)

## Phase 4 — Public site
- [ ] Layout, header/footer, nav, floating WhatsApp
- [ ] Home + every page from SITE-INVENTORY.md matching live design
- [ ] Blog listing/category/tag/author/single with `/page/N/` pagination
- [ ] Forms → DB + queued SMTP email + spam protection
- [ ] Catch-all resolver, canonical/trailing-slash/HTTPS middleware, branded 404

## Phase 5 — SEO engine
- [ ] `<x-seo.head>` (meta, OG, Twitter, canonical, robots)
- [ ] JSON-LD graph builder
- [ ] sitemap index + children, robots.txt (admin-editable), RSS feed
- [ ] Breadcrumbs, image attributes (alt, width/height), heading validator

## Phase 6 — Import & parity
- [ ] `trivo:import-media`, `trivo:import-content`
- [ ] `trivo:verify-urls` → 100% pass; `CONTENT-DIFF.md`
- [ ] Visual QA vs reference screenshots

## Phase 7 — Hardening & performance
- [ ] Security checklist fully ticked + security tests
- [ ] `composer audit`, `npm audit`
- [ ] Response cache, critical CSS, Lighthouse targets
- [ ] Accessibility pass (axe)

## Phase 8 — Release
- [ ] README (install/deploy for VPS + Hostinger/rollback/backup/cron)
- [ ] Staging with noindex + basic auth → UAT
- [ ] Cut-over plan executed, GSC monitoring checklist
