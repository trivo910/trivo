# Testimonial draft management

Testimonial drafts are available under the hidden admin path at `/testimonials`. Administrators and Editors can create and edit a title, typed quote sections, an optional image, an optional exact path and display order. The editor reuses the existing glass components and shared catalog draft actions. No package or source content was added.

## Content and paths

Embedded testimonials do not require a URL. Missing paths are stored as null and multiple pathless drafts are supported. Supplied paths use the shared reserved-route and cross-module collision checks, retaining their original case/trailing slash. Archived paths remain reserved. Imported paths, including imported null paths, cannot be changed through this workflow.

Quote sections accept validated plain text, raster images and safe internal/HTTPS links. Arbitrary HTML and unsupported keys are rejected. Existing source `body` and `details` remain unchanged; the editor does not invent reviewers, companies, ratings or attribution. Verified source mapping and dedicated attribution fields remain separate integration work.

Selected images must be ready, unarchived rasters with alt text. Save and revision restoration lock and recheck media rows. Existing media usage checks protect images referenced by current content, archived entries and historical revisions.

## Permissions and history

Policies use independent `testimonials.view/create/update/delete` permissions. All writes use Form Requests, verified second-factor sessions and CSRF. SEO Managers can view but cannot change testimonial drafts. Published/scheduled and previously published entries remain read-only. Only never-published, non-imported, unlocked drafts can be archived/restored.

Every save records a model-scoped revision and increments `content_version`. Stale saves, archive/restore requests and revision restores fail validation. Restoration creates a new version of the title, quote sections, image and order while keeping the current nullable path and source fields. Cross-model, wrong-owner and wrong-kind revisions are rejected, including when FAQ/testimonial IDs match.

The migration adds version, publication timestamp/lock and soft-delete fields, locking existing published/scheduled rows without inventing dates. It does not implement publication transitions or a testimonial cron publisher.

## Verification and remaining work

Thirteen synthetic tests with 95 assertions cover field isolation, optional paths, unsafe input, versions, source preservation, revision ownership, cross-module aliases, archive/restore, images, permissions, ordering, authentication and CSRF. Run `node tools/check_service_ui.cjs --testimonial` with freshly regenerated isolated browser fixture credentials for the create/edit/list workflow in three layouts. Use the SQLite and compiled-view isolation described in `docs/admin-preview/README.md`.

The full suite passes 323 tests with 1741 assertions. Pint, Larastan level 6, the production asset build and configuration/route/view/event cache checks pass. Chrome create/edit/list checks pass nine axe audits across desktop light/dark and 390px mobile light, including a pathless mobile entry. Evidence is in `docs/admin-preview/testimonials-accessibility.json` and `testimonial-*.png`. The application database remains without testimonial entries or default accounts; QA data stays isolated. Current dependency advisory status remains unverified after the previously recorded Packagist DNS failure; no dependency changed for this module.

Pending: source-verified attribution/template fields, SEO editing, publication, source import, page placement, public rendering and supported production database/hosting acceptance. Saving a draft does not create a public route, publish content or generate review/rating structured data. Videos remain manual.
