// Cover images for guide posts: node tools/covers.js powerbachat [slug]
// Writes powerbachat/content/images/{slug}.jpg (1200x675) for every post, or one slug.
// Each post header sets "cover" (slabs, solar, battery, meter, fan, ac, geyser, clock,
// bill), "cover_title" (2 to 4 words) and optionally "cover_label". Needs: npm i playwright.
const { chromium } = require('playwright');
const fs = require('fs'), path = require('path');
const { card, country } = require('./cover-art');
const root = process.argv[2];
const only = process.argv[3] || '';

(async () => {
  const files = [].concat(...['pk','in','bd','blog'].map(d => fs.readdirSync(path.join(root,'content',d)).filter(f=>f.endsWith('.html')).map(f=>path.join(root,'content',d,f))));
  const b = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH || undefined });
  const p = await b.newPage({ viewport: { width: 1200, height: 675 } });
  for (const f of files) {
    const meta = JSON.parse(fs.readFileSync(f,'utf8').match(/<!--\s*powerbachat\s*(\{[\s\S]*?\})\s*-->/)[1]);
    if (meta.type !== 'post' || (only && meta.slug !== only)) continue;
    const [cname, ccol] = country[meta.category] || ['Guide', undefined];
    await p.setContent(card({ label: meta.cover_title || meta.title, tag: cname + ' guide', tagColor: ccol, cover: meta.cover, coverLabel: meta.cover_label }), { waitUntil: 'load' });
    await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: path.join(root,'content','images',meta.slug+'.jpg'), type: 'jpeg', quality: 84 });
    console.log(meta.slug);
  }
  await b.close();
})();
