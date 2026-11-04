// Synthetic draft workflows only, against the explicitly isolated loopback QA server.
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
            async function inspect(label) {
                await page.evaluate(() => { document.activeElement?.blur(); window.scrollTo({ top: 0, behavior: 'instant' }); });
                await page.evaluate(() => document.fonts.ready);
                assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${label}: horizontal overflow`);
                const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
                const violations = result.violations.map(rule => ({ id: rule.id, targets: rule.nodes.map(node => node.target) }));
                results.push({ page: label, viewport: mode, violations });
                await page.screenshot({ path: path.join(target, `${label}-${mode}.png`), fullPage: true });
                assert.equal(violations.length, 0, JSON.stringify(violations));
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
            await page.goto(`${base}/pages/create`);
            await page.getByLabel('Page title', { exact: true }).fill(`Synthetic page ${mode}`);
            await page.getByLabel('Full page path', { exact: true }).fill(`/qa-${mode}/`);
            await page.getByRole('button', { name: 'Add section', exact: true }).click();
            await page.getByLabel('Section type', { exact: true }).first().selectOption('heading');
            await page.getByLabel('Section text', { exact: true }).first().fill('Synthetic section heading');
            await page.getByRole('button', { name: 'Add section', exact: true }).click();
            await page.getByLabel('Section text', { exact: true }).nth(1).fill('Synthetic section paragraph');
            await page.getByRole('button', { name: 'Add section', exact: true }).click();
            await page.getByLabel('Section type', { exact: true }).nth(2).selectOption('link');
            await page.getByLabel('Link label', { exact: true }).nth(2).fill('Synthetic link');
            await page.getByLabel('Link URL', { exact: true }).nth(2).fill('/about');
            await page.getByRole('button', { name: 'Move section 3 up', exact: true }).click();
            await page.getByRole('button', { name: 'Remove section 3', exact: true }).click();
            await inspect('page-create');
            await page.getByRole('button', { name: 'Save page draft', exact: true }).click();
            await page.waitForURL(new RegExp(`/${credentials.admin_path}/pages/[0-9]+/edit$`));
            const editUrl = page.url();
            assert.equal(await page.getByLabel('Section type', { exact: true }).nth(1).inputValue(), 'link');
            await page.getByLabel('Section text', { exact: true }).first().fill('Changed synthetic heading');
            await page.getByRole('button', { name: 'Save page draft', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Page draft saved.' }).waitFor();
            await page.getByRole('button', { name: /^Restore revision #/ }).last().click();
            await page.getByRole('status').filter({ hasText: 'Revision content restored' }).waitFor();
            assert.equal(await page.getByLabel('Section text', { exact: true }).first().inputValue(), 'Synthetic section heading');
            await inspect('page-edit');
            if (index === 0) {
                const native = await browser.newContext({ storageState: await context.storageState(), javaScriptEnabled: false });
                const nativePage = await native.newPage();
                await nativePage.goto(editUrl);
                await nativePage.getByLabel('Page title', { exact: true }).fill(`Synthetic page ${mode}`);
                await nativePage.getByRole('button', { name: 'Save page draft', exact: true }).click();
                await nativePage.getByRole('status').filter({ hasText: 'Page draft saved.' }).waitFor();
                await native.close();
                await page.goto(editUrl);
                assert.equal(await page.getByLabel('Section type', { exact: true }).count(), 2);
            }
            await page.getByRole('button', { name: 'Archive page draft', exact: true }).click();
            await page.waitForURL(`${base}/pages`);
            await page.goto(editUrl);
            await page.getByRole('button', { name: 'Restore page draft', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Page draft restored.' }).waitFor();
            await page.goto(`${base}/pages`);
            await page.getByLabel('Search pages', { exact: true }).fill(`Synthetic page ${mode}`);
            await page.getByRole('button', { name: 'Filter pages', exact: true }).click();
            await page.getByRole('link', { name: `Synthetic page ${mode}`, exact: true }).waitFor();
            await inspect('page-list');
            assert.deepEqual(errors, [], 'Browser JavaScript errors');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, 'pages-accessibility.json'), JSON.stringify(results, null, 2) + '\n');
        await browser.close();
    }
    console.log('Page browser checks passed: draft sections/order, native fallback, revisions, archive/restore and nine accessibility audits.');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
