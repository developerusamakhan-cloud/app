/**
 * ClaimFairly core helpers shared by every calculator.
 * Everything runs in the browser; nothing is sent anywhere.
 *
 * Usage in a tool script (assets/js/tools/{name}.js):
 *
 *   ClaimFairly.tool('diminished-value', function (root, preset) {
 *     var form = root.querySelector('form');
 *     ClaimFairly.onSubmit(form, function (values) { ... });
 *   });
 */
(function (window, document) {
	'use strict';

	var usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
	var usdCents = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 });
	var plain = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 });

	var CF = {
		/** Format a number as US dollars, e.g. 1234.5 -> "$1,235". */
		money: function (n, cents) {
			if (!isFinite(n)) { return '—'; }
			return (cents ? usdCents : usd).format(n);
		},

		/** Format a plain number with thousands separators. */
		number: function (n) {
			return isFinite(n) ? plain.format(n) : '—';
		},

		/** Format a fraction or percent value: pct(0.333) -> "33.3%", pct(33.3, true) -> "33.3%". */
		pct: function (n, alreadyPercent) {
			if (!isFinite(n)) { return '—'; }
			var v = alreadyPercent ? n : n * 100;
			return plain.format(Math.round(v * 10) / 10) + '%';
		},

		/** Parse user input like "$12,500.00" or "12k" into a number (NaN if empty/invalid). */
		parse: function (value) {
			if (typeof value === 'number') { return value; }
			var s = String(value == null ? '' : value).trim().toLowerCase().replace(/[$,\s%]/g, '');
			if (s === '') { return NaN; }
			var mult = 1;
			if (/k$/.test(s)) { mult = 1e3; s = s.slice(0, -1); } else if (/m$/.test(s)) { mult = 1e6; s = s.slice(0, -1); }
			var n = parseFloat(s);
			return isFinite(n) ? n * mult : NaN;
		},

		clamp: function (n, min, max) {
			return Math.min(Math.max(n, min), max);
		},

		/** Round to whole dollars (or given step). */
		round: function (n, step) {
			step = step || 1;
			return Math.round(n / step) * step;
		},

		/** Escape text for safe insertion into HTML strings. */
		esc: function (s) {
			return String(s).replace(/[&<>"']/g, function (c) {
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
			});
		},

		/** Read all named fields of a form into a plain object (numbers parsed where data-type="number"). */
		values: function (form) {
			var out = {};
			Array.prototype.forEach.call(form.elements, function (el) {
				if (!el.name || el.disabled) { return; }
				if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) {
					if (el.type === 'checkbox' && !(el.name in out)) { out[el.name] = false; }
					return;
				}
				var v = el.type === 'checkbox' ? (el.value === 'on' ? true : el.value) : el.value;
				if (el.dataset.type === 'number' || el.type === 'number') { v = CF.parse(v); }
				out[el.name] = v;
			});
			return out;
		},

		/**
		 * Validate required numeric fields. Marks invalid inputs and shows the
		 * message from data-error (or a default). Returns true when valid.
		 */
		validate: function (form) {
			var ok = true;
			var first = null;
			Array.prototype.forEach.call(form.querySelectorAll('[data-required]'), function (el) {
				var errId = el.id ? el.id + '-error' : '';
				var err = errId ? document.getElementById(errId) : null;
				var v = el.dataset.type === 'number' || el.type === 'number' ? CF.parse(el.value) : el.value.trim();
				var min = el.dataset.min !== undefined ? parseFloat(el.dataset.min) : (el.min !== '' && el.min !== undefined ? parseFloat(el.min) : -Infinity);
				var max = el.dataset.max !== undefined ? parseFloat(el.dataset.max) : (el.max !== '' && el.max !== undefined ? parseFloat(el.max) : Infinity);
				var bad = typeof v === 'number' ? (!isFinite(v) || v < min || v > max) : v === '';
				el.setAttribute('aria-invalid', bad ? 'true' : 'false');
				if (err) {
					err.textContent = bad ? (el.dataset.error || 'Please enter a valid value.') : '';
					err.hidden = !bad;
				}
				if (bad) {
					ok = false;
					first = first || el;
				}
			});
			if (first) { first.focus(); }
			return ok;
		},

		/** Handle a form submit with validation; callback receives CF.values(form). */
		onSubmit: function (form, callback) {
			form.setAttribute('novalidate', '');
			form.addEventListener('submit', function (event) {
				event.preventDefault();
				if (CF.validate(form)) {
					callback(CF.values(form), form);
				}
			});
		},

		/** Reveal a result container, move focus to it for screen readers, and scroll into view. */
		showResult: function (el) {
			el.hidden = false;
			if (!el.hasAttribute('tabindex')) { el.setAttribute('tabindex', '-1'); }
			el.focus({ preventScroll: true });
			var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
		},

		/** Build the standard range markup: "$1,200 – $3,400". */
		rangeHTML: function (low, high) {
			if (Math.round(low) === Math.round(high)) {
				return '<span>' + CF.money(low) + '</span>';
			}
			return '<span>' + CF.money(low) + '</span><span class="cf-range__sep">to</span><span>' + CF.money(high) + '</span>';
		},

		/** Copy text to the clipboard; returns a Promise. */
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
				try { document.execCommand('copy') ? resolve() : reject(); } catch (e) { reject(e); }
				document.body.removeChild(ta);
			});
		},

		/** Wire [data-cf-copy="#selector"] and [data-cf-print] buttons inside a root element. */
		wireActions: function (root) {
			Array.prototype.forEach.call(root.querySelectorAll('[data-cf-copy]'), function (btn) {
				btn.addEventListener('click', function () {
					var target = root.querySelector(btn.getAttribute('data-cf-copy'));
					if (!target) { return; }
					var label = btn.textContent;
					CF.copy(target.value !== undefined ? target.value : target.innerText).then(function () {
						btn.textContent = 'Copied';
						setTimeout(function () { btn.textContent = label; }, 1800);
					});
				});
			});
			Array.prototype.forEach.call(root.querySelectorAll('[data-cf-print]'), function (btn) {
				btn.addEventListener('click', function () { window.print(); });
			});
		},

		/** Register a tool initialiser; runs for every matching [data-cf-tool] on the page. */
		tool: function (name, init) {
			function run() {
				Array.prototype.forEach.call(document.querySelectorAll('[data-cf-tool="' + name + '"]'), function (root) {
					if (root.dataset.cfReady) { return; }
					root.dataset.cfReady = '1';
					init(root, root.dataset.cfPreset || '');
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
