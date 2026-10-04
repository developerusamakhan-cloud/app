// Shared drawing code for PowerBachat cover and share images.
const fs = require('fs');
const FONTS = fs.readFileSync(__dirname + '/cover-fonts.css', 'utf8');
const V = '#f5b82e', P = '#f4efe4', D = 'rgba(244,239,228,.22)';
const art = {
  slabs: `<g fill="none" stroke-width="3">${[0,1,2,3,4].map(i=>`<rect x="${40+i*68}" y="${330-(70+i*52)}" width="46" height="${70+i*52}" rx="6" ${i<2?`fill="${V}" stroke="${V}"`:`stroke="${P}" stroke-opacity=".55"`}/>`).join('')}<path d="M20 238H366" stroke="${V}" stroke-dasharray="8 8"/><text x="372" y="245" fill="${V}" font-family="JetBrains Mono" font-size="20">200</text><path d="M20 332H400" stroke="${D}"/></g>`,
  solar: `<g><circle cx="320" cy="70" r="42" fill="${V}"/><g stroke="${V}" stroke-width="3" stroke-linecap="round">${[0,45,90,135,180,225,270,315].map(a=>{const r=a*Math.PI/180;return `<path d="M${320+Math.cos(r)*56} ${70+Math.sin(r)*56}L${320+Math.cos(r)*72} ${70+Math.sin(r)*72}"/>`}).join('')}</g><g transform="translate(40 150) skewX(-14)">${[0,1,2].map(r=>[0,1,2,3].map(c=>`<rect x="${c*82}" y="${r*58}" width="74" height="50" rx="4" fill="none" stroke="${P}" stroke-opacity="${.35+r*.2}" stroke-width="3"/>`).join('')).join('')}</g><path d="M60 330H380" stroke="${D}" stroke-width="3"/><path d="M150 324v-16M300 324v-16" stroke="${P}" stroke-opacity=".5" stroke-width="3"/></g>`,
  battery: `<g fill="none" stroke-width="4"><rect x="70" y="80" width="250" height="230" rx="22" stroke="${P}" stroke-opacity=".7"/><rect x="150" y="56" width="90" height="26" rx="6" stroke="${P}" stroke-opacity=".7"/>${[0,1,2,3].map(i=>`<rect x="100" y="${262-i*52}" width="190" height="38" rx="8" ${i<3?`fill="${V}" stroke="${V}"`:`stroke="${D}"`}/>`).join('')}<path d="M372 120l-22 44h26l-22 44" stroke="${V}" stroke-linecap="round" stroke-linejoin="round"/></g>`,
  meter: `<g fill="none" stroke-linecap="round"><path d="M50 290a160 160 0 0 1 320 0" stroke="${D}" stroke-width="14"/><path d="M50 290a160 160 0 0 1 120-155" stroke="${V}" stroke-width="14"/>${Array.from({length:11},(_,i)=>{const a=Math.PI+i*Math.PI/10;return `<path d="M${210+Math.cos(a)*128} ${290+Math.sin(a)*128}L${210+Math.cos(a)*112} ${290+Math.sin(a)*112}" stroke="${P}" stroke-opacity=".5" stroke-width="3"/>`}).join('')}<path d="M210 290 150 175" stroke="${P}" stroke-width="7"/><circle cx="210" cy="290" r="16" fill="${V}"/><text x="210" y="345" fill="${P}" fill-opacity=".6" font-family="JetBrains Mono" font-size="22" text-anchor="middle">kWh</text></g>`,
  fan: `<g><circle cx="210" cy="190" r="150" fill="none" stroke="${D}" stroke-width="3"/>${[0,120,240].map(a=>`<path transform="rotate(${a} 210 190)" d="M210 190C200 120 230 60 270 52c22 30 10 100-60 138Z" fill="${a?'none':V}" stroke="${a?P:V}" stroke-opacity="${a?.6:1}" stroke-width="3"/>`).join('')}<circle cx="210" cy="190" r="20" fill="${P}"/><path d="M60 360c40-14 80-14 120 0s80 14 120 0" fill="none" stroke="${V}" stroke-width="3" stroke-dasharray="2 10" stroke-linecap="round"/></g>`,
  ac: `<g fill="none" stroke-width="4" stroke-linecap="round"><rect x="40" y="70" width="340" height="120" rx="20" stroke="${P}" stroke-opacity=".7"/><path d="M70 160H350" stroke="${P}" stroke-opacity=".4"/><circle cx="340" cy="105" r="6" fill="${V}" stroke="none"/>${[0,1,2,3].map(i=>`<path d="M${100+i*70} 225c-14 22 14 40 0 62s14 40 0 62" stroke="${i%2?P:V}" stroke-opacity="${i%2?.5:1}"/>`).join('')}<text x="62" y="118" fill="${V}" stroke="none" font-family="JetBrains Mono" font-size="26">24°C</text></g>`,
  geyser: `<g fill="none" stroke-width="4" stroke-linecap="round"><rect x="120" y="40" width="180" height="270" rx="80" stroke="${P}" stroke-opacity=".7"/><path d="M150 120h120M150 160h120" stroke="${D}"/><path d="M210 190c-30 34-30 62 0 72 30-10 30-38 0-72Z" fill="${V}" stroke="${V}"/><path d="M180 310v40M240 310v40" stroke="${P}" stroke-opacity=".6"/><path d="M330 90c20 18 20 40 0 58M356 70c30 30 30 72 0 102" stroke="${V}"/></g>`,
  clock: `<g fill="none" stroke-linecap="round"><circle cx="210" cy="200" r="150" stroke="${D}" stroke-width="14"/><path d="M285 329.9A150 150 0 0 1 103.9 93.9" stroke="${V}" stroke-width="14"/>${Array.from({length:12},(_,i)=>{const a=i*Math.PI/6;return `<path d="M${210+Math.cos(a)*122} ${200+Math.sin(a)*122}L${210+Math.cos(a)*108} ${200+Math.sin(a)*108}" stroke="${P}" stroke-opacity=".5" stroke-width="4"/>`}).join('')}<path d="M210 200V110M210 200l-30 52" stroke="${P}" stroke-width="7"/><circle cx="210" cy="200" r="12" fill="${V}"/><text x="40" y="380" fill="${V}" font-family="JetBrains Mono" font-size="20">EVENING PEAK</text></g>`,
  bill: `<g><path d="M90 30h240v300l-20-12-20 12-20-12-20 12-20-12-20 12-20-12-20 12-20-12-20 12-20-12-20 12Z" fill="${P}" fill-opacity=".08" stroke="${P}" stroke-opacity=".6" stroke-width="3"/><path d="M120 80h120M120 115h180M120 145h150M120 175h170" stroke="${P}" stroke-opacity=".45" stroke-width="5" stroke-linecap="round"/><path d="M120 230h70" stroke="${P}" stroke-opacity=".6" stroke-width="5" stroke-linecap="round"/><rect x="215" y="212" width="90" height="38" rx="8" fill="${V}"/><circle cx="340" cy="300" r="46" fill="none" stroke="${V}" stroke-width="4"/><path d="M372 332l30 30" stroke="${V}" stroke-width="6" stroke-linecap="round"/></g>`,
};
const country = { pakistan: ['Pakistan', '#7fd3a6'], india: ['India', '#f5b82e'], bangladesh: ['Bangladesh', '#ff9a7a'] };

