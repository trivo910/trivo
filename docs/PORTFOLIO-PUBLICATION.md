# Portfolio publication and scheduling

Portfolio entries now support publish-now, future UTC scheduling, rescheduling and cancellation through the shared publication workflow. These are backend state changes. Public case-study templates, canonical routing, SEO output and source import remain pending; publishing does not create a public route or deploy the website.

## Permissions and readiness

Existing `portfolio.publish` permissions and PortfolioItemPolicy protect transitions, along with verified second-factor sessions, Form Requests and CSRF. Administrators and Editors can publish; SEO Managers retain metadata permissions without publication access. Every change locks the record and checks the current `content_version`.

Readiness uses the portfolio schema context (`WebPage`/`CreativeWork`) and requires non-empty validated sections, SEO title/description, consistent canonical/robots/sitemap preferences and a conflict-free exact path. Content, featured and social images must be ready, unarchived rasters with alt text. Referenced image rows are locked together in sorted order and checked again when a schedule becomes due.

Source body/details, case-study copy, metadata, featured image, display order and path are preserved by publication transitions. Cancellation returns the entry to draft editing and clears the scheduled timestamp/error, while retaining the permanent path lock. Formerly scheduled entries cannot be renamed or archived. Published/previously published content stays read-only in this workflow. SEO corrections remain a separate permission/action.

## Due execution and history

Run `php artisan trivo:publish-portfolio --limit=50` for due entries. Limits 1–100 are accepted; the existing minute scheduler invokes it with overlap protection. `PublishDueContent` explicitly allowlists portfolio alongside pages/services, rechecks due state under a row lock and uses the shared content-path cache lock. It processes a bounded batch with a 45-second soft budget between items, publishes once and retains the intended UTC timestamp even after delayed execution.

Readiness failures retain the schedule with a fixed `validation_failed` code and a visible review message. The first unchanged failure increments the version and records an event. Repeated failures rotate through bounded batches without creating duplicate revision/activity events, and repaired records are rechecked later. Infrastructure failures still fail the command.

Portfolio publication events use the PortfolioItem model and exact ID, including when page/service/portfolio IDs match. Scheduled events are explicitly system/anonymous events. Recent publication history is separate from content/SEO history; publication revisions cannot be restored through those forms.

The existing shared publication panel uses native form actions and explicit UTC schedule inputs. No Redis/Supervisor dependency or new package was introduced. Existing portfolio publication timestamps/path locks are retained; this migration adds an error field and an index on the schedule timestamp.

## Verification and remaining integration

Fifteen synthetic tests with 89 assertions cover readiness/schema/path failures, exact UTC scheduling, cancellation/path retention, stale changes, publisher/CSRF/authentication/archive boundaries, model isolation, due-time image checks, once-only execution, bounded batches/locks and command limits. Existing page/service tests exercise the same shared publisher.

Use `node tools/check_publication_ui.cjs --portfolio` with freshly regenerated isolated fixture credentials. The `--service` variant and default page command remain regression workflows, each recording a separate report under `docs/admin-preview/`.

The full suite passes 292 tests with 1523 assertions; Pint, Larastan level 6 and the production asset build pass. Portfolio and service publication browser checks each pass nine axe audits across desktop light/dark and 390px mobile light, covering readiness failures, schedule/cancel/publish and read-only content. Browser QA has its own compiled-view directory to avoid Windows rename contention. A fresh Composer audit was attempted but Packagist DNS resolution failed; no current advisory conclusion is drawn from that attempt.

Pending: public case-study templates/SEO/routing, source import, public cache invalidation, cross-module path-writer coordination and supported production database/hosting acceptance. The minute scheduler now runs queue work plus page/service/portfolio publication commands sequentially; actual shared-hosting cron runtime/limits still require verification. No real case studies or source media are imported or published by development validation. Videos remain manual.
