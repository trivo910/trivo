# FAQ draft management

FAQ drafts are available under the hidden admin path at `/faqs`. The existing FAQ table stores the question in `title`, the answer as typed ordered `blocks`, an optional image and a bounded display order. This workflow uses the existing shared glass components and catalog draft actions; it adds no dependencies and imports no source content.

## Editing and preservation

Paths are optional: embedded FAQ entries save with a null path rather than an invented URL. Multiple pathless entries are supported. When supplied, a path must pass the same exact-path, reserved-route and cross-module collision checks as other content. The catalog now reserves paths in FAQs, testimonials and clients as well as existing page/blog/service/portfolio tables. Archived FAQ paths remain reserved. Imported paths, including an imported null path, cannot be changed through the editor.

Questions and answer sections accept plain text only. Shared section validation allows headings, paragraphs, ready raster images with alt text and safe internal/HTTPS links; arbitrary HTML and unsupported section keys are rejected. Image rows are locked and rechecked on save or revision restoration. Existing source `body` and `details` are preserved, not rewritten or rendered as raw HTML.

Every save records a model-scoped content revision and increments `content_version`. Stale saves, archive/restore requests and revision restores are rejected. Revision restoration creates a new version of the question, answer sections, image and order while keeping the current nullable path and source fields. Wrong-model, wrong-owner and wrong-kind revisions cannot be restored. Existing image-usage checks protect current, archived and historical references.

## Access and lifecycle

Policies use `faqs.view/create/update/delete` independently of other module permissions. All mutations use Form Requests, the verified admin session and CSRF protection. SEO Managers can view but cannot mutate FAQ content. Only active drafts without a publication timestamp can be edited; published/scheduled and archived entries are read-only. Only never-published, non-imported, unlocked drafts may be archived or restored. Archive is reversible and retains history.

The migration adds version, publication timestamp/lock and soft-delete fields. Existing published/scheduled rows receive a path lock without inventing publication dates. There is no publish action or cron publisher for FAQs yet.

## Verification and remaining work

Eighteen synthetic FAQ tests contribute 123 assertions; the full suite passes 310 tests with 1646 assertions. Pint, Larastan level 6, the production asset build and configuration/route/view/event cache checks pass. Chrome checks cover create/edit/list, section ordering, saves, revisions, archive/restore and a pathless mobile entry across desktop light/dark and 390px mobile light: nine axe audits with no recorded violations. Evidence is in `docs/admin-preview/faqs-accessibility.json` and `faq-*.png`.

Reproduce with a fresh `tools/setup_admin_browser_fixture.php` fixture and `node tools/check_service_ui.cjs --faq`; use the isolated SQLite database and compiled-view directory described in `docs/admin-preview/README.md`. No real accounts, questions, media or public content are created by QA.

Pending: FAQ SEO fields/editor, publication workflow, verified source extraction, page-to-FAQ placement, public visible answers and conditional FAQPage JSON-LD. Saving an FAQ does not create a public route. Supported production database/hosting acceptance and final URL/SEO parity remain pending. Videos remain manual.
