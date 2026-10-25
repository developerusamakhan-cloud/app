<?php
/**
 * Lead capture: validation, spam protection, storage and email alerts.
 *
 * @package AI_Site_Assistant
 */

defined( 'ABSPATH' ) || exit;

/**
 * Leads.
 */
class Aisa_Leads {

	/**
	 * Minimum time between showing and sending a form, in milliseconds. Browser autofill
	 * and one click can be quicker than typing, so this is kept short.
	 */
	const MIN_FILL_MS = 1200;

	/**
	 * Validate submitted lead fields. Pure function, unit tested.
	 *
	 * A filled honeypot or a missing timer is treated as a bot: the request looks successful
	 * and nothing is stored. A form sent faster than MIN_FILL_MS gets a "send again" message
	 * instead, so a quick real visitor can still get through on the second press.
	 *
	 * @param array $in Fields: name, email, phone, consent, website (honeypot), elapsed (ms the form was open).
	 * @param array $s  Settings.
	 * @return array{ok:bool,error:string,spam:bool}
	 */
	public static function validate( array $in, array $s ) {
		$elapsed = isset( $in['elapsed'] ) ? (int) $in['elapsed'] : -1;
		if ( '' !== trim( (string) ( $in['website'] ?? '' ) ) || $elapsed < 0 ) {
			return array(
				'ok'    => false,
				'error' => 'spam',
				'spam'  => true,
			);
		}
		if ( $elapsed < self::MIN_FILL_MS ) {
			return array(
				'ok'    => false,
				'error' => __( 'Please check your details and press send again.', 'ai-site-assistant' ),
				'spam'  => false,
			);
		}
		$checks = array(
			array( ! empty( $s['lead_require_name'] ) && '' === trim( (string) ( $in['name'] ?? '' ) ), __( 'Please add your name.', 'ai-site-assistant' ) ),
			array( ! empty( $s['lead_require_email'] ) && ! is_email( (string) ( $in['email'] ?? '' ) ), __( 'Please add a valid email address.', 'ai-site-assistant' ) ),
			array( '' !== trim( (string) ( $in['email'] ?? '' ) ) && ! is_email( (string) ( $in['email'] ?? '' ) ), __( 'Please add a valid email address.', 'ai-site-assistant' ) ),
			array( ! empty( $s['lead_require_phone'] ) && strlen( preg_replace( '/\D/', '', (string) ( $in['phone'] ?? '' ) ) ) < 6, __( 'Please add your phone number.', 'ai-site-assistant' ) ),
			array( ! empty( $s['lead_consent_enabled'] ) && empty( $in['consent'] ), __( 'Please tick the consent box.', 'ai-site-assistant' ) ),
			array( '' === trim( (string) ( $in['name'] ?? '' ) . ( $in['email'] ?? '' ) . ( $in['phone'] ?? '' ) ), __( 'Please add a way to reach you.', 'ai-site-assistant' ) ),
		);
		foreach ( $checks as $check ) {
			if ( $check[0] ) {
				return array(
					'ok'    => false,
					'error' => $check[1],
					'spam'  => false,
				);
			}
		}
		return array(
			'ok'    => true,
			'error' => '',
			'spam'  => false,
		);
	}

	/**
	 * Verify a Cloudflare Turnstile token when the owner set a secret key.
	 *
	 * @param string $token Token from the widget.
	 * @param string $ip    Visitor IP.
	 * @return bool
	 */
	public static function turnstile_ok( $token, $ip ) {
		$secret = (string) Aisa_Settings::get( 'turnstile_secret', '' );
		if ( '' === $secret || '' === (string) Aisa_Settings::get( 'turnstile_site_key', '' ) ) {
			return true;
		}
		if ( '' === trim( (string) $token ) ) {
			return false;
		}
		$res = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify', // phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- server-side Turnstile check, listed under External services.
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret,
					'response' => (string) $token,
					'remoteip' => $ip,
				),
			)
		);
		if ( is_wp_error( $res ) ) {
			return false;
		}
		$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		return ! empty( $data['success'] );
	}

	/**
	 * Store a lead and send the email alert.
	 *
	 * @param array $lead name, email, phone, message, page_url, session_id, consent, source (chat|transcript).
	 * @return int Lead ID, 0 on failure.
	 */
	public static function save( array $lead ) {
		global $wpdb;
		$t      = Aisa_DB::tables();
		$source = (string) ( $lead['source'] ?? 'chat' );
		$row    = array(
			'created_gmt' => current_time( 'mysql', true ),
			'session_id'  => (string) ( $lead['session_id'] ?? '' ),
			'name'        => sanitize_text_field( (string) ( $lead['name'] ?? '' ) ),
			'email'       => sanitize_email( (string) ( $lead['email'] ?? '' ) ),
			'phone'       => sanitize_text_field( (string) ( $lead['phone'] ?? '' ) ),
			'message'     => sanitize_textarea_field( (string) ( $lead['message'] ?? '' ) ),
			'page_url'    => esc_url_raw( (string) ( $lead['page_url'] ?? '' ) ),
			'consent'     => empty( $lead['consent'] ) ? 0 : 1,
			'source'      => in_array( $source, array( 'chat', 'transcript' ), true ) ? $source : 'chat',
			'status'      => 'new',
		);
		$ok = $wpdb->insert( $t['leads'], $row, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( ! $ok ) {
			return 0;
		}
		$id        = (int) $wpdb->insert_id;
		$row['id'] = $id;
		self::email_alert( $row );

		/**
		 * Fires after a lead was saved. an add-on sends Slack and Telegram alerts here.
		 *
		 * @param int   $id  Lead ID.
		 * @param array $row Lead data.
		 */
		do_action( 'aisa_lead_created', $id, $row );
		return $id;
	}

	/**
	 * Email the owner.
	 *
	 * @param array $row Lead.
	 * @return bool
	 */
	private static function email_alert( array $row ) {
		$to = sanitize_email( (string) Aisa_Settings::get( 'notification_email', '' ) );
		if ( '' === $to ) {
			return false;
		}
		/* translators: %s: site name */
		$subject = sprintf( __( '[%s] New lead from the chat', 'ai-site-assistant' ), wp_strip_all_tags( get_bloginfo( 'name' ) ) );
		$lines   = array(
			/* translators: %d: lead reference number */
			sprintf( __( 'Reference: #%d', 'ai-site-assistant' ), $row['id'] ),
			__( 'Name:', 'ai-site-assistant' ) . ' ' . $row['name'],
			__( 'Email:', 'ai-site-assistant' ) . ' ' . $row['email'],
			__( 'Phone:', 'ai-site-assistant' ) . ' ' . $row['phone'],
			__( 'Page:', 'ai-site-assistant' ) . ' ' . $row['page_url'],
			'',
			__( 'Question:', 'ai-site-assistant' ),
			$row['message'],
			'',
			__( 'All leads:', 'ai-site-assistant' ) . ' ' . admin_url( 'admin.php?page=aisa&tab=leads' ),
		);
		$headers = array();
		if ( is_email( $row['email'] ) ) {
			$headers[] = 'Reply-To: ' . ( '' !== $row['name'] ? str_replace( array( "\r", "\n", '<', '>' ), '', $row['name'] ) . ' ' : '' ) . '<' . $row['email'] . '>';
		}
		return (bool) wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
	}
}
