/**
 * ClaimFairly core helpers shared by every tool.
 * Everything runs in the browser. Nothing is sent anywhere or stored.
 *
 * A tool script registers itself like this:
 *
 *   ClaimFairly.tool('diminished-value', function (root, preset) {
 *     ClaimFairly.onSubmit(root.querySelector('form'), function (values) { ... });
 *   });
 */
(function (window, document) {
	'use strict';

	var usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
	var usdCents = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 });
	var plain = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 });
	var DATA = window.CF_DATA || { states: [], urls: {} };

	var CF = {
		data: DATA,

		/** $1,235 */
		money: function (n, cents) {
			if (!isFinite(n)) { return 'n/a'; }
			return (cents ? usdCents : usd).format(n);
		},

		/** 12,500 */
		number: function (n) {
			return isFinite(n) ? plain.format(n) : 'n/a';
		},

		/** pct(0.333) gives "33.3%"; pct(33.3, true) gives "33.3%". */
		pct: function (n, alreadyPercent) {
			if (!isFinite(n)) { return 'n/a'; }
			var v = alreadyPercent ? n : n * 100;
			return plain.format(Math.round(v * 10) / 10) + '%';
		},

		/** Parse "$12,500", "12.5k" or "1m" into a number. NaN when empty or invalid. */
		parse: function (value) {
			if (typeof value === 'number') { return value; }
			var s = String(value == null ? '' : value).trim().toLowerCase().replace(/[$,\s%]/g, '');
			if (s === '') { return NaN; }
			var mult = 1;
			if (/k$/.test(s)) { mult = 1e3; s = s.slice(0, -1); } else if (/m$/.test(s)) { mult = 1e6; s = s.slice(0, -1); }
			var n = parseFloat(s);
			return isFinite(n) ? n * mult : NaN;
		},

		/** Parse, treating empty as 0. */
		num0: function (value) {
			var n = CF.parse(value);
			return isFinite(n) ? n : 0;
		},

		clamp: function (n, min, max) {
			return Math.min(Math.max(n, min), max);
		},

		esc: function (s) {
			return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
			});
		},

		state: function (code) {
			for (var i = 0; i < DATA.states.length; i++) {
				if (DATA.states[i].code === code) { return DATA.states[i]; }
			}
			return null;
		},

		/** Read a query string value (used to hand numbers from one tool to the next). */
		param: function (key) {
			try { return new URLSearchParams(window.location.search).get(key); } catch (e) { return null; }
		},

		/** Build a link to another tool with values in the query string. */
		link: function (tool, params) {
			var base = DATA.urls[tool] || '/';
			var q = Object.keys(params).filter(function (k) { return params[k] !== '' && params[k] != null; }).map(function (k) {
				return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
			}).join('&');
			return base + (q ? (base.indexOf('?') === -1 ? '?' : '&') + q : '');
		},

		/** Named form fields as an object. Inputs marked data-type="number" are parsed. */
		values: function (form) {
			var out = {};
			Array.prototype.forEach.call(form.elements, function (el) {
				if (!el.name || el.disabled) { return; }
				if (el.type === 'radio' && !el.checked) { return; }
				if (el.type === 'checkbox') { out[el.name] = el.checked; return; }
				var v = el.value;
				if (el.dataset.type === 'number') { v = CF.parse(v); }
				out[el.name] = v;
			});
			return out;
		},

		/** Validate fields marked data-required. Shows the message in #{id}-error. */
		validate: function (form) {
			var first = null;
			Array.prototype.forEach.call(form.querySelectorAll('[data-required]'), function (el) {
				if (el.disabled || el.closest('[hidden]')) { return; }
				var err = el.id ? form.querySelector('#' + el.id + '-error') : null;
				var isNum = el.dataset.type === 'number';
				var v = isNum ? CF.parse(el.value) : el.value.trim();
				var min = el.dataset.min !== undefined ? parseFloat(el.dataset.min) : -Infinity;
				var max = el.dataset.max !== undefined ? parseFloat(el.dataset.max) : Infinity;
				var bad = isNum ? (!isFinite(v) || v < min || v > max) : v === '';
				el.setAttribute('aria-invalid', bad ? 'true' : 'false');
				if (err) {
					err.textContent = bad ? (el.dataset.error || 'Please check this field.') : '';
					err.hidden = !bad;
				}
				if (bad && !first) { first = el; }
			});
			if (first) { first.focus(); }
			return !first;
		},

		onSubmit: function (form, callback) {
			form.setAttribute('novalidate', '');
			form.addEventListener('submit', function (event) {
				event.preventDefault();
				if (CF.validate(form)) {
					callback(CF.values(form), form);
				}
			});
		},

		showResult: function (el) {
			var wasHidden = el.hidden;
			el.hidden = false;
			if (!wasHidden) { return; }
			if (!el.hasAttribute('tabindex')) { el.setAttribute('tabindex', '-1'); }
			el.focus({ preventScroll: true });
			var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
		},

		/** "$1,200 to $3,400" (or a single amount when both match). */
		rangeHTML: function (low, high) {
			if (Math.round(low) === Math.round(high)) {
				return '<span>' + CF.money(low) + '</span>';
			}
			return '<span>' + CF.money(low) + '</span> <span class="cf-range__sep">to</span> <span>' + CF.money(high) + '</span>';
		},

		/** Table rows: [[label, value, className?], ...] */
		rowsHTML: function (rows) {
			return rows.map(function (r) {
				return '<tr' + (r[2] ? ' class="' + r[2] + '"' : '') + '><td>' + r[0] + '</td><td>' + r[1] + '</td></tr>';
			}).join('');
		},

		/**
		 * Donut chart as inline SVG. parts: [{label, value, color}]
		 * Returns markup for the chart plus a legend.
		 */
		donut: function (parts, title) {
			var total = parts.reduce(function (s, p) { return s + Math.max(0, p.value); }, 0);
			if (total <= 0) { return ''; }
			var r = 15.915, c = 2 * Math.PI * r, offset = 0, rings = '', legend = '';
			parts.forEach(function (p) {
				var v = Math.max(0, p.value);
				if (v <= 0) { return; }
				var len = v / total * c;
				rings += '<circle cx="21" cy="21" r="' + r + '" fill="none" stroke="' + p.color + '" stroke-width="6" stroke-dasharray="' + len.toFixed(3) + ' ' + (c - len).toFixed(3) + '" stroke-dashoffset="' + (-offset).toFixed(3) + '"></circle>';
				offset += len;
				legend += '<li><span class="cf-dot" style="background:' + p.color + '"></span>' + CF.esc(p.label) + ' <strong>' + CF.money(v) + '</strong> <span class="cf-muted">(' + CF.pct(v / total) + ')</span></li>';
			});
			return '<div class="cf-donut"><svg viewBox="0 0 42 42" role="img" aria-label="' + CF.esc(title || 'Breakdown chart') + '"><g transform="rotate(-90 21 21)">' + rings + '</g></svg><ul class="cf-legend">' + legend + '</ul></div>';
		},

		copy: function (text) {
			if (navigator.clipboard && window.isSecureContext) {
				return navigator.clipboard.writeText(text);
			}
			return new Promise(function (resolve, reject) {
				var ta = document.createElement('textarea');
				ta.value = text;
				ta.setAttribute('readonly', '');
				ta.style.position = 'fixed';
				ta.style.opacity = '0';
				document.body.appendChild(ta);
				ta.select();
				try { if (document.execCommand('copy')) { resolve(); } else { reject(); } } catch (e) { reject(e); }
				document.body.removeChild(ta);
			});
		},

		flash: function (btn, text) {
			var label = btn.getAttribute('data-label') || btn.textContent;
			btn.setAttribute('data-label', label);
			btn.textContent = text;
			setTimeout(function () { btn.textContent = label; }, 1800);
		},

		/** Buttons: data-cf-print prints the page (print CSS shows only the result). */
		wireActions: function (root) {
			Array.prototype.forEach.call(root.querySelectorAll('[data-cf-print]'), function (btn) {
				btn.addEventListener('click', function () { window.print(); });
			});
		},

		tool: function (name, init) {
			function run() {
				Array.prototype.forEach.call(document.querySelectorAll('[data-cf-tool="' + name + '"]'), function (root) {
					if (root.dataset.cfReady) { return; }
					root.dataset.cfReady = '1';
					var preset = {};
					try { preset = JSON.parse(root.dataset.cfPreset || '{}') || {}; } catch (e) { preset = {}; }
					init(root, preset);
					CF.wireActions(root);
				});
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', run);
			} else {
				run();
			}
		}
	};

	window.ClaimFairly = CF;
})(window, document);
