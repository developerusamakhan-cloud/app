/* ClaimFairly: mobile menu, header shadow, "On this page" list. */
(function () {
	'use strict';

	var header = document.querySelector('.site-header');
	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('primary-nav');

	if (toggle && nav) {
		var setOpen = function (open) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			nav.classList.toggle('is-open', open);
		};
		toggle.addEventListener('click', function () {
			setOpen(toggle.getAttribute('aria-expanded') !== 'true');
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				setOpen(false);
				toggle.focus();
			}
		});
		document.addEventListener('click', function (e) {
			if (nav.classList.contains('is-open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
				setOpen(false);
			}
		});
	}

	if (header) {
		var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* Build the sidebar table of contents from the article's H2s. */
	var toc = document.querySelector('[data-toc]');
	var prose = document.querySelector('.prose');
	if (toc && prose) {
		var heads = Array.prototype.filter.call(prose.querySelectorAll('h2'), function (h) {
			return !h.closest('.cf-tool');
		});
		if (heads.length >= 2) {
			var list = toc.querySelector('ol');
			var links = [];
			heads.forEach(function (h, i) {
				if (!h.id) {
					h.id = (h.textContent || 'section').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 60) || 'section-' + i;
				}
				var li = document.createElement('li');
				var a = document.createElement('a');
				a.href = '#' + h.id;
				a.textContent = h.textContent;
				li.appendChild(a);
				list.appendChild(li);
				links.push(a);
			});
			toc.hidden = false;
			if ('IntersectionObserver' in window) {
				var io = new IntersectionObserver(function (entries) {
					entries.forEach(function (en) {
						if (en.isIntersecting) {
							links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + en.target.id); });
						}
					});
				}, { rootMargin: '-20% 0px -70% 0px' });
				heads.forEach(function (h) { io.observe(h); });
			}
		}
	}

	/* Reading progress bar on blog articles. */
	var bar = document.querySelector('.read-progress span');
	var article = document.querySelector('.entry--post .prose');
	if (bar && article) {
		var update = function () {
			var rect = article.getBoundingClientRect();
			var total = rect.height - window.innerHeight * 0.6;
			var done = Math.min(Math.max(-rect.top + window.innerHeight * 0.2, 0), Math.max(total, 1));
			bar.style.transform = 'scaleX(' + (done / Math.max(total, 1)).toFixed(4) + ')';
		};
		window.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();
	}

	/* Copy link button in the share box. */
	Array.prototype.forEach.call(document.querySelectorAll('[data-copy-url]'), function (btn) {
		btn.addEventListener('click', function () {
			var url = btn.getAttribute('data-copy-url');
			var label = btn.querySelector('span');
			var done = function () {
				if (!label) { return; }
				var old = label.textContent;
				label.textContent = 'Copied';
				setTimeout(function () { label.textContent = old; }, 1800);
			};
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(url).then(done);
			} else {
				window.prompt('Copy this link:', url);
			}
		});
	});
})();
