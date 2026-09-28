<?php
/**
 * Shortcodes used inside page content.
 *
 *   [cf_calculator name="diminished-value"]   Calculator shell (tools are added one by one in inc/tools/).
 *   [cf_faq] [cf_q q="Question?"]Answer[/cf_q] [/cf_faq]   FAQ accordion + FAQPage schema.
 *   [cf_note type="info|warning|tip"]Text[/cf_note]        Highlighted note.
 *   [cf_disclaimer]                            Standard disclaimer box.
 *   [cf_sources] [cf_author_box] [cf_related]  Place a trust block manually.
 *   [cf_last_reviewed]                         Inline "Last reviewed" date.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry of available calculators. Each entry maps a shortcode name to its
 * PHP markup file (inc/tools/{name}.php) and its script (assets/js/tools/{name}.js).
 *
 * @return string[]
 */
function claimfairly_available_tools() {
	$tools = array();
	$files = glob( CLAIMFAIRLY_DIR . '/inc/tools/*.php' );
	foreach ( $files ? $files : array() as $file ) {
		$tools[] = basename( $file, '.php' );
	}
	return apply_filters( 'claimfairly_available_tools', $tools );
}

/**
 * [cf_calculator name="..."]
 *
 * @param array $atts Attributes.
 * @return string
 */
function claimfairly_calculator_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'name'   => '',
			'preset' => '',
		),
		$atts,
		'cf_calculator'
	);
	$name = sanitize_key( $atts['name'] );

	if ( ! $name || ! in_array( $name, claimfairly_available_tools(), true ) ) {
		if ( current_user_can( 'edit_posts' ) ) {
			/* translators: %s: calculator name. */
			return '<div class="cf-note cf-note--warning">' . esc_html( sprintf( __( 'Calculator "%s" is not installed in the theme yet. (Only editors see this message.)', 'claimfairly' ), $name ) ) . '</div>';
		}
		return '';
	}

	wp_enqueue_script( 'claimfairly-core' );
	$script = 'assets/js/tools/' . $name . '.js';
	if ( file_exists( CLAIMFAIRLY_DIR . '/' . $script ) ) {
		wp_enqueue_script(
			'claimfairly-tool-' . $name,
			CLAIMFAIRLY_URI . '/' . $script,
			array( 'claimfairly-core' ),
			claimfairly_asset_version( $script ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	$preset = sanitize_text_field( $atts['preset'] );

	ob_start();
	echo '<section class="cf-tool" data-cf-tool="' . esc_attr( $name ) . '" data-cf-preset="' . esc_attr( $preset ) . '">';
	include CLAIMFAIRLY_DIR . '/inc/tools/' . $name . '.php';
	echo '</section>';
	return ob_get_clean();
}
add_shortcode( 'cf_calculator', 'claimfairly_calculator_shortcode' );

/**
 * FAQ items collected during rendering, printed as FAQPage JSON-LD in the footer.
 *
 * @param array|null $add Item to add.
 * @return array
 */
function claimfairly_faq_store( $add = null ) {
	static $items = array();
	if ( null !== $add ) {
		$items[] = $add;
	}
	return $items;
}

/**
 * [cf_faq] wrapper.
 *
 * @param array  $atts    Attributes.
 * @param string $content Inner content.
 * @return string
 */
function claimfairly_faq_shortcode( $atts, $content = '' ) {
	$atts  = shortcode_atts(
		array( 'title' => __( 'Frequently asked questions', 'claimfairly' ) ),
		$atts,
		'cf_faq'
	);
	$html  = '<section class="cf-faq">';
	if ( '' !== $atts['title'] ) {
		$html .= '<h2>' . esc_html( $atts['title'] ) . '</h2>';
	}
	// wpautop runs before shortcodes, leaving <br>/<p> between items; drop them.
	$items = do_shortcode( shortcode_unautop( trim( (string) $content ) ) );
	$items = preg_replace( '#^(?:<br\s*/?>|</?p>|\s)+#', '', $items );
	$items = preg_replace( '#(?:<br\s*/?>|</?p>|\s)+(?=<details|$)#', '', $items );
	$html .= $items;
	$html .= '</section>';
	return $html;
}
add_shortcode( 'cf_faq', 'claimfairly_faq_shortcode' );

/**
 * [cf_q q="..."]Answer[/cf_q]
 *
 * @param array  $atts    Attributes.
 * @param string $content Answer.
 * @return string
 */
function claimfairly_faq_item_shortcode( $atts, $content = '' ) {
	$atts     = shortcode_atts( array( 'q' => '' ), $atts, 'cf_q' );
	$question = trim( $atts['q'] );
	if ( '' === $question ) {
		return '';
	}
	$answer = wpautop( do_shortcode( trim( (string) $content ) ) );
	$answer = wp_kses_post( $answer );

	claimfairly_faq_store(
		array(
			'q' => wp_strip_all_tags( $question ),
			'a' => trim( wp_strip_all_tags( $answer ) ),
		)
	);

	return '<details class="cf-faq__item"><summary><h3 class="cf-faq__q">' . esc_html( $question ) . '</h3></summary><div class="cf-faq__a">' . $answer . '</div></details>';
}
add_shortcode( 'cf_q', 'claimfairly_faq_item_shortcode' );

/**
 * [cf_note type="info"]...[/cf_note]
 *
 * @param array  $atts    Attributes.
 * @param string $content Content.
 * @return string
 */
function claimfairly_note_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'type' => 'info' ), $atts, 'cf_note' );
	$type = in_array( $atts['type'], array( 'info', 'warning', 'tip' ), true ) ? $atts['type'] : 'info';
	return '<div class="cf-note cf-note--' . esc_attr( $type ) . '">' . wp_kses_post( wpautop( do_shortcode( trim( (string) $content ) ) ) ) . '</div>';
}
add_shortcode( 'cf_note', 'claimfairly_note_shortcode' );

add_shortcode(
	'cf_disclaimer',
	static function () {
		return claimfairly_get_disclaimer();
	}
);

add_shortcode(
	'cf_sources',
	static function () {
		return claimfairly_get_sources_box();
	}
);

add_shortcode(
	'cf_author_box',
	static function () {
		return claimfairly_get_author_box();
	}
);

add_shortcode(
	'cf_related',
	static function () {
		return claimfairly_get_related_box();
	}
);

add_shortcode(
	'cf_last_reviewed',
	static function () {
		$reviewed = claimfairly_last_reviewed_raw();
		if ( ! $reviewed ) {
			return '';
		}
		return '<time datetime="' . esc_attr( $reviewed ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $reviewed ) ) . '</time>';
	}
);
