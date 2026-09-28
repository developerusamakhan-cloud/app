/* Demand Letter Generator: builds the letter in the browser, with a tiny PDF writer (no libraries). */
ClaimFairly.tool('demand-letter', function (root, preset) {
	'use strict';
	var CF = window.ClaimFairly;
	var form = root.querySelector('form');
	var letterBox = root.querySelector('[data-out="letter"]');
	var types = ['injury', 'property', 'dv'];

	/* Prefill from another tool, e.g. ?type=dv&amount=1200 */
	var t = CF.param('type');
	if (t && types.indexOf(t) !== -1) {
		var r = form.querySelector('input[name="type"][value="' + t + '"]');
		if (r) { r.checked = true; }
	}
	['amount', 'medical', 'wages'].forEach(function (k) {
		var v = CF.param(k);
		if (v && isFinite(CF.parse(v)) && CF.parse(v) > 0) { form.elements[k].value = CF.number(Math.round(CF.parse(v))); }
	});

	function currentType() {
		var el = form.querySelector('input[name="type"]:checked');
		return el ? el.value : 'injury';
	}

	function syncFields() {
		var type = currentType();
		Array.prototype.forEach.call(form.querySelectorAll('[data-show]'), function (el) {
			var show = el.getAttribute('data-show').split(' ').indexOf(type) !== -1;
			el.hidden = !show;
		});
	}
	form.addEventListener('change', function (e) {
		if (e.target.name === 'type') { syncFields(); }
	});
	syncFields();

	function longDate(iso) {
		var d = iso ? new Date(iso + 'T12:00:00') : new Date();
		return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
	}

	function addDays(n) {
		var d = new Date();
		d.setDate(d.getDate() + n);
		return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
	}

	function clean(s) {
		return String(s || '').trim();
	}

	function build(d) {
		var type = currentType();
		var L = [];
		var dear = clean(d.adjuster) ? clean(d.adjuster) : 'Claims Adjuster';
		var insuredRef = clean(d.insured) ? clean(d.insured) + ', your insured,' : 'your insured';
		var accident = longDate(d.date);

		L.push(clean(d.name));
		clean(d.address).split(/\n+/).forEach(function (line) { if (line.trim()) { L.push(line.trim()); } });
		if (clean(d.contact)) { L.push(clean(d.contact)); }
		L.push('');
		L.push(longDate(''));
		L.push('');
		L.push(clean(d.insurer));
		L.push('Attn: ' + (clean(d.adjuster) || 'Claims Department'));
		clean(d.insurerAddress).split(/\n+/).forEach(function (line) { if (line.trim()) { L.push(line.trim()); } });
		L.push('');
		if (clean(d.claim)) { L.push('Claim number: ' + clean(d.claim)); }
		if (clean(d.insured)) { L.push('Your insured: ' + clean(d.insured)); }
		L.push('Date of loss: ' + accident);
		L.push('');
		L.push({
			injury: 'RE: Demand for settlement of bodily injury claim',
			property: 'RE: Demand for payment of property damage',
			dv: 'RE: Demand for payment of diminished value'
		}[type]);
		L.push('');
		L.push('Dear ' + dear + ',');
		L.push('');

		if (type === 'injury') {
			L.push('I am writing to make a formal demand for settlement of my injury claim from the collision on ' + accident + ' at ' + clean(d.location) + '.');
			L.push('');
			L.push('WHAT HAPPENED');
			L.push(clean(d.facts));
			L.push('');
			L.push('Based on these facts, ' + insuredRef + ' was responsible for this collision.');
			L.push('');
			L.push('MY INJURIES AND TREATMENT');
			L.push(clean(d.injuries));
			L.push('');
			L.push('I sought treatment promptly and followed my providers\' instructions.');
			L.push('');
			L.push('MY DAMAGES');
			L.push('Medical expenses: ' + CF.money(CF.num0(d.medical)));
			clean(d.items).split(/\n+/).forEach(function (line) { if (line.trim()) { L.push('  * ' + line.trim()); } });
			if (CF.num0(d.wages) > 0) { L.push('Lost wages: ' + CF.money(CF.num0(d.wages))); }
			L.push('');
			L.push('Beyond these costs, the injuries have caused me real pain and disrupted my work, sleep and daily routine. I am asking to be compensated for that as well.');
		} else if (type === 'property') {
			var repair = CF.num0(d.repair), rental = CF.num0(d.rental), towing = CF.num0(d.towing);
			L.push('I am writing to request payment for the damage to my vehicle, a ' + clean(d.vehicle) + ', from the collision on ' + accident + ' at ' + clean(d.location) + '.');
			L.push('');
			L.push('WHAT HAPPENED');
			L.push(clean(d.facts));
			L.push('');
			L.push('Based on these facts, ' + insuredRef + ' was responsible for this collision.');
			L.push('');
			L.push('MY DAMAGES');
			L.push('Repair cost: ' + CF.money(repair));
			if (rental > 0) { L.push('Rental car: ' + CF.money(rental)); }
			if (towing > 0) { L.push('Towing and storage: ' + CF.money(towing)); }
			L.push('Total: ' + CF.money(repair + rental + towing));
		} else {
			var basis = {
				appraisal: 'an independent diminished value appraisal, which is enclosed',
				market: 'my research on comparable vehicles for sale with and without accident history',
				'17c': 'the 17c formula commonly used in the insurance industry, which I consider a conservative minimum'
			}[d.basis] || 'my research';
			L.push('I am writing to request payment for the diminished value of my vehicle, a ' + clean(d.vehicle) + ', after the collision on ' + accident + ' at ' + clean(d.location) + '.');
			L.push('');
			L.push('WHAT HAPPENED');
			L.push(clean(d.facts));
			L.push('');
			L.push('Based on these facts, ' + insuredRef + ' was responsible for this collision.');
			L.push('');
			L.push('WHY MY CAR IS WORTH LESS');
			L.push('My vehicle has been repaired, but it now carries an accident on its history report. Buyers and dealers pay less for a car with an accident history than for the same car without one, even when the repairs are done well. Before the collision, my vehicle was worth about ' + CF.money(CF.num0(d.preValue)) + '.');
			L.push('');
			L.push('My diminished value figure is based on ' + basis + '.');
		}

		L.push('');
		L.push('SETTLEMENT DEMAND');
		L.push('To resolve this claim, I am requesting ' + CF.money(CF.num0(d.amount)) + '. Supporting documents are enclosed, and I can provide anything else you reasonably need to evaluate the claim.');
		L.push('');
		L.push('Please respond in writing within ' + d.days + ' days of the date of this letter, by ' + addDays(d.days) + '.');
		L.push('');
		L.push('Sincerely,');
		L.push('');
		L.push('');
		L.push(clean(d.name));
		L.push('');
		L.push('Enclosures: ' + {
			injury: 'medical records and bills, proof of lost wages, photos, police report',
			property: 'repair estimate or invoice, photos, rental and towing receipts, police report',
			dv: 'repair invoice, photos, ' + (d.basis === 'appraisal' ? 'diminished value appraisal, ' : '') + 'police report'
		}[type]);
		return L.join('\n');
	}

	CF.onSubmit(form, function (d) {
		letterBox.value = build(d);
		CF.showResult(root.querySelector('[data-result]'));
	});

	root.querySelector('[data-act="copy"]').addEventListener('click', function (e) {
		var btn = e.currentTarget;
		CF.copy(letterBox.value).then(function () { CF.flash(btn, 'Copied'); }, function () { CF.flash(btn, 'Copy failed'); });
	});

	root.querySelector('[data-act="print"]').addEventListener('click', function () {
		var w = window.open('', '_blank');
		if (!w) { window.print(); return; }
		w.document.write('<!doctype html><html><head><title>Demand letter</title><style>body{font:12pt/1.5 Georgia,serif;margin:1in;white-space:pre-wrap}</style></head><body></body></html>');
		w.document.body.textContent = letterBox.value;
		w.document.close();
		w.focus();
		w.print();
	});

	root.querySelector('[data-act="pdf"]').addEventListener('click', function () {
		var blob = textToPdf(letterBox.value);
		var a = document.createElement('a');
		a.href = URL.createObjectURL(blob);
		a.download = 'demand-letter.pdf';
		document.body.appendChild(a);
		a.click();
		setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
	});

	/* ------------------------------------------------------------------
	 * Minimal PDF writer: US Letter, Helvetica 11pt, word wrap, multi-page.
	 * Standard font, so no embedding is needed. ASCII only (smart quotes
	 * and similar characters are converted first).
	 * ------------------------------------------------------------------ */
	var W = [278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278, 556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556, 1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778, 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556, 333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556, 556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584];

	function ascii(s) {
		return s.replace(/[\u2018\u2019]/g, "'").replace(/[\u201C\u201D]/g, '"').replace(/[\u2013\u2014]/g, '-').replace(/\u2026/g, '...').replace(/\u00A0/g, ' ').replace(/[^\x20-\x7E\n]/g, '?');
	}

	function width(s, size) {
		var w = 0;
		for (var i = 0; i < s.length; i++) {
			var c = s.charCodeAt(i);
			w += (c >= 32 && c <= 126) ? W[c - 32] : 556;
		}
		return w * size / 1000;
	}

	function wrap(text, size, maxW) {
		var lines = [];
		text.split('\n').forEach(function (para) {
			if (para === '') { lines.push(''); return; }
			var indent = (para.match(/^\s*/) || [''])[0];
			var words = para.trim().split(/\s+/);
			var line = indent;
			words.forEach(function (word) {
				var test = line.trim() === '' ? indent + word : line + ' ' + word;
				if (width(test, size) > maxW && line.trim() !== '') {
					lines.push(line);
					line = indent + '  ' + word;
				} else {
					line = test;
				}
			});
			lines.push(line);
		});
		return lines;
	}

	function pdfEsc(s) {
		return s.replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
	}

	function textToPdf(text) {
		var size = 11, lead = 15.5, margin = 72, pageW = 612, pageH = 792;
		var perPage = Math.floor((pageH - margin * 2) / lead);
		var lines = wrap(ascii(text), size, pageW - margin * 2);
		var pages = [];
		for (var i = 0; i < lines.length; i += perPage) { pages.push(lines.slice(i, i + perPage)); }
		if (!pages.length) { pages.push(['']); }

		var objs = [];
		objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
		objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
		var kids = [];
		pages.forEach(function (pl, idx) {
			var pageId = 4 + idx * 2, contentId = pageId + 1;
			kids.push(pageId + ' 0 R');
			var stream = 'BT /F1 ' + size + ' Tf ' + lead + ' TL ' + margin + ' ' + (pageH - margin - size) + ' Td\n';
			pl.forEach(function (ln) { stream += '(' + pdfEsc(ln) + ') Tj T*\n'; });
			stream += 'ET';
			objs[pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' + pageW + ' ' + pageH + '] /Resources << /Font << /F1 3 0 R >> >> /Contents ' + contentId + ' 0 R >>';
			objs[contentId] = '<< /Length ' + stream.length + ' >>\nstream\n' + stream + '\nendstream';
		});
		objs[2] = '<< /Type /Pages /Kids [' + kids.join(' ') + '] /Count ' + pages.length + ' >>';

		var out = '%PDF-1.4\n';
		var offsets = [];
		for (var n = 1; n < objs.length; n++) {
			offsets[n] = out.length;
			out += n + ' 0 obj\n' + objs[n] + '\nendobj\n';
		}
		var xref = out.length;
		out += 'xref\n0 ' + objs.length + '\n0000000000 65535 f \n';
		for (var k = 1; k < objs.length; k++) { out += ('0000000000' + offsets[k]).slice(-10) + ' 00000 n \n'; }
		out += 'trailer\n<< /Size ' + objs.length + ' /Root 1 0 R >>\nstartxref\n' + xref + '\n%%EOF';
		return new Blob([out], { type: 'application/pdf' });
	}

	/* Exposed for testing. */
	root.cfTextToPdf = textToPdf;
	if (preset && preset.type) { syncFields(); }
});
