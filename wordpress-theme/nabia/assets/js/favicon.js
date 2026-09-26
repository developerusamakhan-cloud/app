/**
 * Nabia animated favicon.
 *
 * - On load (and when the visitor comes back) the "N" draws itself in and the dot pops.
 * - Every few seconds the dot does a small bounce, like the logo.
 * - When the visitor switches to another tab, the icon waves 👋 to call them back.
 * - Respects "reduce motion": then the icon stays still.
 *
 * Colours come from the active colour scheme (nabiaFavicon: ink, accent, second).
 */
( function () {
	'use strict';

	var cfg = window.nabiaFavicon || {};
	var canvas = document.createElement( 'canvas' );
	if ( ! canvas.getContext || typeof Path2D === 'undefined' ) {
		return;
	}

	var SIZE = 64;
	var SCALE = SIZE / 48;
	var ctx = canvas.getContext( '2d' );
	var N_PATH = new Path2D( 'M15 34V15.5a1.5 1.5 0 0 1 2.6-1l12.8 15a1.5 1.5 0 0 0 2.6-1V14' );
	var N_LENGTH = 58;
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	canvas.width = SIZE;
	canvas.height = SIZE;

	var ink = cfg.ink || '#1a1433';
	var accent = cfg.accent || '#7c3aed';
	var second = cfg.second || '#ec4899';

	// One icon link we control; the static ones stay as fallback for browsers without JS.
	var link = document.createElement( 'link' );
	link.rel = 'icon';
	link.type = 'image/png';
	var ready = false;

	function mount() {
		if ( ready ) {
			return;
		}
		ready = true;
		var old = document.querySelectorAll( 'link[rel~="icon"]' );
		for ( var i = 0; i < old.length; i++ ) {
			old[ i ].parentNode.removeChild( old[ i ] );
		}
		document.head.appendChild( link );
	}

	function roundRect( x, y, w, h, r ) {
		ctx.beginPath();
		ctx.moveTo( x + r, y );
		ctx.arcTo( x + w, y, x + w, y + h, r );
		ctx.arcTo( x + w, y + h, x, y + h, r );
		ctx.arcTo( x, y + h, x, y, r );
		ctx.arcTo( x, y, x + w, y, r );
		ctx.closePath();
	}

	function dotFill() {
		var g = ctx.createLinearGradient( 32, 32, 41, 40 );
		g.addColorStop( 0, accent );
		g.addColorStop( 1, second );
		return g;
	}

	/**
	 * Draw one frame of the monogram.
	 *
	 * @param {number} draw  0..1 how much of the "N" is drawn.
	 * @param {number} dot   Dot scale (0 = hidden, 1 = normal).
	 * @param {number} lift  Dot lift in icon units (bounce).
	 * @param {boolean} ring Show a small glow ring around the dot.
	 */
	function frame( draw, dot, lift, ring ) {
		ctx.setTransform( 1, 0, 0, 1, 0, 0 );
		ctx.clearRect( 0, 0, SIZE, SIZE );
		ctx.setTransform( SCALE, 0, 0, SCALE, 0, 0 );

		roundRect( 1, 1, 46, 46, 15 );
		ctx.fillStyle = ink;
		ctx.fill();

		if ( draw > 0 ) {
			ctx.save();
			ctx.strokeStyle = '#fff';
			ctx.lineWidth = 4.5;
			ctx.lineCap = 'round';
			ctx.lineJoin = 'round';
			ctx.setLineDash( [ N_LENGTH, N_LENGTH ] );
			ctx.lineDashOffset = N_LENGTH * ( 1 - draw );
			ctx.stroke( N_PATH );
			ctx.restore();
		}

		if ( ring ) {
			ctx.beginPath();
			ctx.arc( 36.5, 36 - lift, 7, 0, Math.PI * 2 );
			ctx.fillStyle = accent;
			ctx.globalAlpha = 0.35;
			ctx.fill();
			ctx.globalAlpha = 1;
		}

		if ( dot > 0 ) {
			ctx.beginPath();
			ctx.arc( 36.5, 36 - lift, 4 * dot, 0, Math.PI * 2 );
			ctx.fillStyle = dotFill();
			ctx.fill();
		}

		push();
	}

	/** Waving hand, used while the visitor is on another tab. */
	function wave( tilt ) {
		ctx.setTransform( 1, 0, 0, 1, 0, 0 );
		ctx.clearRect( 0, 0, SIZE, SIZE );
		ctx.setTransform( SCALE, 0, 0, SCALE, 0, 0 );
		roundRect( 1, 1, 46, 46, 15 );
		ctx.fillStyle = ink;
		ctx.fill();
		ctx.save();
		ctx.translate( 24, 30 );
		ctx.rotate( tilt );
		ctx.font = '26px "Apple Color Emoji","Segoe UI Emoji","Noto Color Emoji",sans-serif';
		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';
		ctx.fillText( '\uD83D\uDC4B', 0, -6 );
		ctx.restore();
		// Small notification dot.
		ctx.beginPath();
		ctx.arc( 39, 9, 5, 0, Math.PI * 2 );
		ctx.fillStyle = dotFill();
		ctx.fill();
		push();
	}

	var lastHref = '';
	function push() {
		var href = canvas.toDataURL( 'image/png' );
		if ( href !== lastHref ) {
			lastHref = href;
			link.href = href;
		}
	}

	// ---------- Animations ----------
	var raf = 0;
	var idleTimer = 0;
	var awayTimer = 0;

	function stopAll() {
		cancelAnimationFrame( raf );
		clearTimeout( idleTimer );
		clearInterval( awayTimer );
	}

	function easeOut( t ) {
		return 1 - Math.pow( 1 - t, 3 );
	}

	function backOut( t ) {
		var c = 1.9;
		return 1 + ( c + 1 ) * Math.pow( t - 1, 3 ) + c * Math.pow( t - 1, 2 );
	}

	function animate( duration, step, done ) {
		var start = 0;
		var last = 0;
		function tick( now ) {
			if ( ! start ) {
				start = now;
			}
			// About 30 frames per second is plenty for a 16px icon.
			if ( now - last >= 33 || now - start >= duration ) {
				last = now;
				step( Math.min( 1, ( now - start ) / duration ) );
			}
			if ( now - start < duration ) {
				raf = requestAnimationFrame( tick );
			} else if ( done ) {
				done();
			}
		}
		raf = requestAnimationFrame( tick );
	}

	function drawIn() {
		stopAll();
		animate( 1300, function ( t ) {
			var n = easeOut( Math.min( 1, t / 0.7 ) );
			var d = t < 0.65 ? 0 : backOut( ( t - 0.65 ) / 0.35 );
			frame( n, Math.max( 0, d ), 0, false );
		}, scheduleIdle );
	}

	function bounce() {
		animate( 900, function ( t ) {
			// Two hops, the second smaller.
			var lift = t < 0.55 ? Math.sin( ( t / 0.55 ) * Math.PI ) * 6 : Math.sin( ( ( t - 0.55 ) / 0.45 ) * Math.PI ) * 2.5;
			var squash = t > 0.5 && t < 0.6 ? 1.12 : 1;
			frame( 1, squash, lift, t < 0.55 && lift > 3 );
		}, scheduleIdle );
	}

	function scheduleIdle() {
		clearTimeout( idleTimer );
		idleTimer = setTimeout( function () {
			if ( ! document.hidden ) {
				bounce();
			}
		}, 6000 + Math.random() * 3000 );
	}

	function away() {
		stopAll();
		var tilt = [ -0.35, 0.3, -0.2, 0.3 ];
		var i = 0;
		wave( tilt[ 0 ] );
		// Hidden tabs only run timers about once a second, which suits a wave.
		awayTimer = setInterval( function () {
			i = ( i + 1 ) % 6;
			if ( i < 4 ) {
				wave( tilt[ i ] );
			} else {
				frame( 1, 1, 0, 4 === i );
			}
		}, 900 );
	}

	function start() {
		mount();
		if ( reduceMotion ) {
			frame( 1, 1, 0, false );
			return;
		}
		drawIn();
		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				away();
			} else {
				drawIn();
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
