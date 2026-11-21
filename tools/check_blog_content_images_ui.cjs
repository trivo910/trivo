const { chromium } = require('../.audit/browser-qa/node_modules/playwright-core');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');

const directory = path.resolve(__dirname, '../.audit/blog-qa');
const credentials = JSON.parse(fs.readFileSync(directory + '/credentials.json', 'utf8'));
assert.equal(credentials.email, 'blog-browser@example.test');
const base = 'http://127.0.0.1:18767';
const admin = base + '/' + credentials.admin_path;

(async () => {
    const browser = await chromium.launch({
        executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true,
    });
    const results = [];
    try {
        for (const width of [1440, 390]) {
            const context = await browser.newContext({ viewport: { width, height: 1000 } });
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.goto(admin + '/login');
            await page.getByLabel('Email address').fill(credentials.email);
            await page.getByLabel('Password', { exact: true }).fill(credentials.password);
            await page.getByRole('button', { name: 'Sign in', exact: true }).click();
            await page.waitForURL(admin);
            await page.goto(admin + '/posts/create');
            const slug = 'content-images-' + width + '-' + Date.now();
            await page.getByLabel('Post title', { exact: true }).fill('Content images browser check');
            await page.getByLabel('Post slug', { exact: true }).fill(slug);
            await page.getByLabel('Full post path', { exact: true }).fill('/blog/' + slug);
            await page.getByLabel('SEO title', { exact: true }).fill('Content images browser check');
            await page.getByLabel('Meta description', { exact: true }).fill('Image insertion regression check.');
            const editor = page.getByRole('textbox', { name: 'Post body', exact: true });
            const images = page.locator('.blog-editor__visual img');
            const library = JSON.parse(await page.locator('.blog-editor').getAttribute('data-images'))
                .filter(image => image.url.includes('/wp-content/'));
            assert.ok(library.length >= 2, 'Two imported library images are required.');
            const [first, second] = library;
            await editor.fill('Original article text.');
            await page.getByLabel('Library image', { exact: true }).selectOption(String(first.id));
            await page.getByRole('button', { name: 'Insert image', exact: true }).click();
            assert.equal(await images.count(), 1);

            // Native editor changes must invalidate the reference to the removed image.
            await editor.fill('Article text after deleting the selected image.');
            await page.getByLabel('Library image', { exact: true }).selectOption(String(second.id));
            await page.getByRole('button', { name: 'Insert image', exact: true }).click();
            assert.equal(await images.count(), 1, 'Insertion after native deletion');
            assert.equal(await images.first().getAttribute('src'), second.url);

            await page.getByLabel('Library image', { exact: true }).selectOption(String(first.id));
            await page.getByRole('button', { name: 'Update selected image', exact: true }).click();
            assert.equal(await images.count(), 1, 'Replacement keeps one image');
            assert.equal(await images.first().getAttribute('src'), first.url);

            await page.getByLabel('Library image', { exact: true }).selectOption(String(second.id));
            await page.getByRole('button', { name: 'Insert another image', exact: true }).click();
            assert.equal(await images.count(), 2, 'Insert another preserves the first image');
            await page.getByRole('button', { name: 'Save post draft', exact: true }).click();
            await page.waitForURL(/\/posts\/\d+\/edit$/);
            assert.deepEqual(await images.evaluateAll(items => items.map(image => image.src)), [first.url, second.url]);
            await page.reload();
            assert.deepEqual(await images.evaluateAll(items => items.map(image => image.src)), [first.url, second.url]);
            await images.evaluateAll(items => Promise.all(items.map(image => image.decode())));
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);

            await page.getByRole('button', { name: 'Publish now', exact: true }).click();
            await page.getByRole('link', { name: 'View live article', exact: true }).waitFor();
            await page.goto(base + '/blog/' + slug);
            assert.deepEqual(await page.locator('.blog-post__body img').evaluateAll(items => items.map(image => image.src)), [first.url, second.url]);
            assert.deepEqual(errors, []);
            results.push({ width, insertAfterDeletion: true, replace: true, insertAnother: true,
                savedAndReloaded: true, publishedImages: true, javascriptErrors: errors });
            fs.writeFileSync(directory + '/content-images-results.json', JSON.stringify(results, null, 2));
            console.log('Content images ' + width + ': passed');
            await context.close();
        }
    } finally {
        if (results.length !== 2) {
            for (const context of browser.contexts()) for (const page of context.pages()) {
                console.log('Last page: ' + page.url());
                console.log(await page.locator('.field-error,[role=alert]').allTextContents());
                await page.screenshot({ path: directory + '/content-images-failure.png', fullPage: true }).catch(() => {});
            }
        }
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
