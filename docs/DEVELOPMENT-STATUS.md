# Development status — 2026-11-12

The Laravel foundation and most admin workflows are implemented. Public content import and database-backed page/post rendering have started; full visual/SEO parity is still in progress. Nothing has been deployed or pushed.

## Implemented

- Laravel 12, PHP 8.3 project constraint, Pest 4, Pint and Larastan level 6.
- Vite + Tailwind 4 + Alpine, with separate public/admin entries, initial brand tokens and glass surface foundations.
- Self-hosted Montserrat Latin 400/600/700 with `font-display: swap` and its OFL license.
- Core schema/models plus installed permissions and activity-log tables, fixed role definitions and explicit account provisioning.
- Explicit fillable fields; encrypted settings and 2FA-secret storage; no default seeded users.
- Security headers, per-request CSP nonce in report-only mode, staging/error `noindex`, admin frame denial and strict short admin sessions, named login/enquiry limiters.
- Database queue, file cache, daily logs and secure example cookie settings. Apache source/dump guards and an uploads execution deny rule.
- A separate read-only legacy connection and `php artisan trivo:legacy-report`.
- A transactional, repeatable `trivo:import-content` command with dry-run support. The local `newtrivo` MySQL database now contains 35 imported pages and 49 imported posts; the pre-existing `/marketing-solutions` page was preserved and the duplicate Surat post remains a draft.
- Imported page/post records resolve at their exact mapped paths. Imported HTML is allow-list sanitized on import, edit and output; source-site asset links are localized to the existing public uploads path. Menu, taxonomy relationships, builder styling and other unavailable source data are not reconstructed.
- The shared public shell now uses the live Trivo navigation, local logo/partner assets, verified contact and social links, blue footer treatment and WhatsApp action. The imported homepage uses its database copy/images in a source-aware hero, partner strip, accessible service tabs, bullet-paginated case-study carousel, results/testimonials and team layout. Homepage testimonials are seeded from the captured public copy; section spacing continues to be tuned against the live reference.
- Hidden-path admin sign-in with mandatory TOTP enrollment, replay protection, hashed one-use recovery codes, progressive lockout, idle/session-version checks and optional IP restrictions.
- Shared admin components, permission-aware dashboard, protected activity log, persisted light/dark/system theme and a Super Admin styleguide denied in production.
- SMTP settings with encrypted/hidden credentials, validated sender fields, queued/immediate test delivery, encrypted recipient logs and sanitized errors. Console boot excludes decrypted DB mail settings from configuration caches.
- Bounded database queue worker scheduled every minute using a file-cache overlap lock; no Redis/Supervisor dependency.
- Literal redirect CRUD/CSV import, graph loop prevention and chain flattening, live GET/HEAD redirect/410 handling, hit counters and privacy-conscious 404 logging/prefill.
- Image library with guarded uploads, private staging, queued conversion, responsive WebP/AVIF, EXIF orientation/stripping, metadata/folders, usage protection and archive/restore. Published files and legacy uploads are retained; no video library or transcoding is implemented.
- Site/contact/social and analytics/verification settings with separate permissions, encrypted values, safe field validation, brand-image reference protection and section-preserving theme changes. Public tracking and source settings import remain pending.
- Page draft CRUD with typed ordered sections, exact path/collision checks, revision restoration, optimistic version checks and reversible archive. Imported published pages render through the shared public renderer; visual template parity and other publication workflows remain in progress.
- Page SEO form with canonical/robots validation, sitemap preferences, schema type and social overrides, live search preview, protected image references and separate SEO revision restoration. SEO-only permissions cannot modify page content; public SEO output remains pending.
- Backend page publish/schedule/reschedule/cancel with readiness validation, permanent path locks, stale-version protection, publication event history and a bounded minute cron publisher. Due pages are rechecked; blocked schedules retain a fixed error code and rotate retries without duplicate-event storms.
- Service draft CRUD with shared typed sections/path validation, featured images, display order, revision restoration and reversible archive. Existing source body/details/SEO fields are retained. The shared path catalog now covers service/portfolio paths as well as pages/posts/taxonomies; public templates remain pending.
- Service SEO editing reuses the shared admin editor/ContentSeo validation with Service schema choices, independent SEO permissions and model-scoped SEO revisions. Service source/content/path/status/custom schema JSON remain isolated from metadata writes.
- Service publication/scheduling shares readiness, transitions, bounded cron execution and the admin publication panel with pages. Featured images are rechecked, path locks survive cancellation, and model-scoped events prevent page/service ID collisions. Service public routes/templates remain pending.
- Portfolio draft CRUD with shared catalog save/archive/revision actions, typed case-study sections, featured images, ordering, path/version checks and model-scoped history. Source body/details/SEO fields remain retained; public templates are pending.
- Portfolio SEO editing uses the shared form/validator and catalog metadata actions, with WebPage/CreativeWork choices, portfolio-specific permissions, protected social images and isolated SEO revisions. Metadata writes cannot change case-study content/path/status or source fields.
- Portfolio publication/scheduling shares readiness checks, UTC transitions, permanent path locks and bounded cron execution with pages/services. Featured/social images are rechecked when due, and model-scoped history prevents cross-module ID collisions. Public routing/templates remain pending.
- FAQ draft management reuses catalog actions and answer sections with optional exact paths, ordering, revisions, stale-edit checks and reversible archive. Source fields are retained; existing FAQ/testimonial/client paths are reserved across modules. FAQ SEO, publication and public placement/rendering remain pending.
- Testimonial draft management shares catalog actions and quote sections, with optional exact paths, image checks, ordering, versioned revisions and reversible archive. Source fields and imported paths remain protected; attribution fields, SEO, publication and public rendering remain pending.
- Client draft management supports names, ready raster logos, optional exact paths, ordering and optional supporting sections, sharing versioned revisions and reversible archive. Source fields and image references remain protected; SEO, publication and public placement/rendering remain pending.

