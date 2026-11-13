# Lead inbox

The hidden admin `/leads` inbox supports enquiry lists/details, search, filters, status/notes updates and filtered CSV export. It uses the existing glass components and native forms. Public form submission, queued enquiry notifications, outbound replies and legacy form-entry import remain pending; no real enquiries are imported during development.

## Access and preservation

Policies require `leads.view` for list/details and both `leads.view` and `leads.manage` for updates/exports. Existing Admin/Super Admin permissions provide access; Editor and SEO Manager roles remain denied. Every write uses Form Request validation, verified second-factor sessions and CSRF. No manual lead creation or deletion route is exposed.

Follow-up updates accept only status, plain internal notes and the current `content_version`. Status options are New, In progress, Contacted, Qualified, Closed and Spam. Notes support multiline text up to 10,000 characters, without HTML/control payloads. The server sets `handled_by` to the authenticated account. Enquiry name/email/phone/company/service/message/source/tracking/IP fields are preserved. A row lock and optimistic version check reject stale saves; rejected forms retain their old version until reloaded.

Activity records contain generic update/export event codes and actor/record references, without enquiry contact data, notes or search values. Admin responses use no-store/private caching and noindex headers. Detail fields are escaped; source URLs are displayed as text rather than fetched or activated. IP hashes and tracking metadata are not displayed.

## Filters and CSV

Search matches name, email, phone or company. Status and exact service filters combine with inclusive UTC creation dates. Lists paginate 20 rows in descending ID order. Date bounds use the beginning of the first day and exclusive beginning of the day after the last day.

CSV export reuses validated filters and returns at most 1,000 matching records, newest first. The response identifies the limit and uses an attachment filename derived only from UTC time. Rows stream through a bounded cursor; output has a UTF-8 BOM and CSV quoting for commas, quotes and multiline fields. Standard formula prefixes, leading controls and whitespace-prefixed formulas are neutralized by an apostrophe prefix, including numeric-looking phone strings starting with plus/minus.

Exported columns are ID, name, email, phone, company, service, message, status, source URL and creation/update timestamps. Internal notes, handler IDs, tracking metadata and IP hashes are excluded. CSV downloads stay in ignored local QA storage during validation. Export activity records a request, not proof that the browser retained the downloaded file.

## Verification and remaining integration

Synthetic tests cover escaping/privacy, combined filters and UTC bounds, invalid inputs, whitelisted updates, actor/version checks, no email side effects, CSV quoting/formula neutralization/field exclusions, the 1,000-row limit, permissions, authentication and CSRF.

For browser QA, run `php tools/setup_admin_browser_fixture.php --leads` to create only three synthetic enquiries in the isolated database, then `node tools/check_leads_ui.cjs`. Use the explicit SQLite and compiled-view settings in `docs/admin-preview/README.md`. Other fixture modes remain unchanged and create no lead records by default.

The full suite passes 390 tests with 2127 assertions; the 28 lead inbox tests contribute 131 assertions. Pint, Larastan level 6, the production asset build and configuration/route/view/event cache checks pass. Chrome list/validation/details checks pass nine axe audits across desktop light/dark and 390px mobile light, including filtered CSV downloads with private fields excluded. Evidence is in `docs/admin-preview/leads-accessibility.json` and `lead-*.png`. The application database remains without leads or default accounts. No dependency changed; current advisory status remains unverified after the previously recorded Packagist DNS failure.

Pending: public forms/spam protection, queued notifications, reply workflow and delivery history, retention/deletion policy, verified source integration, supported production database/hosting acceptance and final security acceptance. Legacy form submissions remain excluded unless explicitly authorized. Original media stays read-only and videos remain manual.
