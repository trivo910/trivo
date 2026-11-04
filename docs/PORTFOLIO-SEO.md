# Portfolio SEO editing

Portfolio SEO now uses the shared metadata form and validator, with separate save/restore actions and `portfolio.seo` authorization. It edits SEO/search/social metadata without modifying case-study title, sections, source body/details, featured image, display order, path, publication state or custom schema JSON. Backend publication is covered in PORTFOLIO-PUBLICATION.md; public SEO output and source import remain pending.

## Permissions and revisions

Existing roles grant portfolio SEO permission to administrators, Editors and SEO Managers. SEO Managers can edit metadata while their portfolio content access stays read-only. Active published/scheduled entries permit metadata corrections; archived records are excluded. Verified second-factor sessions, Form Requests and CSRF protect all mutations.

Services and portfolio entries share `SaveCatalogSeo` and `RestoreCatalogSeo`, retaining typed model entrypoints. Saves lock the target record, check the shared `content_version`, validate only allowed metadata and lock/recheck social images. Content and SEO edits invalidate stale forms consistently.

Each save records a model-scoped `kind=seo` revision. Restore checks the exact model, owner ID and revision kind, then creates a new metadata version after validating the saved fields/images again. Page/service/portfolio revisions cannot cross boundaries even when IDs match. Content history/restore remains separate and retains current SEO values.

## Metadata and preview

The shared form provides SEO title/description, same-host HTTPS canonical overrides, allowlisted robots directives, sitemap inclusion/priority, social text/image overrides and approximate desktop/mobile search previews. Plain metadata is escaped. HTML/control characters, unsafe canonicals, contradictory robots flags, noindex/sitemap conflicts and incompatible schema types are rejected. Indexing flags are derived from robots settings.

Portfolio schema choices are `WebPage` and `CreativeWork` (case study). Service/AboutPage and other unrelated overrides are not accepted in this context. This stores a classification only; public JSON-LD generation and source/template validation remain pending. Custom schema JSON is retained but cannot be edited here.

Blank optional overrides explicitly clear saved values. Preview fallbacks are displayed without silently writing generated copy. Social images must be ready, unarchived rasters with alt text. History references continue protecting them after the current selection is removed, and restore checks their current readiness. No image bytes are processed by this workflow.

## Verification and remaining scope

Fourteen synthetic tests with 78 assertions cover field isolation, portfolio schema/metadata validation, stale changes, wrong-model/wrong-owner/wrong-kind restoration, historical image protection/readiness, published/archived behavior and permission/authentication/CSRF boundaries. Service SEO regression tests also pass after sharing the catalog actions. The full suite passes 277 tests with 1434 assertions; Pint, Larastan level 6, the production asset build and Composer audit pass.

Use `node tools/check_page_seo_ui.cjs --portfolio` after generating fresh isolated fixture credentials. Portfolio SEO passes nine axe A/AA audits across the normal/narrow preview and validation states in desktop light/dark and 390px mobile light, with no recorded JavaScript errors or document overflow. The `--service` flag verifies the service workflow and the default command checks pages. Each consumes one-use authentication codes and writes its own screenshot/accessibility report. No real portfolio metadata is imported or published by these tools.

Pending: public SEO/JSON-LD/social rendering, verified case-study fields/templates, source import, supported production database/hosting checks and final URL/SEO parity. Videos remain manual.
