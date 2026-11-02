# Content & media migration from the local Trivo project

## Rules
- Local project path: `{{LOCAL_TRIVO_PROJECT_PATH}}` — **read-only**.
- First produce `docs/SITE-INVENTORY.md`: list every page/section found, the source file for its copy, and every image used (path, dimensions, size).
- Never paraphrase or rewrite copy. Preserve headings, text, CTAs, alt text. Fix only obvious typos and log them.

## Steps
1. `php artisan trivo:import-media --source=PATH` — copy images/videos/PDFs into `storage/app/public/media` (and `public/wp-content/uploads/YYYY/MM/` mirror for URLs already indexed), de-duplicate by sha256, generate WebP/AVIF/srcset, write `media` rows. Report images >300 KB original or missing alt.
2. `php artisan trivo:import-content --source=PATH` — create pages/services/portfolio/testimonials/FAQs/menus/settings from local sources; blog posts from WP export (WXR/SQL) if provided: keep `post_name`, dates (`published_at`, `updated_at` = original modified), author, categories, tags, featured image, Yoast/RankMath meta.
3. Rewrite image URLs inside post bodies to new media (keep same path if mirrored), fix relative links, convert shortcodes/page-builder markup (Elementor/Vamtam) to clean semantic HTML; strip inline styles and builder wrapper divs.
4. Run `trivo:verify-urls` and produce `docs/CONTENT-DIFF.md` (old vs new: title, meta, H1, word count, image count, internal-link count per URL).
5. Produce `docs/MISSING-CONTENT.md` for anything not found.

## Acceptance
- Word count per URL within ±3% of old
- All images present with alt; no broken internal links (run link checker)
- Dates preserved (affects freshness signals)
