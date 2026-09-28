import { chromium } from 'playwright';
import fs from 'fs';
const items = JSON.parse(fs.readFileSync(new URL('og-pages.json', import.meta.url),'utf8'));
const icons = JSON.parse(fs.readFileSync(new URL('og-icons.json', import.meta.url),'utf8'));
const logo = fs.readFileSync(new URL('../claimfairly/', import.meta.url).pathname + 'assets/brand/logo.svg','utf8').replace(/width="638" height="110"/,'width="290" height="50"');
const font = 'data:font/woff2;base64,' + fs.readFileSync(new URL('../claimfairly/', import.meta.url).pathname + 'assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2').toString('base64');
const C = {green:['#12805c','#e9f7f0'],blue:['#2e6be6','#edf3ff'],violet:['#6e56cf','#f3f0ff'],orange:['#f76b15','#fff1e8'],rose:['#e5484d','#fff0f1'],navy:['#1f2a44','#eef0f5']};
function iconFor(it){ if(it.label==='State rules')return 'pin'; if(it.label==='Insurer guide')return 'shield'; if(it.label==='Injury claims')return 'bandage';
  return {blue:'calculator',violet:'car-down',rose:'pulse',orange:'pie',navy:'letter',green:'book'}[it.color]; }
const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
const p = await b.newPage({ viewport: { width: 1200, height: 630 } });
for (const it of items) {
  const [c,t] = C[it.color]; const ic = icons[iconFor(it)];
  const size = it.title.length > 80 ? 50 : it.title.length > 55 ? 58 : 66;
  await p.setContent(`<html><head><style>
  @font-face{font-family:J;src:url(${font});font-weight:200 800}
  *{box-sizing:border-box}body{margin:0;width:1200px;height:630px;font-family:J,sans-serif;background:#fff;position:relative;overflow:hidden}
  .bg{position:absolute;inset:0;background:radial-gradient(700px 420px at 100% 0%,${t},transparent 70%),radial-gradient(500px 400px at 0% 100%,${t},transparent 70%)}
  .dots{position:absolute;inset:0;background-image:radial-gradient(rgba(16,24,40,.10) 1.5px,transparent 1.5px);background-size:26px 26px;-webkit-mask-image:linear-gradient(to bottom,#000,transparent 80%)}
  .wrap{position:absolute;inset:64px 72px 56px;display:flex;flex-direction:column}
  .pill{display:inline-flex;align-self:flex-start;align-items:center;gap:10px;margin-top:54px;padding:9px 18px;border-radius:99px;background:${c};color:#fff;font-weight:800;font-size:22px;letter-spacing:.02em}
  h1{margin:26px 0 0;max-width:690px;color:#101828;font-weight:800;font-size:${size}px;line-height:1.08;letter-spacing:-.035em}
  .foot{margin-top:auto;display:flex;justify-content:space-between;align-items:center;color:#667085;font-size:24px;font-weight:600}
  .foot b{color:#101828}
  .tile{position:absolute;right:78px;top:190px;width:230px;height:230px;border-radius:56px;background:${c};color:#fff;display:grid;place-items:center;transform:rotate(8deg);box-shadow:0 40px 70px -30px ${c}}
  .tile svg{width:120px;height:120px;stroke-width:1.6}
  .tile2{position:absolute;right:250px;top:360px;width:120px;height:120px;border-radius:32px;background:#fff;box-shadow:0 24px 50px -20px rgba(16,24,40,.35);transform:rotate(-8deg);display:grid;place-items:center;color:${c};font-weight:800;font-size:44px}
  </style></head><body><div class="bg"></div><div class="dots"></div>
  <div class="tile"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">${ic}</svg></div><div class="tile2">$</div>
  <div class="wrap">${logo}<span class="pill">${it.label}</span><h1>${it.title}</h1>
  <div class="foot"><span>Free. No sign-up. The math is always shown.</span><b>claimfairly.com</b></div></div></body></html>`);
  await p.evaluate(() => document.fonts.ready);
  const out = it.default ? new URL('../claimfairly/', import.meta.url).pathname + 'assets/brand/og-default.jpg' : new URL('../claimfairly/', import.meta.url).pathname + 'assets/og/'+it.slug+'.jpg';
  await p.screenshot({ path: out, type: 'jpeg', quality: 72 });
}
await b.close();
console.log('done');
