/* Diminished Value Calculator: the insurer "17c" method. */
ClaimFairly.tool('diminished-value', function (root) {
	'use strict';
	var CF = window.ClaimFairly;
	var form = root.querySelector('form');
	var out = function (k) { return root.querySelector('[data-out="' + k + '"]'); };
	var damageNames = { '1': 'severe structural', '0.75': 'major structural and panel', '0.5': 'moderate structural and panel', '0.25': 'minor structural and panel', '0': 'no structural' };

	function mileageMultiplier(miles) {
		if (miles < 20000) { return 1; }
		if (miles < 40000) { return 0.8; }
		if (miles < 60000) { return 0.6; }
		if (miles < 80000) { return 0.4; }
		if (miles < 100000) { return 0.2; }
		return 0;
	}

	var v = CF.param('value');
	if (v) { form.elements.value.value = v; }

	CF.onSubmit(form, function (d) {
		var damage = parseFloat(d.damage);
		var mm = mileageMultiplier(d.miles);
		var base = d.value * 0.10;
		var result = Math.round(base * damage * mm);

		out('range').innerHTML = '<span>' + CF.money(result) + '</span>';
		out('sub').textContent = result > 0
			? 'Think of this as the floor. It is roughly where an insurer using 17c would start, not what your car really lost.'
			: 'The formula gives $0 here. That does not mean your car lost nothing. It means 17c ignores it.';

		out('rows').innerHTML = CF.rowsHTML([
			['Value before the accident', CF.money(d.value)],
			['Base loss (17c caps it at 10%)', CF.money(base)],
			['Damage multiplier (' + damageNames[d.damage] + ')', '&times; ' + damage.toFixed(2)],
			['Mileage multiplier (' + CF.number(d.miles) + ' miles)', '&times; ' + mm.toFixed(1)],
			['17c result', CF.money(result), 'is-total']
		]);
		out('formula').textContent = CF.money(d.value) + ' x 10% x ' + damage.toFixed(2) + ' (damage) x ' + mm.toFixed(1) + ' (mileage) = ' + CF.money(result);

		var notes = [];
		if (mm === 0) {
			notes.push('17c sets the mileage multiplier to zero at 100,000 miles. Plenty of cars with more miles than that still sell for less once they have an accident on record.');
		}
		if (damage === 0) {
			notes.push('17c gives nothing for cosmetic-only damage, even though an accident report can still scare off buyers.');
		}
		if (d.repair > 0) {
			var ratio = d.repair / d.value;
			notes.push('Your repair bill is ' + CF.pct(ratio) + ' of the car\'s value. ' + (ratio >= 0.3
				? 'That is a big repair. If you picked a light damage level, double check it.'
				: ratio < 0.1 ? 'That is a fairly light repair, which fits the lower damage levels.' : 'That usually lines up with moderate damage.'));
		}
		notes.push('For comparison, the most 17c can ever give for this car is ' + CF.money(base) + ' (10% of its value).');
		out('callout').innerHTML = '<ul>' + notes.map(function (n) { return '<li>' + CF.esc(n) + '</li>'; }).join('') + '</ul>';
		out('callout').hidden = false;

		out('letter').href = CF.link('demand-letter', { type: 'dv', amount: Math.max(result, 0) });
		CF.showResult(root.querySelector('[data-result]'));
	});
});
