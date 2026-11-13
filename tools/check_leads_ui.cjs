// Synthetic lead inbox workflows against isolated loopback QA data only.
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
            const name = `Synthetic lead ${mode}`;
            await page.goto(`${base}/leads`);
            await page.getByLabel('Search leads', { exact: true }).fill(name);
            await page.getByLabel('Lead status', { exact: true }).selectOption('new');
            await page.getByLabel('Service (exact)', { exact: true }).fill('Synthetic service');
            await page.getByRole('button', { name: 'Filter leads', exact: true }).click();
            await page.getByRole('link', { name, exact: true }).waitFor();
            await inspect('lead-list');
            await page.getByRole('link', { name, exact: true }).click();
            await page.getByLabel('Update status', { exact: true }).selectOption('qualified');
            await page.getByLabel('Internal notes', { exact: true }).fill('<script>unsafe()</script>');
            await page.getByRole('button', { name: 'Save lead', exact: true }).click();
            await page.getByText('Use plain text without HTML or control characters.', { exact: true }).last().waitFor();
            await inspect('lead-validation');
            const note = `Private synthetic note ${mode}`;
            await page.getByLabel('Internal notes', { exact: true }).fill(note);
            await page.getByRole('button', { name: 'Save lead', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Lead updated.' }).waitFor();
            assert.equal(await page.getByLabel('Update status', { exact: true }).inputValue(), 'qualified');
            assert.equal(await page.getByLabel('Internal notes', { exact: true }).inputValue(), note);
            await inspect('lead-details');
            await page.goto(`${base}/leads?q=${encodeURIComponent(name)}&status=qualified`);
            const downloadPromise = page.waitForEvent('download');
            await page.getByRole('link', { name: 'Export filtered CSV', exact: true }).click();
            const download = await downloadPromise;
            assert.equal(await download.failure(), null);
            const downloadPath = path.join(root, '.audit/browser-qa', `leads-${mode}.csv`);
            await download.saveAs(downloadPath);
            const csv = fs.readFileSync(downloadPath, 'utf8');
            assert.ok(csv.includes(name) && csv.includes('qualified'));
            assert.ok(!csv.includes(note) && !csv.includes('synthetic-tracking') && !csv.includes('ip_hash'));
            assert.deepEqual(errors, [], 'Browser errors or external requests');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, 'leads-accessibility.json'), JSON.stringify(results, null, 2) + '\n');
        await browser.close();
    }
    console.log('Lead browser checks passed: filtering, safe note validation, status/notes saves, filtered CSV export and nine accessibility audits.');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
