# Development status — 2026-11-03

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

## Validation

- Authentication/foundation, SMTP/redirect, image-library and website-settings suites: 122 tests pass with 587 assertions.
- Python audit parser/crawler: five passing synthetic tests.
- Larastan level 6: no errors.
- Pint applied; final style check passes.
- Production asset build succeeds. Font URLs intentionally resolve from `public/fonts` at runtime.
- Composer and npm audits: zero known vulnerability advisories.
- Configuration, route, view and event cache commands succeed; generated caches were cleared after verification.
- Core migrations succeed on an isolated SQLite development/test database. Production MySQL/MariaDB validation is still required.
- Chrome browser checks pass for seven screens across desktop light, desktop dark and true 390px mobile light viewports (21 audits), with zero recorded axe A/AA violations. A real SMTP test was accepted by a loopback-only synthetic server; no external email was delivered.
- Three image-library screens also pass nine browser accessibility audits. Their upload/conversion tests use only a synthetic fixture in separate storage; no legacy website images were processed.
- Site, social and analytics screens pass nine additional accessibility audits across desktop light/dark and 390px mobile light. Browser saves, invalid analytics values and theme return paths are verified without external requests.

## Runtime and current behavior

The system CLI is PHP 8.2.4 and local MariaDB is 10.4.28. An ignored, checksum-verified portable PHP 8.3 runtime is available at `.audit/php83/php.exe`, leaving XAMPP unchanged. The application uses isolated SQLite locally; production must use MySQL 8 or MariaDB 10.6+.

The homepage currently returns HTTP 503 until verified content and templates exist. It does not serve the Laravel demo. Unknown/legacy WordPress login routes return 404. Health is available at `/up`; admin sign-in is at `/{ADMIN_PATH}/login`. There are no default accounts or public forms. Provision accounts with `trivo:admin-create`; see ADMIN-AUTH.md.

## Next implementation

Content modules, then import services and public templates against the captured sources. SEO defaults/schema/robots/sitemap settings and CSP-aware custom scripts remain pending. Real provider/cron checks, user/access management actions, authentication alert emails and final security acceptance remain pending. The old live-site mobile references need recapture with explicit viewport emulation; see PHASE-0-AUDIT.md. Module details are in WEBSITE-SETTINGS.md, IMAGE-LIBRARY.md and SMTP-AND-REDIRECTS.md.
