const {chromium}=require('../.audit/browser-qa/node_modules/playwright-core');const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const dir=path.resolve(__dirname,'../.audit/blog-qa'),articles=JSON.parse(fs.readFileSync(dir+'/articles.json','utf8'));
(async()=>{const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
try{const page=await browser.newPage(),results=[];
for(const article of articles){const html=fs.readFileSync(dir+'/reference-'+article.path.slice(1)+'.html','utf8');
const reference=await page.evaluate(html=>{const d=new DOMParser().parseFromString(html,'text/html'),root=d.querySelector('[data-elementor-type="wp-post"]');if(!root)return null;return {hasContentsPanel:/in this article|table of contents/i.test(root.textContent),paragraphs:[...root.querySelectorAll('p')].map(e=>e.textContent.trim()).filter(s=>s.length>40),headings:[...root.querySelectorAll('h1,h2,h3')].map(e=>e.textContent.trim())};},html);
const response=await page.goto('http://127.0.0.1:18767'+article.path,{waitUntil:'domcontentloaded'}),local=await page.locator('.blog-post-page').textContent();const normalize=s=>s.normalize('NFKC').replace(/[\u2018\u2019]/g,"'").replace(/[\u201c\u201d]/g,'"').replace(/[\u2013\u2014]/g,'-').replace(/-+/g,'-').replace(/[\u200B-\u200D\uFEFF]/g,'').replace(/\s+/g,'').trim();const text=normalize(local);
const unexpectedContentsPanel=!!reference&&!reference.hasContentsPanel&&await page.locator('.blog-post__toc').count()>0;assert.equal(unexpectedContentsPanel,false,article.path+' has an extra contents panel absent from the reference');
const missing=reference?.paragraphs.filter(p=>!text.includes(normalize(p)))??[];results.push({path:article.path,status:response.status(),referenceFound:!!reference,referenceParagraphs:reference?.paragraphs.length,missingParagraphs:missing});
fs.writeFileSync(dir+'/reference-comparison.json',JSON.stringify(results,null,2));console.log(article.path+': '+missing.length+' paragraphs differ');}
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1});
