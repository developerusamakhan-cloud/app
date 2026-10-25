<?php
/**
 * Widget assets for the advanced features: streaming transport, product cards, proactive messages, away status.
 *
 * @package AI_Site_Assistant
 */

namespace AisaPro;

defined( 'ABSPATH' ) || exit;

/**
 * Front end.
 */
final class Frontend {

	/**
	 * Hooks.
	 */
	public static function init(): void {
		add_action( 'aisa_enqueue_widget', array( self::class, 'enqueue' ) );
		add_filter( 'aisa_widget_config', array( self::class, 'config' ) );
	}

	/**
	 * Enqueue.
	 */
	public static function enqueue(): void {
		wp_enqueue_style( 'aisa-pro', AISA_URL . 'assets/pro/css/pro.css', array( 'aisa-widget' ), AISA_VERSION );
		wp_enqueue_script( 'aisa-pro', AISA_URL . 'assets/pro/js/pro.js', array( 'aisa-widget' ), AISA_VERSION, true );
	}

	/**
	 * Config.
	 *
	 * @param array $config Config.
	 * @return array
	 */
	public static function config( array $config ): array {
		$config['pro']           = $config['pro'] ?? array();
		$config['pro']['stream'] = Stream::enabled();
		return $config;
	}
}
