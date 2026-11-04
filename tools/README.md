# Migration audit tools

These tools inspect read-only sources. Run from the repository root with Python 3.12 and the packages in `tools/requirements.txt`. All raw downloads, sanitized source exports and portable tools stay in ignored `.audit/`.

- `python tools/inspect_dump.py`: schema names and dump timestamps only.
- `python tools/audit_legacy.py`: parse the selected dump, export allowlisted public fields locally, and generate DB/site reports. Existing live URL observations are retained; candidates are written separately under `.audit/`.
- `python tools/audit_media.py`: dimensions, SHA-256, original attachment matches and missing files. Does not convert or change originals. Requires the audit export first.
- `php tools/import_legacy_audit.php`: create **new** `wp_legacy` from the sanitized export. Refuses to touch an existing database. Optional `MIGRATION_DB_HOST/PORT/USERNAME/PASSWORD` environment variables; never pass credentials as command arguments.
- `python tools/probe_live.py`: bounded read-only endpoint probes.
- `python tools/crawl_live.py`: recurse same-host sitemaps, crawl public links, and capture SEO fields and source membership. Requires outbound network access. Records final response metadata alongside initial redirect status; query variants and static media are outside this crawler's scope.
- `python tools/reconcile_redirects.py`: refresh observed aliases without repeating the full HTML crawl.
- `python tools/capture_reference.py`: capture key live templates with installed headless Chrome at 1440 and 390 pixels. These are viewport captures, not full-page visual parity verification. Check `docs/reference/CAPTURES.csv` and review images before treating them as evidence.
- `python tools/setup_php83.py`: fetch official Windows PHP 8.3 with checksum validation into `.audit/php83`; leaves the installed XAMPP runtime unchanged.
- `python tools/setup_fonts.py`: fetch Montserrat Latin WOFF2 and its license for self-hosting.
- `python -m unittest discover -s tools -p test_*.py`: run synthetic decoder and HTML extraction tests; no production rows or secrets used.
- `php tools/setup_admin_browser_fixture.php` and `node tools/check_admin_ui.cjs`: create an isolated local QA fixture and run true-viewport admin browser/accessibility checks with installed Chrome. See `docs/admin-preview/README.md` for the separate database/server setup. The fixture must never target production.
- `python tools/local_smtp_sink.py`: accept one authenticated synthetic SMTP session on loopback port 25266 and store its message only under ignored `.audit/`. Used by browser QA; it cannot relay mail and must not run as a public mail service.
- `node tools/check_media_ui.cjs`: image-library browser/accessibility checks using a generated fixture, isolated database and isolated storage roots. It runs one queued image conversion and does not process legacy uploads. See `docs/admin-preview/README.md`.
- `node tools/check_website_settings_ui.cjs`: site/social/analytics form saves, validation, theme-return paths and nine accessibility audits in the isolated browser fixture. No SMTP/image conversion is needed, and external requests are blocked and asserted absent.
- `node tools/check_pages_ui.cjs`: synthetic page drafts, section ordering/removal, native fallback, revision restoration and archive/restore in the isolated browser fixture, with desktop/mobile accessibility evidence. Does not publish pages or import source content.
- `node tools/check_page_seo_ui.cjs`: synthetic SEO metadata saves/restoration, live preview/fallback, narrow preview and robots validation across three browser layouts. Regenerate the isolated fixture's one-use authentication codes before running.
- `node tools/check_page_seo_ui.cjs --service`: run the shared SEO browser workflow for services, including Service schema selection. Regenerate the isolated fixture before switching between page/service checks; each consumes one-use authentication codes.
- `node tools/check_page_seo_ui.cjs --portfolio`: run the shared SEO workflow for portfolio entries, including CreativeWork schema selection. Regenerate the isolated fixture before switching contexts; no real metadata is imported.
- `node tools/check_publication_ui.cjs`: synthetic readiness failures, schedule/cancel/publish transitions and nine accessibility audits. Uses the isolated QA database only; no source pages are imported or deployed.
- `node tools/check_publication_ui.cjs --service`: run the shared publication browser workflow for services, including Service schema readiness. Regenerate the isolated fixture before switching contexts because authentication codes are single-use.
- `node tools/check_publication_ui.cjs --portfolio`: run the portfolio publication workflow with CreativeWork schema readiness and model-specific screenshots/reports. Regenerate isolated fixture credentials before each run.
- `node tools/check_service_ui.cjs`: synthetic service drafts, section ordering, display order, revision restore and archive/restore, with three layouts and accessibility evidence. Regenerate the isolated browser fixture before running.
- `node tools/check_service_ui.cjs --portfolio`: run the shared draft browser workflow for portfolio entries. Regenerate the isolated fixture before switching model contexts; one-use authentication codes are consumed by each run.

Audit SQL is **never executed**. The PHP subset importer uses prepared values and hardcoded table/column identifiers. The resulting audit schema intentionally excludes revisions, users, sessions, submission data, arbitrary options and arbitrary metadata. Attachment records have their own `wp_attachments` table; this is not a full WordPress clone. PHP Laravel import commands must account for that schema explicitly.
