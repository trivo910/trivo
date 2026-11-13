# Client draft management

Client drafts are available under the hidden admin path at `/clients`. Administrators and Editors can manage the client name (`title`), an optional ready raster logo (`media_id`), display order, an optional exact path and supporting typed sections. Sections may be empty for a simple logo/name entry. The workflow reuses existing glass components and catalog draft actions, with no new dependency or source import.

## Preservation and validation

Embedded client entries do not require a URL. Missing paths save as null, and multiple pathless drafts are supported. Supplied paths retain case/trailing slash and pass the shared reserved-route and cross-module collision checks. Imported paths, including imported null paths, are immutable. Archived paths remain reserved.

Names and supporting sections use shared plain-text validation. HTML, unsupported section keys and unsafe links are rejected. Selected logos/section images must be ready, unarchived rasters with alt text; rows are locked and rechecked on save or revision restoration. SVG uploads remain unsupported. Historical and archived image references remain protected by existing media usage checks.

Source `body` and `details` are preserved rather than rewritten. Client relationships, descriptions, external website fields and other source-specific metadata must be verified before import or public integration; no real client names, logos or affiliations are invented during development.

## Permissions, revisions and archive

Policies use independent `clients.view/create/update/delete` permissions. Form Requests, verified second-factor sessions and CSRF protect writes. SEO Managers can view but cannot mutate drafts. Published/scheduled and previously published entries stay read-only. Only never-published, non-imported, unlocked drafts may be archived or restored.

Saves create model-scoped revisions and increment `content_version`. Stale changes fail validation. Restore creates a new version of the name, sections, logo and order, retaining the current nullable path and source fields. Wrong-model, wrong-owner and wrong-kind revisions are rejected, including matching testimonial/client IDs. Archive is reversible and retains paths/history/image references.

The migration adds version, publication timestamp/lock and soft-delete fields. Existing published/scheduled rows receive locks without invented publication dates. Publication transitions and a client cron publisher remain pending.

## Verification and remaining integration

Fourteen synthetic tests with 100 assertions cover optional paths and empty sections, field isolation, unsafe input, source preservation, stale writes, revision ownership, aliases, archive/restore, image readiness/references, permissions, ordering, authentication and CSRF. Run `node tools/check_service_ui.cjs --client` using fresh isolated browser fixture credentials and the SQLite/compiled-view setup in `docs/admin-preview/README.md`.

The full suite passes 337 tests with 1841 assertions. Pint, Larastan level 6, the production asset build and configuration/route/view/event cache checks pass. Chrome create/edit/list workflows pass nine axe audits across desktop light/dark and 390px mobile light, including an entry without a path; evidence is in `docs/admin-preview/clients-accessibility.json` and `client-*.png`. The application database remains without client entries or default accounts. No dependencies changed; current advisory status remains unverified after the previously recorded Packagist DNS failure.

Pending: client SEO, publication, verified source metadata/import, page placement and public rendering. Saving a client draft does not create a public route or publish a client relationship. Supported production database/hosting checks and final URL/SEO parity remain pending. Original legacy images stay read-only; videos remain manual.
