/* Nabia Website Audit: fresh security fields on cached pages and a loading screen. */
(function () {
	'use strict';

	var settings = window.nwaSettings || {};
	var forms = document.querySelectorAll('[data-nwa-form]');
	if (!forms.length) {
		return;
	}

	// Cached pages can hold an old copy of the form: fetch fresh values (logged out visitors only).
	if (settings.keys && window.fetch && !document.body.classList.contains('logged-in')) {
		fetch(settings.keys, { credentials: 'omit', cache: 'no-store' })
			.then(function (r) { return r.ok ? r.json() : null; })
			.then(function (k) {
				if (!k) {
					return;
				}
				forms.forEach(function (form) {
					var set = function (name, value) {
						var input = form.querySelector('[name="' + name + '"]');
						if (input && value) {
							input.value = value;
						}
					};
					set('nwa_nonce', k.nonce);
					set('nwa_t', k.token);
					set('nwa_cq', k.cq);
					var q = form.querySelector('.nwa-math-q');
					if (q) {
						q.innerHTML = k.a + ' <b>+</b> ' + k.b + ' <b>=</b>';
					}
					var answer = form.querySelector('[name="nwa_math"]');
					if (answer) {
						answer.setAttribute('aria-label', 'What is ' + k.a + ' plus ' + k.b + '?');
					}
				});
			})
			.catch(function () {});
	}

	forms.forEach(function (form) {
		form.addEventListener('submit', function (e) {
			if (!form.checkValidity()) {
				e.preventDefault();
				form.reportValidity();
				return;
			}
			var loading = form.querySelector('.nwa-loading');
			var button = form.querySelector('.nwa-submit');
			if (button) {
				button.disabled = true;
			}
			if (!loading) {
				return;
			}
			loading.hidden = false;
			var steps = loading.querySelectorAll('.nwa-steps li');
			var i = 0;
			var tick = function () {
				steps.forEach(function (step, n) {
					step.classList.toggle('is-done', n < i);
					step.classList.toggle('is-active', n === i);
				});
				if (i < steps.length - 1) {
					i++;
					setTimeout(tick, 2600 + Math.random() * 1600);
				}
			};
			tick();
		});
	});

	// Opening an emailed report link: bring the results into view.
	var results = document.querySelector('.nwa-results');
	if (results && location.hash === '#nabia-audit') {
		setTimeout(function () {
			results.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}, 300);
	}
})();
