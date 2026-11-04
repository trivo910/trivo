# Service draft management

Service administration now provides draft creation/editing, search/status filters, display ordering, featured-image selection, typed sections, revision restoration and reversible archive/restore. Service SEO editing and backend publication are documented separately in SERVICE-SEO.md and SERVICE-PUBLICATION.md. Verified template schemas, public rendering and legacy import remain pending. This does not create public service URLs or populate real content.

## Access and fields

Existing `services.view/create/update/delete` permissions and ServicePolicy protect each route/action. Administrators and Editors can manage drafts; SEO Managers have read-only access. Published/scheduled services are read-only in this draft workflow. Imported paths cannot change, and imported services cannot be archived. Authenticated second-factor sessions, Form Requests and CSRF are required.

Saves whitelist title, exact full path, ordered sections, optional featured image and display order (integer 0–10,000). Lower order values appear first, with ascending ID as a stable tie-break. Request fields cannot change status, legacy IDs, SEO values, raw source body or source details. Existing body/details/SEO values are retained; this editor does not introduce a raw-HTML write/render path.

Sections share the page editor's `ContentDrafts` validator and reusable section component: headings, plain paragraphs, ready raster images and safe links. HTML, unknown keys/types, malformed/bounded JSON and unsafe links are rejected. Image references require ready, unarchived JPEG/PNG/WebP/AVIF assets with alt text. The featured image and section images are locked together during save, coordinating with image archive protection. Existing images are not reprocessed.

## Paths, revisions and archive

The shared path catalog now includes services and portfolio entries alongside pages, posts and taxonomy/author paths. It reserves case/trailing-slash aliases and archived entries. Owner exclusion includes both model table and ID, so matching page/service IDs cannot bypass a collision. The homepage is reserved for pages. Page saves and scheduled publication use the same expanded catalog. Cross-module writers still need shared lock coordination before comprehensive concurrent URL parity can be claimed.

Every save locks the current service, checks `content_version`, saves atomically and creates a content revision. Restoring verifies the service ID, model type and revision kind; it restores title, sections, image and order as a new version while retaining the current path and source/SEO metadata. Stale saves, restoration and archive actions are rejected.

Archive is a soft delete for non-imported drafts. Paths, revision history and image references remain reserved. Restoring uses the current version and retains the service identity. There is no permanent-delete endpoint. Historical revision image references continue preventing image archive after a later edit clears the current selection.

## Remaining integration

The feature adds 18 synthetic tests covering field isolation, unsafe sections/JSON, ordering bounds, stale edits, cross-module collisions and equal IDs, imported/published protections, revision ownership, image/history retention, archive/restore and role/authentication/CSRF boundaries. The full suite passes 218 tests with 1083 assertions; static analysis, formatting and the production asset build pass.

Browser verification records nine passing axe A/AA audits for create/edit/list across desktop light/dark and 390px mobile light. It exercises native form saves, section/display order, revision restore and archive/restore with an isolated synthetic database. Evidence is in `docs/admin-preview/services-accessibility.json` and screenshots. The application database and original uploads remain untouched.

Rich source/template mappings, public previews, legacy import, actual MySQL/MariaDB/cron acceptance and public cache/URL parity remain pending. Service SEO and backend publication are available separately. No page/blog/video content is generated automatically; videos remain manual as agreed.
