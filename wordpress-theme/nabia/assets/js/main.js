/**
 * Nabia theme interactions. No dependencies.
 */
(function () {
	'use strict';

	var doc = document.documentElement;
	var body = document.body;
	var settings = window.nabiaSettings || {};
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

	function $(sel, ctx) {
		return (ctx || document).querySelector(sel);
	}

	function $$(sel, ctx) {
		return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
	}

	/* ------------------------------------------------------------------
	 * Preloader → then start hero animation.
	 * ------------------------------------------------------------------ */
	function ready() {
		body.classList.remove('is-loading');
		body.classList.add('is-ready');
	}

	var preloader = $('.preloader');
	if (preloader && settings.preloader && !reduceMotion && !sessionStorageGet('nabiaSeen')) {
		body.classList.add('is-loading');
		var countEl = $('.preloader-count', preloader);
		var start = performance.now();
		var duration = 1400;
		(function tick(now) {
			var p = Math.min(1, (now - start) / duration);
			var eased = 1 - Math.pow(1 - p, 3);
			countEl.textContent = Math.round(eased * 100);
			if (p < 1) {
				requestAnimationFrame(tick);
			} else {
				preloader.classList.add('is-done');
				sessionStorageSet('nabiaSeen', '1');
				setTimeout(ready, 350);
				setTimeout(function () {
					preloader.remove();
				}, 1000);
			}
		})(start);
	} else {
		if (preloader) {
			preloader.remove();
		}
		requestAnimationFrame(ready);
	}

	function sessionStorageGet(key) {
		try {
			return window.sessionStorage.getItem(key);
		} catch (e) {
			return null;
		}
	}

	function sessionStorageSet(key, value) {
		try {
			window.sessionStorage.setItem(key, value);
		} catch (e) {
			/* ignore */
		}
	}

	/* ------------------------------------------------------------------
	 * Header: shrink on scroll, hide on scroll down, progress bar.
	 * ------------------------------------------------------------------ */
	var header = $('.site-header');
	var progress = $('.scroll-progress');
	var lastY = window.scrollY;
	var ticking = false;
	var parallaxEls = reduceMotion ? [] : $$('[data-parallax]');

	function onScroll() {
		var y = window.scrollY;
		var max = doc.scrollHeight - window.innerHeight;

		if (header) {
			header.classList.toggle('is-scrolled', y > 20);
			var hide = y > lastY && y > 400 && !body.classList.contains('menu-open');
			header.classList.toggle('is-hidden', hide);
		}
		if (progress) {
			progress.style.transform = 'scaleX(' + (max > 0 ? y / max : 0) + ')';
		}

		parallaxEls.forEach(function (el) {
			var rect = el.getBoundingClientRect();
			var center = rect.top + rect.height / 2 - window.innerHeight / 2;
			var speed = parseFloat(el.getAttribute('data-parallax')) || 0;
			el.style.translate = '0 ' + (center * speed * -1).toFixed(1) + 'px';
		});

		lastY = y;
		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if (!ticking) {
				requestAnimationFrame(onScroll);
				ticking = true;
			}
		},
		{ passive: true }
	);
	onScroll();

	/* ------------------------------------------------------------------
	 * Mobile menu.
	 * ------------------------------------------------------------------ */
	var toggle = $('.menu-toggle');
	var mobileMenu = $('#mobile-menu');

	function setMenu(open) {
		if (!toggle || !mobileMenu) {
			return;
		}
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		body.classList.toggle('menu-open', open);
		if (open) {
			mobileMenu.hidden = false;
			requestAnimationFrame(function () {
				mobileMenu.classList.add('is-open');
			});
			body.style.overflow = 'hidden';
		} else {
			mobileMenu.classList.remove('is-open');
			body.style.overflow = '';
			setTimeout(function () {
				if (!mobileMenu.classList.contains('is-open')) {
					mobileMenu.hidden = true;
				}
			}, 700);
		}
	}

	if (toggle && mobileMenu) {
		toggle.addEventListener('click', function () {
			setMenu(toggle.getAttribute('aria-expanded') !== 'true');
		});
		mobileMenu.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				setMenu(false);
			}
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && body.classList.contains('menu-open')) {
				setMenu(false);
				toggle.focus();
			}
		});
	}

	/* ------------------------------------------------------------------
	 * Split headings into words for a staggered reveal.
	 * ------------------------------------------------------------------ */
	$$('[data-split]').forEach(function (el) {
		var words = el.textContent.trim().split(/\s+/);
		el.setAttribute('aria-label', el.textContent.trim());
		el.textContent = '';
		words.forEach(function (word, i) {
			var outer = document.createElement('span');
			outer.className = 'split-word';
			outer.setAttribute('aria-hidden', 'true');
			var inner = document.createElement('span');
			inner.style.setProperty('--w', i);
			inner.textContent = word;
			outer.appendChild(inner);
			el.appendChild(outer);
			if (i < words.length - 1) {
				el.appendChild(document.createTextNode(' '));
			}
		});
	});

	/* ------------------------------------------------------------------
	 * Reveal on scroll + counters + skill bars.
	 * ------------------------------------------------------------------ */
	function countUp(el) {
		var target = parseFloat(el.getAttribute('data-count'));
		if (isNaN(target) || reduceMotion) {
			return;
		}
		var decimals = (el.getAttribute('data-count').split('.')[1] || '').length;
		var start = performance.now();
		var duration = 1800;
		(function tick(now) {
			var p = Math.min(1, (now - start) / duration);
			var eased = 1 - Math.pow(1 - p, 4);
			el.textContent = (target * eased).toFixed(decimals);
			if (p < 1) {
				requestAnimationFrame(tick);
			}
		})(start);
	}

	var revealTargets = $$('[data-reveal], [data-split], .skills');

	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}
					var el = entry.target;
					el.classList.add('is-visible');
					$$('[data-count]', el).forEach(countUp);
					io.unobserve(el);
				});
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
		);
		revealTargets.forEach(function (el) {
			io.observe(el);
		});
	} else {
		revealTargets.forEach(function (el) {
			el.classList.add('is-visible');
		});
	}

	/* ------------------------------------------------------------------
	 * Hero rotating words.
	 * ------------------------------------------------------------------ */
	var rotator = $('.rotator');
	if (rotator && !reduceMotion) {
		var words = [];
		try {
			words = JSON.parse(rotator.getAttribute('data-words')) || [];
		} catch (e) {
			words = [];
		}
		var wordEl = $('.rotator-word', rotator);
		var index = 0;
		if (words.length > 1 && wordEl) {
			setInterval(function () {
				wordEl.classList.add('is-out');
				setTimeout(function () {
					index = (index + 1) % words.length;
					wordEl.textContent = words[index];
					wordEl.classList.remove('is-out');
					wordEl.classList.add('is-in');
					void wordEl.offsetWidth; // Restart transition.
					wordEl.classList.remove('is-in');
				}, 450);
			}, 2600);
		}
	}

	/* ------------------------------------------------------------------
	 * Giant footer name: scale to exactly fill the container width.
	 * ------------------------------------------------------------------ */
	var giant = $('.footer-giant');
	var giantText = giant ? $('span', giant) : null;

	function fitGiant() {
		if (!giant || !giantText) {
			return;
		}
		giant.style.fontSize = '100px';
		var ratio = giant.clientWidth / giantText.scrollWidth;
		giant.style.fontSize = Math.min(100 * ratio, 320) + 'px';
	}

	if (giant) {
		fitGiant();
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(fitGiant);
		}
		window.addEventListener('resize', fitGiant);
	}

	/* ------------------------------------------------------------------
	 * Testimonials slider: buttons + drag to scroll.
	 * ------------------------------------------------------------------ */
	var slider = $('[data-slider]');
	if (slider) {
		var step = function () {
			var card = slider.firstElementChild;
			return card ? card.getBoundingClientRect().width + 20 : 320;
		};
		$$('[data-slide]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var dir = btn.getAttribute('data-slide') === 'next' ? 1 : -1;
				var atEnd = slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 4;
				if (dir === 1 && atEnd) {
					slider.scrollTo({ left: 0, behavior: 'smooth' });
				} else {
					slider.scrollBy({ left: dir * step(), behavior: 'smooth' });
				}
			});
		});

		var down = false;
		var startX = 0;
		var startScroll = 0;
		slider.addEventListener('pointerdown', function (e) {
			if (e.pointerType !== 'mouse') {
				return;
			}
			down = true;
			startX = e.clientX;
			startScroll = slider.scrollLeft;
			slider.classList.add('is-dragging');
		});
		window.addEventListener('pointermove', function (e) {
			if (down) {
				slider.scrollLeft = startScroll - (e.clientX - startX);
			}
		});
		window.addEventListener('pointerup', function () {
			down = false;
			slider.classList.remove('is-dragging');
		});
	}

	/* ------------------------------------------------------------------
	 * Pointer-only effects: cursor, magnetic buttons, tilt, spotlight.
	 * ------------------------------------------------------------------ */
	if (!finePointer || reduceMotion) {
		return;
	}

	// Custom cursor.
	var cursor = $('.cursor');
	if (cursor && settings.cursor) {
		var label = $('.cursor-label', cursor);
		var cx = window.innerWidth / 2;
		var cy = window.innerHeight / 2;
		var tx = cx;
		var ty = cy;

		window.addEventListener('pointermove', function (e) {
			tx = e.clientX;
			ty = e.clientY;
			cursor.classList.add('is-visible');
		});
		document.addEventListener('pointerleave', function () {
			cursor.classList.remove('is-visible');
		});

		(function loop() {
			cx += (tx - cx) * 0.2;
			cy += (ty - cy) * 0.2;
			cursor.style.transform = 'translate3d(' + cx + 'px,' + cy + 'px,0)';
			requestAnimationFrame(loop);
		})();

		document.addEventListener('pointerover', function (e) {
			var labelled = e.target.closest('[data-cursor]');
			var interactive = e.target.closest('a, button, summary, input, textarea, select, label');
			if (labelled) {
				label.textContent = labelled.getAttribute('data-cursor');
				cursor.classList.add('has-label');
				cursor.classList.remove('is-hover');
			} else {
				cursor.classList.remove('has-label');
				cursor.classList.toggle('is-hover', !!interactive);
			}
		});
	}

	// Magnetic elements.
	$$('[data-magnetic]').forEach(function (el) {
		el.addEventListener('pointermove', function (e) {
			var r = el.getBoundingClientRect();
			var x = e.clientX - r.left - r.width / 2;
			var y = e.clientY - r.top - r.height / 2;
			el.style.transform = 'translate(' + x * 0.25 + 'px,' + y * 0.35 + 'px)';
		});
		el.addEventListener('pointerleave', function () {
			el.style.transform = '';
		});
	});

	// 3D tilt + service card spotlight origin.
	$$('[data-tilt]').forEach(function (el) {
		var isPortrait = el.classList.contains('portrait');
		el.addEventListener('pointermove', function (e) {
			var r = el.getBoundingClientRect();
			var px = (e.clientX - r.left) / r.width;
			var py = (e.clientY - r.top) / r.height;
			el.style.setProperty('--mx', px * 100 + '%');
			el.style.setProperty('--my', py * 100 + '%');
			if (isPortrait) {
				el.style.transform = 'perspective(900px) rotateY(' + (px - 0.5) * 10 + 'deg) rotateX(' + (0.5 - py) * 10 + 'deg)';
			}
		});
		el.addEventListener('pointerleave', function () {
			el.style.transform = '';
		});
	});

	// Contact card glow follows the pointer.
	var contact = $('.contact-card');
	if (contact) {
		contact.addEventListener('pointermove', function (e) {
			var r = contact.getBoundingClientRect();
			contact.style.setProperty('--gx', e.clientX - r.left + 'px');
			contact.style.setProperty('--gy', e.clientY - r.top + 'px');
		});
	}
})();
