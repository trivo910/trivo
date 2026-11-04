# Development decisions

## 2026-11-03 — SMTP and redirect management
- Store SMTP values as one encrypted settings record. Never return the saved password to the browser, serialize it into queue payloads, or cache decrypted DB credentials. HTTP boot loads runtime mail settings; console boot skips them so config:cache stays clean. Queue processing refreshes configuration at execution time.
- The queue toggle controls test delivery only. Public-form notifications remain required to use queued jobs when implemented. A bounded minute-based database worker with a file-cache overlap lock supports shared hosting without Supervisor or Redis.
- A local-only SMTP sink verified the actual transport with synthetic addresses and credentials. It authenticates and stores a message but cannot relay. No external email was sent and no real provider credentials were added.
- Redirect management supports literal 301/302/410 rules with same-site destinations. Protect system/admin entrypoints, reject loops, flatten chains, and apply CSV imports transactionally. Keep regex rows read-only until a reviewed regex implementation is needed.
- Serve saved rules through a public GET/HEAD middleware, while leaving full host/HTTPS/slash canonicalization and content resolution in Phase 4. Do not claim overall URL parity from the redirect tests.
- 404 records omit queries, referrers, raw IPs and admin paths; redact email-like path fragments and hash user agents. Redacted/sensitive paths are not offered as automatic redirect suggestions.
- Browser QA used a separate synthetic SQLite database and tested seven screens in three layouts. The temporary PHP/SMTP servers were stopped after verification; the application database still has no default users or saved SMTP credentials.

## 2026-11-04 — Portfolio publication workflow
- Extend the shared publication workflow and explicit due-model allowlist to portfolio entries. Validate case-study schema context, content/SEO/path and ready content/featured/social images at scheduling and due time.
- Reuse portfolio publication permissions, stale-version/page/media/content-path locks, explicit UTC inputs, permanent path retention after cancellation and separate model-scoped events. No public routes/templates or source data are generated.
- Add a bounded minute portfolio command without Redis/Supervisor or new dependencies. Preserve intended timestamps, record anonymous system events and retain fixed-code rotating failure retries. Shared-hosting sequential cron limits and production/public integration remain pending.
- Isolate browser QA's compiled Blade views under `.audit/browser-qa/views` using VIEW_COMPILED_PATH. Parallel PHP/browser verification hit a Windows rename lock on the shared compiled stat-card view; the isolated cache avoids contending with application/test views without changing runtime permissions.

## 2026-11-04 — Portfolio SEO editing
- Reuse ContentSeo validation and the existing admin SEO form with WebPage/CreativeWork choices for case studies. Public JSON-LD generation remains pending; source custom schema JSON is retained.
- Share catalog metadata save/restore actions between services and portfolio while retaining typed entrypoints, independent permissions, row/version/image locks and model-scoped SEO revision ownership checks.
- SEO Managers can edit portfolio metadata without content mutation. Preserve title/sections/source fields/image/order/path/status; reject archived records and stale updates. Historical social-image references remain protected.
- Portfolio publication, verified case-study templates, public output and source metadata import remain pending. No real source data or images are rewritten by validation.

## 2026-11-04 — Portfolio draft foundation
- Add portfolio draft management under existing permissions. Preserve source body/details/SEO fields and exact imported paths; retain read-only published content and reversible archive with reserved paths/history.
- Share catalog draft save/archive/revision actions with services, keeping typed entrypoints and model-scoped revisions. Reuse existing section/media validation, version checks and content-path/image locks. Portfolio owner exclusion includes table and ID to prevent cross-model collisions/restores.
- Do not invent case-study project/client/results data or expose raw HTML editing. Template-specific source fields, portfolio SEO/publication, public rendering and import remain pending.
- Local application configuration now targets the `newtrivo` MySQL connection on MariaDB 10.4.28; migrations were applied without importing source content. This is local development, not production database acceptance. Tests/browser fixtures remain isolated SQLite, with an explicit DB_CONNECTION override for the QA server.

## 2026-11-03 — Service publication workflow
- Share publication readiness, transitions, bounded due execution and the admin panel between pages/services. Keep typed existing page entrypoints and an explicit service command/model context; events/revisions remain isolated by model and ID.
- Require service-context SEO, valid sections/path and ready content/featured/social images. Preserve source fields and timestamps, reject stale transitions and permanently lock paths on first schedule/publish, including after cancellation.
- Backfill path locks for existing published/scheduled services without inventing historical publication dates. System events remain anonymous; readiness errors are fixed codes with bounded, rotating retries and no duplicate-event storm.
- Add the minute service publisher to existing cron scheduling. Real shared-hosting sequential-runtime/database checks and all public rendering/cache/URL-parity integration remain pending. No source content or real services are published by validation.

