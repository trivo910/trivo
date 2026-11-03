# SMTP and redirects — 2026-11-03

## Mail settings

Users with `settings.mail` can edit SMTP settings and request a test from the Mail / SMTP screen. Form Requests, SettingPolicy, the existing 2FA/session middleware and CSRF protect these mutations. Editors and SEO Managers cannot access this module.

The entire settings value is encrypted at rest. Password fields are never populated from storage or old input. A blank submission keeps the saved password; removal is explicit. Saved sender and reply-to fields are validated against header injection. Production saves allow STARTTLS or implicit TLS; unencrypted transport is available only for local/testing environments.

HTTP boot loads configured SMTP values after checking that the settings table exists. Console boot intentionally does not decrypt them, preventing `config:cache` from writing database SMTP credentials into its PHP file. Queue jobs refresh mail configuration through the job-processing event, and test jobs explicitly configure their transport before sending. Existing mailers are purged when settings are applied.

Test email delivery can be queued or immediate. Queue payloads contain only a delivery-log ID; recipient addresses are encrypted in the database. A job claims a queued test before sending and does not send it again if already processed. Logs record queued/sending/sent/failed states, with SMTP replies sanitized for plaintext, URL-encoded and Base64 credential variants. Raw SMTP exceptions are not rethrown into failed-job storage. `sent` means the SMTP transport accepted the message; it does not prove delivery to a remote inbox.

Test requests are limited to two per minute per administrator. The log is visible on the settings page; refresh after a queued test. Future public-form notifications must use queued jobs and the same error-sanitization discipline. The queue toggle on this screen controls tests, not the mandatory queued form-notification requirement.

## Shared-hosting queue processing

Set queue connection `database` and cache store `file`. Add the normal one-minute cron:

```text
* * * * * php /absolute/app/path/artisan schedule:run
```

The scheduler now runs a database queue worker with `--stop-when-empty --max-time=50 --max-jobs=50 --tries=1 --timeout=30` and a two-minute overlap lock. It requires no Redis or Supervisor. Per-job retry settings can override the worker default for future notification jobs. SMTP transport timeout is ten seconds. Production cron execution and the actual mail provider still require environment-specific verification.

## Redirects and 404s

Users with `redirects.manage` can create, edit, delete and import literal 301/302/410 rules. External targets, protocol-relative URLs, encoded control characters, dot-segment traversal and protected admin/WordPress login/health paths are rejected. Same-site absolute HTTPS targets are stored as relative destinations, keeping staging local. Root, robots and the canonical sitemap index cannot be shadowed by user-entered source rules. Existing regex rows are listed but cannot be edited through this literal-path manager.

The graph validator rejects self-links, cycles and destinations that are already removed. Chains are flattened transactionally; existing aliases are updated when a new intermediate redirect is added. CSV uses exactly `from_path,to_url,status_code`, accepts up to 1 MB/1,000 rows, updates existing source paths and rolls back the entire import on validation/graph errors. No uploaded CSV is published or retained.

The public middleware serves configured literal redirects/410 responses for GET and HEAD and increments hits atomically. This is not the complete public page resolver or combined HTTPS/host/slash canonicalization layer; those remain in Phase 4. Source spelling and trailing slashes are preserved, and the responder checks exact case after the database lookup.

Public 404s record path, count, last-seen time and an HMAC of the user agent. Queries, referrers, raw IP addresses and admin paths are excluded. Email-like path fragments are redacted; common reset/token paths are skipped. Logging-storage failures leave the original 404 response intact. The admin log links safe literal paths into a prefilled redirect form; viewing a log does not create a rule.

## Verification

Pest covers permissions, encrypted storage, blank/cleared passwords, queue payloads, duplicate-send prevention, sanitized SMTP failures, transport configuration, header injection, throttling, CSV rollback, redirect loops, open redirects, 410s, real HTTP redirect responses/hits and 404 privacy/counting.

Browser checks cover seven screens in desktop light, desktop dark and true 390px mobile light layouts (21 axe A/AA audits). They exercise saving settings, password masking, redirect creation and the 404 view. The real SMTP transport authenticated to a loopback-only synthetic SMTP server and its test message was captured locally. No email was sent to an external provider or person. No real SMTP credentials or default admin accounts were added to the application database.

Still pending: actual provider/inbox verification, supported MySQL/MariaDB deployment checks, complete canonical URL behavior and content parity, the media library, other settings groups and notification/monitoring modules.
