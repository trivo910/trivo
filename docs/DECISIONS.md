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


## 2026-11-02 — Verified audit and foundation
- Both migration guides and MASTER-PROMPT.md now consistently name u802165322_KeH3A.sql and public/wp-content/uploads. Earlier conflicting references above are historical.
- Selected dump header is 2026-11-02; alternate is 2026-06-04. Allowlisted site settings verify trivo.in. No source files were modified.
- Import only an allowlisted public subset into a new wp_legacy, using prepared statements. Full raw-dump execution would import prohibited accounts and potentially secrets. Attachments use wp_attachments in this intentionally sanitized schema. Existing databases are never overwritten by the importer.
- Initial web failures were resolved with the identified audit client. The baseline now records initial HTTP status, final status and one-hop alias behavior, rather than counting followed redirects as direct 200s.
- Live HTML identifies page 991442 as the owner of /digital-marketing-agency-in-surat; preserve colliding post 988965 for review and record both source IDs.
- Source-only Python tools handle the pre-Laravel audit. Application code began only after the substantive URL-MAP.csv existed. Exhaustive audit gaps remain explicit rather than being claimed complete.
- System PHP 8.2 and MariaDB 10.4 fall below project requirements. Use checksum-verified portable PHP 8.3 in ignored .audit/php83 and isolated SQLite for local foundation validation; do not change or upgrade XAMPP without a separate environment task. Production remains MySQL 8/MariaDB 10.6+ and PHP 8.3.
- Pest/Larastan are development-only dependencies. Alpine is the only public UI dependency. Python Pillow is audit-only, not an application dependency.
- Encrypt all settings values at rest and exclude values from model serialization, including SMTP data. Do not create seeded/default admin accounts; explicit provisioning and mandatory TOTP belong to the admin phase.
- Public templates and content are pending. The root route returns 503/noindex until verified rendering exists; do not publish a generic demo as the migrated site.
