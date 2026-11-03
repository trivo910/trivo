# Phase 0 audit — 2026-11-02

The initial page/archive/link crawl and public-source audit are recorded. Exhaustive URL coverage and visual/content parity are still pending; this is not a completed migration.

## Sources and database

- Selected source: `u802165322_KeH3A.sql`, generated 2026-11-02 10:23 (dump timezone unspecified). The alternate dump was generated 2026-06-04 07:51.
- Allowlisted options confirm `home` and `siteurl` as `https://trivo.in`, theme `numerique`, front-page ID `19`, permalink `/%postname%`, default category/tag bases and ten posts per page.
- Source counts: 36 published pages, 49 published posts, 921 attachments, 69 published menu items, 49 published Elementor library entries and one published testimonial. Full status/type counts and plugins are in DB-ANALYSIS.md.
- A new, isolated `wp_legacy` holds 85 public page/post records, 921 attachment records, 1,331 allowlisted metadata rows and 13 allowlisted options. The selected SQL file was parsed without executing its statements. No legacy users, sessions, passwords, arbitrary settings, form submissions or mail logs were imported.
- The audit schema is a sanitized subset, not a complete WordPress clone. Attachments are in `wp_attachments`. Laravel's separate `legacy` connection starts in read-only transaction mode and is accessed through CLI audit code.

## URL evidence

`docs/URL-MAP.csv` contains **139 unique observed URLs**: 132 page/archive/link URLs and seven system entrypoints. Initial HTTP responses: **114 × 200, 23 × 301, 2 × 404**. Page aliases have one redirect hop. The CSV distinguishes initial status, final status, final URL and redirect hops.

- Main sitemaps: `/sitemap_index.xml`, `/post-sitemap.xml`, `/page-sitemap.xml`, `/category-sitemap.xml`.
- `/wp-sitemap.xml` and `/sitemap.xml` redirect to `/sitemap_index.xml`.
- The site's normal canonical page/post paths omit the trailing slash; preserve observed aliases separately.
- Existing broken link: `/social-media-marketing-agency-mumbai/` returns 404. Do not invent its destination; confirm against the existing service routes before defining a migration redirect.
- `/cdn-cgi/l/email-protection` returns 404 without its client-side fragment. It is Cloudflare email-protection infrastructure, not a content page.
- Both published post `988965` and page `991442` use `/digital-marketing-agency-in-surat`. Cached live HTML identifies `page-id-991442`; that page owns the served route. Keep the colliding post in source review rather than dropping it or changing its URL silently. Both IDs are recorded in URL-CONFLICTS.csv and URL-MAP.csv.
- CSV titles are extracted from the document head, excluding SVG icon title labels. Copy is not rewritten. Raw HTML stays outside Git under `.audit/crawl/`.

## Media and references

- Source: `public/wp-content/uploads`, year/month folders; 3,685 files, 577,389,202 bytes.
- 3,482 unique SHA-256 hashes; 203 duplicate copies; 395 files over 300 KB.
- 13 attachment originals are absent locally; 749 mapped originals have no populated attachment alt. No raster files failed dimension inspection.
- MEDIA-INVENTORY.csv includes original dimensions, sizes, hashes and attachment matches. Unmapped assets may be WordPress variants or builder files; they are not proven unused.
- 26 initial viewport screenshots cover 13 representative templates. See `docs/reference/CAPTURES.csv`. During admin QA, Chrome CLI narrow-window captures were found to crop a wider layout: the old mobile references must be recaptured with explicit viewport emulation before visual parity work. They are not completed visual QA evidence.

## Remaining audit work

- Exhaustive attachment-page, author-archive, media, feed, search/query and pagination URL coverage; domain/protocol and uppercase behavior.
- Determine which custom types are publicly routable instead of inferring public routes from every published builder/template object.
- Main-content-only sections, image usage and word counts; current CSV word counts include surrounding rendered page text.
- Missing-source downloads, usage-based unused media analysis and alt remediation using verified original sources. Never invent alt text or other copy during migration.
- Complete viewport validation, full-page references and additional unique templates discovered during implementation.
- Content import, target URL parity tests, content diff, visual parity and release checks.
