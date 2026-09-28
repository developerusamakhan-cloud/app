import { chromium } from 'playwright';
import fs from 'fs';
const B=new URL('../claimfairly/', import.meta.url).pathname + 'assets/brand/';
const mark = fs.readFileSync(new URL('logo-mark.svg.txt', import.meta.url),'utf8');
const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
async function shot(svg, w, h, out, bg) {
  const p = await b.newPage({ viewport: { width: w, height: h }, deviceScaleFactor: 1 });
  await p.setContent('<html><body style="margin:0;background:'+(bg||'transparent')+'">'+svg.replace('<svg ','<svg style="display:block;width:'+w+'px;height:'+h+'px" ')+'</body></html>');
  await p.screenshot({ path: out, omitBackground: !bg, clip: {x:0,y:0,width:w,height:h} });
  await p.close();
}
const logo = fs.readFileSync(B+'logo.svg','utf8'), white = fs.readFileSync(B+'logo-white.svg','utf8');
await shot(logo, 1276, 220, B+'logo.png');
await shot(white, 1276, 220, B+'logo-white.png');
await shot(fs.readFileSync(B+'logo-mark.svg','utf8'), 512, 512, B+'logo-mark-512.png');
// favicons: transparent rounded mark
for (const s of [16,32,48]) await shot('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'+mark+'</svg>', s, s, 'fav-'+s+'.png');
fs.copyFileSync('fav-16.png', B+'favicon-16x16.png'); fs.copyFileSync('fav-32.png', B+'favicon-32x32.png');
// full-bleed icons for iOS / Android (the OS rounds the corners itself)
const full = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#14213d"/><g transform="translate(14 14) scale(.72)">'+mark.replace('rx="29" fill="#14213d"','rx="29" fill="none"')+'</g></svg>';
await shot(full, 180, 180, B+'apple-touch-icon.png');
await shot(full, 192, 192, B+'android-chrome-192x192.png');
await shot(full, 512, 512, B+'android-chrome-512x512.png');
await b.close();
