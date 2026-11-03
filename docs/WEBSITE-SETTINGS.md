# Website settings

Three admin screens manage site identity/contact information, social profile URLs and analytics/verification identifiers. This is backend configuration only: public layout, SEO rendering, tracking scripts and source-data import are still pending. No source values or tracking IDs are invented or automatically activated.

## Access and storage

Site and social settings require `settings.site` (Super Admin/Admin by the current roles). Analytics settings require `settings.seo`, also available to SEO Managers. Editors have no access. Authentication, mandatory second factor, session checks, SettingPolicy, Form Requests and CSRF protect the routes and actions.

Values are stored as separate encrypted `website.site`, `website.social` and `website.analytics` records. Only explicitly allowed fields for the selected section are saved. Unknown fields cannot overwrite SMTP, credentials or another settings group. Activity events identify the section without recording contact values or tokens. No new dependencies, seeded settings or default accounts were added.

`WebsiteSettings::read($section)` returns only that section's declared fields. It performs a fresh database read; no decrypted settings are written into configuration caches. Once public response caching is implemented, its invalidation must cover these setting changes.

## Fields and validation

- Site: site name (required on save), tagline, organisation name, contact email, phone, international WhatsApp number, postal address, footer text, logo and site icon.
- Social: Facebook, Instagram, LinkedIn, YouTube, X/Twitter and Pinterest HTTPS profile URLs. URLs are not fetched; credentials, spaces, control characters and executable schemes are rejected.
- Analytics: enabled flag (default disabled), GA4 measurement ID, GTM container ID, Meta Pixel ID and Google/Bing verification tokens. Enabling requires at least one valid tracker ID. Supply IDs/tokens rather than HTML snippets. Existing GA4/GTM IDs must be preserved during import.

All text accepts plain text only; output is escaped even for values inserted outside the forms. Addresses and footer text support newlines. Each form saves its complete section: blank or omitted optional fields clear their prior values. Other sections are retained.

Brand images must be ready, unarchived JPEG/PNG/WebP/AVIF entries with alt text. Site icons must be square and at least 48 pixels wide. Save rechecks these constraints while locking the selected media rows, sharing the archive action's lock protocol. Selected images cannot be archived until their references are removed. Legacy SVG handling and a complete favicon asset set remain part of import/public integration.

## Verification and remaining scope

The settings feature has 38 passing synthetic tests covering encrypted storage, group isolation, role boundaries, authentication/2FA/CSRF, malformed inputs, escaping, safe URLs, tracker validation, media state/usage protection, clearing and validated theme return paths.

`node tools/check_website_settings_ui.cjs` exercises saves and validation in desktop light/dark and 390px mobile light. All nine axe A/AA audits pass with no horizontal overflow, JavaScript errors or external requests. Fixtures use only `.audit/browser-qa/ui.sqlite`; the application database and original uploads remain untouched.

Pending: import verified business/social/analytics values, connect public layout and SEO components, decide tracking/consent and CSP behavior against the captured live site, avoid duplicate GA4 installation through GTM, add SEO defaults/schema/robots/sitemap settings and the Super Admin-only CSP-aware custom-script feature. Menus/footer structure and backups documentation belong to their content/release modules.