## 2026-11-03 — Service SEO editing
- Reuse shared ContentSeo validation and the shared admin SEO editor for pages and services. Keep the page workflow's default schema choices; service overrides allow Service/WebPage/FAQPage.
- Guard service metadata under `services.seo` independently of content permissions. Lock the service/version and social images, retain source/content/path/status/custom schema fields and record model-scoped SEO revisions.
- Reject wrong-kind, wrong-owner and cross-model restoration even when IDs match. Historical social-image references remain protected. Public output, service publication and legacy metadata import remain pending.

## 2026-11-03 — Service draft foundation
- Add service draft management using existing service permissions. Keep published/scheduled services read-only, preserve imported paths, and prohibit imported-service archive. Source body/details/SEO values are retained rather than rewritten by the section editor.
- Extract the page validator as shared ContentDrafts and reuse the existing section UI. Validate title/path/sections, featured image and bounded display order; lock all selected images and reject stale versions.
- Extend the shared path catalog to services and portfolio entries. Exclude only the exact owner table/ID, reserve archived/case/trailing-slash aliases, and keep the homepage page-only. Existing page publishing now rechecks this expanded catalog.
- Service revisions restore only draft content/image/order while retaining the current path and source metadata. Archive remains reversible and keeps paths/history/image references. Service SEO/publication, public templates and import remain explicit pending work.

## 2026-11-03 — Page publication workflow
- Add explicit backend publish/schedule/cancel transitions under `pages.publish`. Require non-empty sections, title/description metadata, consistent robots/sitemap state and valid ready image references. Recheck all readiness rules at scheduled execution.
- Keep schedule inputs explicitly UTC and preserve the intended publication timestamp. First schedule/publish permanently locks the path, including after cancellation; no archive/unpublish workflow is exposed for these pages. Published content stays read-only while richer editorial workflows remain pending.
- Use a bounded minute cron command with page/media/content-path locks, stale-version protection and separate publication event revisions. Readiness failures retain the schedule with a fixed code, suppress duplicate events and rotate retries. System worker events have no inferred human actor.
- Publication state does not enable public routes or deploy templates. Real source import, supported database/cron validation, concurrent cross-module URL coordination and public cache/template integration remain pending.

## 2026-11-03 — Page SEO editing
- Keep SEO mutations separate from content, guarded by `pages.seo`. Permit metadata editing on active published/scheduled pages without granting content mutation; reject archived pages.
- Share content version checks and page/media locks with existing workflows. Mark SEO revisions explicitly, isolate their history/restore actions and retain historical image references. SEO restore cannot change content, paths, status or custom schema JSON.
- Validate plain metadata, same-host HTTPS canonicals and explicit robots directives. Derive noindex/nofollow flags, reject noindex/sitemap conflicts and preserve source copy without automatic title/description generation.
- Search preview uses text bindings and unsaved fallbacks. This step does not publish pages, enable public SEO output, implement blog scoring or import legacy metadata. Keep remaining requirements visible in TASKS.md.

## 2026-11-03 — Page draft foundation
- Begin content management with drafts only. Keep published/scheduled/previously published pages read-only until the SEO/publication workflow exists. Do not create public routes or import content in this milestone.
- Preserve entered canonical paths; reject reserved routes and catalog collisions, including case/trailing-slash aliases. Imported paths are immutable. Shared content-write locking and database constraints protect draft writes; remaining modules must participate before claiming cross-module concurrency guarantees.
- Use bounded typed plain-text/image/link sections. Reject HTML and unsupported keys/types; richer source/template schemas and the Tiptap sanitisation pipeline remain pending. Video handling remains manual.
- Every save creates a revision. Restore title/sections as a new content version while retaining the current path and SEO values. Version checks reject stale changes. Archive only never-published, non-imported drafts, retain paths/history and continue protecting historical image references.

## 2026-11-03 — Website settings
- Separate site/social (`settings.site`) and analytics/verification (`settings.seo`) sections. Use existing role permissions; grant no new permissions to Editors. Validate and encrypt each section independently, and audit section events without values.
- Accept plain text, safe HTTPS social URLs and tracker IDs/tokens only. Do not invent or import values during implementation. Public tracking stays inactive until the public rendering/CSP integration; custom scripts and the rest of SEO settings remain separate pending work.
- Brand images must be ready, unarchived rasters with alt text; site icons must be square and at least 48 pixels. Lock selected media rows on save so archive cannot race with assignment, and retain image reference protection.
- Reuse shared glass form components and preserve the current section when changing theme. Synthetic tests/browser checks use isolated data and send no tracking requests.

