# Trivo Laravel migration

Laravel 12 / PHP 8.3 / Blade / Tailwind 4 / Alpine rebuild of trivo.in. This checkout currently contains the source audit and application foundation. Admin modules, content import, public templates and release acceptance are still in development. See docs/DEVELOPMENT-STATUS.md and docs/TASKS.md.

## Requirements

PHP 8.3 with curl, EXIF, fileinfo, GD (WebP/AVIF support), intl, mbstring, OpenSSL, PDO MySQL, PDO SQLite and zip; Composer; Node 20.19+ or 22.12+; MySQL 8 or MariaDB 10.6+. Database queues and file cache are the deployment defaults; Redis and Supervisor are optional. For image uploads, configure PHP upload/post limits to at least 8M/12M; see `docs/IMAGE-LIBRARY.md`.

The current Windows machine has PHP 8.2 and MariaDB 10.4. An isolated PHP 8.3 CLI was installed at `.audit/php83/php.exe`; XAMPP remains unchanged. The local application currently uses the `newtrivo` database through the MySQL connection. Automated tests and browser QA use isolated SQLite databases. Do not treat local MariaDB as a supported production database.

## Install

Use PHP 8.3 on PATH when running Composer:

```text
composer install
npm ci
```

Copy `.env.example` to `.env` only if it does not exist. Set the intended application database and a private random `ADMIN_PATH`; create a least-privilege DB user. Set `APP_KEY` with `php artisan key:generate`. Review the target connection before `php artisan migrate`. No default admin is created; provision one with `php artisan trivo:admin-create` and complete mandatory authenticator enrollment on first sign-in.

For a new local SQLite setup, use `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite`, create that file, and set `SESSION_SECURE_COOKIE=false` only for local HTTP. Never overwrite an existing env or database.

The current machine is already prepared. Run:

```powershell
& .\.audit\php83\php.exe artisan serve
```

The homepage returns 503 while templates/content are pending. `/up` is the health endpoint. Admin sign-in is available at `/{ADMIN_PATH}/login`; provision an account explicitly with `php artisan trivo:admin-create` and enroll an authenticator on first sign-in. There are no default accounts or public registration. See `docs/ADMIN-AUTH.md` for setup, role permissions and remaining work.

## Checks

With PHP 8.3 selected:

```text
composer test
composer lint
composer analyse
composer audit
npm audit
npm run build
python -m unittest discover -s tools -p test_*.py
```

On this machine, PHP commands can use `& .\.audit\php83\php.exe artisan test` or `& .\.audit\php83\php.exe vendor/bin/phpstan analyse --memory-limit=512M`. The audit tools and their independent Python requirements are documented in `tools/README.md`.

## Source material

The SQL dumps and `public/wp-content/uploads` are sensitive/read-only migration sources excluded from Git. Never stage them or unrestricted database exports. A new checkout must receive these sources separately. `.audit` contains local downloads and raw snapshots and is also excluded.

`wp_legacy` contains a deliberately sanitized audit subset, not a full WordPress clone. `LEGACY_DB_*` is separate from the application DB and must use a SELECT-only account on shared/staging systems. Its Laravel connection starts in read-only mode. `php artisan trivo:legacy-report` reports the public page/post counts without exposing credentials. See `docs/PHASE-0-AUDIT.md` for limitations.

## Deployment preparation — not release approval

Do not cut over until the full URL/content/visual parity and security checklists pass. Use HTTP authentication and `noindex` on staging. Never point the live domain at this unfinished application.

VPS: set nginx DocumentRoot to the app's `public/` directory, use PHP-FPM 8.3, deny dotfiles and execution below `/wp-content/uploads/`, and use a dedicated least-privilege application DB account. Provision TLS, request size limits, compression, log rotation and daily encrypted off-server backups. Apache rules included here protect common local mistakes; verify equivalent nginx rules before use.

Hostinger: keep the application outside `public_html`; copy only the contents of `public/` into `public_html`. Adjust `public_html/index.php` to the actual application's `vendor/autoload.php` and `bootstrap/app.php` paths. Preserve `wp-content/uploads` and its execution deny rules at the same public paths. Configure a storage symlink where supported; otherwise use a deliberate non-executable public-media copy strategy. Never expose `.env`, dumps, storage, vendor or application source under the web root.

Set `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, `APP_URL=https://trivo.in`, secure cookies and the production DB. Keep queue `database`, cache `file` and daily logs. Build assets with `npm ci` and `npm run build`; install production PHP dependencies using `composer install --no-dev --optimize-autoloader`. Run reviewed migrations and Laravel config/route/view/event cache commands with the correct PHP executable. Enforce CSP only after reviewing report-only behavior with the actual pages and third parties.

Use a one-minute cron invoking `php /absolute/app/path/artisan schedule:run`. A bounded database queue worker is now scheduled every minute, with an overlap lock, a 50-second runtime limit between jobs and a 30-second per-job timeout. Validate actual cron execution before relying on queued delivery. SMTP settings, test delivery and redirect management are documented in `docs/SMTP-AND-REDIRECTS.md`.

Before cut-over, lower DNS TTL, retain the WordPress backup, run the completed URL verifier and content diff, then switch only after UAT. Roll back by restoring the previous app/public release and its matching database backup; keep APP_KEY and encrypted media/settings backups together. Do not run destructive migrations against a live DB to simulate rollback. Keep the WordPress backup for at least 30 days and monitor Search Console, sitemaps and 404s after release.
