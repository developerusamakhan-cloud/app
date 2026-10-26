/**
 * Mobile menu toggle.
 *
 * @package Chatbot_Demo
 */
( function () {
	var btn = document.querySelector( '.nav-toggle' );
	var nav = document.getElementById( 'main-nav' );
	if ( ! btn || ! nav ) {
		return;
	}
	btn.addEventListener(
		'click',
		function () {
			var open = nav.classList.toggle( 'is-open' );
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}
	);
}() );