## 2026-11-03 — Image library and manual videos
- User requested manual video handling: keep ordinary `<video>` tags for future embeds. Do not build video libraries, transcoding or automatic video processing.
- New image uploads use private staging and queued Intervention Image/GD conversion to sanitized JPEG/PNG masters plus responsive WebP/AVIF. Require EXIF/fileinfo and GD support for both output formats; preserve orientation while stripping metadata.
- Keep original legacy uploads read-only. Development verification uses generated synthetic images in isolated database/storage roots; no legacy images were converted or imported.
- Deduplicate uploads by hash, require alt text, restrict metadata edits, and check content references before archiving. Archive is reversible and retains published files; legacy assets cannot be archived through this module.
- Admin previews require authentication. Public picture rendering only selects generated variants; it never processes images during a request. Production storage/queue and legacy import checks remain pending.

## 2026-11-02 — Requirements and audit order
- Follow MASTER-PROMPT.md and CLAUDE.md, beginning with Phase 0. Do not write application code before a substantive docs/URL-MAP.csv exists.
- Use root CONTENT-MIGRATION.md for the migration workflow. The docs/CONTENT-MIGRATION.md copy describes an unavailable local reference project and is stale relative to the supplied SQL dumps and uploads folder.
- Both named SQL dumps exist. Neither is designated authoritative: inspect and compare their public-content metadata before selecting an import source. Never merge blindly or import legacy users, sessions, secrets, or form entries.
- Treat SQL dumps and public/wp-content/uploads as read-only source material. Exclude both from Git. A separate wp_legacy database is required for analysis; no application database may be overwritten.
- Live observations must be recorded as evidence, not assumed from the requirements. Initial attempts to access robots.txt and the three sitemap entrypoints through the web tool failed; this does not establish the origin server HTTP status.

## 2026-11-02 — Updated media location
- Rechecked root CONTENT-MIGRATION.md: media source is now public/wp-content/uploads, and that directory exists. The former root _uploads directory is absent. Preserve this source in place; future media commands must handle source and mirror destination being the same path without overwriting originals.
- The root migration guide names u802165322_KeH3A.sql; MASTER-PROMPT.md still names u364121842_3Zy6A.sql. Use the updated root migration guide as the working selection, verifying its site identity before any import.


## 2026-11-02 — Design reference supplied
- docs/DESIGN-trivo-in.md is now available, alongside docs/SKILL-trivo-in.md. The missing design-reference gap is resolved. CLAUDE.md corrections continue to override the auto-extracted type scale; verified live visuals remain authoritative.


## 2026-11-02 — Verified audit and foundation
- Both migration guides and MASTER-PROMPT.md now consistently name u802165322_KeH3A.sql and public/wp-content/uploads. Earlier conflicting references above are historical.
- Selected dump header is 2026-11-02; alternate is 2026-06-04. Allowlisted site settings verify trivo.in. No source files were modified.
- Import only an allowlisted public subset into a new wp_legacy, using prepared statements. Full raw-dump execution would import prohibited accounts and potentially secrets. Attachments use wp_attachments in this intentionally sanitized schema. Existing databases are never overwritten by the importer.
- Initial web failures were resolved with the identified audit client. The baseline now records initial HTTP status, final status and one-hop alias behavior, rather than counting followed redirects as direct 200s.
- Live HTML identifies page 991442 as the owner of /digital-marketing-agency-in-surat; preserve colliding post 988965 for review and record both source IDs.
- Source-only Python tools handle the pre-Laravel audit. Application code began only after the substantive URL-MAP.csv existed. Exhaustive audit gaps remain explicit rather than being claimed complete.
- System PHP 8.2 and MariaDB 10.4 fall below project requirements. Use checksum-verified portable PHP 8.3 in ignored .audit/php83 and isolated SQLite for local foundation validation; do not change or upgrade XAMPP without a separate environment task. Production remains MySQL 8/MariaDB 10.6+ and PHP 8.3.
- Pest/Larastan are development-only dependencies. Alpine is the only public UI dependency. Python Pillow is audit-only, not an application dependency.
- Encrypt all settings values at rest and exclude values from model serialization, including SMTP data. Do not create seeded/default admin accounts; explicit provisioning and mandatory TOTP belong to the admin phase.
- Public templates and content are pending. The root route returns 503/noindex until verified rendering exists; do not publish a generic demo as the migrated site.
