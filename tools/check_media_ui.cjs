// Isolated browser QA; processes only the generated synthetic fixture in .audit.
const { chromium } = require('../.audit/browser-qa/node_modules/playwright-core');
const AxeBuilder = require('../.audit/browser-qa/node_modules/@axe-core/playwright').default;
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '..');
const fixture = path.join(root, '.audit/browser-qa');
const credentials = JSON.parse(fs.readFileSync(path.join(fixture, 'credentials.json'), 'utf8'));
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
            page.setDefaultNavigationTimeout(30000);
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            async function inspect(label) {
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
            await page.waitForFunction(theme => document.documentElement.dataset.theme === theme, dark ? 'dark' : 'light');
            await page.goto(`${base}/media/create`);
            await inspect('media-upload');
            if (index === 0) {
                await page.getByLabel('Image file', { exact: true }).setInputFiles(path.join(fixture, 'upload.png'));
                await page.getByLabel('Alt text', { exact: true }).fill('Synthetic blue and yellow image');
                await page.getByLabel('Image title (optional)', { exact: true }).fill('Browser image fixture');
                await page.getByLabel('Folder (optional)', { exact: true }).fill('QA Images');
                await page.getByRole('button', { name: 'Upload image', exact: true }).click();
                await page.waitForURL(new RegExp(`/${credentials.admin_path}/media/[0-9]+$`));
                execFileSync(path.join(root, '.audit/php83/php.exe'), ['artisan', 'queue:work', 'database', '--stop-when-empty', '--max-jobs=1', '--tries=1', '--timeout=60'], {
                    cwd: root, timeout: 90000, windowsHide: true, stdio: 'pipe',
                    env: { ...process.env, DB_CONNECTION: 'sqlite', DB_DATABASE: path.join(fixture, 'ui.sqlite'), CACHE_STORE: 'array',
                        MEDIA_PUBLIC_ROOT: path.join(fixture, 'media-public'), MEDIA_PRIVATE_ROOT: path.join(fixture, 'media-private') },
                });
                await page.locator('.media-detail-image').waitFor({ timeout: 30000 });
                await page.getByLabel('Image title', { exact: true }).fill('Updated browser image fixture');
                await page.getByRole('button', { name: 'Save image details', exact: true }).click();
            } else {
                await page.goto(`${base}/media`);
                await page.locator('.media-card-link').first().click();
            }
            await page.locator('.media-detail-image').waitFor();
            await page.waitForFunction(() => {
                const image = document.querySelector('.media-detail-image');
                return image && image.complete && image.naturalWidth > 0;
            });
            await inspect('media-details');
            await page.goto(`${base}/media`);
            await page.getByLabel('Folder', { exact: true }).selectOption('QA Images');
            await page.getByRole('button', { name: 'Filter images', exact: true }).click();
            await page.locator('.media-card-link').first().waitFor();
            await inspect('media-library');
            assert.deepEqual(errors, [], 'Browser JavaScript errors');
            await context.close();
        }
    } finally {
        fs.writeFileSync(path.join(target, 'media-accessibility.json'), JSON.stringify(results, null, 2));
        await browser.close();
    }
    console.log('Media browser checks passed: synthetic upload, queue conversion, preview, metadata, folders and nine accessibility audits.');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
