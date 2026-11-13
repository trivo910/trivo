# Page drafts and sections

The first page-management milestone provides draft creation/editing, search/status filtering, ordered sections, revision history, content restoration and reversible archive/restore. The draft save itself does not publish pages or create public routes. Page SEO editing and the separate backend publication workflow are now documented in PAGE-SEO.md and PAGE-PUBLICATION.md. Public SEO output, templates and import remain pending.

## Access and workflow

Existing `pages.view/create/update/delete` permissions and PagePolicy protect the module. Editors and administrators can manage drafts; SEO Managers can list/read pages but cannot change their content. All mutations use Form Requests, policy checks and CSRF. Published, scheduled or previously published pages are read-only in this draft workflow. No imported page can be archived, and imported paths cannot be changed.

Each save creates a revision containing only title, path, sections and the content version. Restoring a revision checks that it belongs to the same page and creates a new version of its title/sections, retaining the current path, template and SEO fields. The original revision remains. Stale editor submissions and stale archive/restore/revision actions are rejected using `content_version`; reload and review before saving again.

Archive is a soft delete for never-published, never-scheduled, non-imported drafts only. Child pages prevent archiving their parent. An archived draft releases its path for a new page while retaining the former path in `archived_path` and its revision history. Restoring is allowed only if that path remains available; resolve any collision first. Archived page and revision image references continue to protect those images.

## Section schema

The shared section component offers up to 50 ordered sections:

| Type | Saved fields | Constraints |
| --- | --- | --- |
| Heading | `type`, `text` | Plain text, up to 250 characters; intended as H2 beneath the page title |
| Paragraph | `type`, `text` | Plain text, up to 8,000 characters; newlines retained |
| Image | `type`, `media_id` | Ready, unarchived JPEG/PNG/WebP/AVIF with alt text |
| Link/button | `type`, `label`, `url` | Plain label; safe site-relative or HTTPS URL without credentials |

HTML and unknown section types/keys are rejected. This editor does not accept rich HTML, arbitrary embeds, custom scripts or video-library data. Rich content sanitisation/Tiptap and verified template-specific sections remain later work. Video handling stays manual as agreed.

Alpine adds/removes/reorders sections and sends a bounded JSON field through a native form. Temporary client keys are removed before submission. Without JavaScript, title/path saving retains existing sections; section editing requires JavaScript. All displayed text is escaped.

Image rows are locked and rechecked during the save transaction, sharing the archive action's lock protocol. Image references in revisions are retained even after a later edit removes them from the current page.

## URL safeguards and integration

Canonical paths retain the submitted spelling/case/trailing slash. Validation reserves the homepage, rejects unsafe/reserved routes and detects case/trailing-slash aliases against active pages, posts, categories, tags, authors and literal redirect sources. Archived draft paths are available for reuse, but their saved path must be conflict-free before the draft can be restored. It never derives a path from a title. New pages cannot claim system/asset paths. Existing public paths require a separately reviewed redirect workflow rather than casual renaming.

Page writes use a shared `content-path-writes` cache lock and a row-locked transaction. Future content/redirect writers must coordinate that lock and reuse the catalog check before cross-module path uniqueness can be claimed under concurrent writes. This is an editor safeguard, not the Phase 4 canonical resolver or proof of URL parity. Arbitrarily percent-encoded aliases, server rewrites/static assets and full legacy URL reconciliation still require release/import checks.

## Verification and remaining work

The page feature has 35 passing synthetic tests covering exact/reserved/colliding paths, input bounds and injection, role boundaries, CSRF/authentication, stale edits, revision ownership/restoration, image reference retention and archive/restore. The full application suite passes 157 tests with 746 assertions.

Page list/create/edit pass nine axe A/AA audits across desktop light/dark and a true 390px mobile viewport. Browser workflows exercise section order/removal, native saves without JavaScript, revisions and archive/restore. Evidence is in `docs/admin-preview/pages-accessibility.json` and its screenshots. All fixtures remain isolated under `.audit/`.

Pending: SEO editing/scoring, publication/scheduling, parent/template controls, verified page-builder schemas, public previews/rendering, import and supported MySQL/MariaDB acceptance. No real pages or original uploads are modified by the synthetic validation tools.
