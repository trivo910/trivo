# Architecture

## Folder layout
```
app/
  Actions/            single-purpose classes (PublishPost, SubmitLead...)
  Console/Commands/   trivo:inventory-urls, import-content, import-media, verify-urls, generate-sitemap
  Http/Controllers/Web/      public controllers (thin)
  Http/Controllers/Admin/    admin controllers
  Http/Middleware/    SecurityHeaders, ForceCanonicalUrl, LogNotFound, AdminIpAllow (optional)
  Http/Requests/      FormRequests
  Models/  Policies/  Services/
  Support/Seo/        SeoScorer, ReadabilityScorer, KeywordAnalyzer, SchemaBuilder, MetaResolver
resources/views/{layouts,components,pages,blog,admin}
routes/{web.php,admin.php}
database/{migrations,seeders,factories}
tests/{Feature,Unit}
```

## Database (MySQL, utf8mb4, InnoDB, FKs + indexes)
- `users` (name, email unique, password, role via spatie, two_factor_secret(enc), two_factor_confirmed_at, last_login_at/ip)
- `pages` (id, legacy_wp_id, title, **path unique**, template, blocks JSON, status, published_at, parent_id, sort, + seo_*)
- `posts` (id, legacy_wp_id, title, slug, **path unique**, excerpt, body LONGTEXT, featured_media_id, author_id, category_id, status[draft/scheduled/published], published_at, reading_time, + seo_* , `focus_keyword`, `secondary_keywords` JSON, `seo_score` tinyint, `readability_score` tinyint, `seo_analysis` JSON)
- `categories`, `tags`, `post_tag` (with path/seo fields), `authors` (or users) with bio/avatar/socials
- `services`, `portfolio_items`, `testimonials`, `faqs`, `clients`
- `menus`, `menu_items`
- `media` (disk, path, original_name, mime, size, width, height, alt, title, variants JSON, sha256)
- `redirects` (from_path unique, to_url, status_code, hits, last_hit_at, is_regex)
- `not_found_logs` (path, referrer, ua_hash, count, last_seen)
- `leads` (name, email, phone, company, service, message, source_url, utm JSON, ip_hash, status, notes, handled_by)
- `settings` (key unique, value (encrypted flag), group) — site, seo, social, analytics, **mail**
- `mail_logs`, `activity_log` (spatie), `revisions` (polymorphic JSON snapshot)
- Shared `seo_*` columns: `seo_title, seo_description, canonical_url, robots, og_title, og_description, og_image_id, twitter_*, schema_type, schema_json, noindex, nofollow, sitemap_include, sitemap_priority`

## Rendering & caching
- Blade server-render; `ResponseCache` for guests (flush on publish/update); `Cache::remember` for menus/settings; Vite hashed assets with immutable cache headers (`.htaccess`/nginx snippet in README).
- Images: on upload generate WebP + AVIF at 480/768/1200/1920, `<picture>` with `srcset/sizes`.

## SMTP at runtime
`MailConfigServiceProvider` reads `settings(group=mail)` (decrypt password) and sets `config('mail.mailers.smtp')` + `mail.from` at boot (guard for missing table during migrate). Queue `database`. Failed mail → `mail_logs` + admin alert. "Send test email" action returns the exact SMTP error (sanitised).

## Admin UI
Glassmorphism design system — `docs/ADMIN-DESIGN-GLASSMORPHISM.md`. Separate Vite entry `admin.css`/`admin.js`; Blade components in `resources/views/components/admin`.

## Admin routing
`ADMIN_PATH` env (default random e.g. `/hq-7f3k`). Not `/admin`, `/login`, `/wp-admin`. Optional IP allow-list setting.

## Deployment
- VPS: nginx + php-fpm 8.3, doc root `public/`.
- Hostinger shared: keep app outside `public_html`, put contents of `public/` in `public_html`, adjust `index.php` paths; `storage:link` alternative via symlink or copy; cron `* * * * * php artisan schedule:run`.
- `README.md`: install, env vars, deploy, rollback, backup (daily DB dump + uploads), cron.
