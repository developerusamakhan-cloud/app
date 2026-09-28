<?php
/**
 * Theme supports, menus and assets.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme features.
 */
function claimfairly_setup() {
	load_theme_textdomain( 'claimfairly', CLAIMFAIRLY_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Keep the editor palette on-brand so content stays consistent.
	add_theme_support(
		'editor-color-palette',
		array(
			array( 'name' => __( 'Navy', 'claimfairly' ), 'slug' => 'navy', 'color' => '#0f2a47' ),
			array( 'name' => __( 'Teal', 'claimfairly' ), 'slug' => 'teal', 'color' => '#0f766e' ),
			array( 'name' => __( 'Ink', 'claimfairly' ), 'slug' => 'ink', 'color' => '#1f2937' ),
			array( 'name' => __( 'Muted', 'claimfairly' ), 'slug' => 'muted', 'color' => '#5b6472' ),
			array( 'name' => __( 'Soft', 'claimfairly' ), 'slug' => 'soft', 'color' => '#f3f6f9' ),
			array( 'name' => __( 'White', 'claimfairly' ), 'slug' => 'white', 'color' => '#ffffff' ),
		)
	);
	add_theme_support( 'disable-custom-colors' );

	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus(
		array(
			'primary'      => __( 'Primary (header)', 'claimfairly' ),
			'footer-tools' => __( 'Footer: Tools', 'claimfairly' ),
			'footer-site'  => __( 'Footer: Site & policies', 'claimfairly' ),
		)
	);
}
add_action( 'after_setup_theme', 'claimfairly_setup' );

/**
 * Content width for embeds.
 */
function claimfairly_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'claimfairly_content_width', 0 );

/**
 * Version an asset by file modification time so caches bust on every edit.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function claimfairly_asset_version( $relative ) {
	$file = CLAIMFAIRLY_DIR . '/' . ltrim( $relative, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : CLAIMFAIRLY_VERSION;
}

/**
 * Front-end styles and scripts.
 */
function claimfairly_assets() {
	wp_enqueue_style(
		'claimfairly-main',
		CLAIMFAIRLY_URI . '/assets/css/main.css',
		array(),
		claimfairly_asset_version( 'assets/css/main.css' )
	);

	wp_enqueue_script(
		'claimfairly-site',
		CLAIMFAIRLY_URI . '/assets/js/site.js',
		array(),
		claimfairly_asset_version( 'assets/js/site.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	// Shared calculator helpers. Enqueued on demand by the [cf_calculator] shortcode.
	wp_register_script(
		'claimfairly-core',
		CLAIMFAIRLY_URI . '/assets/js/cf-core.js',
		array(),
		claimfairly_asset_version( 'assets/js/cf-core.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'claimfairly_assets' );

/**
 * Menu fallback shown until a Primary menu is assigned.
 */
function claimfairly_menu_fallback() {
	echo '<ul class="menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'claimfairly' ) . '</a></li>';
	if ( current_user_can( 'edit_theme_options' ) ) {
		echo '<li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Set up menu', 'claimfairly' ) . '</a></li>';
	}
	echo '</ul>';
}
