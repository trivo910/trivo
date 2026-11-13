# Missing content and unresolved audit work

## Current findings — 2026-11-02

- Thirteen attachment originals are absent from the media source. See MISSING-MEDIA.csv; these include theme icon assets, builder screenshots and one client image. Live recovery and actual-usage confirmation remain pending.
- 749 matched originals have no populated legacy attachment alt. Preserve known alt from verified rendered sources; do not generate replacement copy silently.
- `/social-media-marketing-agency-mumbai/` is a linked 404 on the current live site. Its intended destination needs source verification.
- `/cdn-cgi/l/email-protection` returns 404 without its browser fragment; it is infrastructure, not a migrated content page.
- A page and post share `/digital-marketing-agency-in-surat`. Live HTML identifies page 991442; post 988965 is retained as a local draft for review. See URL-CONFLICTS.csv.
- Main-content sections, image usage, author/attachment/query/feed coverage, full-page visual references and target parity remain incomplete. See PHASE-0-AUDIT.md.

## Public content import — 2026-11-12

- The local `newtrivo` database now has 35 imported pages and 49 imported posts (48 published; the documented duplicate post is a draft). The second dry run reports all 84 records as idempotent updates with no missing URL-map entries.
- The sanitized `wp_legacy` subset contains page/post content and only allowlisted Rank Math metadata. It omits `post_excerpt`, author accounts/profiles, taxonomy-to-post relationships, Elementor data, navigation item records, and custom post types. The importer does not invent these values.
- The existing local `/marketing-solutions` page was preserved instead of overwritten; its legacy page record was not imported.
- The importer sanitizes and renders page/post HTML but strips builder styling. Pixel-level page layout, unavailable menu data, taxonomy archives, and media-library attachment records still require separate migration work. Existing files under `public/wp-content/uploads` remain available at their source URLs.

## Resolved

- DESIGN-trivo-in.md is present; apply CLAUDE.md corrections to its extracted type scale.
- The selected dump is u802165322_KeH3A.sql, and its site identity is verified as trivo.in.
- Live access works with the identified audit client; sitemap and HTML evidence is saved.
- Migration documents now consistently name public/wp-content/uploads as the source folder.
