# Page SEO editing

Page SEO has a separate form and save/restore workflow. It edits metadata without modifying page title, sections, path, template, publication state or custom schema JSON. This is an admin milestone; public SEO output, blog scoring and full source import remain pending. The backend publication/scheduling workflow is now documented in PAGE-PUBLICATION.md.

## Access and revisions

`pages.seo` and PagePolicy protect every route/action, along with authentication, mandatory second factor, session checks, Form Requests and CSRF. The existing roles grant this permission to administrators, Editors and SEO Managers. SEO Managers still cannot edit page content. SEO metadata can be edited on published/scheduled pages without changing their content or status; archived pages are excluded.

SEO writes lock the page and check the shared `content_version`. Content saves, SEO saves and revision restoration therefore invalidate stale forms consistently. Each SEO save creates a revision with `kind=seo`. Content history excludes these entries, and both restoration actions reject the wrong revision kind or another page's revision. A restore creates a new version after validating all metadata and image references again.

SEO restoration retains the current page content/path/status and custom schema JSON. Content restoration retains the current SEO values. Activity events record the action without copying metadata values into the event properties.

## Fields and validation

- SEO title/description and Open Graph/Twitter overrides accept plain text, never HTML or control characters. Limits are 255 characters for titles and 500 for descriptions so longer verified source copy is not silently truncated. Shorter length recommendations are advisory.
- Canonical overrides must be HTTPS on the configured canonical host, without credentials, queries, fragments or protected system/admin destinations. URLs are not fetched. Blank overrides remain unset; public fallback generation is still pending.
- Robots accepts an explicit allowlist: index/noindex, follow/nofollow, noarchive, nosnippet, noimageindex, notranslate and supported maximum snippet/video/image preview directives. Conflicting flags and duplicate preview settings are rejected. `noindex`/`nofollow` columns are derived from the saved directives, preventing drift. Unknown legacy directives require review rather than silent removal.
- Noindex pages must explicitly be excluded from the sitemap. Sitemap priority is optional, between 0 and 1 with one decimal place. Inclusion is only a stored preference; it never publishes a draft or generates a sitemap by itself.
- Schema type can be WebPage, AboutPage, ContactPage or FAQPage. Custom JSON is retained but cannot be edited here. Matching visible FAQ content and final schema generation remain integration checks.
- Social images must be ready, unarchived raster images with alt text. Image rows are locked on save to coordinate with archive. Revision image references remain protected after the current override is cleared.

Each form saves the complete metadata section; blank/omitted optional overrides clear their previous values. Unknown submitted fields cannot alter content, identifiers, flags, schema JSON or publication state.

## Preview and remaining work

An approximate desktop/mobile search preview updates using Alpine text bindings. It displays the page title/canonical path as fallbacks when overrides are empty, without silently saving those fallbacks. Character counts and width are advisory; this is not a search-engine rendering guarantee. No external tracking or search-engine requests are needed for the preview. The native form still saves without JavaScript.

## Verification

The feature adds 25 synthetic tests covering immutable content, stale writes, revision ownership/kinds, permission/authentication/CSRF boundaries, malformed inputs, robots/sitemap conflicts, canonical restrictions, explicit clearing and historical social-image protection. The full suite passes 182 tests with 872 assertions. Pint, Larastan level 6, the asset build, dependency audit and configuration/route/view/event cache checks pass; generated caches are cleared after verification.

`node tools/check_page_seo_ui.cjs` records nine passing axe A/AA audits for the normal form, narrow preview and validation state across desktop light/dark and 390px mobile light. It exercises live preview/fallback, saving and revision restore using only the isolated synthetic QA database. Evidence is in `docs/admin-preview/page-seo-accessibility.json` and screenshots. No real website images or metadata were imported or modified.

Pending: public SEO head/JSON-LD output, social card rendering, SEO defaults/settings, actual template/publication integration, blog focus-keyword and readability scoring, verified legacy metadata import and release URL/SEO parity acceptance. Native video handling remains manual.
