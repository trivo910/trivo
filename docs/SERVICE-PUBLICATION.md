# Service publication and scheduling

Services now support publish-now, future UTC scheduling, rescheduling and cancellation through the shared content publication workflow. Publication changes backend state only. Public service templates, URL resolution, SEO output and legacy import remain pending; this workflow does not deploy a frontend or create public routes.

## Permissions, readiness and path retention

`services.publish`, ServicePolicy, verified second-factor sessions, Form Requests and CSRF protect transitions. Administrators and Editors can publish; SEO Managers retain metadata access without publication permission. The service is locked and its current `content_version` checked before every transition.

Publishing or scheduling requires non-empty validated sections, an SEO title and description, a conflict-free exact path, valid service-context schema/robots/canonical/sitemap settings and consistent indexing flags. Content, featured and social image references must be ready, unarchived raster images with alt text. All referenced image rows are locked in order before readiness checks. Source body/details, metadata, display order and paths are retained rather than rewritten.

First scheduling/publication permanently reserves the path with `publication_locked_at`. Cancelling clears the future publication timestamp and error and returns to draft editing, but does not permit renaming or archiving. Published/previously published service content remains read-only in this workflow. Existing published/scheduled services receive a path lock during migration; unknown historical publication dates remain unset rather than invented. Imported paths retain their existing protection.

## Shared execution and isolation

`ContentPublication` provides readiness and event recording for pages/services. `ChangeContentPublication` handles validated transitions; typed page/service actions preserve the existing entrypoints. `PublishDueContent` selects only an explicit allowlisted model kind, and the existing page command retains its behavior. Matching numeric page/service IDs do not share publication state or revisions.

Run `php artisan trivo:publish-services --limit=50` for due services; limits 1–100 are accepted. The existing minute scheduler invokes it with an overlap lock. Pages, services and other content edits share the content-path cache lock. The worker checks due state again under a row lock, validates readiness, processes a bounded batch and stops starting new items after a 45-second soft budget. It publishes once and retains the intended UTC timestamp even after delayed cron execution.

Readiness failures retain the schedule with the fixed `validation_failed` code. Only the first unchanged failure increments the version/records an event. Repeated failures rotate through bounded batches, and repaired records are rechecked later. Unexpected infrastructure errors fail the command. Scheduled events are explicitly system/anonymous events. Publication revisions appear in their own recent-history list and cannot be restored through content or SEO actions.

The shared admin publication panel displays state, UTC schedule, readiness errors and recent events. It supports native form actions without JavaScript. It does not expose unpublishing or deletion of indexed services.

## Remaining deployment and public integration

The existing `schedule:run` cron drives queue work plus page/service publishing without Redis or Supervisor. Real hosting/database/cron checks remain pending, including total sequential runtime of scheduled commands under shared-hosting limits. Cross-module path-writer coordination, public response-cache invalidation, canonical resolution, verified templates and final URL/SEO parity still require integration. All validation fixtures use isolated synthetic data; no real services or source images are imported or published.
