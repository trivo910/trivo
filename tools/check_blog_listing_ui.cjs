const {chromium}=require('../.audit/browser-qa/node_modules/playwright-core');const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const dir=path.resolve(__dirname,'../.audit/blog-qa');
(async()=>{const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});const results=[];
try{for(const width of [1440,390]){const page=await browser.newPage({viewport:{width,height:1000}}),paths=new Set(),errors=[];page.on('pageerror',e=>errors.push(e.message));await page.goto('http://127.0.0.1:18767/blog');
for(let number=1;number<=2;number++){const cards=page.locator('[data-blog-card]'),count=await cards.count();assert.ok(count>6);assert.equal(await page.locator('[data-blog-card]:visible').count(),6);
while(await page.locator('[data-blog-load-more]').count())await page.locator('[data-blog-load-more]').click();assert.equal(await page.locator('[data-blog-card]:visible').count(),count);
for(const url of await cards.locator('h2 a').evaluateAll(links=>links.map(a=>a.pathname))){assert.equal(paths.has(url),false,'duplicate across pages');assert.equal(['/new-layout','/new-landing-2','/new-landing-3'].includes(url),false,'design demo excluded');paths.add(url);}
await cards.locator('img').evaluateAll(imgs=>Promise.all(imgs.map(async i=>{i.loading='eager';await i.decode().catch(()=>{});})));assert.deepEqual(await cards.locator('img').evaluateAll(imgs=>imgs.filter(i=>!i.naturalWidth).map(i=>i.src)),[],'card images');
assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,'listing overflow');
if(number===1){assert.equal(count,18);await page.locator('a[rel=next]:visible').first().click();await page.waitForURL('**/blog?page=2');}}
assert.ok(paths.size>=31);assert.deepEqual(errors,[]);results.push({width,pagination:true,progressiveLoad:true,articleCount:paths.size,brokenImages:[],jsErrors:errors});await page.close();}
fs.writeFileSync(dir+'/listing-results.json',JSON.stringify(results,null,2));console.log('Blog listing desktop and mobile: passed');}finally{await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1});
