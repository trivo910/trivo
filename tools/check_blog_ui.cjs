const {chromium}=require('../.audit/browser-qa/node_modules/playwright-core');
const fs=require('node:fs');const path=require('node:path');const assert=require('node:assert/strict');
const dir=path.resolve(__dirname,'../.audit/blog-qa'),creds=JSON.parse(fs.readFileSync(dir+'/credentials.json','utf8')),articles=JSON.parse(fs.readFileSync(dir+'/articles.json','utf8'));
assert.equal(creds.email,'blog-browser@example.test');
const normalizeTitle=s=>s.replace(/[\u200B-\u200D\uFEFF]/g,'').trim();
const base='http://127.0.0.1:18767',admin=base+'/'+creds.admin_path,results=[];
(async()=>{const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
try{for(const width of [1440,390]){const context=await browser.newContext({viewport:{width,height:1000}}),page=await context.newPage();page.setDefaultTimeout(20000);const errors=[];page.on('pageerror',e=>errors.push(e.message));
await page.goto(admin+'/login');await page.getByLabel('Email address').fill(creds.email);await page.getByLabel('Password',{exact:true}).fill(creds.password);await page.getByRole('button',{name:'Sign in',exact:true}).click();await page.waitForURL(admin);
for(const article of articles){const before=errors.length,response=await page.goto(base+article.path);assert.equal(response.status(),200,article.path);
await page.locator('.blog-post-page img').evaluateAll(imgs=>Promise.all(imgs.map(async i=>{i.loading='eager';await i.decode().catch(()=>{});})));const broken=await page.locator('.blog-post-page img').evaluateAll(imgs=>imgs.filter(i=>!i.complete||!i.naturalWidth).map(i=>i.src));
assert.deepEqual(broken,[],article.path+' images');assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,article.path+' overflow');assert.equal(await page.locator('main h1').count(),1,article.path+' single H1');
await page.goto(admin+'/posts/'+article.id+'/edit');await page.getByRole('textbox',{name:'Post body',exact:true}).waitFor();assert.equal(normalizeTitle(await page.getByLabel('Post title',{exact:true}).inputValue()),normalizeTitle(article.title));
assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,article.path+' admin overflow');
await page.getByLabel('Excerpt',{exact:true}).fill('Browser verified article '+article.id+' at '+width);await page.getByRole('button',{name:'Save published post',exact:true}).click();await page.getByRole('status').filter({hasText:'Published post updated.'}).waitFor();
await page.goto(base+article.path);assert.equal(normalizeTitle(await page.locator('main h1').textContent()),normalizeTitle(article.title));assert.deepEqual(errors.slice(before),[],article.path+' JS');
results.push({path:article.path,width,status:200,brokenImages:broken,overflow:false,adminEdit:true,jsErrors:errors.slice(before)});fs.writeFileSync(dir+'/articles-results.json',JSON.stringify(results,null,2));console.log(article.path+' '+width+': passed');
if(article===articles[0])await page.screenshot({path:dir+'/article-'+width+'.png',fullPage:true});}
await page.goto(base+'/blog');assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,'listing overflow');await page.screenshot({path:dir+'/listing-'+width+'.png',fullPage:true});await context.close();}
}finally{if(results.length!==articles.length*2){for(const c of browser.contexts())for(const p of c.pages()){console.log('Last page: '+p.url());console.log(await p.locator('[role=alert],.field-error').allTextContents());await p.screenshot({path:dir+'/failure.png',fullPage:true}).catch(()=>{});}}await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1});
