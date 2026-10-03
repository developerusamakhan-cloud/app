<?php
/**
 * Shortcodes so the same tools can be dropped into any calculator / price page.
 *
 * [powerbachat_calculator country="pk" utility="lesco" units="200"]
 * [powerbachat_slab_chart country="pk"]
 * [powerbachat_solar country="in"]
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a template part into a string.
 *
 * @param string $slug Template slug.
 * @param array  $args Arguments.
 * @return string
 */
function powerbachat_render_part( $slug, $args = array() ) {
	ob_start();
	get_template_part( $slug, null, $args );
	return ob_get_clean();
}

function powerbachat_shortcode_args( $atts, $tag ) {
	return shortcode_atts(
		array(
			'country' => '',
			'utility' => '',
			'units'   => '',
		),
		$atts,
		$tag
	);
}

function powerbachat_calculator_shortcode( $atts ) {
	$args          = powerbachat_shortcode_args( $atts, 'powerbachat_calculator' );
	$args['embed'] = true;
	return powerbachat_render_part( 'template-parts/calculator', $args );
}
add_shortcode( 'powerbachat_calculator', 'powerbachat_calculator_shortcode' );

function powerbachat_slab_chart_shortcode( $atts ) {
	$args          = powerbachat_shortcode_args( $atts, 'powerbachat_slab_chart' );
	$args['embed'] = true;
	return powerbachat_render_part( 'template-parts/home/slabs', $args );
}
add_shortcode( 'powerbachat_slab_chart', 'powerbachat_slab_chart_shortcode' );

function powerbachat_solar_shortcode( $atts ) {
	$args          = powerbachat_shortcode_args( $atts, 'powerbachat_solar' );
	$args['embed'] = true;
	return powerbachat_render_part( 'template-parts/home/solar', $args );
}
add_shortcode( 'powerbachat_solar', 'powerbachat_solar_shortcode' );
