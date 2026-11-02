# SECURITY — mandatory checklist (OWASP Top 10 aligned)

## Application
- [ ] `APP_DEBUG=false`, `APP_ENV=production`; unique `APP_KEY`; `.env` outside web root / denied by server config
- [ ] Only `public/` is web-accessible; deny dotfiles, `composer.*`, `storage`, `vendor`, `*.sql`, `*.log`, `*.bak`
- [ ] CSRF on all state-changing routes; `SameSite=Lax` (Strict for admin), `Secure`, `HttpOnly` cookies; session regenerate on login; short admin session lifetime + idle timeout
- [ ] Passwords: bcrypt/argon2id, min 12 chars, breach check (`Password::uncompromised()`), no default accounts; admin seeder reads credentials from env once
- [ ] **2FA (TOTP) mandatory** for all admin users; recovery codes hashed
- [ ] Login rate limiting (5/min per IP+email), progressive lockout, generic error messages, login + failed-login audit log
- [ ] Hidden admin path (`ADMIN_PATH`), optional IP allow-list; `/wp-admin`, `/wp-login.php`, `/xmlrpc.php` return plain 404
- [ ] RBAC via spatie/permission + Policies on every model action; deny by default; no IDOR (scope queries, use route-model binding with policy checks)
- [ ] Form Requests validate and whitelist fields; **never** `$request->all()` into `create()`; models use `$fillable`
- [ ] SQL: Eloquent/bindings only; `orderBy` columns whitelisted
- [ ] XSS: Blade `{{ }}` everywhere; blog HTML sanitised with HTMLPurifier allow-list on save; strip `<script>`, `on*`, `javascript:` URLs; custom header/footer scripts editable by Super Admin only
- [ ] Mass-assignment, open-redirect (validate `redirect` targets to same host), SSRF (no user-supplied URL fetching; if needed, allow-list + block private IPs), path traversal (never use user input in file paths)
- [ ] **File uploads**: whitelist extensions + verify MIME by content (finfo) + max size; images re-encoded via Intervention (strips EXIF/payloads); randomised filenames; stored outside executable paths; **no PHP execution in `uploads/`** (server rule); SVG disallowed unless sanitised; PDF only if needed
- [ ] Contact/lead forms: CSRF, honeypot + time-trap, rate limit (3/min/IP), optional Cloudflare Turnstile/reCAPTCHA v3 via settings, header-injection safe (no user input in headers), emails queued, input length limits
- [ ] SMTP password encrypted (`encrypted` cast), never logged or rendered back (show masked)
- [ ] Secrets/config cached: `config:cache`, `route:cache`, `view:cache`, `event:cache`
- [ ] Logging: daily rotation, no PII/passwords/tokens; IPs hashed in leads/404 logs where full IP not needed
- [ ] Dependency hygiene: `composer audit` + `npm audit` in CI; lock files committed; no abandoned packages
- [ ] Error pages never leak stack traces; custom 403/404/419/429/500

## HTTP security headers (middleware `SecurityHeaders`)
```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN        (admin: DENY)
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()
Cross-Origin-Opener-Policy: same-origin
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{nonce}' https://www.googletagmanager.com https://www.google-analytics.com; img-src 'self' data: https:; style-src 'self' 'unsafe-inline'; font-src 'self'; frame-src https://www.google.com/maps https://www.youtube-nocookie.com; connect-src 'self' https://www.google-analytics.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'
```
Use per-request nonce for inline scripts (GTM/analytics); tighten as far as the live site's third parties allow; start in Report-Only, then enforce. Remove `X-Powered-By`/`Server` version exposure.

## Server (document in README)
- nginx/Apache: block `.env`, `.git`, `*.php` in uploads, directory listing off, request size limits, gzip/brotli, HTTP/2, TLS 1.2+, auto-renew certs
- Fail2ban / Cloudflare WAF + bot fight mode recommended; rate-limit `/` login path
- PHP: `expose_php=Off`, `disable_functions` (exec, shell_exec, system, passthru, proc_open, popen), `open_basedir`, `session.cookie_*` secure flags
- DB: dedicated least-privilege user (no `GRANT`/`FILE`), no remote access
- File permissions: 755 dirs / 644 files; only `storage` and `bootstrap/cache` writable
- Backups: daily encrypted DB + uploads, off-server copy, restore tested

## Monitoring
Activity log for admin actions; alert email on new admin login from new IP, 5+ failed logins, SMTP failures, spike in 404s; uptime check.

## Tests to include
Auth required on all admin routes; role/permission matrix; IDOR attempts; XSS payload in post body is neutralised; upload of `.php`/polyglot rejected; rate-limit returns 429; headers present; CSRF missing → 419.
