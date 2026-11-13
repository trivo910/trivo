# Blog categories and tags

The hidden admin has category and tag management at `/blog/categories` and `/blog/tags`. Shared native forms cover name, slug, exact archive path and plain description, with category parents, revisions and reversible archive. The existing `posts.view/create/update/delete` permissions apply; SEO Managers can view content but cannot mutate it. No post editor, public archive page or source import is enabled by this module.

## Paths, hierarchy and preservation

Paths are required and checked against the shared content/redirect catalog, including archived records and case/trailing-slash aliases. The saved exact path is immutable for all terms; changes require a reviewed redirect workflow. Slugs are stored separately and do not regenerate paths. Unicode letters/numbers, hyphens/underscores and valid percent-encoded bytes are supported in slugs. Do not invent source category/tag names or URLs.

Category parents must be active categories. Ancestor checks reject self-parenting and indirect cycles. Descendant checks prevent moves that push a subtree beyond five levels. Tags are flat and cannot have parents. Archive active child categories before their parent, or move them first; restore parents before children.

Content saves preserve legacy IDs and existing SEO/custom schema fields. Descriptions accept bounded plain text rather than raw HTML. Imported terms cannot be archived. Any category or tag referenced by a post is protected from archive, including draft posts. Archive retains the exact path, SEO fields and revision history.

## Revisions and authorization

Mutations use Form Requests, Policies, verified second-factor sessions and CSRF. Shared content-path locking serializes term saves/archive/restore; row locks and `content_version` reject stale changes. Successful saves record model-scoped taxonomy revisions and activity events. Restore creates a new version of name, slug, description and parent while retaining the current path and SEO. Wrong-model, wrong-owner and wrong-kind revisions are rejected even when category/tag IDs match. Historical parents are revalidated before restore.

The migration adds version and soft-delete fields to both tables. It was applied incrementally to the CLI MySQL database, the existing browser SQLite database and isolated QA/test databases. A consistent ignored SQLite backup was taken first; existing accounts/content were preserved.

## Verification and remaining integration

Synthetic tests cover field isolation, plain text and slug/path validation, cross-module aliases, stale saves, revision ownership, hierarchy bounds, parent-first restoration, archive/import/reference guards, permissions, authentication and CSRF. Reproduce category browser QA with a fresh isolated fixture and `node tools/check_blog_terms_ui.cjs`; regenerate the fixture before `node tools/check_blog_terms_ui.cjs --tags` because authentication codes are single-use. Use the isolated SQLite/compiled-view setup in `docs/admin-preview/README.md`.

Verification completed on 2026-11-05: the full suite passes 410 tests with 2303 assertions; the 20 taxonomy tests contribute 176 assertions. Pint, Larastan level 6, the production asset build and isolated configuration/route/view/event cache checks pass. Category and tag browser workflows each pass nine axe audits across desktop light/dark and 390px mobile light, with no recorded violations. Existing browser SQLite category/tag list/create checks and all six previously affected module lists return HTTP 200; both accounts remain intact. No dependency changed; current advisory status remains unverified after the previously recorded Packagist DNS failure.

Pending: term SEO editing/output, post editor and term assignment, coordinated post/term write locks, author profiles, public archives/pagination/caches, verified source import and production database/hosting acceptance. Future post assignment must use active terms and participate in the shared content-write lock. Raw source SQL/uploads remain read-only, and videos remain manual.
