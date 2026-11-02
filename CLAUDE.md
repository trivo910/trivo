# CLAUDE.md — Project rules for trivo.in Laravel rebuild

## Project
Rebuild of https://trivo.in (digital marketing agency, travel & tourism focus) from WordPress to Laravel. SEO equity must be preserved 100%.

## Stack (fixed — do not swap)
- Laravel 12, PHP 8.3, MySQL 8 / MariaDB 10.6+
- Blade + Tailwind CSS 4 (Vite) + Alpine.js. No React/Vue/Livewire on public site.
- Admin: custom Blade + Tailwind + Alpine, Tiptap editor (self-hosted via Vite)
- Auth: Laravel Breeze-style session auth (custom, admin only) + 2FA (TOTP) — no public registration
- Image processing: intervention/image (WebP/AVIF, responsive srcset)
- Tests: Pest; Static analysis: Larastan; Style: Pint
- Packages allowed: spatie/laravel-permission, spatie/laravel-activitylog, spatie/laravel-honeypot, spatie/laravel-responsecache, spatie/laravel-sitemap (or own generator), pragmarx/google2fa-laravel, mews/purifier (or ezyang/htmlpurifier)
- Keep dependencies minimal; justify any other in `docs/DECISIONS.md`.

## Absolute rules
1. **Never change an existing public URL.** See `docs/SEO-URL-PRESERVATION.md`.
2. **Security checklist in `docs/SECURITY.md` is mandatory.**
3. Public pages are server-rendered with full HTML, one H1, semantic landmarks.
4. All DB access via Eloquent/Query Builder with bindings. No raw string-concatenated SQL.
5. All user HTML (blog body) is sanitised on save AND escaped/purified on output. Never `{!! !!}` unsanitised data.
6. Secrets only in `.env`; never commit `.env`. SMTP password stored encrypted (`Crypt`/`encrypted` cast).
7. Every mutation: Form Request validation + Policy authorisation + CSRF.
8. Use Pest tests for URL parity, SEO score logic, auth/permissions, and form submissions.
9. Do not modify the local reference project. Copy assets into `storage/app/public` / `public/` via the import command.
10. Log decisions in `docs/DECISIONS.md`; log missing content in `docs/MISSING-CONTENT.md`.

## Design — use `docs/DESIGN-trivo-in.md` WITH these corrections
The auto-extracted file has errors. Apply:
- Font: **Montserrat** (self-host woff2, `font-display: swap`, preload 400/600/700 latin subset). No Google Fonts CDN call (privacy + speed).
- Primary `#006ace`, secondary `#ffbe11`, bg `#ffffff`, section bg `#f7f7fa`, accent surface `#e9e5ff`, text `#000` headings / `#69727d` muted. WhatsApp green `#25d366` only for the WhatsApp button.
- **Type scale in the file is broken** (body 30–40px is wrong). Use: H1 `clamp(40px, 6vw, 66px)` /700 /-1px; H2 `clamp(30px, 4vw, 48px)` /700; H3 `clamp(22px, 3vw, 32px)` /600; H4 24px /600; body 16px /300–400, line-height 1.6. Verify against live site visually and match it.
- Radius: buttons 18px (primary) / 50px (pill), cards 24–40px. Spacing unit 10px grid. Section padding ~100px desktop, ~60px mobile.
- Tinted shadows from the design file. Footer may be dark if the live site has a dark footer — match the live site.
- Mobile touch targets ≥ 44px. Breakpoints 640 / 1024 / 1440.
- When file and live site disagree, **the live site wins**.

## Admin design — Glassmorphism (admin only)
- The admin backend uses a **glassmorphism** design system: frosted translucent panels (`backdrop-filter: blur`), soft borders/shadows, mesh-gradient background in brand colours (`#006ace`, `#ffbe11`, `#e9e5ff`), Montserrat, light + dark theme.
- Follow `docs/ADMIN-DESIGN-GLASSMORPHISM.md` exactly. Use only shared `<x-admin.*>` components; no ad-hoc styling.
- Use `.glass-strong` (opaque-ish) for tables, forms and the editor canvas; always provide the no-`backdrop-filter` fallback; AA contrast; respect reduced-motion/transparency.
- The "no dark backgrounds / light only" rule in `DESIGN-trivo-in.md` applies to the **public site only**.
- Admin CSS/JS is a separate Vite entry so the public site never loads it.

## Code conventions
- Folders: `app/Services`, `app/Actions`, `app/Http/Requests`, `app/Policies`, `app/Support/Seo`, `app/Console/Commands`.
- Routes: public routes in `routes/web.php` (explicit static routes first, dynamic catch-all last), admin in `routes/admin.php` under `/{ADMIN_PATH}` (configurable, not `/admin` or `/wp-admin` by default).
- Blade components in `resources/views/components`; SEO head in one `<x-seo.head :model="$page"/>` component.
- Config for SEO defaults in `config/seo.php`.

## Commands
`php artisan trivo:inventory-urls` · `trivo:import-content` · `trivo:import-media` · `trivo:verify-urls` · `trivo:generate-sitemap`
`composer test` · `composer lint` · `composer analyse` · `npm run build`
