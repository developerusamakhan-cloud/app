/**
 * PowerBachat front-end.
 * No dependencies. Everything reads from window.PowerBachat (printed by inc/enqueue.php).
 */
(function () {
	'use strict';

	var CFG = window.PowerBachat || {};
	var DATA = CFG.data || { countries: {}, tariffs: {}, solar: {} };
	var root = document.documentElement;
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ------------------------------------------------------------------
	 * Small helpers
	 * ---------------------------------------------------------------- */

	function $(sel, ctx) {
		return (ctx || document).querySelector(sel);
	}

	function $$(sel, ctx) {
		return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
	}

	function clamp(n, min, max) {
		return Math.min(max, Math.max(min, n));
	}

	function store(key, value) {
		try {
			if (value === undefined) {
				return window.localStorage.getItem(key);
			}
			window.localStorage.setItem(key, value);
		} catch (e) {
			/* Storage can be unavailable (private mode); the page still works. */
		}
		return null;
	}

	function emit(name, detail) {
		document.dispatchEvent(new CustomEvent(name, { detail: detail }));
	}

	function country(code) {
		return DATA.countries[code] || null;
	}

	function money(amount, code, decimals) {
		var c = country(code) || { currency: '', locale: 'en' };
		var d = decimals === undefined ? 0 : decimals;
		var n;
		try {
			n = new Intl.NumberFormat(c.locale, { minimumFractionDigits: d, maximumFractionDigits: d }).format(amount);
		} catch (e) {
			n = amount.toFixed(d);
		}
		return c.currency + ' ' + n;
	}

	function shortMoney(amount, code) {
		var c = country(code) || { currency: '' };
		if (amount >= 100000 && code !== 'bd') {
			if (code === 'in' || code === 'pk') {
				return c.currency + ' ' + (amount / 100000).toFixed(amount >= 1000000 ? 1 : 2).replace(/\.?0+$/, '') + ' lakh';
			}
		}
		if (amount >= 1000) {
			return c.currency + ' ' + (amount / 1000).toFixed(amount >= 10000 ? 0 : 1).replace(/\.0$/, '') + 'k';
		}
		return c.currency + ' ' + Math.round(amount);
	}

	function setRangeFill(range) {
		var min = parseFloat(range.min) || 0;
		var max = parseFloat(range.max) || 100;
		var p = ((parseFloat(range.value) - min) / (max - min)) * 100;
		range.style.setProperty('--p', clamp(p, 0, 100) + '%');
	}

	/** Animate a number inside an element. */
	function countTo(el, to, format, ms) {
		var from = parseFloat(el.getAttribute('data-value')) || 0;
		el.setAttribute('data-value', to);
		if (reduceMotion || from === to) {
			el.textContent = format(to);
			return;
		}
		var start = null;
		var dur = ms || 650;
		if (el._raf) {
			cancelAnimationFrame(el._raf);
		}
		function step(t) {
			if (!start) {
				start = t;
			}
			var k = Math.min(1, (t - start) / dur);
			var e = 1 - Math.pow(1 - k, 3);
			el.textContent = format(from + (to - from) * e);
			if (k < 1) {
				el._raf = requestAnimationFrame(step);
			}
		}
		el._raf = requestAnimationFrame(step);
	}

	/* ------------------------------------------------------------------
	 * Tariff engine
	 * ---------------------------------------------------------------- */

	/**
	 * Estimate a bill.
	 * @param {string}  tariffId   Key in DATA.tariffs.
	 * @param {number}  units      kWh in the billing period.
	 * @param {boolean} wantProtected Ask for the protected plan when the tariff has one.
	 */
	function computeBill(tariffId, units, wantProtected) {
		var tariff = DATA.tariffs[tariffId];
		if (!tariff) {
			return null;
		}
		units = Math.max(0, Math.round(units));

		var plans = tariff.plans || {};
		var plan = plans.standard;
		var warn = '';
		if (wantProtected && plans.protected) {
			if (plans.protected.max_units === null || units <= plans.protected.max_units) {
				plan = plans.protected;
			} else {
				warn = 'Above ' + plans.protected.max_units + ' units — protected rates no longer apply';
			}
		}

		var schedule = null;
		for (var i = 0; i < plan.schedules.length; i++) {
			var s = plan.schedules[i];
			if (s.max_units === null || units <= s.max_units) {
				schedule = s;
				break;
			}
		}
		if (!schedule) {
			schedule = plan.schedules[plan.schedules.length - 1];
		}

		var lines = [];
		var energy = 0;

		if (schedule.mode === 'flat') {
			var rate = schedule.slabs[schedule.slabs.length - 1][1];
			for (var f = 0; f < schedule.slabs.length; f++) {
				if (schedule.slabs[f][0] === null || units <= schedule.slabs[f][0]) {
					rate = schedule.slabs[f][1];
					break;
				}
			}
			energy = units * rate;
			lines.push({ label: 'All ' + units, units: units, rate: rate, amount: energy });
		} else {
			var prev = 0;
			for (var t = 0; t < schedule.slabs.length; t++) {
				var upto = schedule.slabs[t][0];
				var r = schedule.slabs[t][1];
				var cap = upto === null ? Infinity : upto;
				var take = Math.min(units, cap) - prev;
				if (take <= 0) {
					break;
				}
				var amt = take * r;
				energy += amt;
				lines.push({
					label: upto === null ? 'Above ' + prev : prev + 1 + '–' + upto,
					units: take,
					rate: r,
					amount: amt
				});
				prev = cap;
			}
		}

		var fixed = (tariff.fixed || []).map(function (x) {
			return { label: x.label, amount: units > 0 ? x.amount : 0 };
		});
		var base = energy + fixed.reduce(function (a, x) {
			return a + x.amount;
		}, 0);
		var taxes = (tariff.taxes || []).map(function (x) {
			return { label: x.label + ' ' + x.pct + '%', amount: (base * x.pct) / 100 };
		});
		var total = base + taxes.reduce(function (a, x) {
			return a + x.amount;
		}, 0);

		return {
			plan: plan.label,
			planKey: plan === plans.protected ? 'protected' : 'standard',
			lines: lines,
			energy: energy,
			fixed: fixed,
			taxes: taxes,
			total: total,
			avg: units > 0 ? total / units : 0,
			warn: warn,
			label: tariff.label,
			verified: tariff.verified,
			cycle: tariff.cycle || 'monthly'
		};
	}

	function tariffFor(code, utilityId) {
		var c = country(code);
		if (!c) {
			return '';
		}
		var list = c.utilities.filter(function (u) {
			return u.tariff;
		});
		var hit = list.filter(function (u) {
			return u.id === utilityId;
		})[0];
		return (hit || list[0] || {}).tariff || '';
	}

	/* ------------------------------------------------------------------
	 * Country (chosen automatically from the visitor's location)
	 * ---------------------------------------------------------------- */

	function currentCountry() {
		var c = root.getAttribute('data-country');
		return DATA.countries[c] ? c : CFG.defaultCountry || 'pk';
	}

	function setCountry(code) {
		if (!DATA.countries[code] || code === currentCountry()) {
			return;
		}
		root.setAttribute('data-country', code);
		emit('pb:country', { country: code });
		// Newly shown cards may still be waiting to reveal.
		$$('.reveal:not(.is-in)').forEach(function (el) {
			if (el.getBoundingClientRect().top < window.innerHeight) {
				el.classList.add('is-in');
			}
		});
	}

	/**
	 * First visit with no cookie: ask the server where the visitor is (this also
	 * fixes pages served from a full-page cache), then remember it for 30 days.
	 */
	function confirmCountry() {
		if (!window.pbGeoPending || !CFG.geoUrl || !window.fetch) {
			return;
		}
		fetch(CFG.geoUrl, { credentials: 'same-origin', cache: 'no-store' })
			.then(function (r) {
				return r.ok ? r.json() : null;
			})
			.then(function (j) {
				if (!j || !DATA.countries[j.country]) {
					return;
				}
				document.cookie = 'pb_cc=' + j.country + ';path=/;max-age=2592000;SameSite=Lax';
				setCountry(j.country);
			})
			.catch(function () {
				/* Keep what the server rendered. */
			});
	}

	/* ------------------------------------------------------------------
	 * Bill calculator
	 * ---------------------------------------------------------------- */

	var lastUnits = null;

	function initCalc(el) {
		var locked = el.getAttribute('data-country');
		var code = locked || currentCountry();
		var select = $('[data-calc-utility]', el);
		var unitsInput = $('[data-calc-units]', el);
		var range = $('[data-calc-range]', el);
		var protectedBox = $('[data-calc-protected]', el);
		var protectedWrap = $('[data-calc-protected-wrap]', el);
		var linesEl = $('[data-calc-lines]', el);
		var totalEl = $('[data-calc-total]', el);
		var avgEl = $('[data-calc-avg]', el);
		var planEl = $('[data-calc-plan]', el);
		var warnEl = $('[data-calc-warn]', el);
		var sourceEl = $('[data-calc-source]', el);
		var periodEl = $('[data-calc-period]', el);
		var linkEl = $('[data-calc-link]', el);
		var linkText = $('[data-calc-link-text]', el);
		var presets = $$('[data-calc-preset]', el);
		var wanted = el.getAttribute('data-utility');
		var lastSig = '';

		if (store('pb-protected') === '1') {
			protectedBox.checked = true;
		}

		function fillUtilities() {
			var c = country(code);
			select.innerHTML = '';
			var remembered = store('pb-utility-' + code);
			var pick = wanted || remembered || c.default;
			c.utilities.forEach(function (u) {
				if (!u.tariff) {
					return;
				}
				var o = document.createElement('option');
				o.value = u.id;
				o.textContent = u.abbr === u.name ? u.name : u.abbr + ' — ' + u.name;
				if (u.id === pick) {
					o.selected = true;
				}
				select.appendChild(o);
			});
			protectedWrap.hidden = code !== 'pk';
		}

		function utility() {
			var c = country(code);
			return c.utilities.filter(function (u) {
				return u.id === select.value;
			})[0] || c.utilities[0];
		}

		function render(animateLines) {
			var u = utility();
			var units = clamp(parseInt(unitsInput.value, 10) || 0, 0, 5000);
			var bill = computeBill(u.tariff, units, code === 'pk' && protectedBox.checked);
			if (!bill) {
				return;
			}

			range.value = Math.min(units, parseFloat(range.max));
			setRangeFill(range);
			presets.forEach(function (p) {
				p.classList.toggle('is-active', parseInt(p.getAttribute('data-calc-preset'), 10) === units);
			});

			periodEl.textContent = bill.cycle === 'bimonthly' ? 'in this 2-month cycle' : 'this month';
			planEl.textContent = u.abbr + ' · ' + bill.plan;

			var sig = u.id + '|' + bill.planKey + '|' + bill.lines.length;
			var fresh = animateLines || sig !== lastSig;
			lastSig = sig;

			var html = '';
			bill.lines.forEach(function (l, i) {
				html +=
					'<li' + (fresh ? ' style="animation-delay:' + i * 40 + 'ms"' : ' style="animation:none"') + '><span>' +
					l.label + ' units</span><span>' + money(l.amount, code) + '</span><small>' +
					l.units + ' × ' + money(l.rate, code, 2) + '</small></li>';
			});
			bill.fixed.forEach(function (f) {
				html += '<li style="animation:none"><span>' + f.label + '</span><span>' + money(f.amount, code) + '</span></li>';
			});
			if (bill.taxes.length) {
				html += '<li class="is-sub" style="animation:none"><span>Before tax</span><span>' + money(bill.energy + bill.fixed.reduce(function (a, f) {
					return a + f.amount;
				}, 0), code) + '</span></li>';
				bill.taxes.forEach(function (t) {
					html += '<li class="is-tax" style="animation:none"><span>' + t.label + '</span><span>' + money(t.amount, code) + '</span></li>';
				});
			}
			linesEl.innerHTML = html;

			countTo(totalEl, bill.total, function (v) {
				return money(v, code);
			});
			avgEl.textContent = money(bill.avg, code, 2);

			warnEl.hidden = !bill.warn;
			warnEl.textContent = bill.warn;

			sourceEl.textContent = bill.label + ', checked ' + (CFG.tariffChecked || bill.verified) + '.';
			linkEl.href = u.url;
			linkText.textContent = 'Full ' + u.abbr + ' calculator & slab table';

			lastUnits = units;
			emit('pb:units', { units: units, country: code, utility: u.id, protected: protectedBox.checked });
		}

		fillUtilities();
		render(true);

		select.addEventListener('change', function () {
			store('pb-utility-' + code, select.value);
			render(true);
		});
		unitsInput.addEventListener('input', function () {
			render(false);
		});
		range.addEventListener('input', function () {
			unitsInput.value = range.value;
			render(false);
		});
		protectedBox.addEventListener('change', function () {
			store('pb-protected', protectedBox.checked ? '1' : '0');
			render(true);
		});
		presets.forEach(function (p) {
			p.addEventListener('click', function () {
				unitsInput.value = p.getAttribute('data-calc-preset');
				render(false);
			});
		});

		if (!locked) {
			document.addEventListener('pb:country', function (e) {
				code = e.detail.country;
				wanted = '';
				fillUtilities();
				render(true);
			});
		}
	}

	/* ------------------------------------------------------------------
	 * Slab chart
	 * ---------------------------------------------------------------- */

	var CHARTS = {
		pk: { from: 50, to: 700, step: 50, cliff: 200, split: true, title: 'Monthly bill by units used' },
		in: { from: 100, to: 1000, step: 50, cliff: 500, split: false, title: 'Two-month bill by units used (TNEB)' },
		bd: { from: 50, to: 800, step: 50, cliff: 400, split: false, title: 'Monthly bill by units used' }
	};
	var SVGNS = 'http://www.w3.org/2000/svg';

	function svg(tag, attrs, parent) {
		var n = document.createElementNS(SVGNS, tag);
		Object.keys(attrs || {}).forEach(function (k) {
			n.setAttribute(k, attrs[k]);
		});
		if (parent) {
			parent.appendChild(n);
		}
		return n;
	}

	function niceStep(max, count) {
		var raw = max / count;
		var mag = Math.pow(10, Math.floor(Math.log10(raw)));
		var norm = raw / mag;
		var nice = norm < 1.5 ? 1 : norm < 3 ? 2 : norm < 7 ? 5 : 10;
		return nice * mag;
	}

	function initChart(el) {
		var locked = el.getAttribute('data-country');
		var code = locked || currentCountry();
		var plot = $('[data-chart-plot]', el);
		var tip = $('[data-chart-tip]', el);
		var figure = $('[data-chart-figure]', el);
		var tableWrap = $('[data-chart-table]', el);
		var tableBtn = $('[data-chart-table-toggle]', el);
		var you = lastUnits;
		var shown = false;
		var points = [];

		function series() {
			var cfg = CHARTS[code];
			var tariffId = tariffFor(code, country(code).default);
			var out = [];
			for (var u = cfg.from; u <= cfg.to; u += cfg.step) {
				var b = computeBill(tariffId, u, cfg.split && u <= cfg.cliff);
				out.push({ units: u, total: b.total, avg: b.avg, plan: b.plan });
			}
			return out;
		}

		function cliffText() {
			var cfg = CHARTS[code];
			var tariffId = tariffFor(code, country(code).default);
			var a = computeBill(tariffId, cfg.cliff, cfg.split);
			var b = computeBill(tariffId, cfg.cliff + 1, false);
			var jump = b.total - a.total;
			$('[data-chart-cliff]', el).textContent = '+' + money(jump, code);
			$('[data-chart-cliff-detail]', el).textContent =
				'Going from ' + cfg.cliff + ' to ' + (cfg.cliff + 1) + ' units takes the bill from ' +
				money(a.total, code) + ' to ' + money(b.total, code) + '.';
			$('[data-chart-cliff-label]', el).textContent = 'Unit number ' + (cfg.cliff + 1) + ' costs you';
			$('[data-chart-note]', el).textContent = country(code).chartNote;
			$('[data-chart-title]', el).textContent = cfg.title;
		}

		function draw() {
			var cfg = CHARTS[code];
			points = series();
			plot.innerHTML = '';
			var w = plot.clientWidth || 640;
			var h = plot.clientHeight || 400;
			var m = { t: 14, r: 8, b: 30, l: 52 };
			var iw = w - m.l - m.r;
			var ih = h - m.t - m.b;
			var max = Math.max.apply(null, points.map(function (p) {
				return p.total;
			}));
			var step = niceStep(max, 4);
			var top = Math.ceil(max / step) * step;
			var s = svg('svg', { viewBox: '0 0 ' + w + ' ' + h, width: w, height: h }, plot);

			for (var v = 0; v <= top + 0.001; v += step) {
				var y = m.t + ih - (v / top) * ih;
				svg('line', { class: 'grid-line', x1: m.l, x2: w - m.r, y1: y, y2: y }, s);
				var tx = svg('text', { class: 'axis-text', x: m.l - 8, y: y + 4, 'text-anchor': 'end' }, s);
				tx.textContent = v === 0 ? '0' : shortMoney(v, code).replace(/^\S+\s/, '');
			}

			var n = points.length;
			var slot = iw / n;
			var bw = Math.max(4, Math.min(34, slot - 4));
			var labelEvery = Math.max(1, Math.ceil(30 / slot));

			points.forEach(function (p, i) {
				var x = m.l + slot * i + (slot - bw) / 2;
				var bh = Math.max(1, (p.total / top) * ih);
				var g = svg('g', { class: 'bar-group', tabindex: '0', 'data-i': i, role: 'img', 'aria-label': p.units + ' units: ' + money(p.total, code) }, s);
				var r = Math.min(4, bw / 2);
				var y0 = m.t + ih;
				var path = 'M' + x + ',' + y0 + 'V' + (y0 - bh + r) + 'Q' + x + ',' + (y0 - bh) + ' ' + (x + r) + ',' + (y0 - bh) +
					'H' + (x + bw - r) + 'Q' + (x + bw) + ',' + (y0 - bh) + ' ' + (x + bw) + ',' + (y0 - bh + r) + 'V' + y0 + 'Z';
				svg('path', { class: 'bar' + (p.units > cfg.cliff ? ' is-above' : ''), d: path, 'data-units': p.units }, g);
				svg('rect', { class: 'bar-hit', x: m.l + slot * i, y: m.t, width: slot, height: ih }, g);
				if (i % labelEvery === 0) {
					var lab = svg('text', { class: 'axis-text', x: x + bw / 2, y: h - 10, 'text-anchor': 'middle' }, s);
					lab.textContent = p.units;
				}
				p.x = x + bw / 2;
				p.y = y0 - bh;
			});

			// Cliff marker between the cliff bar and the next one.
			var ci = points.findIndex(function (p) {
				return p.units > cfg.cliff;
			});
			if (ci > 0) {
				var cx = m.l + slot * ci;
				svg('line', { class: 'cliff-line', x1: cx, x2: cx, y1: m.t, y2: m.t + ih }, s);
				var leftHalf = cx < m.l + iw / 2;
				var ct = svg('text', { class: 'cliff-text', x: leftHalf ? cx + 6 : cx - 6, y: m.t + 12, 'text-anchor': leftHalf ? 'start' : 'end' }, s);
				ct.textContent = cfg.cliff + '-unit line';
			}

			s.addEventListener('pointermove', onHover);
			s.addEventListener('pointerleave', hideTip);
			s.addEventListener('focusin', function (e) {
				var g = e.target.closest('.bar-group');
				if (g) {
					showTip(parseInt(g.getAttribute('data-i'), 10));
				}
			});
			s.addEventListener('focusout', hideTip);

			if (!shown) {
				$$('.bar', s).forEach(function (b, i) {
					b.style.transformBox = 'fill-box';
					b.style.transformOrigin = 'bottom';
					b.style.transform = 'scaleY(0)';
					b.style.transition = 'transform 0.9s cubic-bezier(.22,1,.36,1) ' + i * 35 + 'ms, fill .2s, opacity .2s';
				});
			}
			markYou();
			buildTable();
		}

		function onHover(e) {
			var g = e.target.closest('.bar-group');
			if (!g) {
				hideTip();
				return;
			}
			showTip(parseInt(g.getAttribute('data-i'), 10));
		}

		function showTip(i) {
			var p = points[i];
			if (!p) {
				return;
			}
			var plotBox = plot.getBoundingClientRect();
			var figBox = figure.getBoundingClientRect();
			tip.innerHTML = '<strong>' + money(p.total, code) + '</strong>' + p.units + ' units <span>· ' + money(p.avg, code, 2) + '/unit</span>';
			tip.hidden = false;
			var left = plotBox.left - figBox.left + p.x;
			left = clamp(left, 90, figBox.width - 90);
			tip.style.left = left + 'px';
			tip.style.top = plotBox.top - figBox.top + p.y + 'px';
		}

		function hideTip() {
			tip.hidden = true;
		}

		function markYou() {
			if (you === null) {
				return;
			}
			var bars = $$('.bar', plot);
			var best = -1;
			var bestD = Infinity;
			points.forEach(function (p, i) {
				var d = Math.abs(p.units - you);
				if (d < bestD) {
					bestD = d;
					best = i;
				}
			});
			bars.forEach(function (b, i) {
				b.classList.toggle('is-you', i === best && you >= points[0].units - CHARTS[code].step && you <= points[points.length - 1].units + CHARTS[code].step);
			});
		}

		function buildTable() {
			var html = '<table><thead><tr><th>Units</th><th>Estimated bill</th><th>Per unit</th></tr></thead><tbody>';
			points.forEach(function (p) {
				html += '<tr><td>' + p.units + '</td><td>' + money(p.total, code) + '</td><td>' + money(p.avg, code, 2) + '</td></tr>';
			});
			tableWrap.innerHTML = html + '</tbody></table>';
		}

		function grow() {
			if (shown) {
				return;
			}
			shown = true;
			requestAnimationFrame(function () {
				$$('.bar', plot).forEach(function (b) {
					b.style.transform = 'scaleY(1)';
				});
			});
		}

		tableBtn.addEventListener('click', function () {
			var open = tableWrap.hidden;
			tableWrap.hidden = !open;
			tableBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
			$('span', tableBtn).textContent = open ? 'Hide table' : 'View as table';
		});

		cliffText();
		draw();

		if ('IntersectionObserver' in window && !reduceMotion) {
			new IntersectionObserver(function (entries, obs) {
				if (entries[0].isIntersecting) {
					grow();
					obs.disconnect();
				}
			}, { threshold: 0.3 }).observe(plot);
		} else {
			grow();
		}

		var resizeT;
		window.addEventListener('resize', function () {
			clearTimeout(resizeT);
			resizeT = setTimeout(draw, 150);
		});

		document.addEventListener('pb:units', function (e) {
			if (e.detail.country !== code) {
				return;
			}
			you = e.detail.units;
			markYou();
		});

		if (!locked) {
			document.addEventListener('pb:country', function (e) {
				code = e.detail.country;
				you = lastUnits;
				cliffText();
				draw();
				$$('.bar', plot).forEach(function (b) {
					b.style.transform = 'scaleY(1)';
				});
			});
		}
	}

	/* ------------------------------------------------------------------
	 * Solar sizing
	 * ---------------------------------------------------------------- */

	function initSolar(el) {
		var locked = el.getAttribute('data-country');
		var code = locked || currentCountry();
		var range = $('[data-solar-range]', el);
		var out = $('[data-solar-out]', el);
		var roof = $('[data-solar-roof]', el);
		var slots = window.innerWidth < 720 ? 24 : 32;
		var panels = [];

		for (var i = 0; i < slots; i++) {
			var p = document.createElement('span');
			p.className = 'panel';
			p.style.setProperty('--n', i);
			roof.appendChild(p);
			panels.push(p);
		}

		function render() {
			var units = parseInt(range.value, 10);
			var cfg = DATA.solar[code];
			var c = country(code);
			var perKwMonth = cfg.sun_hours * 30 * 0.78;
			var kw = Math.max(1, Math.ceil((units / perKwMonth) * 2) / 2);
			var count = Math.ceil((kw * 1000) / cfg.panel_watt);
			var lo = kw * cfg.per_kw[0];
			var hi = kw * cfg.per_kw[1];

			var bill = computeBill(tariffFor(code, c.default), units, false);
			var monthlyBill = bill.cycle === 'bimonthly' ? computeBill(tariffFor(code, c.default), units * 2, false).total / 2 : bill.total;
			var saving = monthlyBill * 0.85;

			var subsidy = 0;
			var subEl = $('[data-solar-subsidy]', el);
			if (cfg.subsidy === 'pm-surya-ghar') {
				subsidy = Math.min(78000, Math.min(kw, 2) * 30000 + (kw > 2 ? Math.min(kw - 2, 1) * 18000 : 0));
				subEl.hidden = false;
				subEl.textContent = 'PM Surya Ghar subsidy of about ' + money(subsidy, code) + ' brings your share down to ' +
					money(lo - subsidy, code) + '–' + money(hi - subsidy, code) + '.';
			} else {
				subEl.hidden = true;
			}

			var mid = (lo + hi) / 2 - subsidy;
			var years = saving > 0 ? mid / (saving * 12) : 0;

			out.textContent = units.toLocaleString();
			setRangeFill(range);
			countTo($('[data-solar-kw]', el), kw, function (v) {
				return (Math.round(v * 2) / 2).toFixed(1);
			}, 400);
			$('[data-solar-panels]', el).textContent = count + ' × ' + cfg.panel_watt + ' W';
			$('[data-solar-cost]', el).textContent = shortMoney(lo, code) + ' – ' + shortMoney(hi, code).replace(/^\S+\s/, '');
			$('[data-solar-save]', el).textContent = money(saving, code) + ' / mo';
			$('[data-solar-payback]', el).textContent = years ? years.toFixed(1) + ' years' : '—';

			panels.forEach(function (pn, i) {
				pn.classList.toggle('is-on', i < count);
			});
			$('[data-solar-caption]', el).textContent = count > slots ?
				count + ' panels · roof shows the first ' + slots :
				count + ' panels · about ' + Math.round(count * 2.6) + ' m² of roof';
			$('[data-solar-fine]', el).textContent =
				'Assumes ' + cfg.sun_hours + ' peak sun hours, 22% system losses and on-grid installation at ' +
				money(cfg.per_kw[0], code) + '–' + money(cfg.per_kw[1], code) + ' per kW. Savings assume about 85% of your bill is offset. ' +
				'Batteries, structure upgrades and net-metering fees are extra.';
		}

		range.addEventListener('input', render);
		render();

		// The sun drifts across its arc while the section is on screen.
		var sun = $('[data-solar-sun]', el);
		var running = false;
		var t0 = null;
		function sunFrame(t) {
			if (!running) {
				return;
			}
			if (t0 === null) {
				t0 = t;
			}
			var k = ((t - t0) / 16000) % 2;
			var phase = k < 1 ? k : 2 - k;
			var ease = 0.5 - Math.cos(phase * Math.PI) / 2;
			var a = Math.PI * (1 - (0.08 + ease * 0.84));
			sun.setAttribute('cx', (200 + 180 * Math.cos(a)).toFixed(1));
			sun.setAttribute('cy', (140 - 120 * Math.sin(a)).toFixed(1));
			requestAnimationFrame(sunFrame);
		}
		if (sun && !reduceMotion && 'IntersectionObserver' in window) {
			new IntersectionObserver(function (entries) {
				running = entries[0].isIntersecting;
				if (running) {
					requestAnimationFrame(sunFrame);
				}
			}).observe(sun.ownerSVGElement);
		}

		if (!locked) {
			document.addEventListener('pb:country', function (e) {
				code = e.detail.country;
				render();
			});
		}
	}

	/* ------------------------------------------------------------------
	 * Price board digit roll
	 * ---------------------------------------------------------------- */

	function rollDigits(el) {
		var text = el.getAttribute('data-flip');
		if (reduceMotion || !text) {
			return;
		}
		el.textContent = '';
		var cols = [];
		text.split('').forEach(function (ch) {
			if (!/\d/.test(ch)) {
				var s = document.createElement('span');
				s.textContent = ch;
				el.appendChild(s);
				return;
			}
			var d = document.createElement('span');
			d.className = 'flip__d';
			var col = document.createElement('span');
			col.className = 'flip__col';
			for (var k = 0; k <= 9; k++) {
				var n = document.createElement('span');
				n.textContent = k;
				col.appendChild(n);
			}
			d.appendChild(col);
			el.appendChild(d);
			cols.push([col, parseInt(ch, 10)]);
		});
		el.setAttribute('aria-label', text);
		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				cols.forEach(function (c, i) {
					c[0].style.transitionDelay = i * 90 + 'ms';
					c[0].style.transform = 'translateY(-' + c[1] * 1.2 + 'em)';
				});
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Page chrome: reveal, sticky header, mobile nav, demo form
	 * ---------------------------------------------------------------- */

	function initReveal() {
		var items = $$('.reveal');
		if (!('IntersectionObserver' in window) || reduceMotion) {
			items.forEach(function (el) {
				el.classList.add('is-in');
			});
			$$('[data-flip]').forEach(rollDigits);
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) {
					return;
				}
				en.target.classList.add('is-in');
				$$('[data-flip]', en.target).forEach(rollDigits);
				io.unobserve(en.target);
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
		items.forEach(function (el) {
			io.observe(el);
		});
	}

	function initHeader() {
		var header = $('[data-header]');
		if (!header) {
			return;
		}
		var stuck = false;
		function check() {
			var s = window.scrollY > 8;
			if (s !== stuck) {
				stuck = s;
				header.classList.toggle('is-stuck', s);
			}
		}
		window.addEventListener('scroll', check, { passive: true });
		check();

		var toggle = $('[data-nav-toggle]');
		if (toggle) {
			toggle.addEventListener('click', function () {
				var open = !document.body.classList.contains('nav-open');
				document.body.classList.toggle('nav-open', open);
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
			$$('#site-nav a').forEach(function (a) {
				a.addEventListener('click', function () {
					document.body.classList.remove('nav-open');
					toggle.setAttribute('aria-expanded', 'false');
				});
			});
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && document.body.classList.contains('nav-open')) {
					document.body.classList.remove('nav-open');
					toggle.setAttribute('aria-expanded', 'false');
					toggle.focus();
				}
			});
		}
	}

	function initDemoForms() {
		$$('[data-demo-form]').forEach(function (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var thanks = form.parentNode.querySelector('[data-form-thanks]');
				form.hidden = true;
				if (thanks) {
					thanks.hidden = false;
				}
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Boot
	 * ---------------------------------------------------------------- */

	$$('[data-calc]').forEach(initCalc);
	$$('[data-chart]').forEach(initChart);
	$$('[data-solar]').forEach(initSolar);
	initReveal();
	initHeader();
	initDemoForms();
	confirmCountry();
})();
