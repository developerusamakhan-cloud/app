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
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
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

	add_image_size( 'powerbachat-card', 720, 480, true );

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
