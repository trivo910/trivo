# Content & media migration (from DB dump + images folder + live site)

## Sources & rules
- DB dump: u802165322_KeH3A.sql · Images: public/wp-content/uploads (folder) · Live: https://trivo.in
- All sources are **read-only**. The dump contains users, password hashes, form entries, possibly API keys in `wp_options` — **do not import users, sessions, or secrets**, never print them in logs/docs, never commit the dump.
- Never paraphrase or rewrite copy. Preserve headings, text, CTAs, alt text. Fix only obvious typos and log them in `docs/DECISIONS.md`.

## Step 1 — Load & analyse the dump
1. Import dump into a **separate MySQL database `wp_legacy`** (`mysql wp_legacy < dump.sql`, gunzip first if needed). Configure a second Laravel connection `legacy` used only by import commands.
2. Detect table prefix (`wp_` or custom). Report counts: published pages, posts, attachments, categories, tags, menus, custom post types (`SELECT post_type, post_status, COUNT(*) ... GROUP BY`), plugins in use (Yoast / RankMath / Elementor / Vamtam / Contact Form 7 / Joinchat).
3. Read from `wp_options`: `siteurl`, `home`, `blogname`, `blogdescription`, `permalink_structure`, `category_base`, `tag_base`, `page_on_front`, `page_for_posts`, `posts_per_page`, active theme. These define the URL rules → feed `docs/SEO-URL-PRESERVATION.md`.
4. Write findings to `docs/DB-ANALYSIS.md`.

## Step 2 — URL inventory (combine DB + live)
`trivo:inventory-urls` builds `docs/URL-MAP.csv` from: published `post`/`page`/custom types (`post_name`, `post_parent` hierarchy → full path), term archives, author archives, attachment pages, **plus** the live sitemaps/crawl. Flag URLs present in only one source. Live-site truth wins for what is actually indexed/200.

## Step 3 — Media
`trivo:import-media --source={{IMAGES_FOLDER_PATH}}`:
- Inspect folder layout (WP `YYYY/MM/` vs flat) and report.
- Map files to DB `attachment` rows via `_wp_attached_file` and `_wp_attachment_metadata` (sizes), carry over alt (`_wp_attachment_image_alt`), title, caption.
- Mirror originals to `public/wp-content/uploads/YYYY/MM/<same filename>` so already-indexed image URLs keep working; keep WP size variants (`-300x200.jpg`) if present in the folder.
- De-duplicate by sha256, generate WebP/AVIF + srcset into the new media library, create `media` rows.
- Report: missing files, images >300 KB, missing alt, unused images.

## Step 4 — Content
`trivo:import-content` (idempotent, uses `legacy_wp_id` as key, supports `--dry-run`):
- **Blog posts**: `post_title`, `post_name`, `post_content`, `post_excerpt`, `post_date`/`post_modified` (preserve both), author name, categories, tags, featured image (`_thumbnail_id`), Yoast/RankMath meta (`_yoast_wpseo_title`, `_yoast_wpseo_metadesc`, `_yoast_wpseo_focuskw`, `_yoast_wpseo_canonical`, `_yoast_wpseo_meta-robots-noindex`, `_yoast_wpseo_opengraph-*`, or `rank_math_*`) → `seo_*` + `focus_keyword`. Then run the new SeoScorer on each post and store the score.
- Clean body HTML: strip builder wrappers/inline styles/shortcodes, rewrite image URLs to new media (same path when mirrored), fix internal links, sanitise via HTMLPurifier.
- **Pages/services/city pages**: if `_elementor_data` exists, **do not trust it for copy**; use the rendered live HTML of that URL (main content only) as the copy source, cross-checked with `post_content`. Map to blocks/sections.
- **Menus**: from `nav_menu` terms + `nav_menu_item` posts (`_menu_item_*` meta) → `menus`, `menu_items`.
- **Testimonials/portfolio/FAQs/clients**: from custom post types or Elementor widgets / live HTML.
- **Settings**: site name, tagline, contacts, social links from options + live footer/header.
- **Form entries** (CF7/Flamingo/other) are personal data: import only if I ask; default is skip.
- Redirect rules from Redirection/Yoast plugin tables (`wp_redirection_items`, `_yoast_wpseo_redirect`) → `redirects`.

## Step 5 — Design source
There is no local template, so design is rebuilt from `docs/DESIGN-trivo-in.md` + the **live site**. Take desktop (1440) and mobile (390) screenshots of every unique page template into `docs/reference/` and match them section by section.

## Step 6 — Verify
- `trivo:verify-urls` → 100% pass; `docs/CONTENT-DIFF.md` (old vs new per URL: title, meta, H1, word count ±3%, image count, internal links)
- No broken internal links/images; all images have alt; dates preserved
- `docs/MISSING-CONTENT.md` lists everything that could not be found
