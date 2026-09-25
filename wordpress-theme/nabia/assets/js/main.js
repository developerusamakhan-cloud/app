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
	 * Smooth scrolling (Lenis). Native scrolling stays for touch screens,
	 * reduced-motion users and scrollable boxes marked data-lenis-prevent.
	 * ------------------------------------------------------------------ */
	var lenis = null;
	if (settings.smooth && window.Lenis && !reduceMotion) {
		$$('.video-list, textarea, select').forEach(function (el) {
			el.setAttribute('data-lenis-prevent-wheel', '');
		});
		// The menu and popups must scroll by wheel AND touch while page scrolling is paused.
		$$('.mobile-menu, .gchat-modal').forEach(function (el) {
			el.setAttribute('data-lenis-prevent', '');
		});
		lenis = new window.Lenis({
			lerp: 0.09,
			wheelMultiplier: 1,
			autoRaf: true,
			// The fixed-header gap comes from CSS scroll-padding-top, which Lenis respects.
			anchors: true
		});
		window.nabiaLenis = lenis;
	}

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
		if (lenis) {
			if (open) {
				lenis.stop();
			} else {
				lenis.start();
			}
		}
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
	// Pull trailing punctuation closer: this font spaces . , ? ! widely at large sizes.
	function appendTightText(el, text) {
		text.split(/([.,?!]+)/).forEach(function (part, i) {
			if (!part) {
				return;
			}
			if (i % 2) {
				var p = document.createElement('span');
				p.className = 'punct';
				p.textContent = part;
				el.appendChild(p);
			} else {
				el.appendChild(document.createTextNode(part));
			}
		});
	}

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
			appendTightText(inner, word);
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

	var revealTargets = $$('[data-reveal], [data-split], .skills, .footer-giant');

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
					wordEl.textContent = '';
					appendTightText(wordEl, words[index]);
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
	 * Reviews: clamp long texts with "Read more", and "Show more reviews".
	 * ------------------------------------------------------------------ */
	function clampReview(text) {
		if (text.hasAttribute('data-clamp-done')) {
			return;
		}
		text.setAttribute('data-clamp-done', '');
		text.classList.add('is-clamped');
		var p = $('p', text);
		if (!p || p.scrollHeight <= p.clientHeight + 2) {
			text.classList.remove('is-clamped');
			return;
		}
		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'review-readmore';
		btn.textContent = 'Read more';
		btn.addEventListener('click', function () {
			var open = text.classList.toggle('is-clamped');
			btn.textContent = open ? 'Read more' : 'Read less';
		});
		text.parentNode.appendChild(btn);
	}
	$$('[data-review-text]').forEach(function (text) {
		if (text.offsetParent !== null) {
			clampReview(text);
		}
	});

	$$('[data-review-more]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var wall = btn.closest('.section, .nabia-reviews-embed');
			wall = wall ? $('[data-review-wall]', wall) : null;
			if (wall) {
				wall.classList.add('is-expanded');
				$$('[data-review-text]', wall).forEach(clampReview);
			}
			btn.parentNode.remove();
		});
	});

	/* ------------------------------------------------------------------
	 * Video reviews: load YouTube only when visible, autoplay muted.
	 * ------------------------------------------------------------------ */
	function embedUrl(id, muted) {
		return 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) +
			'?autoplay=1&mute=' + (muted ? 1 : 0) +
			'&loop=1&playlist=' + encodeURIComponent(id) +
			'&playsinline=1&rel=0&modestbranding=1';
	}

	function loadVideo(frame, muted) {
		var id = frame.getAttribute('data-video-id');
		if (!id) {
			return;
		}
		var iframe = $('iframe', frame);
		if (!iframe) {
			iframe = document.createElement('iframe');
			iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture; fullscreen');
			iframe.setAttribute('allowfullscreen', '');
			iframe.setAttribute('title', 'Client video review');
			frame.appendChild(iframe);
		}
		iframe.src = embedUrl(id, muted);
		frame.classList.add('is-loaded');
		frame.classList.toggle('is-unmuted', !muted);

		if (!muted) {
			muteOthers(frame);
		}
	}

	// In a carousel only one video has sound: switching sound on for one mutes the rest.
	function muteOthers(frame) {
		var reel = frame.closest('[data-reel]');
		if (!reel) {
			return;
		}
		$$('.video-frame.is-unmuted', reel).forEach(function (other) {
			if (other === frame) {
				return;
			}
			if (other.classList.contains('is-file')) {
				$('video', other).muted = true;
				other.classList.remove('is-unmuted');
			} else {
				loadVideo(other, true);
			}
		});
	}

	/* Self-hosted videos: load when visible, play muted, pause when scrolled away. */
	function startFileVideo(frame) {
		var video = $('video', frame);
		var source = $('source', video);
		if (source && !source.getAttribute('src')) {
			source.setAttribute('src', source.getAttribute('data-src'));
			video.preload = 'auto';
			video.load();
		}
		var p = video.play();
		if (p && p.catch) {
			p.catch(function () {});
		}
		frame.classList.add('is-loaded');
	}

	$$('.video-frame.is-file').forEach(function (frame) {
		var video = $('video', frame);
		// Show clean muted autoplay; native controls appear once sound is on.
		video.removeAttribute('controls');

		if ('IntersectionObserver' in window && !reduceMotion) {
			new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							startFileVideo(frame);
						} else if (!video.paused) {
							video.pause();
						}
					});
				},
				{ threshold: 0.35 }
			).observe(frame);
		} else {
			video.setAttribute('controls', '');
			startFileVideo(frame);
		}

		function unmute() {
			startFileVideo(frame);
			video.muted = false;
			video.setAttribute('controls', '');
			frame.classList.add('is-unmuted');
			muteOthers(frame);
		}

		$('.video-sound', frame).addEventListener('click', unmute);
		video.addEventListener('click', function (e) {
			if (video.muted) {
				e.preventDefault();
				unmute();
			}
		});
	});

	var frames = $$('.video-frame[data-video-id]');
	if (frames.length) {
		if ('IntersectionObserver' in window && !reduceMotion) {
			var vio = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting && !entry.target.classList.contains('is-loaded')) {
							loadVideo(entry.target, true);
							vio.unobserve(entry.target);
						}
					});
				},
				{ threshold: 0.35 }
			);
			frames.forEach(function (frame) {
				// Shorts carousel: every video autoplays muted once it scrolls into view.
				var reelEl = frame.closest('[data-reel]');
				if (!reelEl || reelEl.hasAttribute('data-autoplay-all') || frame.hasAttribute('data-autoplay')) {
					vio.observe(frame);
				}
			});
		}

		frames.forEach(function (frame) {
			// Click on the poster (before autoplay kicked in) plays with sound.
			frame.addEventListener('click', function (e) {
				if (e.target.closest('.video-sound') || !frame.classList.contains('is-loaded')) {
					loadVideo(frame, false);
				}
			});
		});
	}

	$$('[data-video-stage]').forEach(function (stage) {
		var main = $('.video-frame', stage);
		var thumbs = $$('.video-thumb', stage);
		var nameEl = $('[data-caption-name]', stage);
		var metaEl = $('[data-caption-meta]', stage);
		var avatarEl = $('.video-caption-avatar', stage);
		var list = $('.video-list', stage);
		var current = 0;

		function select(index, withSound) {
			current = (index + thumbs.length) % thumbs.length;
			var thumb = thumbs[current];
			thumbs.forEach(function (t, i) {
				t.classList.toggle('is-active', i === current);
			});
			var id = thumb.getAttribute('data-id');
			main.setAttribute('data-video-id', id);
			var poster = $('img', main);
			if (poster) {
				poster.src = 'https://i.ytimg.com/vi/' + encodeURIComponent(id) + '/hqdefault.jpg';
			}
			var name = thumb.getAttribute('data-name');
			if (nameEl) {
				nameEl.textContent = name;
			}
			if (metaEl) {
				metaEl.textContent = thumb.getAttribute('data-meta');
			}
			if (avatarEl) {
				avatarEl.textContent = (name || '★').charAt(0);
			}
			// Keep the active item visible inside the playlist without scrolling the page.
			if (list) {
				var li = thumb.parentNode;
				if (list.scrollHeight > list.clientHeight) {
					list.scrollTo({ top: li.offsetTop - list.offsetTop - 8, behavior: 'smooth' });
				} else if (list.scrollWidth > list.clientWidth) {
					list.scrollTo({ left: li.offsetLeft - list.offsetLeft, behavior: 'smooth' });
				}
			}
			loadVideo(main, !withSound);
		}

		thumbs.forEach(function (thumb, i) {
			thumb.addEventListener('click', function () {
				select(i, true);
				if (window.innerWidth < 900) {
					main.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
			});
		});

		$$('[data-video-step]', stage).forEach(function (btn) {
			btn.addEventListener('click', function () {
				select(current + parseInt(btn.getAttribute('data-video-step'), 10), true);
			});
		});
	});

	/* ------------------------------------------------------------------
	 * Shorts carousel: arrows, dots and swipe (native scroll-snap).
	 * ------------------------------------------------------------------ */
	$$('[data-carousel]').forEach(function (carousel) {
		var track = $('[data-reel]', carousel);
		var dotsWrap = $('[data-carousel-dots]', carousel);
		if (!track) {
			return;
		}

		function cardWidth() {
			var card = track.firstElementChild;
			var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
			return card ? card.getBoundingClientRect().width + gap : track.clientWidth;
		}

		function pages() {
			return Math.max(1, Math.round((track.scrollWidth - track.clientWidth) / cardWidth()) + 1);
		}

		function current() {
			return Math.round(track.scrollLeft / cardWidth());
		}

		function renderDots() {
			if (!dotsWrap) {
				return;
			}
			var count = pages();
			dotsWrap.innerHTML = '';
			carousel.classList.toggle('is-static', count < 2);
			for (var i = 0; i < count; i++) {
				var dot = document.createElement('button');
				dot.type = 'button';
				dot.className = 'carousel-dot' + (i === current() ? ' is-active' : '');
				dot.setAttribute('aria-label', 'Go to video ' + (i + 1));
				dot.addEventListener('click', goTo.bind(null, i));
				dotsWrap.appendChild(dot);
			}
		}

		function goTo(index) {
			var count = pages();
			index = (index + count) % count;
			track.scrollTo({ left: index * cardWidth(), behavior: 'smooth' });
		}

		$$('[data-carousel-step]', carousel).forEach(function (btn) {
			btn.addEventListener('click', function () {
				goTo(current() + parseInt(btn.getAttribute('data-carousel-step'), 10));
			});
		});

		var scrollTimer;
		track.addEventListener('scroll', function () {
			clearTimeout(scrollTimer);
			scrollTimer = setTimeout(function () {
				$$('.carousel-dot', dotsWrap).forEach(function (dot, i) {
					dot.classList.toggle('is-active', i === current());
				});
			}, 80);
		}, { passive: true });

		renderDots();
		window.addEventListener('resize', renderDots);
	});

	/* ------------------------------------------------------------------
	 * Pricing tabs (websites / monthly maintenance).
	 * ------------------------------------------------------------------ */
	$$('.pricing-tabs').forEach(function (tablist) {
		var tabs = $$('[role="tab"]', tablist);
		function select(tab) {
			tabs.forEach(function (t) {
				var on = t === tab;
				t.setAttribute('aria-selected', on ? 'true' : 'false');
				t.tabIndex = on ? 0 : -1;
				var panel = document.getElementById(t.getAttribute('aria-controls'));
				if (panel) {
					panel.hidden = !on;
					if (on) {
						$$('[data-reveal]', panel).forEach(function (el) {
							el.classList.add('is-visible');
						});
					}
				}
			});
		}
		tabs.forEach(function (tab, i) {
			tab.addEventListener('click', function () {
				select(tab);
			});
			tab.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
					var next = tabs[(i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
					next.focus();
					select(next);
				}
			});
		});
	});

	/* ------------------------------------------------------------------
	 * Blog post: "On this page" contents built from the H2 headings.
	 * ------------------------------------------------------------------ */
	var toc = $('[data-toc]');
	var tocSource = $('[data-toc-source]');
	if (toc && tocSource) {
		var headings = $$('h2', tocSource);
		if (headings.length >= 2) {
			var list = $('ol', toc);
			headings.forEach(function (h, i) {
				if (!h.id) {
					h.id = 'section-' + (i + 1) + '-' + h.textContent.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 40);
				}
				var li = document.createElement('li');
				var a = document.createElement('a');
				a.href = '#' + h.id;
				a.textContent = h.textContent;
				li.appendChild(a);
				list.appendChild(li);
			});
			toc.hidden = false;

			if ('IntersectionObserver' in window) {
				var links = $$('a', list);
				var tocObserver = new IntersectionObserver(
					function (entries) {
						entries.forEach(function (entry) {
							if (entry.isIntersecting) {
								links.forEach(function (l) {
									l.classList.toggle('is-active', l.getAttribute('href') === '#' + entry.target.id);
								});
							}
						});
					},
					{ rootMargin: '0px 0px -70% 0px' }
				);
				headings.forEach(function (h) {
					tocObserver.observe(h);
				});
			}
		}
	}

	/* ------------------------------------------------------------------
	 * Portfolio page: instant category filter + "Show more".
	 * ------------------------------------------------------------------ */
	$$('[data-portfolio]').forEach(function (section) {
		var items = $$('.pf-item', section);
		var buttons = $$('[data-filter]', section);
		var more = $('[data-portfolio-more]', section);
		var empty = $('.pf-empty', section);
		var expanded = false;

		function apply(filter) {
			var shown = 0;
			items.forEach(function (item) {
				var cats = (item.getAttribute('data-cats') || '').split(' ');
				var match = filter === '*' || cats.indexOf(filter) !== -1;
				var collapsed = filter === '*' && !expanded && item.classList.contains('is-more');
				item.hidden = !match || collapsed;
				if (!item.hidden) {
					shown++;
					item.classList.add('is-visible');
					$$('[data-reveal]', item).forEach(function (el) {
						el.classList.add('is-visible');
					});
				}
			});
			if (empty) {
				empty.hidden = shown > 0;
			}
			if (more) {
				more.parentNode.hidden = expanded || filter !== '*';
			}
		}

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				buttons.forEach(function (b) {
					b.classList.toggle('is-active', b === btn);
					b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
				});
				apply(btn.getAttribute('data-filter'));
			});
		});

		if (more) {
			more.addEventListener('click', function () {
				expanded = true;
				apply('*');
			});
		}
		apply('*');
	});

	/* ------------------------------------------------------------------
	 * Intro video: play muted while visible, pause when scrolled away.
	 * ------------------------------------------------------------------ */
	$$('[data-intro-video]').forEach(function (video) {
		var frame = video.parentNode;
		var soundBtn = $('[data-intro-sound]', frame);
		var figure = video.closest('.intro-video');
		video.removeAttribute('controls');

		// Landscape clips switch the phone mockup to a widescreen card.
		function checkShape() {
			if (figure && video.videoWidth && video.videoWidth > video.videoHeight) {
				figure.classList.add('is-landscape');
			}
		}
		video.addEventListener('loadedmetadata', checkShape);
		checkShape();

		function play() {
			var p = video.play();
			if (p && p.catch) {
				p.catch(function () {
					video.setAttribute('controls', '');
				});
			}
		}

		if ('IntersectionObserver' in window && !reduceMotion) {
			new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							play();
						} else {
							video.pause();
						}
					});
				},
				{ threshold: 0.4 }
			).observe(video);
		} else {
			video.setAttribute('controls', '');
		}

		function unmute() {
			video.muted = false;
			video.setAttribute('controls', '');
			frame.classList.add('is-unmuted');
			play();
		}

		if (soundBtn) {
			soundBtn.addEventListener('click', unmute);
		}
		video.addEventListener('click', function () {
			if (video.muted) {
				unmute();
			}
		});
	});

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

	// Cached pages can hold an old copy of the form: fetch fresh security fields.
	var forms = $$('[data-nabia-form]');
	if (forms.length && settings.formKeys && !document.body.classList.contains('logged-in') && window.fetch) {
		fetch(settings.formKeys, { credentials: 'omit', cache: 'no-store' })
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
					set('nabia_contact_nonce', k.contact);
					set('nabia_audit_nonce', k.audit);
					set('nabia_t', k.token);
					if (form.querySelector('[name="nabia_cq"]') && k.cq) {
						set('nabia_cq', k.cq);
						var q = form.querySelector('.cf-math-q');
						if (q) {
							q.innerHTML = k.a + ' <b>+</b> ' + k.b + ' <b>=</b>';
						}
						var answer = form.querySelector('[name="cf_math"]');
						if (answer) {
							answer.setAttribute('aria-label', 'What is ' + k.a + ' plus ' + k.b + '?');
						}
					}
				});
			})
			.catch(function () {});
	}

	// Google Chat buttons open a popup with the chat email, a Copy button and "Copy and open".
	var gchat = document.getElementById('gchat-modal');
	var copyText = function (text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}
		var area = document.createElement('textarea');
		area.value = text;
		area.setAttribute('readonly', '');
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild(area);
		area.select();
		try {
			document.execCommand('copy');
		} catch (err) {}
		area.remove();
		return Promise.resolve();
	};
	if (gchat && typeof gchat.showModal === 'function') {
		var email = ($('[data-gchat-email]', gchat) || {}).textContent || '';
		var copyBtn = $('[data-gchat-copy]', gchat);
		var copyLabel = copyBtn ? copyBtn.querySelector('span').textContent : '';
		var markCopied = function () {
			if (!copyBtn) {
				return;
			}
			copyBtn.classList.add('is-done');
			copyBtn.querySelector('span').textContent = copyBtn.getAttribute('data-done');
			setTimeout(function () {
				copyBtn.classList.remove('is-done');
				copyBtn.querySelector('span').textContent = copyLabel;
			}, 2500);
		};
		$$('[data-copy]').forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				gchat.showModal();
			});
		});
		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				copyText(email.trim()).then(markCopied).catch(function () {});
			});
		}
		var openBtn = $('[data-gchat-open]', gchat);
		if (openBtn) {
			openBtn.addEventListener('click', function () {
				copyText(email.trim()).then(markCopied).catch(function () {});
			});
		}
		$$('[data-gchat-close]', gchat).forEach(function (btn) {
			btn.addEventListener('click', function () {
				gchat.close();
			});
		});
		gchat.addEventListener('click', function (e) {
			if (e.target === gchat) {
				gchat.close();
			}
		});
	}

	// "Live chat" buttons open the Tawk.to chat window. Without Tawk.to they go to the contact page.
	$$('[data-livechat]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var api = window.Tawk_API;
			if (api && typeof api.maximize === 'function') {
				api.maximize();
				return;
			}
			if (api && document.querySelector('script[src*="embed.tawk.to"]')) {
				// Tawk.to is still loading: open it as soon as it is ready.
				var previous = api.onLoad;
				api.onLoad = function () {
					if (typeof previous === 'function') {
						previous();
					}
					api.maximize();
				};
				return;
			}
			if (settings.chatUrl) {
				window.location.href = settings.chatUrl;
			}
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
