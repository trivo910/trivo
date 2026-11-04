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
- [x] Image library: validated private uploads, queued WebP/AVIF/srcset, metadata/folders, usage checks and archive/restore (legacy import/deployment checks remain pending; no video library)
- [x] Site, social and analytics settings: encrypted sections, role checks, safe IDs/URLs, brand-image reference protection (verified source import/public rendering remain pending)
- [x] SMTP settings + encrypted credentials, test email/log, runtime mail configuration and bounded cron queue worker (external provider/cron verification pending)
- [x] Literal redirects manager + atomic CSV import + 404 log + GET/HEAD 301/302/410 handling (complete canonical resolver remains Phase 4)

## Phase 3 — Content modules
- [x] Page draft foundation: exact paths, typed section builder, revision restore, stale-edit checks and reversible archive (public integration pending)
- [x] Page SEO editing: metadata/canonical/robots/social fields, search preview and separate SEO revisions (public output and blog scoring pending)
- [x] Page publication workflow: readiness checks, UTC schedules, cancel/reschedule, permanent path locks and bounded cron publisher (public rendering/hosting acceptance pending)
- [x] Service draft foundation: shared sections/path validation, featured images, display order, revisions and archive/restore (public templates pending)
- [x] Service SEO editing: shared metadata validation/form, service schema choices and isolated SEO revisions (public output pending)
- [x] Service publication workflow: shared readiness/cron execution, featured-image checks, UTC schedules and retained path locks (public rendering/hosting acceptance pending)
- [x] Portfolio draft foundation: shared content actions/sections, featured images, ordering, revisions and archive/restore (public templates pending)
- [x] Portfolio SEO editing: shared metadata form/validation, case-study schema choices and isolated SEO revisions (public output pending)
- [x] Portfolio publication workflow: shared readiness/cron execution, image checks, UTC schedules and permanent path locks (public rendering/hosting acceptance pending)
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
