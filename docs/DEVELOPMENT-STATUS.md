# Development status — 2026-11-04

The Laravel foundation is implemented. The public migration and admin application are not finished, and nothing has been deployed or pushed.

## Implemented

- Laravel 12, PHP 8.3 project constraint, Pest 4, Pint and Larastan level 6.
- Vite + Tailwind 4 + Alpine, with separate public/admin entries, initial brand tokens and glass surface foundations.
- Self-hosted Montserrat Latin 400/600/700 with `font-display: swap` and its OFL license.
- Core schema/models plus installed permissions and activity-log tables, fixed role definitions and explicit account provisioning.
- Explicit fillable fields; encrypted settings and 2FA-secret storage; no default seeded users.
- Security headers, per-request CSP nonce in report-only mode, staging/error `noindex`, admin frame denial and strict short admin sessions, named login/enquiry limiters.
- Database queue, file cache, daily logs and secure example cookie settings. Apache source/dump guards and an uploads execution deny rule.
- A separate read-only legacy connection and `php artisan trivo:legacy-report`.
- Hidden-path admin sign-in with mandatory TOTP enrollment, replay protection, hashed one-use recovery codes, progressive lockout, idle/session-version checks and optional IP restrictions.
- Shared admin components, permission-aware dashboard, protected activity log, persisted light/dark/system theme and a Super Admin styleguide denied in production.
- SMTP settings with encrypted/hidden credentials, validated sender fields, queued/immediate test delivery, encrypted recipient logs and sanitized errors. Console boot excludes decrypted DB mail settings from configuration caches.
- Bounded database queue worker scheduled every minute using a file-cache overlap lock; no Redis/Supervisor dependency.
- Literal redirect CRUD/CSV import, graph loop prevention and chain flattening, live GET/HEAD redirect/410 handling, hit counters and privacy-conscious 404 logging/prefill.
- Image library with guarded uploads, private staging, queued conversion, responsive WebP/AVIF, EXIF orientation/stripping, metadata/folders, usage protection and archive/restore. Published files and legacy uploads are retained; no video library or transcoding is implemented.
- Site/contact/social and analytics/verification settings with separate permissions, encrypted values, safe field validation, brand-image reference protection and section-preserving theme changes. Public tracking and source settings import remain pending.
- Page draft CRUD with typed ordered sections, exact path/collision checks, revision restoration, optimistic version checks and reversible archive. Published/scheduled/previously published page content remains read-only; public template integration remains pending.
- Page SEO form with canonical/robots validation, sitemap preferences, schema type and social overrides, live search preview, protected image references and separate SEO revision restoration. SEO-only permissions cannot modify page content; public SEO output remains pending.
- Backend page publish/schedule/reschedule/cancel with readiness validation, permanent path locks, stale-version protection, publication event history and a bounded minute cron publisher. Due pages are rechecked; blocked schedules retain a fixed error code and rotate retries without duplicate-event storms. Public routes/templates are still pending.
- Service draft CRUD with shared typed sections/path validation, featured images, display order, revision restoration and reversible archive. Existing source body/details/SEO fields are retained. The shared path catalog now covers service/portfolio paths as well as pages/posts/taxonomies; public templates remain pending.
- Service SEO editing reuses the shared admin editor/ContentSeo validation with Service schema choices, independent SEO permissions and model-scoped SEO revisions. Service source/content/path/status/custom schema JSON remain isolated from metadata writes.
- Service publication/scheduling shares readiness, transitions, bounded cron execution and the admin publication panel with pages. Featured images are rechecked, path locks survive cancellation, and model-scoped events prevent page/service ID collisions. Service public routes/templates remain pending.
- Portfolio draft CRUD with shared catalog save/archive/revision actions, typed case-study sections, featured images, ordering, path/version checks and model-scoped history. Source body/details/SEO fields remain retained; public templates are pending.
- Portfolio SEO editing uses the shared form/validator and catalog metadata actions, with WebPage/CreativeWork choices, portfolio-specific permissions, protected social images and isolated SEO revisions. Metadata writes cannot change case-study content/path/status or source fields.
- Portfolio publication/scheduling shares readiness checks, UTC transitions, permanent path locks and bounded cron execution with pages/services. Featured/social images are rechecked when due, and model-scoped history prevents cross-module ID collisions. Public routing/templates remain pending.

## Validation

- Authentication/foundation, SMTP/redirect, image-library, website-settings, and page/service/portfolio draft, SEO and publication suites: 292 tests pass with 1523 assertions.
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

## Runtime and current behavior

The system CLI is PHP 8.2.4 and local MariaDB is 10.4.28. An ignored, checksum-verified portable PHP 8.3 runtime is available at `.audit/php83/php.exe`, leaving XAMPP unchanged. The application now uses the local `newtrivo` database through the MySQL connection; tests and browser QA remain isolated SQLite. Production must use MySQL 8 or MariaDB 10.6+.

The homepage currently returns HTTP 503 until verified content and templates exist. It does not serve the Laravel demo. Unknown/legacy WordPress login routes return 404. Health is available at `/up`; admin sign-in is at `/{ADMIN_PATH}/login`. There are no default accounts or public forms. Provision accounts with `trivo:admin-create`; see ADMIN-AUTH.md.

## Next implementation

Remaining content modules (testimonials, FAQs, clients, menus, blog and leads), then import services and public templates against the captured sources. Public SEO output, blog scoring, SEO defaults/schema/robots/sitemap settings and CSP-aware custom scripts remain pending. Real provider/cron checks, user/access management actions, authentication alert emails and final security acceptance remain pending. The old live-site mobile references need recapture with explicit viewport emulation; see PHASE-0-AUDIT.md. Module details are in PORTFOLIO-PUBLICATION.md, PORTFOLIO-SEO.md, PORTFOLIO-DRAFTS.md, SERVICE-PUBLICATION.md, SERVICE-SEO.md, SERVICE-DRAFTS.md, PAGE-PUBLICATION.md, PAGE-SEO.md, PAGE-DRAFTS.md, WEBSITE-SETTINGS.md, IMAGE-LIBRARY.md and SMTP-AND-REDIRECTS.md.
