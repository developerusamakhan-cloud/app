/* Car Accident Settlement Estimator: economic damages + multiplier, adjusted for the state fault rule. */
ClaimFairly.tool('settlement-estimator', function (root, preset) {
	'use strict';
	var CF = window.ClaimFairly;
	var form = root.querySelector('form');
	var out = function (k) { return root.querySelector('[data-out="' + k + '"]'); };
	var MULT = { minor: [1.5, 2], moderate: [2, 3], serious: [3, 4], severe: [4, 5] };
	var RULE = {
		pure: 'Pure comparative negligence',
		mod50: '50% bar rule',
		mod51: '51% bar rule',
		mod51_noneco: '51% bar for pain and suffering',
		contributory: 'Contributory negligence',
		slight_gross: 'Slight vs gross negligence'
	};

	['med', 'future', 'wages', 'pd'].forEach(function (k) {
		var v = CF.param(k);
		if (v && form.elements[k]) { form.elements[k].value = v; }
	});
	var st = CF.param('state');
	if (st && !preset.state) { form.elements.state.value = st.toUpperCase(); }

	/* Returns multipliers for economic and non-economic damages under a state's rule. */
	function faultFactors(rule, f) {
		var keep = 1 - f / 100;
		switch (rule) {
			case 'mod50': return f >= 50 ? [0, 0] : [keep, keep];
			case 'mod51': return f > 50 ? [0, 0] : [keep, keep];
			case 'mod51_noneco': return [keep, f > 50 ? 0 : keep];
			case 'contributory': return f > 0 ? [0, 0] : [1, 1];
			default: return [keep, keep];
		}
	}

	CF.onSubmit(form, function (d) {
		var state = CF.state(d.state);
		var rule = state ? state.rule : 'pure';
		var f = CF.clamp(d.fault, 0, 100);
		var medical = d.med + CF.num0(d.future);
		var wages = CF.num0(d.wages);
		var pd = CF.num0(d.pd);
		var econ = medical + wages;
		var m = MULT[d.severity] || MULT.moderate;
		var nonLow = medical * m[0], nonHigh = medical * m[1];
		var ff = faultFactors(rule, f);
		var low = econ * ff[0] + nonLow * ff[1];
		var high = econ * ff[0] + nonHigh * ff[1];
		var limit = CF.num0(d.limit);
		var capped = limit > 0 && high > limit;
		if (limit > 0) { low = Math.min(low, limit); high = Math.min(high, limit); }
		var pdAdj = pd * ff[0];

		out('range').innerHTML = CF.rangeHTML(low, high);
		if (low === 0 && high === 0 && econ > 0) {
			out('sub').textContent = 'Under ' + (state ? state.name + '\'s' : 'this') + ' fault rule, ' + f + '% fault can wipe out the claim. Talk to a local attorney before you accept that.';
		} else {
			out('sub').textContent = capped
				? 'Capped at the ' + CF.money(limit) + ' policy limit you entered. The claim may be worth more than the policy pays.'
				: 'A realistic starting range, not a promise. Insurers often open lower.';
		}

		var rows = [
			['Medical bills' + (d.future ? ' (incl. future)' : ''), CF.money(medical), CF.money(medical)],
			['Lost wages', CF.money(wages), CF.money(wages)],
			['Pain and suffering (' + m[0] + 'x to ' + m[1] + 'x medical)', CF.money(nonLow), CF.money(nonHigh)],
			['Before fault', CF.money(econ + nonLow), CF.money(econ + nonHigh)]
		];
		if (f > 0) { rows.push(['After ' + f + '% fault', CF.money(econ * ff[0] + nonLow * ff[1]), CF.money(econ * ff[0] + nonHigh * ff[1])]); }
		if (limit > 0) { rows.push(['Policy limit', CF.money(limit), CF.money(limit)]); }
		rows.push(['Estimated range', CF.money(low), CF.money(high), 'is-total']);
		out('rows').innerHTML = rows.map(function (r) {
			return '<tr' + (r[3] ? ' class="' + r[3] + '"' : '') + '><td>' + r[0] + '</td><td>' + r[1] + '</td><td>' + r[2] + '</td></tr>';
		}).join('') + (pd ? '<tr><td colspan="3" class="cf-muted">Property damage, claimed separately: ' + CF.money(pd) + (ff[0] < 1 ? ', about ' + CF.money(pdAdj) + ' after fault' : '') + '</td></tr>' : '');

		out('formula').textContent =
			'Economic = ' + CF.money(medical) + ' medical + ' + CF.money(wages) + ' wages = ' + CF.money(econ) + '\n' +
			'Pain and suffering = ' + CF.money(medical) + ' x ' + m[0] + ' to ' + m[1] + ' = ' + CF.money(nonLow) + ' to ' + CF.money(nonHigh) + '\n' +
			'Fault (' + RULE[rule] + ', ' + f + '%): economic x ' + ff[0].toFixed(2) + ', pain and suffering x ' + ff[1].toFixed(2) +
			(limit > 0 ? '\nCapped at policy limit ' + CF.money(limit) : '');

		var html = '<p><strong>' + (state ? CF.esc(state.name) : 'Your state') + ': ' + RULE[rule] + '.</strong> ';
		if (rule === 'contributory') { html += 'Any fault on your side can bar the claim. There are narrow exceptions, so get a local opinion before giving up.'; }
		else if (rule === 'mod50') { html += 'At 50% fault or more you recover nothing. Below that, you lose your share.'; }
		else if (rule === 'mod51') { html += 'Above 50% fault you recover nothing. At 50% or less, you lose your share.'; }
		else if (rule === 'mod51_noneco') { html += 'Economic losses shrink by your share of fault. Pain and suffering is gone above 50% fault.'; }
		else if (rule === 'slight_gross') { html += 'There is no fixed cutoff. We applied a straight reduction, but a court could bar the claim entirely.'; }
		else { html += 'You lose your share of the fault, but even at high fault you can recover something.'; }
		html += '</p>';
		if (state && (state.fault === 'no-fault' || state.fault === 'choice')) {
			html += '<p><strong>' + (state.fault === 'choice' ? 'Choice no-fault state.' : 'No-fault state.') + '</strong> Your own PIP coverage pays medical bills and some lost wages first. To claim pain and suffering from the other driver, your injury usually has to meet a legal threshold.</p>';
		}
		if (!state) { html = '<p>No state picked, so we used pure comparative fault.</p>'; }
		out('state').innerHTML = html;

		var mid = Math.round((low + high) / 2);
		out('takehome').href = CF.link('settlement-take-home', { amount: mid });
		out('pain').href = CF.link('pain-and-suffering', { medical: Math.round(medical), severity: d.severity });
		out('letter').href = CF.link('demand-letter', { type: 'injury', amount: Math.round(high), medical: Math.round(medical), wages: Math.round(wages) });

		CF.showResult(root.querySelector('[data-result]'));
	});
});
