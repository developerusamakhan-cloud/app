/**
 * Mobile menu toggle and chat buttons.
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

// Buttons that open the chat popup, or open it and ask a question.
document.addEventListener(
	'click',
	function ( e ) {
		var el = e.target.closest ? e.target.closest( '[data-open-chat], [data-ask]' ) : null;
		if ( ! el || ! window.AisaChat ) {
			return;
		}
		e.preventDefault();
		var question = el.getAttribute( 'data-ask' );
		if ( question ) {
			window.AisaChat.send( question );
		} else {
			window.AisaChat.open();
		}
	}
);
