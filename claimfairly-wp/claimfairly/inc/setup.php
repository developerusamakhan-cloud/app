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
			'height'      => 40,
			'width'       => 180,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'editor-color-palette',
		array(
			array( 'name' => __( 'Ink', 'claimfairly' ), 'slug' => 'ink', 'color' => '#101828' ),
			array( 'name' => __( 'Brand green', 'claimfairly' ), 'slug' => 'brand', 'color' => '#12805c' ),
			array( 'name' => __( 'Mint', 'claimfairly' ), 'slug' => 'mint', 'color' => '#e9f7f0' ),
			array( 'name' => __( 'Lavender', 'claimfairly' ), 'slug' => 'lavender', 'color' => '#f3f0ff' ),
			array( 'name' => __( 'Sky', 'claimfairly' ), 'slug' => 'sky', 'color' => '#edf3ff' ),
			array( 'name' => __( 'Peach', 'claimfairly' ), 'slug' => 'peach', 'color' => '#fff1e8' ),
			array( 'name' => __( 'Soft gray', 'claimfairly' ), 'slug' => 'soft', 'color' => '#f6f7fb' ),
			array( 'name' => __( 'White', 'claimfairly' ), 'slug' => 'white', 'color' => '#ffffff' ),
		)
	);
	add_theme_support( 'disable-custom-colors' );

	add_editor_style( 'assets/css/editor.css' );
	add_post_type_support( 'page', 'excerpt' );

	register_nav_menus(
		array(
			'primary'      => __( 'Primary (header)', 'claimfairly' ),
			'footer-tools' => __( 'Footer: Calculators', 'claimfairly' ),
			'footer-learn' => __( 'Footer: Guides', 'claimfairly' ),
			'footer-site'  => __( 'Footer: Company and policies', 'claimfairly' ),
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
 * Preload the one font file so headings render without a flash.
 */
function claimfairly_preload_font() {
	echo '<link rel="preload" href="' . esc_url( CLAIMFAIRLY_URI . '/assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'claimfairly_preload_font', 1 );

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
}
add_action( 'wp_enqueue_scripts', 'claimfairly_assets' );

/**
 * Menu fallback until menus are created (the setup importer creates them).
 */
function claimfairly_menu_fallback() {
	echo '<ul class="menu">';
	foreach ( claimfairly_get_tools( 5 ) as $tool ) {
		echo '<li><a href="' . esc_url( get_permalink( $tool ) ) . '">' . esc_html( get_the_title( $tool ) ) . '</a></li>';
	}
	if ( current_user_can( 'edit_theme_options' ) ) {
		echo '<li><a href="' . esc_url( admin_url( 'themes.php?page=claimfairly-setup' ) ) . '">' . esc_html__( 'Run setup', 'claimfairly' ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Add a "has-icon" class and a small colored icon to menu items that point
 * at tool pages, so the Calculators dropdown looks like a real product menu.
 *
 * @param string   $title Item title.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu args.
 * @param int      $depth Depth.
 * @return string
 */
function claimfairly_menu_item_icon( $title, $item, $args, $depth ) {
	if ( ! isset( $args->theme_location ) || 'primary' !== $args->theme_location || 0 === $depth ) {
		return $title;
	}
	if ( 'post_type' !== $item->type || 'page' !== $item->object ) {
		return $title;
	}
	$tool = (string) get_post_meta( (int) $item->object_id, '_cf_tool_key', true );
	if ( '' === $tool ) {
		return $title;
	}
	$style  = claimfairly_page_style( (int) $item->object_id );
	$styles = claimfairly_tool_styles();
	$desc   = isset( $styles[ $tool ]['desc'] ) ? $styles[ $tool ]['desc'] : '';
	$html   = '<span class="menu-icon c-' . esc_attr( $style['color'] ) . '">' . claimfairly_icon( $style['icon'], 18 ) . '</span><span class="menu-text"><span class="menu-title">' . $title . '</span>';
	if ( $desc ) {
		$html .= '<span class="menu-desc">' . esc_html( $desc ) . '</span>';
	}
	return $html . '</span>';
}
add_filter( 'nav_menu_item_title', 'claimfairly_menu_item_icon', 10, 4 );

/**
 * Show all guides on one page (the plan has 12), and more per page later on.
 *
 * @param WP_Query $query Query.
 */
function claimfairly_guides_per_page( $query ) {
	if ( ! is_admin() && $query->is_main_query() && ( $query->is_home() || $query->is_category() ) ) {
		$query->set( 'posts_per_page', 24 );
	}
}
add_action( 'pre_get_posts', 'claimfairly_guides_per_page' );
