// Synthetic taxonomy editing against the isolated loopback QA database only.
const { chromium } = require('../.audit/browser-qa/node_modules/playwright-core');
const AxeBuilder = require('../.audit/browser-qa/node_modules/@axe-core/playwright').default;
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '..');
const credentials = JSON.parse(fs.readFileSync(path.join(root, '.audit/browser-qa/credentials.json'), 'utf8'));
const base = `http://127.0.0.1:18765/${credentials.admin_path}`;
const target = path.join(root, 'docs/admin-preview');
const kind = process.argv.includes('--tags') ? 'tags' : 'categories';
const prefix = `blog-${kind}`;
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
            const name = `Synthetic ${kind} ${mode}`;
            const exactPath = `/qa-${kind}-${mode}/`;
            await page.goto(`${base}/blog/${kind}/create`);
            await page.getByLabel('Term name', { exact: true }).fill(name);
            await page.getByLabel('Term slug', { exact: true }).fill(`qa-${kind}-${mode}`);
            await page.getByLabel('Full archive path', { exact: true }).fill(exactPath);
            await page.getByLabel('Term description', { exact: true }).fill('Synthetic taxonomy description.');
            assert.equal(await page.getByLabel('Parent category', { exact: true }).count(), kind === 'categories' ? 1 : 0);
            await inspect(`${prefix}-create`);
            await page.getByRole('button', { name: 'Save blog term', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Blog term created.' }).waitFor();
            const editUrl = page.url();
            await page.getByLabel('Term name', { exact: true }).fill('Changed synthetic term');
            await page.getByRole('button', { name: 'Save blog term', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Blog term saved.' }).waitFor();
            await page.getByLabel('Full archive path', { exact: true }).fill('/qa-renamed-path');
            await page.getByRole('button', { name: 'Save blog term', exact: true }).click();
            await page.getByText('Keep the saved term path. URL changes need a reviewed redirect workflow.', { exact: true }).last().waitFor();
            await page.getByLabel('Full archive path', { exact: true }).fill(exactPath);
            await page.getByRole('button', { name: 'Save blog term', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Blog term saved.' }).waitFor();
            await page.getByRole('button', { name: /^Restore term revision #/ }).last().click();
            await page.getByRole('status').filter({ hasText: 'Blog term revision restored.' }).waitFor();
            assert.equal(await page.getByLabel('Term name', { exact: true }).inputValue(), name);
            assert.equal(await page.getByLabel('Full archive path', { exact: true }).inputValue(), exactPath);
            await inspect(`${prefix}-edit`);
            await page.getByRole('button', { name: 'Archive blog term', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Blog term archived.' }).waitFor();
            await page.goto(editUrl);
            await page.getByRole('button', { name: 'Restore blog term', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Blog term restored.' }).waitFor();
            await page.goto(`${base}/blog/${kind}`);
            await page.getByLabel('Search terms', { exact: true }).fill(name);
            await page.getByRole('button', { name: 'Filter terms', exact: true }).click();
            await page.getByRole('link', { name, exact: true }).waitFor();
            await inspect(`${prefix}-list`);
            assert.deepEqual(errors, [], 'Browser errors or external requests');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, `${prefix}-accessibility.json`), JSON.stringify(results, null, 2) + '\n');
        await browser.close();
    }
    console.log(`Blog ${kind} browser checks passed: saves, fixed paths, revisions, archive/restore and nine accessibility audits.`);
})().catch(error => { console.error(error.message); process.exitCode = 1; });