function esc(t) { return t.replace(/&/g,'&amp;').replace(/</g,'&lt;'); }

// One card: brand, coloured tag, big label (last word in yellow italic), drawing.
function card({ label, tag, tagColor, cover, coverLabel, width = 1200, height = 675 }) {
  const words = label.split(' ');
  const last = words.pop();
  const title = (words.length ? esc(words.join(' ')) + ' ' : '') + '<em>' + esc(last) + '</em>';
  const size = label.length > 26 ? 72 : label.length > 18 ? 84 : label.length > 12 ? 98 : 112;
  const artSvg = (art[cover] || art.meter).replace('>200</text>', '>' + (coverLabel || '200') + '</text>');
  const col = tagColor || V;
  return `<!doctype html><html><head><style>${FONTS}
  *{box-sizing:border-box}body{margin:0;width:${width}px;height:${height}px;overflow:hidden;background:#10201b;color:${P};font-family:'JetBrains Mono'}
  .bg{position:absolute;inset:0;background:radial-gradient(55% 75% at 80% 30%,rgba(245,184,46,.20),transparent 62%),linear-gradient(rgba(244,239,228,.045) 1px,transparent 1px) 0 0/48px 48px,linear-gradient(90deg,rgba(244,239,228,.045) 1px,transparent 1px) 0 0/48px 48px}
  .wrap{position:absolute;inset:0;padding:${height < 660 ? 54 : 60}px 72px;display:grid;grid-template-columns:1fr 470px;gap:24px}
  .left{display:flex;flex-direction:column;min-width:0}
  .brand{display:flex;align-items:center;gap:14px;font-family:Fraunces;font-size:30px;font-weight:600;letter-spacing:-.02em}.brand em{font-weight:400;color:${V}}
  .tag{margin-top:auto;display:inline-flex;align-self:flex-start;gap:12px;align-items:center;font-size:19px;letter-spacing:.16em;text-transform:uppercase;color:${col}}
  .tag i{width:40px;height:2px;background:${col};display:block}
  h1{margin:20px 0 0;font-family:Fraunces;font-weight:500;font-size:${size}px;line-height:.98;letter-spacing:-.045em;font-variation-settings:"SOFT" 60,"opsz" 144}
  h1 em{font-style:italic;font-weight:400;color:${V}}
  .url{margin-top:28px;font-size:17px;color:rgba(244,239,228,.5);letter-spacing:.08em}
  .art{align-self:center;justify-self:end}
  </style></head><body><div class="bg"></div><div class="wrap"><div class="left">
  <div class="brand"><svg width="44" height="44" viewBox="0 0 40 40"><circle cx="20" cy="20" r="20" fill="#22403a"/><path d="M8 26a12.4 12.4 0 0 1 24 0" fill="none" stroke="${P}" stroke-opacity=".28" stroke-width="3.2" stroke-linecap="round"/><path d="M8 26a12.4 12.4 0 0 1 9.4-11.7" fill="none" stroke="${V}" stroke-width="3.2" stroke-linecap="round"/><path d="M20 26 14.3 15.6" stroke="${P}" stroke-width="2.4" stroke-linecap="round"/><circle cx="20" cy="26" r="2.8" fill="${V}"/></svg><span>Power<em>Bachat</em></span></div>
  <span class="tag"><i></i>${esc(tag)}</span>
  <h1>${title}</h1>
  <div class="url">powerbachat.com</div></div>
  <svg class="art" width="470" height="${height < 660 ? 430 : 448}" viewBox="0 0 420 400">${artSvg}</svg></div></body></html>`;
}

module.exports = { art, country, card, V, P };
