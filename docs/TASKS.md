# TASKS — tick as you go

## Phase 0 — Audit (no app code)
- [x] Initial page/link crawl + recursive live sitemaps → `docs/URL-MAP.csv` (139 observed URLs; exhaustive resource/query coverage remains pending)
- [ ] Detect permalink/category/tag/author bases, trailing slash, www/non-www
- [ ] Read local project → `docs/SITE-INVENTORY.md`
- [x] Capture viewport screenshots of 13 key templates (desktop/mobile) → `docs/reference/` (review and full-page coverage pending)

## Phase 1 — Foundation
- [x] Laravel 12 install, env, Pint, Larastan, Pest, Vite, Tailwind, Alpine
- [x] Foundation security middleware + headers, named rate limiters, session/cookie config (full security acceptance remains Phase 7)
- [x] Core migrations + models; remove demo seeder (RBAC/activity-log and explicit admin provisioning remain Phase 2)
- [x] Self-hosted Montserrat, design tokens in Tailwind theme

## Phase 2 — Admin core
- [x] Shared glass components, light/dark/system preference, development styleguide; tested implemented screens with axe (remaining module screens still require QA)
- [x] Hidden admin path, password + mandatory TOTP/recovery sign-in, roles/permissions, session expiry, activity log and explicit account provisioning (monitoring emails/user-management actions remain pending)
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
