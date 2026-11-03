# Database analysis — Phase 0, preliminary

Source: `u802165322_KeH3A.sql`; dump header: 2026-11-02 10:23 (timezone unspecified).
Older alternate dump header: 2026-06-04 07:51 (timezone unspecified).
Prefix: `wp_`. Source parsed read-only. An allowlisted public subset was imported into isolated `wp_legacy`; see PHASE-0-AUDIT.md.
No users, credentials, sessions, form entries or unrestricted options are exported.

## Allowlisted site settings

```json
{
  "siteurl": "https://trivo.in",
  "home": "https://trivo.in",
  "blogname": "Trivo",
  "blogdescription": "Trivo",
  "posts_per_page": "10",
  "permalink_structure": "/%postname%",
  "category_base": "",
  "template": "numerique",
  "stylesheet": "numerique",
  "tag_base": "",
  "page_for_posts": "0",
  "page_on_front": "19"
}
```

## Content counts

| Type | Status | Count |
|---|---|---:|
| attachment | inherit | 921 |
| custom_css | publish | 1 |
| elementor_icons | draft | 1 |
| elementor_icons | publish | 1 |
| elementor_library | draft | 1 |
| elementor_library | publish | 49 |
| nav_menu_item | draft | 1 |
| nav_menu_item | publish | 69 |
| page | private | 8 |
| page | publish | 36 |
| post | auto-draft | 1 |
| post | publish | 49 |
| revision | inherit | 7645 |
| rm_content_editor | draft | 1 |
| spt_testimonial | publish | 1 |
| wp_navigation | publish | 1 |
| wpcf7_contact_form | publish | 7 |
| wpcode | draft | 2 |

## Active plugins

- `advanced-excerpt/advanced-excerpt.php`
- `classic-editor/classic-editor.php`
- `contact-form-7/wp-contact-form-7.php`
- `creame-whatsapp-me/joinchat.php`
- `disable-feeds-wp/disable-feeds-wp.php`
- `elementor-pro/elementor-pro.php`
- `elementor/elementor.php`
- `ga-google-analytics/ga-google-analytics.php`
- `health-check/health-check.php`
- `loginpress/loginpress.php`
- `premium-addons-for-elementor/premium-addons-for-elementor.php`
- `seo-by-rank-math/rank-math.php`
- `testimonial-free/testimonial-free.php`
- `updraftplus/updraftplus.php`
- `vamtam-elementor-integration-numerique/vamtam-elementor-integration.php`
- `vamtam-importers-e/vamtam-importers.php`
- `wp-mail-smtp/wp_mail_smtp.php`
- `wp-reviews-plugin-for-google/wp-reviews-plugin-for-google.php`

## Verification limits

- URL-MAP.csv contains database candidates, not verified live URLs. Blank HTTP/SEO fields are unknown.
- Titles are raw database titles, not resolved SEO titles. Live titles must still be captured.
- Author, attachment, custom-type, feed, pagination and media URL inventory remain pending.
- Live access initially returned HTTP 403; the identified audit client subsequently retrieved all entrypoints. See LIVE-PROBES.csv and CRAWL-SUMMARY.json for current evidence.
- PHP CLI is 8.2.4 and MariaDB is 10.4.28, below the specified PHP 8.3 and MariaDB 10.6 minimums.
- Laravel foundation checks now pass; see DEVELOPMENT-STATUS.md. Exhaustive Phase 0 coverage remains pending.
