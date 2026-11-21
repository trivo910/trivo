# Blog verification — 18 November 2026

## Scope and results

- All **31 existing published articles** were opened in Chrome at **1440px and 390px**. All 62 checks passed: HTTP 200, one main H1, no broken article images, no horizontal page overflow, and no JavaScript errors. Each article was opened in admin, saved while published, and checked again publicly.
- Desktop and mobile workflows passed: create a rich-content draft, upload/process a new image, insert an inline image and caption, choose a featured image, publish, edit the published article and caption, delete, restore privately as a draft, and republish.
- Listing checks passed at both sizes: six initial cards, progressive loading, pagination without duplicate articles, valid card images, and exclusion of portfolio design demos. The QA listing contains 31 imported articles plus two synthetic workflow articles.
- All 31 corresponding live Trivo article references returned HTTP 200. **492 reference paragraphs** longer than 40 characters were present in the local rendered articles after normalizing whitespace and typographic quotes/dashes. This checks article text, not pixel-identical layout.
- The targeted automated run passed **83 tests / 680 assertions**, covering blog drafts, metadata, publication, images, stale saves, revision restoration, deletion/restoration, public routing, source classification, media processing, and security headers. The production frontend build succeeded.

The canonical reference listing is `https://trivo.in/blog`. The supplied `/blogs` URL returned 403. The current reference listing also contains a newer “What Does a Social Media Consultant Do? Costs and Hiring Tips” article outside the existing imported snapshot; this verification did not automatically publish new external content into the application.

## Corrections made during verification

- Fixed the visual editor's upload/refresh buttons: their event context could resolve `$el` to the clicked button instead of the editor, losing the API endpoint URLs.
- Added `2026_11_18_000002_repair_imported_blog_image_paths.php` to replace eight unavailable Elementor crops in two articles with their matching imported originals.
- Added `2026_11_18_000003_classify_imported_portfolio_design_demos.php`. `/new-layout`, `/new-landing-2`, and `/new-landing-3` are imported case-study design demos, not articles. Their content and direct URLs are retained; they are excluded from blog listings and blog administration.
- Fixed saved trailing-slash article paths returning 404 or redirecting repeatedly under `/blog/{slug}`.
- Updated obsolete tests that expected sample articles, rejected HTML, prohibited published edits/deletion, or queried unmigrated databases. Public article H1 assertions now use the database title.

## Reproducing the checks

Run automated tests with GD enabled in the same PHP process:

```powershell
php -d extension=gd vendor/bin/pest --compact
```

The default PHP CLI on this workstation has GD disabled. Enabling it only on `php artisan test` does not propagate that flag to the child test process. Media processing and the browser upload checks require a PHP process with GD and a writable upload temporary directory.

`php tools/setup_blog_browser_fixture.php` copies published content and ready media metadata to `.audit/blog-qa/qa.sqlite`, creates one synthetic admin, and writes ignored local credentials. It refuses to run outside the local environment or against a fixture containing unexpected accounts. It does not change application accounts or content. The QA server must use this SQLite file, `.audit/blog-qa/views`, and isolated `MEDIA_PRIVATE_ROOT` / `MEDIA_PUBLIC_ROOT` directories.

The local browser scripts require the existing `.audit/browser-qa/node_modules/playwright-core` and installed Chrome:

```powershell
node tools/check_blog_ui.cjs
node tools/check_blog_crud_ui.cjs
node tools/check_blog_listing_ui.cjs
node tools/capture_blog_reference.cjs
node tools/compare_blog_references.cjs
```

Start the QA server from `public` on port 18767 with `tools/blog_browser_server.php` as the router. That router serves QA uploads from their isolated directory. `check_blog_crud_ui.cjs` runs the dedicated QA database queue worker to process its synthetic uploads. Credentials, reports, reference HTML and screenshots remain under ignored `.audit/blog-qa/`.

## Broader regression checks

Final full-suite result: **492 passed, 2 failed, 3301 assertions**. Both remaining failures concern service-page layouts outside the blog workflow:

- `PublicPerformanceMarketingNagpurTest`: expects three `data-home-enquiry` hooks but the rendered page contains one.
- `PublicSeoCompanySuratTest`: expects the `service-section--surat-audit` section, which is absent from the rendered service page.

Those service templates were not changed to force this blog verification to pass. The full suite is therefore not completely green. The blog, media, public article, and security checks pass. `git diff --check` passes, and the dedicated QA server was stopped after verification.


## Follow-up: remove the extra contents panel

The user identified an added ?In this article? panel absent from Trivo's original articles. The initial paragraph-presence comparison did not detect extra generated sections. Migration `2026_11_18_000004_disable_automatic_blog_contents.php` disables this flag on imported legacy articles and changes its database default to false. New admin forms also default to unchecked; editors can still explicitly enable it. The migration was applied locally.

The reference comparison now rejects a generated contents panel when the reference has none. All 31 article comparisons passed again, and the reported Surat article was checked at 1440px and 390px without the panel or horizontal overflow. The focused blog suite passed 18 tests / 130 assertions. Full-suite totals above are from the preceding run.
