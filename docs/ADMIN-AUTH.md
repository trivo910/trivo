# Admin access and authentication

Admin authentication and shared UI are implemented; media, SMTP, redirects and content management modules remain in development. The public site is still a migration work in progress.

## Provision an account

Use PHP 8.3 and migrate the intended application database first. There are no default accounts. On the current Windows machine:

```powershell
& .\.audit\php83\php.exe artisan trivo:admin-create
```

The command asks for name, email, role and a hidden password confirmation. Passwords require 12 or more characters, mixed case, a number and a symbol, and pass Laravel's compromised-password check. The command seeds the fixed permission definitions and roles; it does not enroll an authenticator or bypass 2FA.

For explicit one-time environment provisioning, use `trivo:admin-create --from-env` with `ADMIN_INITIAL_NAME`, `ADMIN_INITIAL_EMAIL`, `ADMIN_INITIAL_PASSWORD` and optionally `ADMIN_INITIAL_ROLE` in the **process environment**. These values are not added to cached Laravel configuration. Remove the bootstrap variables after use. Never pass a password in a command argument or commit it.

Set a private `ADMIN_PATH` in the environment; invalid, missing or reserved paths register no admin routes. Sign in at `/{ADMIN_PATH}/login`. `/admin`, `/login`, `/wp-admin` and `/wp-login.php` are not sign-in endpoints. `ADMIN_ALLOWED_IPS` can contain comma-separated IPs/CIDRs; leave blank to disable that optional restriction. Configure trusted proxies deliberately before relying on forwarded client addresses.

## Sign-in behavior

1. A valid password creates a new, five-minute pending session, not an authenticated session.
2. A new account must add the displayed setup key to a TOTP authenticator and verify a current code. An `otpauth` link is provided; no secret is sent to an external QR service.
3. Successful enrollment encrypts the authenticator secret and stores hashes of ten random recovery codes. Plain codes are displayed once on a `no-store` response. Save them privately before continuing.
4. Subsequent sign-ins require a current TOTP or an unused recovery code. A consumed TOTP time step cannot be replayed, and recovery-code consumption occurs under a database row lock.
5. The complete sign-in rotates the session ID and records an authenticated user/version marker. Every protected admin route checks active status, an approved role, enrollment, marker/version and a 20-minute idle timeout. Password-only or forged partial sessions do not pass.

Sign-in attempts are limited to five per minute per email/IP and 30 per minute per IP. Five repeated failures start a progressive lockout, increasing up to 16 minutes. The second step has its own limiter. Failure messages do not disclose whether an account exists. Authentication events record event type, actor ID where applicable and an HMAC of the IP; passwords, codes and authenticator secrets are excluded.

## Roles

| Role | Initial permissions |
|---|---|
| Super Admin | All defined permissions, including users/roles and the development styleguide |
| Admin | Defined operational/content permissions, excluding users/roles management |
| Editor | Content editing/publishing and media; no mail/site settings, leads or activity log |
| SEO Manager | Content view/SEO permissions, SEO settings and redirects; no general content mutation, leads or activity log |

Permissions for unfinished modules do not expose routes for those modules. Future endpoints must apply Form Requests and Policies. Personal theme changes use UserPolicy and only affect the signed-in user. Theme redirects accept an allowlisted route name, never an arbitrary URL. `auth_version` is available to revoke sessions when future user-management actions change credentials/access; those actions must increment it.

The styleguide requires Super Admin and is denied in production. Activity history is available only to users with `activity.view`. Dashboard counts respect the viewer's permissions and show actual stored data, with no fabricated metrics.

## Verification and limits

Pest covers password/2FA separation, enrollment, encrypted secrets, hashed one-use recovery codes, replay rejection, expiration, disabled users, role restrictions, CSRF, IP restrictions, theme validation and account provisioning. Playwright uses an installed Chrome with an isolated synthetic database. Login, dashboard and styleguide pass the recorded axe A/AA checks in desktop light, desktop dark and true 390px mobile light modes. Reduced transparency, modal Escape dismissal and mobile navigation are also checked.

These checks are not a full security or accessibility certification. New-IP alert emails, administrative password/2FA recovery management, all remaining modules, full production MySQL/MariaDB validation and final deployment checks remain pending. Strict CSP enforcement also remains pending review of the public site and Alpine's evaluation requirements; current headers are report-only.
