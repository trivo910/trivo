# Development status — 2026-11-02

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

## Validation

- Pest: 34 passing tests, 166 assertions.
- Python audit parser/crawler: five passing synthetic tests.
- Larastan level 6: no errors.
- Pint applied; final style check recorded with the foundation handoff.
- Production asset build succeeds. Font URLs intentionally resolve from `public/fonts` at runtime.
- Composer and npm audits: zero known vulnerability advisories.
- Core migrations succeed on an isolated SQLite development/test database. Production MySQL/MariaDB validation is still required.
- Chrome browser checks pass for login/dashboard/styleguide across desktop light, desktop dark and true 390px mobile light viewports, with zero recorded axe A/AA violations. Navigation, dialog, reduced-transparency and sign-in flows are exercised against an isolated synthetic database.

## Runtime and current behavior

The system CLI is PHP 8.2.4 and local MariaDB is 10.4.28. An ignored, checksum-verified portable PHP 8.3 runtime is available at `.audit/php83/php.exe`, leaving XAMPP unchanged. The application uses isolated SQLite locally; production must use MySQL 8 or MariaDB 10.6+.

The homepage currently returns HTTP 503 until verified content and templates exist. It does not serve the Laravel demo. Unknown/legacy WordPress login routes return 404. Health is available at `/up`; admin sign-in is at `/{ADMIN_PATH}/login`. There are no default accounts or public forms. Provision accounts with `trivo:admin-create`; see ADMIN-AUTH.md.

## Next implementation

Media/SMTP/redirect modules, then content modules, import services and public templates against the captured sources. User/access management actions, authentication alert emails and final security acceptance remain pending. The old live-site mobile references need recapture with explicit viewport emulation; see PHASE-0-AUDIT.md.
