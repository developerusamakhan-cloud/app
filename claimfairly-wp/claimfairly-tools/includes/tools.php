<?php
/**
 * Tool registry and asset loading.
 *
 * To add a tool: create views/{name}.php and assets/js/{name}.js, then add
 * an entry to cft_tools(). The shortcode [cf_tool name="{name}"] does the rest.
 *
 * @package ClaimFairlyTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registered tools.
 *
 * @return array<string,array{title:string,path:string}>
 */
function cft_tools() {
	$tools = array(
		'diminished-value'     => array(
			'title' => __( 'Diminished Value Calculator', 'claimfairly-tools' ),
			'path'  => '/diminished-value-calculator/',
		),
		'settlement-take-home' => array(
			'title' => __( 'Settlement Take-Home Calculator', 'claimfairly-tools' ),
			'path'  => '/settlement-calculator-take-home/',
		),
		'settlement-estimator' => array(
			'title' => __( 'Car Accident Settlement Calculator', 'claimfairly-tools' ),
			'path'  => '/car-accident-settlement-calculator/',
		),
		'pain-and-suffering'   => array(
			'title' => __( 'Pain and Suffering Calculator', 'claimfairly-tools' ),
			'path'  => '/pain-and-suffering-calculator/',
		),
		'demand-letter'        => array(
			'title' => __( 'Demand Letter Generator', 'claimfairly-tools' ),
			'path'  => '/demand-letter-generator/',
		),
	);
	return apply_filters( 'cft_tools', $tools );
}

/**
 * URL of a tool page (the page may be moved; filter to override).
 *
 * @param string $name Tool name.
 * @return string
 */
function cft_tool_url( $name ) {
	$tools = cft_tools();
	$path  = isset( $tools[ $name ] ) ? $tools[ $name ]['path'] : '/';
	return apply_filters( 'cft_tool_url', home_url( $path ), $name );
}

/**
 * Register scripts and styles. Enqueued only where a tool is used.
 */
function cft_register_assets() {
	$ver = static function ( $rel ) {
		$file = CFT_DIR . $rel;
		return file_exists( $file ) ? (string) filemtime( $file ) : CFT_VERSION;
	};
	$args = array(
		'in_footer' => true,
		'strategy'  => 'defer',
	);

	wp_register_style( 'cft-tools', CFT_URL . 'assets/css/tools.css', array(), $ver( 'assets/css/tools.css' ) );
	wp_register_script( 'cft-core', CFT_URL . 'assets/js/cf-core.js', array(), $ver( 'assets/js/cf-core.js' ), $args );

	foreach ( array_keys( cft_tools() ) as $name ) {
		$rel = 'assets/js/' . $name . '.js';
		if ( file_exists( CFT_DIR . $rel ) ) {
			wp_register_script( 'cft-' . $name, CFT_URL . $rel, array( 'cft-core' ), $ver( $rel ), $args );
		}
	}

	$urls = array();
	foreach ( array_keys( cft_tools() ) as $name ) {
		$urls[ $name ] = cft_tool_url( $name );
	}
	wp_add_inline_script(
		'cft-core',
		'window.CF_DATA = ' . wp_json_encode(
			array(
				'states' => cft_states_for_js(),
				'urls'   => $urls,
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'cft_register_assets', 5 );

/**
 * Load tool styles in <head> on singular pages that contain a tool, so there
 * is no flash of unstyled form.
 */
function cft_maybe_enqueue_styles() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( $post && ( has_shortcode( $post->post_content, 'cf_tool' ) || has_shortcode( $post->post_content, 'cf_state_facts' ) || has_shortcode( $post->post_content, 'cf_state_table' ) ) ) {
		wp_enqueue_style( 'cft-tools' );
	}
}
add_action( 'wp_enqueue_scripts', 'cft_maybe_enqueue_styles', 20 );
