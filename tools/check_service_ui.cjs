// Synthetic service draft workflows in the isolated loopback QA database only.
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
const contexts = {
    '--portfolio': ['portfolio', 'Portfolio', 'portfolio', false],
    '--faq': ['faq', 'FAQ', 'faqs', true],
    '--testimonial': ['testimonial', 'Testimonial', 'testimonials', true],
    '--client': ['client', 'Client', 'clients', true],
};
const selected = Object.keys(contexts).find(flag => process.argv.includes(flag));
const [subject, subjectTitle, modulePath, optionalPath] = selected ? contexts[selected] : ['service', 'Service', 'services', false];
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
            await page.getByLabel('Theme', { exact: true }).selectOption(dark ? 'dark' : 'light');
            await page.getByRole('button', { name: 'Apply', exact: true }).click();
            await page.waitForURL(base);
            await page.goto(`${base}/${modulePath}/create`);
            const titleLabel = subject === 'faq' ? 'FAQ question' : (subject === 'client' ? 'Client name' : `${subjectTitle} title`);
            const pathLabel = `Full ${subject} path${optionalPath ? ' (optional)' : ''}`;
            await page.getByLabel(titleLabel, { exact: true }).fill(`Synthetic ${subject} ${mode}`);
            await page.getByLabel(pathLabel, { exact: true }).fill(optionalPath && mobile ? '' : `/qa-${subject}-${mode}/`);
            await page.getByLabel('Display order', { exact: true }).fill('5');
            await page.getByRole('button', { name: 'Add section', exact: true }).click();
            await page.getByLabel('Section text', { exact: true }).first().fill('First synthetic service section.');
            await page.getByRole('button', { name: 'Add section', exact: true }).click();
            await page.getByLabel('Section text', { exact: true }).nth(1).fill('Second synthetic service section.');
            await page.getByRole('button', { name: 'Move section 2 up', exact: true }).click();
            await inspect(`${subject}-create`);
            await page.getByRole('button', { name: `Save ${subject} draft`, exact: true }).click();
            await page.waitForURL(new RegExp(`/${credentials.admin_path}/${modulePath}/[0-9]+/edit$`));
            assert.equal(await page.getByLabel('Section text', { exact: true }).first().inputValue(), 'Second synthetic service section.');
            await page.getByLabel(titleLabel, { exact: true }).fill(`Changed synthetic ${subject}`);
            await page.getByLabel('Display order', { exact: true }).fill('0');
            await page.getByRole('button', { name: `Save ${subject} draft`, exact: true }).click();
            await page.getByRole('status').filter({ hasText: `${subjectTitle} draft saved.` }).waitFor();
            await page.getByRole('button', { name: new RegExp(`^Restore ${subject} revision #`) }).last().click();
            await page.getByRole('status').filter({ hasText: `${subjectTitle} revision restored` }).waitFor();
            assert.equal(await page.getByLabel('Display order', { exact: true }).inputValue(), '5');
            assert.equal(await page.getByLabel(titleLabel, { exact: true }).inputValue(), `Synthetic ${subject} ${mode}`);
            if (optionalPath && mobile) assert.equal(await page.getByLabel(pathLabel, { exact: true }).inputValue(), '');
            await inspect(`${subject}-edit`);
            const editUrl = page.url();
            await page.getByRole('button', { name: `Archive ${subject} draft`, exact: true }).click();
            await page.waitForURL(`${base}/${modulePath}`);
            await page.goto(editUrl);
            await page.getByRole('button', { name: `Restore ${subject} draft`, exact: true }).click();
            await page.getByRole('status').filter({ hasText: `${subjectTitle} draft restored.` }).waitFor();
            await page.goto(`${base}/${modulePath}`);
            await page.getByLabel(`Search ${modulePath}`, { exact: true }).fill(`Synthetic ${subject} ${mode}`);
            await page.getByRole('button', { name: `Filter ${modulePath}`, exact: true }).click();
            await page.getByRole('link', { name: `Synthetic ${subject} ${mode}`, exact: true }).waitFor();
            await inspect(`${subject}-list`);
            assert.deepEqual(errors, [], 'Browser JavaScript errors');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, `${modulePath}-accessibility.json`), JSON.stringify(results, null, 2) + '\n');
        await browser.close();
    }
    console.log(`${subjectTitle} browser checks passed: drafts, section order, display order, revisions, archive/restore and nine accessibility audits.`);
})().catch(error => { console.error(error.message); process.exitCode = 1; });
