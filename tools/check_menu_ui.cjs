// Synthetic menu editing in the isolated loopback QA database only.
const { chromium } = require('../.audit/browser-qa/node_modules/playwright-core');
const AxeBuilder = require('../.audit/browser-qa/node_modules/@axe-core/playwright').default;
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '..');
const credentials = JSON.parse(fs.readFileSync(path.join(root, '.audit/browser-qa/credentials.json'), 'utf8'));
const base = `http://127.0.0.1:18765/${credentials.admin_path}`;
const target = path.join(root, 'docs/admin-preview');
const results = [];
(async () => {
    const browser = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
    try {
        for (const [index, mode] of ['desktop-light', 'mobile-light', 'desktop-dark'].entries()) {
            const mobile = mode.startsWith('mobile'), dark = mode.endsWith('dark');
            const context = await browser.newContext({ viewport: { width: mobile ? 390 : 1440, height: mobile ? 844 : 1000 }, colorScheme: dark ? 'dark' : 'light', isMobile: mobile, hasTouch: mobile });
            const page = await context.newPage();
            page.setDefaultTimeout(15000);
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.route('**/*', route => {
                const url = new URL(route.request().url());
                if (url.hostname !== '127.0.0.1') { errors.push(`External request: ${url.hostname}`); return route.abort(); }
                return route.continue();
            });
            async function inspect(label) {
                const violations = (await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze()).violations;
                results.push({ page: label, viewport: mode, violations });
                await page.screenshot({ path: path.join(target, `${label}-${mode}.png`), fullPage: true });
                assert.equal(violations.length, 0, JSON.stringify(violations));
                assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, 'Page overflows viewport');
            }
            await page.goto(`${base}/login`);
            await page.getByLabel('Email address').fill(credentials.email);
            await page.getByLabel('Password', { exact: true }).fill(credentials.password);
            await page.getByRole('button', { name: 'Continue', exact: true }).click();
            await page.waitForURL(`${base}/two-factor/challenge`);
            await page.getByLabel('Authenticator or recovery code').fill(credentials.codes[index]);
            await page.getByRole('button', { name: 'Verify and sign in' }).click();
            await page.waitForURL(base);
            await page.getByLabel('Theme', { exact: true }).selectOption(dark ? 'dark' : 'light');
            await page.getByRole('button', { name: 'Apply', exact: true }).click();
            await page.waitForURL(base);
            await page.goto(`${base}/menus/create`);
            await page.getByLabel('Menu name', { exact: true }).fill(`Synthetic menu ${mode}`);
            await page.getByLabel('Menu location', { exact: true }).fill(`qa-${mode}`);
            await inspect('menu-create');
            await page.getByRole('button', { name: 'Save menu', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu created.' }).waitFor();
            const menuUrl = page.url();
            await page.getByLabel('Link label', { exact: true }).fill('Synthetic parent');
            await page.getByLabel('Link URL', { exact: true }).fill('/synthetic-parent/');
            await page.getByRole('button', { name: 'Save menu item', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu item saved.' }).waitFor();
            const parentId = new URL(page.url()).searchParams.get('item');
            await page.getByRole('link', { name: 'Add another item', exact: true }).click();
            await page.getByLabel('Link label', { exact: true }).fill('Synthetic child');
            await page.getByLabel('Link URL', { exact: true }).fill('https://example.test/synthetic-child');
            await page.getByLabel('Parent item', { exact: true }).selectOption(parentId);
            await page.getByLabel('Display order', { exact: true }).fill('1');
            await page.getByLabel('Open link', { exact: true }).selectOption('_blank');
            await page.getByRole('button', { name: 'Save menu item', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu item saved.' }).waitFor();
            const childId = new URL(page.url()).searchParams.get('item');
            await page.getByRole('link', { name: `Edit menu item #${parentId}`, exact: true }).click();
            await page.getByLabel('Parent item', { exact: true }).selectOption(childId);
            await page.getByRole('button', { name: 'Save menu item', exact: true }).click();
            await page.getByText('Choose an active parent in this menu without cycles or more than five levels.', { exact: true }).last().waitFor();
            await page.getByLabel('Parent item', { exact: true }).selectOption('');
            await page.getByRole('button', { name: 'Save menu item', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu item saved.' }).waitFor();
            await page.getByRole('button', { name: `Archive item #${childId}`, exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu item archived.' }).waitFor();
            await page.getByRole('button', { name: `Restore item #${childId}`, exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu item restored.' }).waitFor();
            await inspect('menu-edit');
            await page.getByRole('button', { name: 'Archive menu', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu archived.' }).waitFor();
            await page.goto(menuUrl);
            await page.getByRole('button', { name: 'Restore menu', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Menu restored.' }).waitFor();
            await page.goto(`${base}/menus`);
            await page.getByLabel('Search menus', { exact: true }).fill(`Synthetic menu ${mode}`);
            await page.getByRole('button', { name: 'Filter menus', exact: true }).click();
            await page.getByRole('link', { name: `Synthetic menu ${mode}`, exact: true }).waitFor();
            await inspect('menu-list');
            assert.deepEqual(errors, [], 'Browser errors or external requests');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, 'menus-accessibility.json'), JSON.stringify(results, null, 2) + '\n');
        await browser.close();
    }
    console.log('Menu browser checks passed: native saves, hierarchy/cycle validation, item/menu archive/restore and nine accessibility audits.');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
