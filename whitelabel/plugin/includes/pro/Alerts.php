<?php
/**
 * Lead alerts: Slack incoming webhook and Telegram bot (email comes from the
 * free plugin). 
 *
 * @package AI_Site_Assistant
 */

namespace AisaPro;

defined( 'ABSPATH' ) || exit;

/**
 * Alerts.
 */
final class Alerts {

	/**
	 * Hooks.
	 */
	public static function init(): void {
		add_action( 'aisa_lead_created', array( self::class, 'send' ), 10, 2 );
		add_action( 'wp_ajax_aisa_pro_test_alert', array( self::class, 'ajax_test' ) );
	}

	/**
	 * Alert text. Pure, unit tested.
	 *
	 * @param array  $lead Lead.
	 * @param string $site Site name.
	 */
	public static function text( array $lead, string $site ): string {
		$lines = array(
			/* translators: %s: site name */
			sprintf( __( 'New lead on %s', 'ai-site-assistant' ), $site ),
		);
		foreach ( array(
			'name'     => __( 'Name', 'ai-site-assistant' ),
			'email'    => __( 'Email', 'ai-site-assistant' ),
			'phone'    => __( 'Phone', 'ai-site-assistant' ),
			'message'  => __( 'Question', 'ai-site-assistant' ),
			'page_url' => __( 'Page', 'ai-site-assistant' ),
		) as $key => $label ) {
			if ( '' !== trim( (string) ( $lead[ $key ] ?? '' ) ) ) {
				$lines[] = $label . ': ' . trim( (string) $lead[ $key ] );
			}
		}
		return implode( "\n", $lines );
	}

	/**
	 * Send alerts for a lead.
	 *
	 * @param int   $id   Lead ID.
	 * @param array $lead Lead.
	 * @return array<string, bool|string> Result per channel.
	 */
	public static function send( $id, $lead ): array {
		$s      = \Aisa_Settings::all();
		$text   = self::text( (array) $lead, wp_strip_all_tags( get_bloginfo( 'name' ) ) ) . "\n" . admin_url( 'admin.php?page=aisa&tab=leads' );
		$result = array();
		if ( ! empty( $s['pro_slack_webhook'] ) ) {
			$res             = wp_remote_post(
				(string) $s['pro_slack_webhook'],
				array(
					'timeout'  => 8,
					'blocking' => true,
					'headers'  => array( 'Content-Type' => 'application/json' ),
					// Slack reads <!channel>, <@user> and <url|label> in text: escape what visitors typed.
					'body'     => wp_json_encode( array( 'text' => str_replace( array( '&', '<', '>' ), array( '&amp;', '&lt;', '&gt;' ), $text ) ) ),
				)
			);
			$result['slack'] = ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ? true : ( is_wp_error( $res ) ? $res->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $res ) );
		}
		if ( ! empty( $s['pro_telegram_token'] ) && ! empty( $s['pro_telegram_chat'] ) && ! preg_match( '/^\d+:[A-Za-z0-9_-]+$/', (string) $s['pro_telegram_token'] ) ) {
			$result['telegram'] = __( 'The Telegram bot token looks wrong. It should look like 123456:ABC-xyz.', 'ai-site-assistant' );
		} elseif ( ! empty( $s['pro_telegram_token'] ) && ! empty( $s['pro_telegram_chat'] ) ) {
			$res                = wp_remote_post(
				'https://api.telegram.org/bot' . (string) $s['pro_telegram_token'] . '/sendMessage',
				array(
					'timeout' => 8,
					'body'    => array(
						'chat_id'                  => (string) $s['pro_telegram_chat'],
						'text'                     => $text,
						'disable_web_page_preview' => 'true',
					),
				)
			);
			$body               = is_wp_error( $res ) ? array() : (array) json_decode( (string) wp_remote_retrieve_body( $res ), true );
			$result['telegram'] = ! empty( $body['ok'] ) ? true : ( is_wp_error( $res ) ? $res->get_error_message() : (string) ( $body['description'] ?? 'Failed' ) );
		}
		return $result;
	}

	/**
	 * Send a test alert.
	 */
	public static function ajax_test(): void {
		check_ajax_referer( 'aisa_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'ai-site-assistant' ) ), 403 );
		}
		$result = self::send(
			0,
			array(
				'name'    => __( 'Test lead', 'ai-site-assistant' ),
				'email'   => 'test@example.com',
				'message' => __( 'This is a test alert from {brand}.', 'ai-site-assistant' ),
			)
		);
		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Add a Slack webhook or a Telegram bot first, then save.', 'ai-site-assistant' ) ), 400 );
		}
		$parts = array();
		foreach ( $result as $channel => $ok ) {
			$parts[] = ucfirst( $channel ) . ': ' . ( true === $ok ? __( 'sent', 'ai-site-assistant' ) : (string) $ok );
		}
		wp_send_json_success( array( 'message' => implode( '. ', $parts ) ) );
	}
}
