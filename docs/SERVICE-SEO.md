# Service SEO editing

Service SEO now has a separate metadata form and revision workflow. It uses the shared admin SEO editor and `ContentSeo` validator; the existing `PageSeo` class retains the page workflow's schema context. Service content, ordering, featured image, source fields, path, status and custom schema JSON are not changed by SEO saves.

## Permissions and revisions

`services.seo` and ServicePolicy protect all service SEO routes/actions, along with authenticated second-factor sessions, Form Requests and CSRF. SEO Managers can edit metadata without content-edit permission. Administrators and Editors retain their existing SEO permissions. Active published/scheduled services can receive metadata corrections; archived records are excluded.

Saves lock the service and require the current `content_version`, sharing the content editor's stale-change protection. Each save creates a `kind=seo` revision belonging to the Service model and its exact ID. Restoration checks model, owner and revision kind, revalidates metadata and images, then creates a new version. Page/service revisions cannot cross model boundaries even if their numeric IDs match. Content history and restoration remain separate from SEO history.

## Shared fields and validation

The editor includes SEO title/description, same-host HTTPS canonical override, allowlisted robots directives, sitemap inclusion/priority, Open Graph and Twitter/X overrides, social images and a desktop/mobile search preview. Plain text is escaped; HTML/control characters, unsafe canonicals, contradictory robots flags and noindex/sitemap conflicts are rejected. Noindex/nofollow flags are derived from robots directives. Blank optional fields explicitly clear overrides; displayed fallbacks are never silently persisted.

Service schema overrides allow `Service`, `WebPage` and `FAQPage`. Page-only AboutPage/ContactPage choices are not accepted for services. Schema JSON generation and checking visible FAQ content belong to public/template integration; raw custom schema JSON cannot be edited here.

Social images must be ready, unarchived rasters with alt text. Save/restore locks and rechecks them, coordinating with image archive. Revision references continue protecting an image after the current override is cleared. No website image is processed by this metadata workflow.

## Verification and remaining scope

Fourteen new synthetic tests cover service field isolation, shared validation, service schema choices, stale edits, wrong-model/wrong-owner/wrong-kind revisions, image history and readiness, published/archived behavior and permission/authentication/CSRF boundaries. The focused page/service SEO suite passes 39 tests with 207 assertions. The full suite passes 232 tests with 1164 assertions; Pint, Larastan level 6, the production asset build and Composer audit pass.

Browser reproduction uses `node tools/check_page_seo_ui.cjs --service` with freshly generated isolated fixture credentials. The default command continues checking pages, so the same form behavior can be verified in both contexts. Both variants pass nine axe A/AA audits across normal/narrow preview and validation states in desktop light/dark and 390px mobile light, with no recorded JavaScript errors or page overflow. Neither tool imports source content or publishes services. Reports are in `docs/admin-preview/service-seo-accessibility.json` and `page-seo-accessibility.json`.

Service publication/scheduling is now documented in SERVICE-PUBLICATION.md. Pending: public SEO/JSON-LD/social output, actual service template mappings, verified legacy metadata import, production database acceptance and release parity. This feature does not create public routes or deploy a frontend. Video handling remains manual.
