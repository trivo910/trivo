# Luhaif Perfumes: first database-backed case study

This implementation covers **Luhaif Perfumes only**, at `/luhaif-perfumes`, using `https://trivo.in/luhaif-perfumes` as the reference. Other project detail pages have not been migrated. The existing shared public header, footer and working enquiry form remain shared components.

## Populate the database

Run `php artisan db:seed --class=LuhaifPortfolioSeeder --force` against the intended application database. This is an explicit opt-in seeder, not part of the default development seed. It uses `database/seeders/data/luhaif-perfumes.json` and existing local files under `public/wp-content/uploads/`; it makes no network requests, changes no original image files, and sends no email. No schema migration is required because `portfolio_items.details` already supports JSON.

The import creates one published PortfolioItem, registers its original images in the media library, and records an initial content revision. If the original imported Post owns this URL, its ID/body/metadata are retained as a draft at `/portfolio-source/luhaif-perfumes-{id}`. A source-transfer revision records its original attributes, and the Portfolio details retain its source ID. The canonical public URL transfers to Portfolio. Conflicting Pages, unexpected Posts, missing images and unsafe source paths stop the transaction. Repeating the seeder retains existing Portfolio content, including subsequent admin edits, rather than overwriting it.

The reference's existing SEO title and description incorrectly mention Arihant Panel Fittings. They are preserved in the source data to avoid inventing replacement content and can be corrected under **Edit portfolio SEO**.

## Admin editing

Open **Admin → Portfolio → Luhaif Perfumes**. Its design editor controls:

**Create portfolio draft** opens the same visual editor with a blank four-section design. New profiles can be saved, reopened, edited, given SEO metadata and published without a database import. Choose a card thumbnail to include the new profile in the public portfolio listing.

- Display month, listing thumbnail, content width, page/text colours and banner gradient.
- Hero heading, eyebrow, description, image, button and growth badge.
- Five report sections, with image selection, formatted copy and left/right placement.
- Results heading, introduction and all statistic values/labels.
- Related-project cards with images, titles, dates, links and ordering.
- Closing call to action, including its text and button.
- Section visibility, spacing and ordering, and adding/removing sections or cards.

Media alt text and replacement uploads use the existing media library. Descriptions support sanitized basic HTML; URLs accept internal paths or HTTPS, and colours/spacing/types are validated. Portfolio title, sort order and SEO use the existing controls. Published portfolios remain editable; saving updates the live page. Published URLs cannot be renamed and published entries cannot be archived as drafts. SEO Managers retain SEO access without content-edit access.

The layout is stored under `details.case_study`, version 1. Every save snapshots it with the existing content revision and increments `content_version`; stale submissions are rejected. Restoring a layout revision preserves the current URL, source details, SEO and publication state. Referenced layout images also appear in generated content blocks, allowing the existing image validation, publication checks and media-reference protection to apply.

The `/portfolio` listing gets Luhaif's title, thumbnail and display date from its Portfolio record. The 13 remaining project cards retain their existing data until each page is migrated. Their appearance in Luhaif's related carousel is editable card data, not a migration of those projects.

## Verification

`tests/Feature/PortfolioCaseStudyTest.php` covers import/source retention, repeat imports, published editing, HTML sanitization, image/URL/layout validation, live rendering, listing updates, revision restoration, visibility/order, roles, stale saves and public publication state. Existing portfolio draft/publication/SEO tests provide shared-workflow regression coverage. Browser checks use a separate SQLite database and verify desktop/mobile rendering, all portfolio images, no horizontal overflow or JavaScript errors, and admin save → live update without modifying the application record.

Validation for this change: 56 portfolio tests (342 assertions), 63 shared catalogue regression tests (432 assertions), and the portfolio listing test (13 assertions) pass. Targeted PHPStan checks and the production build pass. Browser checks pass at 1440px and 390px, with no portfolio image failures, JavaScript errors or page overflow, and successful admin-save/live-update checks.

## Add/edit browser regression

Run `php tools/setup_portfolio_browser_fixture.php` to prepare the dedicated `.audit/portfolio-reference/qa.sqlite` database and a synthetic local administrator. Start a local server on `127.0.0.1:18766` with `DB_CONNECTION=sqlite`, `DB_DATABASE` set to that absolute file path, `CACHE_STORE=array`, and `VIEW_COMPILED_PATH` set to `.audit/portfolio-reference/views`. Then run `node tools/check_portfolio_profiles_ui.cjs`.

The browser check creates uniquely named synthetic profiles in desktop light, mobile light and desktop dark. It verifies creation through the admin menu, reopening, title/body/image selection persistence, added sections and statistic cards, section reordering, invalid URL rejection with retained input, two-tab stale-edit rejection, SEO saves, publishing, published edits and listing/public-page visibility. It checks JavaScript errors and horizontal overflow. No enquiry forms are submitted or emails sent. Results and screenshots remain in ignored local QA storage; the application database is never used for these test profiles.
