/**
 * Talkwyn review reminder on the Dashboard and Plugins screens.
 * "Write a review" opens WordPress.org and marks the reminder done; the close button means "Maybe later".
 */
( function () {
	'use strict';
	var notice = document.querySelector( '.talkwyn-review-notice' );
	if ( ! notice ) {
		return;
	}
	function ping( url ) {
		if ( url && window.fetch ) {
			window.fetch( url, { credentials: 'same-origin', keepalive: true } );
		}
	}
	notice.addEventListener( 'click', function ( e ) {
		var review = e.target.closest( '[data-talkwyn-review]' );
		if ( review ) {
			ping( review.getAttribute( 'data-talkwyn-review' ) );
			notice.style.display = 'none';
			return;
		}
		if ( e.target.closest( '.notice-dismiss' ) ) {
			ping( notice.getAttribute( 'data-later' ) );
		}
	} );
} )();
