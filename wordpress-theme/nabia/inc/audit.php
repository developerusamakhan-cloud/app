<?php
/**
 * Free website audit requests.
 *
 * The form posts to the page it is on (handled in inc/forms.php). Each request is emailed to the contact email and
 * saved under Dashboard → Audit Requests, so nothing is lost if an email goes missing.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Private post type that stores audit requests.
 */
function nabia_register_audit_requests() {
	register_post_type(
		'audit_request',
		array(
			'labels'          => array(
				'name'          => __( 'Audit Requests', 'nabia' ),
				'singular_name' => __( 'Audit Request', 'nabia' ),
				'menu_name'     => __( 'Audit Requests', 'nabia' ),
				'all_items'     => __( 'Audit Requests', 'nabia' ),
				'edit_item'     => __( 'Audit request', 'nabia' ),
				'not_found'     => __( 'No audit requests yet.', 'nabia' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 27,
			'menu_icon'       => 'dashicons-search',
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor' ),
		)
	);
}
add_action( 'init', 'nabia_register_audit_requests' );

/**
 * Goals offered in the form.
 *
 * @return array
 */
function nabia_audit_goals() {
	return array(
		'leads'    => __( 'Get more enquiries / sales', 'nabia' ),
		'speed'    => __( 'Make it faster', 'nabia' ),
		'seo'      => __( 'Rank better on Google', 'nabia' ),
		'redesign' => __( 'Refresh the design', 'nabia' ),
		'fix'      => __( 'Fix something that is broken', 'nabia' ),
		'other'    => __( 'Something else', 'nabia' ),
	);
}

/**
 * Handle a submitted audit request.
 */
function nabia_handle_audit_request() {
	$back = wp_validate_redirect( (string) wp_get_raw_referer(), home_url( '/' ) );
	$back = remove_query_arg( 'audit', $back );

	$fail = function ( $code ) use ( $back ) {
		nabia_form_log( 'audit', $code );
		wp_safe_redirect( add_query_arg( 'audit', $code, $back ) . '#audit' );
		exit;
	};

	if ( ! isset( $_POST['nabia_audit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nabia_audit_nonce'] ) ), 'nabia_audit' ) ) {
		$fail( 'expired' );
	}

	// Honeypot: real people never fill this hidden field.
	if ( ! empty( $_POST['company_website'] ) ) {
		nabia_form_log( 'audit', 'honeypot' );
		wp_safe_redirect( add_query_arg( 'audit', 'sent', $back ) . '#audit' );
		exit;
	}

	$name    = isset( $_POST['audit_name'] ) ? sanitize_text_field( wp_unslash( $_POST['audit_name'] ) ) : '';
	$email   = isset( $_POST['audit_email'] ) ? sanitize_email( wp_unslash( $_POST['audit_email'] ) ) : '';
	$site    = isset( $_POST['audit_site'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['audit_site'] ) ) ) : '';
	$goal    = isset( $_POST['audit_goal'] ) ? sanitize_key( wp_unslash( $_POST['audit_goal'] ) ) : '';
	$message = isset( $_POST['audit_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['audit_message'] ) ) : '';

	if ( $site && ! preg_match( '#^https?://#i', $site ) ) {
		$site = 'https://' . $site;
	}
	$site = esc_url_raw( $site );
	if ( ! is_email( $email ) || '' === $site ) {
		$fail( 'invalid' );
	}

	// Max 3 requests per hour from one address.
	$ip_key = 'nabia_audit_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 3 ) {
		$fail( 'limit' );
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	$goals      = nabia_audit_goals();
	$goal_label = isset( $goals[ $goal ] ) ? $goals[ $goal ] : '';

	$body = sprintf( "Website: %s\nEmail: %s", $site, $email );
	if ( $name ) {
		$body .= "\nName: " . $name;
	}
	if ( $goal_label ) {
		$body .= "\nMain goal: " . $goal_label;
	}
	if ( $message ) {
		$body .= "\n\nMessage:\n" . $message;
	}

	wp_insert_post(
		array(
			'post_type'    => 'audit_request',
			'post_status'  => 'private',
			/* translators: 1: website, 2: email */
			'post_title'   => sprintf( __( '%1$s (%2$s)', 'nabia' ), $site, $email ),
			'post_content' => $body,
		)
	);

	$to = nabia_mod( 'contact_email' ) ? nabia_mod( 'contact_email' ) : get_option( 'admin_email' );
	$mailed = wp_mail(
		$to,
		/* translators: %s: website URL */
		sprintf( __( 'New free audit request: %s', 'nabia' ), $site ),
		$body,
		array( 'Reply-To: ' . ( $name ? $name . ' ' : '' ) . '<' . $email . '>' )
	);
	if ( $mailed ) {
		nabia_form_log( 'audit', 'sent' );
	} else {
		nabia_form_log( 'audit', 'mail_failed', nabia_last_mail_error() );
	}

	wp_safe_redirect( add_query_arg( 'audit', 'sent', $back ) . '#audit' );
	exit;
}
add_action( 'admin_post_nopriv_nabia_audit', 'nabia_handle_audit_request' );
add_action( 'admin_post_nabia_audit', 'nabia_handle_audit_request' );
