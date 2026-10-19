// Pinterest pin (1000x1500) for the LESCO bill calculator: node tools/pin-lesco.js
// Rates come from powerbachat/inc/data.php (pk-nepra, non-protected). Writes pinterest/lesco-unit-rates-pin.jpg.
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const { V, P } = require('./cover-art');
const FONTS = fs.readFileSync(__dirname + '/cover-fonts.css', 'utf8');
const slabs = [[100, 22.44], [200, 28.91], [300, 33.10], [400, 37.99], [500, 40.22], [600, 41.62], [700, 42.76], [null, 47.69]];
let from = 1;
const rows = slabs.map(([max, rate], i) => {
  const range = max ? `${from} to ${max}` : `${from}+`;
  from = (max || 0) + 1;
  const w = Math.round(rate / 47.69 * 100);
  return `<div class="row"><span class="r">${range}</span><span class="bar"><i style="width:${w}%;${i < 2 ? `background:${V}` : ''}"></i></span><span class="p">Rs ${rate.toFixed(2)}</span></div>`;
}).join('');
const html = `<!doctype html><html><head><style>${FONTS}
*{box-sizing:border-box}body{margin:0;width:1000px;height:1500px;overflow:hidden;background:#10201b;color:${P};font-family:'JetBrains Mono'}
.bg{position:absolute;inset:0;background:radial-gradient(60% 40% at 85% 12%,rgba(245,184,46,.22),transparent 65%),linear-gradient(rgba(244,239,228,.045) 1px,transparent 1px) 0 0/48px 48px,linear-gradient(90deg,rgba(244,239,228,.045) 1px,transparent 1px) 0 0/48px 48px}
.wrap{position:absolute;inset:0;padding:70px 76px;display:flex;flex-direction:column}
.brand{display:flex;align-items:center;gap:14px;font-family:Fraunces;font-size:34px;font-weight:600;letter-spacing:-.02em}.brand em{font-weight:400;color:${V}}
.tag{margin-top:64px;display:flex;gap:14px;align-items:center;font-size:21px;letter-spacing:.16em;text-transform:uppercase;color:#7fd3a6}.tag i{width:44px;height:2px;background:#7fd3a6;display:block}
h1{margin:22px 0 0;font-family:Fraunces;font-weight:500;font-size:104px;line-height:.96;letter-spacing:-.045em;font-variation-settings:"SOFT" 60,"opsz" 144}
h1 em{font-style:italic;font-weight:400;color:${V}}
.sub{margin-top:26px;font-size:24px;line-height:1.45;color:rgba(244,239,228,.72)}
.table{margin-top:48px;border:2px solid rgba(244,239,228,.16);border-radius:22px;padding:30px 34px;background:rgba(244,239,228,.035)}
.head{display:flex;justify-content:space-between;font-size:18px;letter-spacing:.14em;text-transform:uppercase;color:rgba(244,239,228,.5);margin-bottom:14px}
.row{display:grid;grid-template-columns:200px 1fr 170px;align-items:center;gap:22px;padding:14px 0;border-top:1px solid rgba(244,239,228,.1);font-size:27px}
.row:first-of-type{border-top:0}.r{color:rgba(244,239,228,.85)}.p{text-align:right;font-weight:700}
.bar{height:14px;border-radius:7px;background:rgba(244,239,228,.08);overflow:hidden}.bar i{display:block;height:100%;border-radius:7px;background:rgba(244,239,228,.45)}
.ex{margin-top:34px;display:flex;justify-content:space-between;align-items:center;padding:26px 34px;border-radius:22px;background:${V};color:#10201b}
.ex b{font-family:Fraunces;font-size:54px;font-weight:600;letter-spacing:-.03em}.ex span{font-size:23px;line-height:1.35}
.note{margin-top:22px;font-size:19px;line-height:1.5;color:rgba(244,239,228,.55)}
.cta{margin-top:auto;display:flex;justify-content:space-between;align-items:center;padding-top:30px;border-top:2px solid rgba(244,239,228,.14)}
.cta div{font-family:Fraunces;font-size:38px;font-weight:500;letter-spacing:-.02em}.cta div em{color:${V}}
.cta span{font-size:21px;color:rgba(244,239,228,.6);letter-spacing:.06em}
</style></head><body><div class="bg"></div><div class="wrap">
<div class="brand"><svg width="50" height="50" viewBox="0 0 40 40"><circle cx="20" cy="20" r="20" fill="#22403a"/><path d="M8 26a12.4 12.4 0 0 1 24 0" fill="none" stroke="${P}" stroke-opacity=".28" stroke-width="3.2" stroke-linecap="round"/><path d="M8 26a12.4 12.4 0 0 1 9.4-11.7" fill="none" stroke="${V}" stroke-width="3.2" stroke-linecap="round"/><path d="M20 26 14.3 15.6" stroke="${P}" stroke-width="2.4" stroke-linecap="round"/><circle cx="20" cy="26" r="2.8" fill="${V}"/></svg><span>Power<em>Bachat</em></span></div>
<div class="tag"><i></i>LESCO · October 2026 rates</div>
<h1>What each unit <em>costs</em></h1>
<div class="sub">Residential slab rates for non-protected homes in Lahore, Kasur, Okara and Sheikhupura.</div>
<div class="table"><div class="head"><span>Units</span><span>Per unit</span></div>${rows}</div>
<div class="ex"><span>300 units<br>before taxes</span><b>Rs 8,445</b></div>
<div class="note">Each slab is charged at its own rate. Electricity duty 1.5% and GST 18% are added, plus any fuel price adjustment.</div>
<div class="cta"><div>Check your bill <em>free</em></div><span>powerbachat.com</span></div>
</div></body></html>`;
(async () => {
  const b = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH || undefined });
  const p = await b.newPage({ viewport: { width: 1000, height: 1500 } });
  await p.setContent(html, { waitUntil: 'load' });
  await p.evaluate(() => document.fonts.ready);
  await p.screenshot({ path: path.join(__dirname, '..', 'pinterest', 'lesco-unit-rates-pin.jpg'), type: 'jpeg', quality: 88 });
  await b.close();
})();
