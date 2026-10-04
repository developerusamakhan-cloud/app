<?php
/**
 * Theme supports, menus and image sizes.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function powerbachat_setup() {
	load_theme_textdomain( 'powerbachat', POWERBACHAT_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
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
			'height'      => 64,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'powerbachat-card', 720, 405, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'powerbachat' ),
			'footer'  => __( 'Footer menu', 'powerbachat' ),
		)
	);

	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'powerbachat_setup' );

function powerbachat_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'powerbachat_content_width', 0 );

function powerbachat_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Article sidebar', 'powerbachat' ),
			'id'            => 'sidebar-article',
			'description'   => __( 'Shown beside single posts and pages.', 'powerbachat' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'powerbachat_widgets_init' );

/**
 * Shorter, plainer excerpts for cards.
 */
function powerbachat_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'powerbachat_excerpt_length' );

function powerbachat_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'powerbachat_excerpt_more' );

/**
 * Favicons from the theme until a Site Icon is set in Customize > Site Identity.
 */
function powerbachat_favicons() {
	if ( has_site_icon() ) {
		return;
	}
	$base = POWERBACHAT_URI . '/assets/img/';
	$ver  = '?v=' . rawurlencode( POWERBACHAT_VERSION );
	printf( '<link rel="icon" href="%s" sizes="any">' . "\n", esc_url( $base . 'favicon.ico' . $ver ) );
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $base . 'favicon.svg' . $ver ) );
	// Google Search shows favicons that are a multiple of 48px, so offer 48, 96 and 192.
	printf( '<link rel="icon" href="%s" type="image/png" sizes="48x48">' . "\n", esc_url( $base . 'favicon-48.png' . $ver ) );
	printf( '<link rel="icon" href="%s" type="image/png" sizes="96x96">' . "\n", esc_url( $base . 'favicon-96.png' . $ver ) );
	printf( '<link rel="icon" href="%s" type="image/png" sizes="192x192">' . "\n", esc_url( $base . 'icon-192.png' . $ver ) );
	printf( '<link rel="icon" href="%s" type="image/png" sizes="32x32">' . "\n", esc_url( $base . 'favicon-32.png' . $ver ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . 'apple-touch-icon.png' . $ver ) );
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( $base . 'site.webmanifest' ) );
}
add_action( 'wp_head', 'powerbachat_favicons', 3 );
add_action( 'admin_head', 'powerbachat_favicons' );
add_action( 'login_head', 'powerbachat_favicons' );

/**
 * Browsers ask for /favicon.ico directly. Without a Site Icon, WordPress answers
 * with its own logo, so send the theme icon instead.
 */
function powerbachat_favicon_ico() {
	if ( ! has_site_icon() ) {
		wp_safe_redirect( POWERBACHAT_URI . '/assets/img/favicon.ico', 302 );
		exit;
	}
}
add_action( 'do_favicon', 'powerbachat_favicon_ico', 5 );
