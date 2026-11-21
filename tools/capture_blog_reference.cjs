const {chromium}=require('../.audit/browser-qa/node_modules/playwright-core');
const fs=require('node:fs'),path=require('node:path');
const dir=path.resolve(__dirname,'../.audit/blog-qa'),articles=JSON.parse(fs.readFileSync(dir+'/articles.json','utf8'));
(async()=>{const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
try{const page=await browser.newPage({viewport:{width:1440,height:1000}}),results=[];
for(const item of [{path:'/blog',title:'Blog'},...articles]){try{const response=await page.goto('https://trivo.in'+item.path,{waitUntil:'domcontentloaded',timeout:30000});
const result={path:item.path,status:response.status(),title:await page.title(),headings:await page.locator('h1,h2,h3').allTextContents()};results.push(result);
fs.writeFileSync(dir+'/reference-'+(item.path.slice(1)||'home')+'.html',await page.content());console.log(item.path+': '+result.status);
if(item.path==='/blog')await page.screenshot({path:dir+'/reference-listing.png',fullPage:true,timeout:15000});
}catch(e){results.push({path:item.path,error:e.message});console.log(item.path+': unavailable');}fs.writeFileSync(dir+'/reference-results.json',JSON.stringify(results,null,2));}
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1});
