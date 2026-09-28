/* Settlement Take-Home Calculator. */
ClaimFairly.tool('settlement-take-home', function (root, preset) {
	'use strict';
	var CF = window.ClaimFairly;
	var form = root.querySelector('form');
	var out = function (k) { return root.querySelector('[data-out="' + k + '"]'); };

	function calc(amount, feePct, timing, costs, liens) {
		var f = feePct / 100;
		var fee = timing === 'after' ? Math.max(0, amount - costs) * f : amount * f;
		return { fee: fee, net: amount - fee - costs - liens };
	}

	var fromUrl = CF.param('amount');
	if (fromUrl && isFinite(CF.parse(fromUrl))) {
		form.elements.amount.value = CF.number(Math.round(CF.parse(fromUrl)));
	}

	function run(d, silent) {
		var costs = CF.num0(d.costs);
		var liensRaw = CF.num0(d.liens);
		var red = CF.clamp(CF.num0(d.reduction), 0, 100) / 100;
		var liens = liensRaw * (1 - red);
		var r = calc(d.amount, d.fee, d.timing, costs, liens);
		var other = calc(d.amount, d.fee, d.timing === 'after' ? 'before' : 'after', costs, liens);

		out('range').innerHTML = '<span>' + CF.money(r.net) + '</span>';
		out('sub').textContent = r.net > 0
			? 'That is about ' + CF.pct(r.net / d.amount) + ' of the ' + CF.money(d.amount) + ' settlement.'
			: 'Costs and liens use up the whole settlement. Ask your lawyer about reducing the liens or the fee before you sign anything.';

		out('chart').innerHTML = CF.donut([
			{ label: 'You', value: r.net, color: '#F5A524' },
			{ label: 'Attorney fee', value: r.fee, color: '#14213D' },
			{ label: 'Case costs', value: costs, color: '#94A3B8' },
			{ label: 'Medical liens', value: liens, color: '#8E7CF0' }
		], 'Where the settlement goes');

		var rows = [
			['Settlement', CF.money(d.amount)],
			['Attorney fee (' + CF.pct(d.fee, true) + (d.timing === 'after' ? ' of settlement minus costs' : ' of full settlement') + ')', '-' + CF.money(r.fee)]
		];
		if (costs) { rows.push(['Case costs', '-' + CF.money(costs)]); }
		if (liensRaw) { rows.push(['Medical bills and liens' + (red ? ' (after ' + CF.pct(red) + ' reduction)' : ''), '-' + CF.money(liens)]); }
		rows.push(['You keep', CF.money(r.net), 'is-total']);
		out('rows').innerHTML = CF.rowsHTML(rows);

		out('formula').textContent = d.timing === 'after'
			? 'Fee = (' + CF.money(d.amount) + ' - ' + CF.money(costs) + ' costs) x ' + CF.pct(d.fee, true) + ' = ' + CF.money(r.fee) + '\nYou keep = ' + CF.money(d.amount) + ' - ' + CF.money(costs) + ' - ' + CF.money(r.fee) + ' - ' + CF.money(liens) + ' = ' + CF.money(r.net)
			: 'Fee = ' + CF.money(d.amount) + ' x ' + CF.pct(d.fee, true) + ' = ' + CF.money(r.fee) + '\nYou keep = ' + CF.money(d.amount) + ' - ' + CF.money(r.fee) + ' - ' + CF.money(costs) + ' - ' + CF.money(liens) + ' = ' + CF.money(r.net);

		var diff = other.net - r.net;
		out('compare').innerHTML = costs > 0 && d.fee > 0
			? '<p><strong>Fee timing matters.</strong> If the fee were taken ' + (d.timing === 'after' ? 'from the full settlement' : 'after costs') + ', you would keep ' + CF.money(other.net) + ' (' + (diff >= 0 ? CF.money(diff) + ' more' : CF.money(-diff) + ' less') + '). It is worth asking which one your agreement uses.</p>'
			: '<p><strong>Tip:</strong> add your case costs to see how much the fee timing changes your share.</p>';

		out('examples').innerHTML = [50000, 100000, 250000].map(function (amt) {
			var fee = amt * d.fee / 100;
			return '<tr><td>' + CF.money(amt) + '</td><td>' + CF.money(fee) + '</td><td>' + CF.money(amt - fee) + '</td></tr>';
		}).join('');

		if (silent) { root.querySelector('[data-result]').hidden = false; } else { CF.showResult(root.querySelector('[data-result]')); }
	}

	CF.onSubmit(form, function (d) { run(d, false); });
	if ((preset.amount || fromUrl) && isFinite(CF.parse(form.elements.amount.value))) {
		run(CF.values(form), true);
	}
});
