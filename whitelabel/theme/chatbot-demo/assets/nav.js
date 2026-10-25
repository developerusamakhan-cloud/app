( function () {
	var btn = document.querySelector( '.nav-toggle' );
	var nav = document.getElementById( 'main-nav' );
	if ( ! btn || ! nav ) {
		return;
	}
	btn.addEventListener( 'click', function () {
		var open = nav.classList.toggle( 'is-open' );
		btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );
}() );

// "Ask the chat" buttons open the floating chat when it is on the page.
document.addEventListener( 'click', function ( e ) {
	var link = e.target.closest ? e.target.closest( '[data-open-chat]' ) : null;
	if ( ! link ) {
		return;
	}
	var launcher = document.querySelector( '.twc--floating .twc-launcher' ) || document.querySelector( '.twc-launcher' );
	if ( launcher ) {
		e.preventDefault();
		var panel = document.querySelector( '.twc--floating .twc-panel' );
		if ( ! panel || panel.hidden || ! panel.offsetParent ) {
			launcher.click();
		}
	}
} );