- Menu management supports native metadata/item forms, safe links, ordering and bounded parent trees, version checks, model-scoped change history and reversible archive/restore. Imported IDs/locations remain protected; source mapping, historical replay and public navigation remain pending.

- Lead inbox supports protected lists/details, combined search/status/service/UTC-date filters, status/notes updates with stale-version checks and bounded CSV exports with formula protection. Original enquiry fields remain unchanged; public forms, notifications, replies and retention/deletion remain pending.

- Blog category/tag management supports exact immutable paths, separate slugs, plain descriptions, category hierarchy/depth checks, versioned revisions and protected archive/restore. Imported/assigned terms and source SEO fields remain protected; term SEO, post assignment/editor and public archives remain pending.
- Blog author-profile management keeps author content records separate from administration users, with exact immutable paths, safe social-link/avatar validation, versioned revisions and protected archive/restore. Post editing/assignment, author SEO and public archives remain pending.
- Blog post plain-text drafts support immutable paths, active author/category/tag assignment, ready featured images, stale-edit protection, scoped revision restoration and reversible draft archive/restore. A transparent server-side 0–100 SEO score and readability guidance are saved with each draft; score filters identify drafts below 50 or at least 80. Posts can publish immediately or on a bounded UTC schedule; publication rechecks readiness, retains a permanent path lock and records history. A dedicated SEO editor provides canonical, robots, sitemap, social metadata/image and Article/BlogPosting controls with isolated revisions. New drafts remain plain text; imported legacy HTML is sanitized and public posts resolve at their exact mapped paths.

## Validation

- Authentication/foundation, SMTP/redirect, image-library, website-settings, page/service/portfolio draft, SEO and publication, FAQ/testimonial/client draft, menu management, lead inbox, blog category/tag and author-profile suites: 421 tests pass with 2368 assertions. The blog-post draft suite additionally passes 9 tests with 63 assertions; a combined regression run remains pending.
- Python audit parser/crawler: five passing synthetic tests.
- Larastan level 6: no errors.
- Pint applied; final style check passes.
- Production asset build succeeds. Font URLs intentionally resolve from `public/fonts` at runtime.
- Previous Composer and npm audits reported zero known vulnerability advisories. The latest Composer refresh could not complete because Packagist DNS resolution failed, including with elevated execution; current advisory status remains unverified.
- Configuration, route, view and event cache commands succeed; generated caches were cleared after verification.
- Core migrations succeed on isolated SQLite test databases and the configured local `newtrivo` MariaDB 10.4.28 database. Supported production MySQL/MariaDB acceptance is still required.
- Chrome browser checks pass for seven screens across desktop light, desktop dark and true 390px mobile light viewports (21 audits), with zero recorded axe A/AA violations. A real SMTP test was accepted by a loopback-only synthetic server; no external email was delivered.
- Three image-library screens also pass nine browser accessibility audits. Their upload/conversion tests use only a synthetic fixture in separate storage; no legacy website images were processed.
- Site, social and analytics screens pass nine additional accessibility audits across desktop light/dark and 390px mobile light. Browser saves, invalid analytics values and theme return paths are verified without external requests.
- Page list/create/edit pass nine additional accessibility audits across the same layouts, covering section ordering/removal, draft saves, native JavaScript-disabled fallback, revisions and archive/restore with synthetic data.
- Page SEO passes nine additional accessibility audits across desktop light/dark and 390px mobile light, including normal/narrow live previews, unsaved fallbacks, saves, SEO revision restore and robots validation.
- Publication draft/scheduled/published states pass nine additional accessibility audits in the same layouts. The browser exercises missing-SEO rejection, scheduling, cancellation, publication and read-only content with isolated synthetic fixtures.
- Service create/edit/list pass nine additional accessibility audits across the same layouts, with draft saves, section/display order, revision restoration and archive/restore using only synthetic fixtures.
- Service SEO passes nine additional accessibility audits across the same layouts, covering the shared form, narrow preview, validation state, saves and revision restoration. The page SEO browser workflow is also rerun after the shared editor extraction.
- Service publication draft/scheduled/published states pass nine additional accessibility audits across desktop light/dark and 390px mobile light, covering readiness failures and schedule/cancel/publish. The page publication browser workflow is rerun after the shared publisher/panel extraction.
- Portfolio create/edit/list pass nine additional accessibility audits across the same layouts, exercising sections/order, revisions and archive/restore. The service draft browser workflow is rerun after extracting shared catalog actions.
- Portfolio SEO passes nine additional accessibility audits across desktop light/dark and 390px mobile light, exercising live/fallback/narrow previews, metadata saves/restoration and validation. The service SEO browser workflow is rerun after extracting shared catalog metadata actions.
- Portfolio publication draft/scheduled/published states pass nine additional accessibility audits across the same layouts. The service publication regression also passes nine audits. Browser QA uses a separate compiled-view directory to avoid Windows view-file rename contention with application tests.
- FAQ create/edit/list pass nine additional accessibility audits across the same layouts, covering ordered answer sections, saves, revisions, archive/restore and a pathless mobile entry using isolated synthetic data.
- Testimonial create/edit/list pass nine additional accessibility audits across the same layouts, covering ordered quote sections, saves, revisions, archive/restore and a pathless mobile entry. QA creates only synthetic data in the isolated database.
- Client create/edit/list pass nine additional accessibility audits across the same layouts, covering supporting sections, display order, saves, revision restore, archive/restore and a pathless mobile entry. No source logos or real client relationships are imported.

