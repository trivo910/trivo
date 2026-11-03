# Admin preview evidence

These screenshots use synthetic data in an isolated local-only SQLite database. No default account was created in the application database.

`login-*`, `dashboard-*`, `styleguide-*`, `mail-settings-*`, `redirects-*`, `redirect-form-*` and `not-found-*` show desktop light (1440), desktop dark (1440) and genuine mobile light (390) viewports. `accessibility.json` records axe WCAG A/AA results for those 21 combinations. Browser checks also exercise sign-in, single-use recovery codes, saved themes, dialog dismissal, reduced-transparency surfaces, navigation, masked SMTP settings and redirect creation.

The initial Chrome CLI window captures were replaced because its narrow window cropped a wider layout. Use explicit viewport emulation for future mobile screenshots, including recapturing the older live-site mobile references before visual parity work.

`media-upload-*`, `media-details-*` and `media-library-*` have a separate nine-entry report in `media-accessibility.json`. These captures use a generated synthetic image, not a website asset. Run `node tools/check_media_ui.cjs` against the same isolated database with `MEDIA_PUBLIC_ROOT` and `MEDIA_PRIVATE_ROOT` pointing under `.audit/browser-qa/`. The image test invokes its own bounded queue worker; no SMTP sink is needed for this separate check.

Reproduce with ignored `playwright-core` and `@axe-core/playwright` packages under `.audit/browser-qa` and installed Chrome:

1. Run `php tools/setup_admin_browser_fixture.php` locally; it creates only `.audit/browser-qa/ui.sqlite` and ignored synthetic credentials.
2. Start PHP with **public/** as its working directory, port 18765, `DB_DATABASE` pointing at that absolute QA database path and `CACHE_STORE=array`. Use Laravel's development router from `vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`.
3. Start `python tools/local_smtp_sink.py` in another terminal. It accepts one synthetic message on loopback port 25266 and never relays it. The browser test uses only `example.test` addresses and local test credentials.
4. Run `node tools/check_admin_ui.cjs`.
5. Stop the owned preview server afterwards. The fixture and captured mail under `.audit/` are excluded from Git and must never be deployed.

Do not run the browser fixture against production, use real accounts in this flow, or publish its credentials. Regenerate the fixture before repeating the test because authentication codes are deliberately single-use.
