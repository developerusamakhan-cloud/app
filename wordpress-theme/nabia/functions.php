<?php
/**
 * Nabia theme functions and definitions.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NABIA_VERSION', '2.4.0' );
define( 'NABIA_DIR', get_template_directory() );
define( 'NABIA_URI', get_template_directory_uri() );

require NABIA_DIR . '/inc/defaults.php';
require NABIA_DIR . '/inc/customizer.php';
require NABIA_DIR . '/inc/post-types.php';
require NABIA_DIR . '/inc/template-tags.php';
require NABIA_DIR . '/inc/google-reviews.php';

/**
 * Theme setup.
 */
function nabia_setup() {
	load_theme_textdomain( 'nabia', NABIA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'nabia-project', 1200, 900, true );
	add_image_size( 'nabia-card', 800, 600, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'nabia' ),
			'footer'  => __( 'Footer Menu', 'nabia' ),
		)
	);

	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'nabia_setup' );

/**
 * Content width.
 */
function nabia_content_width() {
	$GLOBALS['content_width'] = 780;
}
add_action( 'after_setup_theme', 'nabia_content_width', 0 );

/**
 * Widget areas.
 */
function nabia_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer', 'nabia' ),
			'id'            => 'footer-1',
			'description'   => __( 'Widgets shown in the footer.', 'nabia' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'nabia_widgets_init' );

/**
 * Cache-busting version string for a theme file.
 *
 * @param string $file Path relative to the theme folder.
 * @return string
 */
function nabia_asset_version( $file ) {
	$path = NABIA_DIR . '/' . $file;
	return NABIA_VERSION . ( file_exists( $path ) ? '.' . filemtime( $path ) : '' );
}

/**
 * Enqueue styles and scripts.
 */
function nabia_scripts() {
	// Version = theme version + file time, so browsers and caches always pick up a new upload.
	wp_enqueue_style( 'nabia-style', get_stylesheet_uri(), array(), nabia_asset_version( 'style.css' ) );

	wp_add_inline_style( 'nabia-style', nabia_scheme_css() );

	wp_enqueue_script( 'nabia-main', NABIA_URI . '/assets/js/main.js', array(), nabia_asset_version( 'assets/js/main.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script(
		'nabia-main',
		'nabiaSettings',
		array(
			'preloader' => (bool) nabia_mod( 'enable_preloader' ),
			'cursor'    => (bool) nabia_mod( 'enable_cursor' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'nabia_scripts' );

/**
 * Preload the self-hosted fonts so headlines render without a flash.
 */
function nabia_preload_fonts() {
	foreach ( array( 'jakarta', 'inter' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( NABIA_URI . '/assets/fonts/' . $font . '.woff2' )
		);
	}
}
add_action( 'wp_head', 'nabia_preload_fonts', 1 );

/**
 * Use the monogram as favicon until a Site Icon is set in the Customizer.
 */
function nabia_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	$schemes = nabia_color_schemes();
	$scheme  = isset( $schemes[ nabia_mod( 'color_scheme' ) ] ) ? $schemes[ nabia_mod( 'color_scheme' ) ] : reset( $schemes );
	$accent  = sanitize_hex_color( nabia_mod( 'accent_color' ) );
	$accent  = $accent ? $accent : $scheme['accent'];
	$svg     = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><rect x="1" y="1" width="46" height="46" rx="15" fill="' . $scheme['ink'] . '"/><path d="M15 34V15.5a1.5 1.5 0 0 1 2.6-1l12.8 15a1.5 1.5 0 0 0 2.6-1V14" fill="none" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="36.5" cy="36" r="4" fill="' . $accent . '"/></svg>';
	echo '<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,' . rawurlencode( $svg ) . '">' . "\n";
}
add_action( 'wp_head', 'nabia_favicon', 2 );

/**
 * Add a class when JS runs so reveal animations never hide content for no-JS visitors.
 */
function nabia_js_class() {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
}
add_action( 'wp_head', 'nabia_js_class', 0 );

/**
 * Body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function nabia_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'is-home';
	}
	return $classes;
}
add_filter( 'body_class', 'nabia_body_classes' );

/**
 * Shorter excerpts.
 */
function nabia_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'nabia_excerpt_length' );

function nabia_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'nabia_excerpt_more' );
