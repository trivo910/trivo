// Synthetic settings only; run against the isolated browser-QA database on loopback.
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
            const context = await browser.newContext({ viewport: { width: mobile ? 390 : 1440, height: mobile ? 844 : 1000 },
                colorScheme: dark ? 'dark' : 'light', isMobile: mobile, hasTouch: mobile });
            const page = await context.newPage();
            page.setDefaultTimeout(15000);
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            const external = [];
            // This module must never load analytics or contact the entered profiles.
            await context.route('**/*', async route => {
                if (new URL(route.request().url()).hostname !== '127.0.0.1') {
                    external.push(route.request().url());
                    return route.abort();
                }
                return route.continue();
            });
            await page.goto(`${base}/login`);
            await page.getByLabel('Email address').fill(credentials.email);
            await page.getByLabel('Password', { exact: true }).fill(credentials.password);
            await page.getByRole('button', { name: 'Continue', exact: true }).click();
            await page.waitForURL(`${base}/two-factor/challenge`);
            await page.getByLabel('Authenticator or recovery code').fill(credentials.codes[index]);
            await page.getByRole('button', { name: 'Verify and sign in' }).click();
            await page.waitForURL(base);
            for (const section of ['site', 'social', 'analytics']) {
                await page.goto(`${base}/settings/${section}`);
                await page.getByLabel('Theme', { exact: true }).selectOption(dark ? 'dark' : 'light');
                await page.getByRole('button', { name: 'Apply', exact: true }).click();
                await page.waitForURL(`${base}/settings/${section}`);
                await page.waitForFunction(theme => document.documentElement.dataset.theme === theme, dark ? 'dark' : 'light');
                if (section === 'site') {
                    await page.getByLabel('Site name', { exact: true }).fill('Synthetic website fixture');
                    await page.getByLabel('Contact email', { exact: true }).fill('hello@example.test');
                    await page.getByLabel('Postal address', { exact: true }).fill('Synthetic address\nSecond line');
                    await page.getByLabel('Footer text', { exact: true }).fill('Synthetic footer text');
                } else if (section === 'social') {
                    await page.getByLabel('LinkedIn profile URL', { exact: true }).fill('https://www.linkedin.com/company/synthetic-fixture');
                } else {
                    await page.getByLabel('Tracking configuration', { exact: true }).selectOption('1');
                    await page.getByLabel('GA4 measurement ID', { exact: true }).fill('INVALID');
                    await page.getByRole('button', { name: 'Save analytics settings', exact: true }).click();
                    await page.locator('#ga4_id[aria-invalid="true"]').waitFor();
                    await page.getByLabel('GA4 measurement ID', { exact: true }).fill('G-TEST123456');
                    await page.getByLabel('Google site verification token', { exact: true }).fill('synthetic_token');
                }
                await page.getByRole('button', { name: `Save ${section} settings`, exact: true }).click();
                await page.getByRole('status').filter({ hasText: `${section[0].toUpperCase() + section.slice(1)} settings saved.` }).waitFor();
                if (section === 'site') {
                    assert.equal(await page.getByLabel('Postal address', { exact: true }).inputValue(), 'Synthetic address\nSecond line');
                    assert.equal(await page.getByLabel('Footer text', { exact: true }).inputValue(), 'Synthetic footer text');
                }
                await page.evaluate(() => document.fonts.ready);
                assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${section}: horizontal overflow`);
                const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
                const violations = result.violations.map(rule => ({ id: rule.id, targets: rule.nodes.map(node => node.target) }));
                results.push({ page: `${section}-settings`, viewport: mode, violations });
                await page.screenshot({ path: path.join(target, `${section}-settings-${mode}.png`), fullPage: true });
                assert.equal(violations.length, 0, JSON.stringify(violations));
            }
            assert.deepEqual(errors, [], 'Browser JavaScript errors');
            assert.deepEqual(external, [], 'Unexpected external requests');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, 'website-settings-accessibility.json'), JSON.stringify(results, null, 2) + '\n');
        await browser.close();
    }
    console.log('Website settings browser checks passed: saves, validation, theme return paths, no external tracking and nine accessibility audits.');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
