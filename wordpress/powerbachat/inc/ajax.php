<?php
/**
 * AJAX for the theme's forms: contact, tariff alerts and the lead popup.
 *
 * The forms post to admin-ajax.php, which security plugins and hosts almost never
 * block for visitors (unlike the REST API). The same handlers also stay available
 * under /wp-json/powerbachat/v1/ for anything that already uses them.
 *
 * The contact form also works with JavaScript switched off: it posts to
 * admin-post.php and comes back to the page with a message.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX action name => handler.
 *
 * @return array
 */
function powerbachat_ajax_actions() {
	return array(
		'pb_contact'   => 'powerbachat_rest_contact',
		'pb_subscribe' => 'powerbachat_rest_subscribe',
		'pb_lead'      => 'powerbachat_rest_lead',
	);
}

/**
 * Run a form handler with the posted fields.
 *
 * @param string $action AJAX action.
 * @return array Response data and HTTP status.
 */
function powerbachat_run_form( $action ) {
	$actions = powerbachat_ajax_actions();
	$request = new WP_REST_Request( 'POST' );
	foreach ( wp_unslash( $_POST ) as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( is_scalar( $value ) && 'action' !== $key ) {
			$request->set_param( sanitize_key( $key ), (string) $value );
		}
	}
	$response = call_user_func( $actions[ $action ], $request );
	return array( $response->get_data(), $response->get_status() );
}

/**
 * admin-ajax.php handler.
 */
function powerbachat_ajax_form() {
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( powerbachat_ajax_actions()[ $action ] ) ) {
		wp_send_json( array( 'ok' => false ), 400 );
	}
	nocache_headers();
	list( $data, $status ) = powerbachat_run_form( $action );
	wp_send_json( $data, $status );
}
foreach ( array_keys( powerbachat_ajax_actions() ) as $powerbachat_action ) {
	add_action( 'wp_ajax_' . $powerbachat_action, 'powerbachat_ajax_form' );
	add_action( 'wp_ajax_nopriv_' . $powerbachat_action, 'powerbachat_ajax_form' );
}

/**
 * Contact form without JavaScript: handle it, then go back to the page with a message.
 */
function powerbachat_contact_fallback() {
	list( $data ) = powerbachat_run_form( 'pb_contact' );
	$back = wp_get_referer();
	$back = $back ? $back : home_url( '/contact-us/' );
	$back = remove_query_arg( array( 'pb_sent', 'pb_error' ), $back );
	$back = ! empty( $data['ok'] )
		? add_query_arg( 'pb_sent', '1', $back )
		: add_query_arg( 'pb_error', rawurlencode( isset( $data['message'] ) ? $data['message'] : '' ), $back );
	wp_safe_redirect( $back . '#contact-form' );
	exit;
}
add_action( 'admin_post_pb_contact', 'powerbachat_contact_fallback' );
add_action( 'admin_post_nopriv_pb_contact', 'powerbachat_contact_fallback' );