- Menu create/edit/list pass nine additional accessibility audits across the same layouts, covering native metadata/link saves, cycle rejection, parent selection, item/menu archive/restore and list filtering. No external links are fetched and no source navigation is imported.

- Lead list/validation/details pass nine additional accessibility audits across the same layouts, covering filters, rejected unsafe notes, follow-up saves and filtered CSV downloads. No external requests or mail are sent; all enquiry data is synthetic and isolated.

- Blog category and tag create/edit/list workflows each pass nine accessibility audits (18 total) across desktop light/dark and 390px mobile light. Saves, fixed-path rejection, revision restore and archive/restore use isolated synthetic data. Authenticated list/create checks also pass against the existing browser SQLite database, retaining both accounts.

## Runtime and current behavior

The system CLI is PHP 8.2.4 and local MariaDB is 10.4.28. An ignored, checksum-verified portable PHP 8.3 runtime is available at `.audit/php83/php.exe`, leaving XAMPP unchanged. The application now uses the local `newtrivo` database through the MySQL connection; tests and browser QA remain isolated SQLite. Production must use MySQL 8 or MariaDB 10.6+.

With the configured `newtrivo` MySQL database, the homepage and imported public pages/posts now resolve from the imported content. The static demo templates remain fallbacks for empty databases; full design parity is not complete. At a 1440px viewport, the results/team/blog heading landmarks now match the captured live positions (team 5206px; blog 5923px), and the two team/results images are placed at their observed coordinates. Unknown/legacy WordPress login routes return 404. Health is available at `/up`; admin sign-in is at `/{ADMIN_PATH}/login`. There are no default accounts or public forms. Provision accounts with `trivo:admin-create`; see ADMIN-AUTH.md.

An existing local browser process was still using `database/database.sqlite` despite the CLI MySQL configuration. Its seven pending migrations were applied after a consistent ignored backup, preserving both existing accounts and all data. Authenticated portfolio/FAQ/testimonial/client/menu/lead list checks now return HTTP 200 against that database. The CLI MySQL database remains separate; no accounts or source content were copied between connections.

## Next implementation

Remaining work includes recovering missing taxonomy relationships, navigation/menu records, author/featured-media mappings and unavailable custom-post-type content; public archive and visual/template parity; public SEO/schema/sitemap behavior; FAQ/testimonial/client publication; lead forms/replies; and provider/cron/security acceptance. The historical browser SQLite database is separate from the configured MySQL import target and was not modified by the content import. The old live-site mobile references need recapture with explicit viewport emulation; see PHASE-0-AUDIT.md. Module details are in BLOG-POST-DRAFTS.md, AUTHOR-PROFILES.md, BLOG-TERMS.md, LEAD-INBOX.md, MENU-MANAGEMENT.md, CLIENT-DRAFTS.md, TESTIMONIAL-DRAFTS.md, FAQ-DRAFTS.md, PORTFOLIO-PUBLICATION.md, PORTFOLIO-SEO.md, PORTFOLIO-DRAFTS.md, SERVICE-PUBLICATION.md, SERVICE-SEO.md, SERVICE-DRAFTS.md, PAGE-PUBLICATION.md, PAGE-SEO.md, PAGE-DRAFTS.md, WEBSITE-SETTINGS.md, IMAGE-LIBRARY.md and SMTP-AND-REDIRECTS.md.
