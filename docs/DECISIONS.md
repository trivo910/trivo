# Development decisions

## 2026-11-02 — Requirements and audit order
- Follow MASTER-PROMPT.md and CLAUDE.md, beginning with Phase 0. Do not write application code before a substantive docs/URL-MAP.csv exists.
- Use root CONTENT-MIGRATION.md for the migration workflow. The docs/CONTENT-MIGRATION.md copy describes an unavailable local reference project and is stale relative to the supplied SQL dumps and uploads folder.
- Both named SQL dumps exist. Neither is designated authoritative: inspect and compare their public-content metadata before selecting an import source. Never merge blindly or import legacy users, sessions, secrets, or form entries.
- Treat SQL dumps and public/wp-content/uploads as read-only source material. Exclude both from Git. A separate wp_legacy database is required for analysis; no application database may be overwritten.
- Live observations must be recorded as evidence, not assumed from the requirements. Initial attempts to access robots.txt and the three sitemap entrypoints through the web tool failed; this does not establish the origin server HTTP status.

## 2026-11-02 — Updated media location
- Rechecked root CONTENT-MIGRATION.md: media source is now public/wp-content/uploads, and that directory exists. The former root _uploads directory is absent. Preserve this source in place; future media commands must handle source and mirror destination being the same path without overwriting originals.
- The root migration guide names u802165322_KeH3A.sql; MASTER-PROMPT.md still names u364121842_3Zy6A.sql. Use the updated root migration guide as the working selection, verifying its site identity before any import.


## 2026-11-02 — Design reference supplied
- docs/DESIGN-trivo-in.md is now available, alongside docs/SKILL-trivo-in.md. The missing design-reference gap is resolved. CLAUDE.md corrections continue to override the auto-extracted type scale; verified live visuals remain authoritative.

