# Admin preview evidence

`portfolio-publication-draft-*`, `portfolio-publication-scheduled-*` and `portfolio-publication-published-*` are recorded in `portfolio-publication-accessibility.json`. Regenerate the isolated fixture and run `node tools/check_publication_ui.cjs --portfolio` for nine audits. Regenerate again before the `--service` regression. Both workflows passed with no recorded axe violations; they change only synthetic backend publication state.

`portfolio-seo-*`, `portfolio-seo-narrow-preview-*` and `portfolio-seo-validation-*` are recorded in `portfolio-seo-accessibility.json`. Run `node tools/check_page_seo_ui.cjs --portfolio` with a fresh isolated fixture for nine SEO audits. Regenerate the fixture before running `--service` to verify service metadata behavior after shared-action changes.

`portfolio-create-*`, `portfolio-edit-*` and `portfolio-list-*` are recorded in `portfolio-accessibility.json`. Run `node tools/check_service_ui.cjs --portfolio` after regenerating the isolated fixture to verify draft saves, section/display order, revision restoration and archive/restore. The default command remains the service regression workflow; regenerate credentials between runs.

`service-publication-draft-*`, `service-publication-scheduled-*` and `service-publication-published-*` are recorded in `service-publication-accessibility.json`. Regenerate the isolated fixture and run `node tools/check_publication_ui.cjs --service` for nine service audits; regenerate again and run the default command to verify the page workflow after shared publisher/panel changes. These fixtures change only isolated backend publication state.

`service-seo-*`, `service-seo-narrow-preview-*` and `service-seo-validation-*` are recorded in `service-seo-accessibility.json`. Regenerate the fixture and run `node tools/check_page_seo_ui.cjs --service` for the service variant of the shared SEO workflow. The default command reruns page SEO; each variant produces nine audits across desktop light/dark and mobile light.

`service-create-*`, `service-edit-*` and `service-list-*` are recorded in `services-accessibility.json`. Regenerate the isolated fixture and run `node tools/check_service_ui.cjs` to exercise section/display order, revisions and archive/restore in all three layouts. No real service content is imported or published.

`publication-draft-*`, `publication-scheduled-*` and `publication-published-*` are recorded in `publication-accessibility.json`. Regenerate the isolated fixture and run `node tools/check_publication_ui.cjs` to check readiness errors, scheduling, cancellation, publication and content locks in three layouts. Publication states are synthetic database fixtures; no source content is imported or deployed.

`page-seo-*`, `page-seo-narrow-preview-*` and `page-seo-validation-*` are recorded in `page-seo-accessibility.json`. Regenerate the isolated fixture and run `node tools/check_page_seo_ui.cjs` for nine audits covering metadata saves/restoration, live fallback and robots validation. This tool neither publishes content nor imports source metadata/images.

`page-list-*`, `page-create-*` and `page-edit-*` are recorded in `pages-accessibility.json`. Regenerate the isolated fixture and run `node tools/check_pages_ui.cjs`. It creates only synthetic drafts, exercises ordering/removal, revision restoration, archive/restore and native saving without JavaScript, then checks all three layouts. It does not publish or import content.

`site-settings-*`, `social-settings-*` and `analytics-settings-*` have a separate nine-entry report in `website-settings-accessibility.json`. Run `node tools/check_website_settings_ui.cjs` against the isolated browser fixture after regenerating its one-use authentication codes. This checks native form saves, analytics validation and theme return paths in all three layouts. It neither needs SMTP nor processes images; external requests are blocked and asserted absent.

These screenshots use synthetic data in an isolated local-only SQLite database. No default account was created in the application database.

`login-*`, `dashboard-*`, `styleguide-*`, `mail-settings-*`, `redirects-*`, `redirect-form-*` and `not-found-*` show desktop light (1440), desktop dark (1440) and genuine mobile light (390) viewports. `accessibility.json` records axe WCAG A/AA results for those 21 combinations. Browser checks also exercise sign-in, single-use recovery codes, saved themes, dialog dismissal, reduced-transparency surfaces, navigation, masked SMTP settings and redirect creation.

The initial Chrome CLI window captures were replaced because its narrow window cropped a wider layout. Use explicit viewport emulation for future mobile screenshots, including recapturing the older live-site mobile references before visual parity work.

`media-upload-*`, `media-details-*` and `media-library-*` have a separate nine-entry report in `media-accessibility.json`. These captures use a generated synthetic image, not a website asset. Run `node tools/check_media_ui.cjs` against the same isolated database with `MEDIA_PUBLIC_ROOT` and `MEDIA_PRIVATE_ROOT` pointing under `.audit/browser-qa/`. The image test invokes its own bounded queue worker; no SMTP sink is needed for this separate check.

Reproduce with ignored `playwright-core` and `@axe-core/playwright` packages under `.audit/browser-qa` and installed Chrome:

1. Run `php tools/setup_admin_browser_fixture.php` locally; it creates only `.audit/browser-qa/ui.sqlite` and ignored synthetic credentials.
2. Start PHP with **public/** as its working directory, port 18765, `DB_CONNECTION=sqlite`, `DB_DATABASE` pointing at that absolute QA database path, `CACHE_STORE=array`, and `VIEW_COMPILED_PATH` pointing at `.audit/browser-qa/views` as an absolute path. The fixture creates this separate view directory to avoid Windows compiled-view rename locks during parallel application tests. The explicit connection override is required when the application env uses MySQL. Use Laravel's development router from `vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`.
3. Start `python tools/local_smtp_sink.py` in another terminal. It accepts one synthetic message on loopback port 25266 and never relays it. The browser test uses only `example.test` addresses and local test credentials.
4. Run `node tools/check_admin_ui.cjs`.
5. Stop the owned preview server afterwards. The fixture and captured mail under `.audit/` are excluded from Git and must never be deployed.

Do not run the browser fixture against production, use real accounts in this flow, or publish its credentials. Regenerate the fixture before repeating the test because authentication codes are deliberately single-use.
