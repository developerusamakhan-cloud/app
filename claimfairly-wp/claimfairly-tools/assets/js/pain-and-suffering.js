/* Pain and Suffering Calculator: multiplier vs per diem. */
ClaimFairly.tool('pain-and-suffering', function (root) {
	'use strict';
	var CF = window.ClaimFairly;
	var form = root.querySelector('form');
	var out = function (k) { return root.querySelector('[data-out="' + k + '"]'); };
	var MULT = { minor: [1.5, 2], moderate: [2, 3], serious: [3, 4], severe: [4, 5] };

	var med = CF.param('medical');
	if (med) { form.elements.medical.value = CF.number(CF.num0(med)); }
	var sev = CF.param('severity');
	if (sev && MULT[sev]) {
		var radio = form.querySelector('input[name="severity"][value="' + sev + '"]');
		if (radio) { radio.checked = true; }
	}

	form.elements.income.addEventListener('input', function () {
		var inc = CF.parse(form.elements.income.value);
		if (isFinite(inc) && inc > 0) {
			form.elements.rate.value = Math.round(inc / 260);
		}
	});

	CF.onSubmit(form, function (d) {
		var m = MULT[d.severity] || MULT.moderate;
		var multLow = d.medical * m[0], multHigh = d.medical * m[1];
		var diem = d.rate * d.days;
		var low = Math.min(multLow, diem), high = Math.max(multHigh, diem);

		out('range').innerHTML = CF.rangeHTML(low, high);
		out('mult').innerHTML = CF.rangeHTML(multLow, multHigh);
		out('multMath').textContent = CF.money(d.medical) + ' x ' + m[0] + ' to ' + m[1];
		out('diem').textContent = CF.money(diem);
		out('diemMath').textContent = CF.money(d.rate) + ' x ' + CF.number(d.days) + ' days';

		var note;
		if (diem > multHigh * 1.5) {
			note = 'Per diem comes out much higher here. That is common with long recoveries and modest bills. Expect an adjuster to push back and lean on the multiplier.';
		} else if (diem < multLow * 0.67) {
			note = 'The multiplier gives more here, which usually happens when treatment was expensive but recovery was fairly quick.';
		} else {
			note = 'Both methods land in a similar place, which makes the number easier to defend.';
		}
		out('note').innerHTML = '<p>' + CF.esc(note) + '</p>';
		out('estimator').href = CF.link('settlement-estimator', { med: Math.round(d.medical) });
		CF.showResult(root.querySelector('[data-result]'));
	});
});
