# Admin preview evidence

These screenshots use synthetic data in an isolated local-only SQLite database. No default account was created in the application database.

`login-*`, `dashboard-*` and `styleguide-*` show desktop light (1440), desktop dark (1440) and genuine mobile light (390) viewports. `accessibility.json` records axe WCAG A/AA results for those nine page/viewport combinations. Browser checks also exercise sign-in, single-use recovery codes, saved themes, dialog dismissal, reduced-transparency surfaces and mobile sidebar focus handling.

The initial Chrome CLI window captures were replaced because its narrow window cropped a wider layout. Use explicit viewport emulation for future mobile screenshots, including recapturing the older live-site mobile references before visual parity work.

Reproduce with ignored `playwright-core` and `@axe-core/playwright` packages under `.audit/browser-qa` and installed Chrome:

1. Run `php tools/setup_admin_browser_fixture.php` locally; it creates only `.audit/browser-qa/ui.sqlite` and ignored synthetic credentials.
2. Start PHP with **public/** as its working directory, port 18765, `DB_DATABASE` pointing at that absolute QA database path and `CACHE_STORE=array`. Use Laravel's development router from `vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`.
3. Run `node tools/check_admin_ui.cjs`.
4. Stop the owned preview server afterwards. The fixture is excluded from Git and must never be deployed.

Do not run the browser fixture against production, use real accounts in this flow, or publish its credentials. Regenerate the fixture before repeating the test because authentication codes are deliberately single-use.
