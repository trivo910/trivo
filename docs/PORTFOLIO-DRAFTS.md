# Portfolio draft management

Portfolio administration now provides draft creation/editing, case-study sections, featured-image selection, display order, search/status filters, revision restoration and reversible archive/restore. Portfolio SEO is available separately in PORTFOLIO-SEO.md. Publication/scheduling, verified source schemas, public rendering and import remain pending. No real case-study copy, project facts or public routes are generated.

## Shared content safeguards

Existing `portfolio.view/create/update/delete` permissions and PortfolioItemPolicy guard every route/action, with verified second-factor sessions, Form Requests and CSRF. Administrators and Editors can manage drafts. SEO Managers can read entries but cannot change their content. Published/scheduled/previously published content is read-only in this draft workflow; imported paths cannot change and imported entries cannot be archived.

Portfolio and service drafts share `SaveCatalogDraft`, `ArchiveCatalogDraft` and `RestoreCatalogRevision`, with typed entrypoints for each model. They reuse `ContentDrafts` validation and the existing section component. Each write uses the shared content-path lock, a freshly locked model row and `content_version`; stale edits, restores and archive actions are rejected.

Saves whitelist title, exact full path, typed ordered sections, optional featured image and display order (0–10,000). Existing source body/details/SEO values and publication fields cannot be overwritten through the draft form. Rich source fields remain retained until their schema and sanitisation/rendering pipeline are verified.

Sections support plain headings/paragraphs, ready raster images and safe links; HTML, unknown keys/types, malformed JSON and unsafe URLs are rejected. Featured and section images must be ready, unarchived JPEG/PNG/WebP/AVIF entries with alt text. Image rows are locked together before saving; image bytes and legacy uploads are not processed by this module.

## Paths, history and ordering

The existing catalog check now permits PortfolioItem as an explicit owner while retaining cross-module collisions against pages, posts, services, taxonomies/authors, portfolio entries and literal redirect sources. Only the matching table and ID are excluded. Case/trailing-slash aliases and archived paths remain reserved; the homepage belongs to pages.

Saving creates a model-scoped content revision. Restore verifies owner ID, model type and revision kind, then creates a new version of title/sections/image/order while preserving the current path and source/SEO fields. Matching service/portfolio IDs cannot cross restoration boundaries. Historical image references remain protected after a current selection is removed.

Archive is a soft delete for non-imported, never-published/locked drafts. Paths, revisions and image references remain reserved. Restoration retains identity and requires the current version. There is no permanent-delete endpoint.

Entries sort by ascending display order with ID as the stable tie-break. Search and status filtering use bound queries and allowlisted filters. No arbitrary database ordering or fields are accepted from the browser.

## Verification and remaining scope

Synthetic tests cover field isolation, unsafe sections/JSON, ordering bounds, stale changes, equal IDs across content models, reserved/archived aliases, revision ownership, image-history retention, imported/published protections, ordering/filtering and permissions/authentication/CSRF. The new portfolio feature has 16 passing tests with 107 assertions; service draft regression tests also pass after the shared-action extraction. The full suite passes 263 tests with 1356 assertions; Pint, Larastan level 6, the asset build and Composer audit pass.

Browser reproduction uses `node tools/check_service_ui.cjs --portfolio` after regenerating the isolated fixture. The default command continues testing services. Portfolio create/edit/list pass nine axe A/AA audits across desktop light/dark and 390px mobile light. Both workflows operate on synthetic SQLite fixtures; the application database and source uploads remain separate.

Backend publication workflows are available; see PORTFOLIO-PUBLICATION.md. Pending: template-specific project/client/result fields verified from source, public previews/rendering, import, supported production database/hosting acceptance and full URL/SEO parity. Video handling remains manual as agreed.
