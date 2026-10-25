<?php
/**
 * Gutenberg block: the plugin Chat (rendered on the server).
 *
 * @package AI_Site_Assistant
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block registration.
 */
class Aisa_Block {

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the block.
	 *
	 * @return void
	 */
	public static function register() {
		wp_register_script(
			'aisa-block-editor',
			AISA_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			AISA_VERSION,
			true
		);
		wp_set_script_translations( 'aisa-block-editor', 'ai-site-assistant' );
		register_block_type(
			AISA_DIR . 'blocks/chat',
			array( 'render_callback' => array( __CLASS__, 'render' ) )
		);
	}

	/**
	 * Render.
	 *
	 * @param array $attributes Attributes.
	 * @return string
	 */
	public static function render( $attributes ) {
		$height = isset( $attributes['height'] ) ? absint( $attributes['height'] ) : 600;
		$html   = Aisa_Frontend::shortcode(
			array(
				'mode'   => 'inline',
				'height' => min( 900, max( 420, $height ) ),
			)
		);
		return '' === $html ? '' : '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>';
	}
}
