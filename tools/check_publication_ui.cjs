// Synthetic publication states only; public routing/deployment remains out of scope.
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
const isService = process.argv.includes('--service');
const isPortfolio = process.argv.includes('--portfolio');
const subject = isPortfolio ? 'portfolio' : isService ? 'service' : 'page';
const subjectTitle = isPortfolio ? 'Portfolio' : isService ? 'Service' : 'Page';
const modulePath = isPortfolio ? 'portfolio' : isService ? 'services' : 'pages';
const evidencePrefix = isPortfolio ? 'portfolio-publication' : isService ? 'service-publication' : 'publication';
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
            async function inspect(label) {
                await page.evaluate(() => { document.activeElement?.blur(); window.scrollTo({ top: 0, behavior: 'instant' }); });
                await page.evaluate(() => document.fonts.ready);
                assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${label}: overflow`);
                const audit = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
                const violations = audit.violations.map(rule => ({ id: rule.id, targets: rule.nodes.map(node => node.target) }));
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
            const dashboard = await page.goto(base);
            assert.equal(dashboard.status(), 200, 'Dashboard did not load after verified sign-in');
            await page.getByLabel('Theme', { exact: true }).selectOption(dark ? 'dark' : 'light');
            await page.getByRole('button', { name: 'Apply', exact: true }).click();
            await page.waitForURL(base);
            await page.goto(`${base}/${modulePath}/create`);
            await page.getByLabel(`${subjectTitle} title`, { exact: true }).fill(`Synthetic ${subject} publication ${mode}`);
            await page.getByLabel(`Full ${subject} path`, { exact: true }).fill(`/qa-${subject}-publication-${mode}`);
            await page.getByRole('button', { name: 'Add section', exact: true }).click();
            await page.getByLabel('Section text', { exact: true }).first().fill('Synthetic publication content.');
            await page.getByRole('button', { name: `Save ${subject} draft`, exact: true }).click();
            await page.getByRole('button', { name: 'Publish now', exact: true }).click();
            await page.getByRole('alert').filter({ hasText: 'SEO title and a meta description' }).waitFor();
            await page.getByRole('link', { name: `Edit ${subject} SEO`, exact: true }).click();
            await page.getByLabel('SEO title', { exact: true }).fill('Synthetic publication SEO');
            await page.getByLabel('Meta description', { exact: true }).fill('Synthetic description for a publication fixture.');
            await page.getByLabel(`${subjectTitle} schema type`, { exact: true }).selectOption(isPortfolio ? 'CreativeWork' : isService ? 'Service' : 'WebPage');
            await page.getByRole('button', { name: `Save ${subject} SEO`, exact: true }).click();
            await page.getByRole('status').filter({ hasText: `${subjectTitle} SEO saved.` }).waitFor();
            await page.getByRole('link', { name: `Back to ${subject}`, exact: true }).click();
            await inspect(`${evidencePrefix}-draft`);
            await page.getByLabel('Scheduled date and time (UTC)', { exact: true }).fill(new Date(Date.now() + 86400000).toISOString().slice(0, 16));
            await page.getByRole('button', { name: `Schedule ${subject}`, exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Publication state updated.' }).waitFor();
            await page.getByRole('button', { name: 'Cancel schedule', exact: true }).waitFor();
            assert.equal(await page.getByRole('button', { name: `Save ${subject} draft`, exact: true }).count(), 0);
            await inspect(`${evidencePrefix}-scheduled`);
            await page.getByRole('button', { name: 'Cancel schedule', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Publication state updated.' }).waitFor();
            await page.getByRole('button', { name: `Save ${subject} draft`, exact: true }).waitFor();
            await page.getByRole('button', { name: 'Publish now', exact: true }).click();
            await page.getByRole('status').filter({ hasText: 'Publication state updated.' }).waitFor();
            assert.equal(await page.getByRole('button', { name: 'Publish now', exact: true }).count(), 0);
            assert.equal(await page.getByRole('button', { name: `Save ${subject} draft`, exact: true }).count(), 0);
            await inspect(`${evidencePrefix}-published`);
            assert.deepEqual(errors, [], 'Browser JavaScript errors');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, `${evidencePrefix}-accessibility.json`), JSON.stringify(results, null, 2) + '\n');
        await browser.close();
    }
    console.log(`${subjectTitle} publication browser checks passed: readiness failures, publish/schedule/cancel, locked content and nine accessibility audits.`);
})().catch(error => { console.error(error.message); process.exitCode = 1; });
