# SEO & URL PRESERVATION (CRITICAL)

## Phase 0 — Build the URL inventory BEFORE coding
1. Fetch `https://trivo.in/sitemap_index.xml`, `/wp-sitemap.xml`, `/sitemap.xml`, `/robots.txt`; recurse all child sitemaps (pages, posts, categories, tags, authors, images).
2. Also crawl internal links from the home page + the local project to catch unlisted URLs.
3. For each URL record: `url, path, trailing_slash(Y/N), type(page/post/category/tag/author/attachment/other), status, title, meta_description, canonical, robots, h1, og_image, schema_types, word_count, last_modified`.
4. Save as `docs/URL-MAP.csv`. This is the **source of truth**. Command: `php artisan trivo:inventory-urls`.
5. Identify the WordPress permalink structure (e.g. `/%postname%/` or `/blog/%postname%/`) and category base (`/category/`), tag base, author base. Replicate EXACTLY.

## Routing rules
- Store the **full path** in DB (`pages.path`, `posts.path`) e.g. `/services/seo-company-in-nagpur/` — do not derive from slug only. This guarantees parity even for odd legacy paths.
- Route order: (1) static system routes (`/sitemap.xml`, `/robots.txt`, `/feed/`, `/contact/` etc.), (2) admin, (3) form POST endpoints, (4) catch-all `Route::get('/{path?}', ResolveController::class)->where('path', '.*')` that looks up `redirects` → `pages` → `posts` → `categories`/`tags`/`authors` → 404.
- **Trailing slash**: match what the live site serves as canonical (WordPress usually uses trailing slash). The non-canonical variant gets a single 301 to canonical. Never serve both with 200.
- Force HTTPS + one host (www vs non-www exactly as today) via middleware, single 301 hop.
- Lowercase enforcement: 301 uppercase → lowercase only if the live site did.
- Pagination URLs: keep `/page/2/` pattern for blog/category archives (WP style), NOT `?page=2`. Add `rel=prev/next` not needed; self-canonical per page.
- WordPress leftovers to keep working or redirect:
  - `/wp-content/uploads/YYYY/MM/file.ext` → serve the same files at the same paths (copy to `public/wp-content/uploads/`). Include WP size suffixes (`-300x200.jpg`) used in indexed pages/posts' images; if resized variants are missing, generate on demand or 301 to the original.
  - `/feed/`, `/comments/feed/`, `/category/x/feed/`, `/?s=`, `/wp-json/` (410 or 301 — decide per inventory), `/wp-login.php` and `/wp-admin` → return **404** (do not reveal; admin lives at hidden path).
  - `/?p=123`, `/?page_id=45` → 301 to canonical path (store `legacy_wp_id`).
  - `/author/xyz/`: keep if indexed; else 301 to blog index. Add `noindex` for thin archives only if live site did.
- 404 handling: log to `not_found_logs`; show branded 404 with noindex; 410 for intentionally removed.

## Per-page head output (all public pages)
`<title>` (template: `{title} | Trivo` — replicate current live titles EXACTLY from URL-MAP.csv), meta description, canonical (absolute, https, same as old), robots (`index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1`), OG (title, description, image 1200×630, url, type, site_name, locale), Twitter card `summary_large_image`, `article:published_time/modified_time/author/section/tag` for posts, `theme-color`, favicon set.
**Import existing titles/descriptions from Yoast/RankMath data (postmeta `_yoast_wpseo_title`, `_yoast_wpseo_metadesc`, `_yoast_wpseo_focuskw`, `rank_math_*`) — do NOT regenerate them.**

## Structured data (JSON-LD, one `@graph`)
- Sitewide: `Organization` (+ `sameAs` socials, logo, contactPoint), `WebSite` (+ SearchAction only if search exists)
- Pages: `WebPage`, `BreadcrumbList`
- Services: `Service` + `Organization` provider
- Blog posts: `BlogPosting`/`Article` (headline, image, datePublished, dateModified, author `Person`, publisher), `BreadcrumbList`
- FAQ blocks: `FAQPage` only where visible FAQ exists
- Contact: `LocalBusiness`/`ProfessionalService` with address, geo, hours (from live site)
Validate with Rich Results Test; add a test that JSON-LD is valid JSON.

## Sitemaps / robots / feeds
- `/sitemap.xml` (index) + `post-sitemap.xml`, `page-sitemap.xml`, `category-sitemap.xml`, `image` entries — keep Yoast-style names if these are what GSC already has submitted (check URL-MAP). `lastmod` = real `updated_at`. Only 200+indexable URLs.
- `robots.txt` editable in admin; default: allow all, disallow admin path/`/search`/`?s=`, `Sitemap:` line.
- RSS `/feed/` for blog.
- Ping/submit not needed; document GSC steps.

## On-page rules enforced by code
Exactly one H1; heading hierarchy check; images require `alt`; `width/height` attributes (CLS); `loading=lazy` except LCP image (`fetchpriority=high`); external links `rel="noopener"` (+`nofollow` per setting); internal links relative-safe.

## Cut-over plan (document in README)
1. Build on staging domain with `noindex` + HTTP auth.
2. Run `trivo:verify-urls --against=docs/URL-MAP.csv` → must be 100% pass.
3. Compare titles/descriptions/canonicals/H1 vs old (diff report).
4. Lower DNS TTL → switch → keep WP backup 30 days.
5. Day 0: verify GSC property, resubmit sitemap, inspect top 20 URLs, monitor Coverage + crawl stats + 404 log daily for 30 days.
6. Keep GA4/GTM IDs identical.
