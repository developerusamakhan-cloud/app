// Social share image for the home page: node tools/share-home.js powerbachat
// Writes powerbachat/assets/img/share-home.jpg (1200x630). Needs: npm i playwright.
const { chromium } = require('playwright');
const path = require('path');
const { card } = require('./cover-art');
const root = process.argv[2];
(async () => {
  const b = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH || undefined });
  const p = await b.newPage({ viewport: { width: 1200, height: 630 } });
  await p.setContent(card({ label: 'Your electricity bill, explained', tag: 'Pakistan · India · Bangladesh', cover: 'meter', width: 1200, height: 630 }), { waitUntil: 'load' });
  await p.evaluate(() => document.fonts.ready);
  await p.screenshot({ path: path.join(root, 'assets', 'img', 'share-home.jpg'), type: 'jpeg', quality: 86 });
  await b.close();
})();
