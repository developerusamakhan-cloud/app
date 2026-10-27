<?php
/**
 * Page speed: keep plugin styles and scripts out of the critical path.
 *
 * The audit form and the chat widget sit below the first screen, so their files can
 * load after the page has painted. Scripts get the "defer" strategy (WordPress falls
 * back to a normal script if a plugin adds inline code that needs it right away) and
 * styles load without blocking the first paint.
 *
 * @package Nabia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin handles that do not need to block the first paint.
 *
 * @return array { scripts: string[], styles: string[] }
 */
function nabia_deferred_assets() {
	return apply_filters(
		'nabia_deferred_assets',
		array(
			'scripts' => array( 'nabia-audit', 'talkwyn-widget' ),
			'styles'  => array( 'nabia-audit', 'talkwyn-widget' ),
		)
	);
}

/**
 * Give the plugin scripts the defer strategy once they are registered.
 */
function nabia_defer_plugin_scripts() {
	$assets = nabia_deferred_assets();
	foreach ( $assets['scripts'] as $handle ) {
		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_script_add_data( $handle, 'strategy', 'defer' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'nabia_defer_plugin_scripts', 100 );
add_action( 'wp_print_footer_scripts', 'nabia_defer_plugin_scripts', 1 );

/**
 * Load the plugin stylesheets without blocking the first paint.
 *
 * @param string $tag    Link tag.
 * @param string $handle Style handle.
 * @return string
 */
function nabia_async_plugin_styles( $tag, $handle ) {
	$assets = nabia_deferred_assets();
	if ( is_admin() || ! in_array( $handle, $assets['styles'], true ) || false !== strpos( $tag, 'onload=' ) ) {
		return $tag;
	}
	$async = preg_replace( "/media=(['\"])all\\1/", "media='print' onload=\"this.media='all'\"", $tag, 1 );
	if ( $async === $tag ) {
		return $tag;
	}
	return $async . '<noscript>' . trim( $tag ) . "</noscript>\n";
}
add_filter( 'style_loader_tag', 'nabia_async_plugin_styles', 10, 2 );
