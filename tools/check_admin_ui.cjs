// Uses existing Chrome. Dependencies live only under ignored .audit/browser-qa.
const { chromium } = require('../.audit/browser-qa/node_modules/playwright-core');
const AxeBuilder = require('../.audit/browser-qa/node_modules/@axe-core/playwright').default;
const { createHmac } = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '..');
const credentials = JSON.parse(fs.readFileSync(path.join(root, '.audit/browser-qa/credentials.json'), 'utf8'));
const origin = 'http://127.0.0.1:18765';
const base = `${origin}/${credentials.admin_path}`;
const target = path.join(root, 'docs/admin-preview');
fs.mkdirSync(target, { recursive: true });

function otp(secret) {
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    const bits = [...secret].map(char => alphabet.indexOf(char).toString(2).padStart(5, '0')).join('');
    const key = Buffer.from((bits.match(/.{8}/g) || []).map(value => parseInt(value, 2)));
    const counter = Buffer.alloc(8); counter.writeBigUInt64BE(BigInt(Math.floor(Date.now() / 30000)));
    const hash = createHmac('sha1', key).update(counter).digest();
    const offset = hash[19] & 15;
    return ((hash.readUInt32BE(offset) & 0x7fffffff) % 1000000).toString().padStart(6, '0');
}

(async () => {
    const browser = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
    const results = [];
    try {
        let index = 0;
        for (const mode of ['desktop-light', 'mobile-light', 'desktop-dark']) {
            const mobile = mode.startsWith('mobile'), dark = mode.endsWith('dark');
            const context = await browser.newContext({ viewport: { width: mobile ? 390 : 1440, height: mobile ? 844 : 1000 },
                colorScheme: dark ? 'dark' : 'light', isMobile: mobile, hasTouch: mobile, deviceScaleFactor: 1 });
            const page = await context.newPage();
            page.setDefaultTimeout(15000);
            page.setDefaultNavigationTimeout(30000);
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            async function inspect(label) {
                await page.evaluate(() => document.fonts.ready);
                assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${label}: horizontal overflow`);
                const audit = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
                const violations = audit.violations.map(rule => ({ id: rule.id, impact: rule.impact, targets: rule.nodes.map(node => node.target) }));
                results.push({ page: label, viewport: mode, violations });
                await page.screenshot({ path: path.join(target, `${label}-${mode}.png`), fullPage: true });
                assert.equal(violations.length, 0, `${label}: axe violations ${JSON.stringify(violations)}`);
            }
            await page.goto(`${base}/login`);
            await page.getByRole('heading', { name: 'Sign in', exact: true }).waitFor();
            await inspect('login');
            await page.getByLabel('Email address').fill(credentials.email);
            await page.getByLabel('Password', { exact: true }).fill(credentials.password);
            await page.getByRole('button', { name: 'Continue', exact: true }).click();
            await page.waitForURL(`${base}/two-factor/challenge`);
            await page.getByLabel('Authenticator or recovery code').fill(index === 0 ? otp(credentials.secret) : credentials.codes[index - 1]);
            await page.getByRole('button', { name: 'Verify and sign in' }).click();
            await page.waitForURL(base);
            await page.getByLabel('Theme', { exact: true }).selectOption(dark ? 'dark' : 'light');
            await page.getByRole('button', { name: 'Apply', exact: true }).click();
            await page.waitForURL(base);
            await page.waitForFunction(theme => document.documentElement.dataset.theme === theme, dark ? 'dark' : 'light');
            await inspect('dashboard');
            if (mobile) {
                const toggle = page.getByRole('button', { name: 'Toggle navigation' });
                assert.equal(await page.locator('#admin-sidebar').evaluate(element => element.inert), true);
                await toggle.click();
                await page.waitForFunction(() => !document.querySelector('#admin-sidebar').inert);
                await page.keyboard.press('Escape');
                await page.waitForFunction(() => document.querySelector('#admin-sidebar').inert);
            }
            await page.goto(`${base}/_styleguide`);
            await page.getByRole('heading', { name: 'Shared admin components' }).waitFor();
            await inspect('styleguide');
            await page.getByRole('button', { name: 'Open example' }).click();
            await page.getByRole('dialog').waitFor();
            await page.keyboard.press('Escape');
            assert.equal(await page.locator('dialog').evaluate(element => element.open), false);
            await page.goto(`${base}/settings/mail`);
            await page.getByRole('heading', { name: 'SMTP delivery' }).waitFor();
            if (index === 0) {
                await page.getByLabel('SMTP host', { exact: true }).fill('127.0.0.1');
                await page.getByLabel('Port', { exact: true }).fill('25266');
                await page.getByLabel('Encryption', { exact: true }).selectOption('none');
                await page.getByLabel('SMTP username', { exact: true }).fill('smtp-fixture');
                await page.getByLabel('SMTP password', { exact: true }).fill('Smtp-Fixture-Password-123!');
                await page.getByLabel('From email', { exact: true }).fill('sender@example.test');
                await page.getByLabel('From name', { exact: true }).fill('Trivo');
                await page.getByLabel('Test email delivery', { exact: true }).selectOption('0');
                await page.getByRole('button', { name: 'Save SMTP settings', exact: true }).click();
                await page.getByRole('status').filter({ hasText: 'SMTP settings saved.' }).waitFor();
                assert.equal(await page.getByLabel('SMTP password', { exact: true }).inputValue(), '');
                await page.getByRole('button', { name: 'Send test email', exact: true }).click();
                await page.getByRole('cell', { name: /^SMTP accepted at/ }).waitFor();
            }
            await inspect('mail-settings');
            await page.goto(`${base}/redirects`);
            await page.getByRole('heading', { name: 'URL redirects' }).waitFor();
            if (index === 0) {
                await page.getByRole('link', { name: 'Add redirect', exact: true }).click();
                await page.getByLabel('Old path', { exact: true }).fill('/browser-old');
                await page.getByLabel('Destination', { exact: true }).fill('/browser-current');
                await page.getByRole('button', { name: 'Save redirect', exact: true }).click();
                await page.waitForURL(`${base}/redirects`);
            }
            await inspect('redirects');
            await page.getByRole('link', { name: 'Edit', exact: true }).first().click();
            await inspect('redirect-form');
            await page.goto(`${origin}/browser-missing?token=private-fixture`);
            await page.goto(`${base}/not-found`);
            await page.getByRole('heading', { name: 'Missing URLs' }).waitFor();
            await inspect('not-found');
            if (!dark) {
                const cdp = await context.newCDPSession(page);
                await cdp.send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-transparency', value: 'reduce' }] });
                assert.equal(await page.locator('.glass').first().evaluate(element => getComputedStyle(element).backdropFilter), 'none');
            }
            assert.deepEqual(errors, [], 'Browser JavaScript errors');
            await context.close(); index++;
        }
    } finally {
        fs.writeFileSync(path.join(target, 'accessibility.json'), JSON.stringify(results, null, 2));
        await browser.close();
    }
    console.log('Admin browser checks passed: light/dark, 390px viewport, accessibility, auth, SMTP loopback delivery, redirects and 404 views.');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
