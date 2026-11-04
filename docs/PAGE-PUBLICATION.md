# Page publication and scheduling

Page details now offers publish-now, schedule, reschedule and cancel-schedule actions. These change backend state only. Public templates and the canonical resolver remain pending; publishing does not create a public route or deploy the website.

## Permissions and state

`pages.publish` and PagePolicy guard all transitions, alongside verified admin sessions, Form Requests and CSRF. Administrators and Editors have this permission; SEO Managers do not. Stale requests are rejected through the shared `content_version` and a freshly locked page row. Archived pages cannot be published.

Drafts can be published or scheduled. Scheduled pages can be rescheduled, published immediately or returned to draft. Cancellation clears the scheduled timestamp and error, but retains the permanent publication path lock. Content editing becomes available again after cancellation; renaming or archiving the formerly scheduled page remains forbidden. Published content stays read-only in this workflow; unpublishing or removing indexed pages requires a separate reviewed workflow.

`publication_locked_at` is populated on first schedule/publish and backfilled for existing published/scheduled/previously published rows. Original imported paths remain immutable as before. No automatic redirects or legacy-content modifications occur.

## Readiness

Before publishing or scheduling, and again when a scheduled page is due, the application checks:

- The full canonical path remains valid and available in the current content/redirect catalog.
- Sections are non-empty and pass the existing typed plain-text/image/link validation.
- SEO title and meta description are present; canonicals, robots, indexing flags, schema type and sitemap preferences pass SEO validation.
- All content and social image references are ready, unarchived rasters with alt text.

Page and image rows are locked inside a transaction. The complete image ID set is locked in order before checking content/social groups. Publication does not rewrite source copy, SEO metadata, URLs or templates. Noindex publication is allowed when explicitly excluded from the sitemap. Rich legacy section schemas require their own importer/template support before this readiness check can publish them.

## Cron and failures

`php artisan trivo:publish-pages --limit=50` publishes a bounded batch of due schedules. Limits 1–100 are accepted. The existing minute scheduler invokes this command with an overlap lock; Redis and Supervisor are unnecessary. Configure the existing `schedule:run` cron in deployment. Actual hosting cron and supported MySQL/MariaDB acceptance remain pending.

Schedule inputs are explicit UTC `YYYY-MM-DDTHH:mm` values, in the future, stored at zero seconds. Due publication retains the intended timestamp, including when cron runs late. Future/null dates, archived rows and already published pages are skipped. The worker uses the shared content-path lock, rechecks each page under a row lock and stops starting new items after a 45-second soft budget.

Readiness failures leave the page scheduled and set only the fixed `validation_failed` code. The page details screen explains how to review/cancel/reschedule. The first failure increments the version and records a publication event; repeated unchanged failures do not create revision/log storms. Failed batches rotate using last-check update times, and unchecked due pages are prioritized so blocked schedules do not monopolize the batch. Repaired pages are checked on later runs. Unexpected infrastructure exceptions still fail the command rather than being reported as success.

Publication events have their own revision kind and recent-history list. They cannot be restored through content or SEO revision actions. Scheduled worker events are explicitly anonymous/system events; no administrator identity is inferred from an unrelated session.

## Verification and remaining scope

The feature adds 18 synthetic tests covering readiness, exact UTC schedules, cancellation/path retention, stale transitions, publisher permissions, CSRF/authentication, due-time image checks, once-only publication, bounded batches/locks, repeated failures, anonymous system events and retry fairness. The full suite passes 200 tests with 969 assertions. Pint, Larastan, the asset build and Composer audit pass.

`node tools/check_publication_ui.cjs` passes nine axe A/AA audits across draft/scheduled/published states in desktop light/dark and 390px mobile light. It checks missing-SEO errors, schedule/cancel/publish actions and content locks. Evidence is recorded under `docs/admin-preview/` using only the isolated QA database.

Cross-module redirect/content writers still need shared lock coordination before comprehensive concurrent URL parity can be claimed. Public response-cache invalidation, production cron timing and final source/template parity remain release integration work. All development publication fixtures are synthetic and isolated; no real pages have been imported or published.
