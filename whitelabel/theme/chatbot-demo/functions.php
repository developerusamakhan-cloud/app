<?php
/**
 * Chatbot Demo theme setup.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

define( 'CHATBOT_DEMO_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/demo-content.php';

/**
 * Theme supports and menus.
 *
 * @return void
 */
function chatbot_demo_setup() {
	load_theme_textdomain( 'chatbot-demo', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	register_nav_menus(
		array(
			'primary' => __( 'Main menu', 'chatbot-demo' ),
			'footer'  => __( 'Footer menu', 'chatbot-demo' ),
		)
	);
}
add_action( 'after_setup_theme', 'chatbot_demo_setup' );

/**
 * Styles and the small menu script.
 *
 * @return void
 */
function chatbot_demo_assets() {
	wp_enqueue_style( 'chatbot-demo', get_stylesheet_uri(), array(), CHATBOT_DEMO_VERSION );
	$accent = sanitize_hex_color( (string) get_theme_mod( 'chatbot_demo_accent', '#4F46E5' ) );
	if ( $accent && '#4F46E5' !== strtoupper( $accent ) ) {
		wp_add_inline_style( 'chatbot-demo', ':root{--accent:' . $accent . ';--accent-dark:color-mix(in srgb,' . $accent . ' 82%,#000);--accent-soft:color-mix(in srgb,' . $accent . ' 9%,#fff);}' );
	}
	wp_enqueue_script( 'chatbot-demo-nav', get_template_directory_uri() . '/assets/nav.js', array(), CHATBOT_DEMO_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'chatbot_demo_assets' );

/**
 * Customizer: accent colour, front page texts and contact details.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function chatbot_demo_customize( $wp_customize ) {
	$wp_customize->add_section(
		'chatbot_demo',
		array(
			'title'    => __( 'Chatbot demo', 'chatbot-demo' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'chatbot_demo_accent',
		array(
			'default'           => '#4F46E5',
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'chatbot_demo_accent',
			array(
				'label'   => __( 'Accent colour', 'chatbot-demo' ),
				'section' => 'chatbot_demo',
			)
		)
	);

	$texts = array(
		'chatbot_demo_hero_title' => array( __( 'Front page heading', 'chatbot-demo' ), 'text' ),
		'chatbot_demo_hero_text'  => array( __( 'Front page intro', 'chatbot-demo' ), 'textarea' ),
		'chatbot_demo_email'      => array( __( 'Contact email', 'chatbot-demo' ), 'email' ),
		'chatbot_demo_phone'      => array( __( 'Contact phone or WhatsApp', 'chatbot-demo' ), 'text' ),
	);
	foreach ( $texts as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => 'email' === $field[1] ? 'sanitize_email' : ( 'textarea' === $field[1] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field[0],
				'section' => 'chatbot_demo',
				'type'    => $field[1],
			)
		);
	}
}
add_action( 'customize_register', 'chatbot_demo_customize' );

/**
 * A theme setting, or its default when empty.
 *
 * @param string $key      Setting.
 * @param string $fallback Default.
 * @return string
 */
function chatbot_demo_mod( $key, $fallback = '' ) {
	$value = trim( (string) get_theme_mod( $key, '' ) );
	return '' !== $value ? $value : $fallback;
}

/**
 * Whether the chatbot plugin is active.
 *
 * @return bool
 */
function chatbot_demo_has_chat() {
	return shortcode_exists( 'aisa_chat' );
}

/**
 * The chat icon used in the header and cards.
 *
 * @param string $name Icon name.
 * @return string Safe SVG markup.
 */
function chatbot_demo_icon( $name ) {
	$paths = array(
		'chat'    => '<path d="M5 5h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-8l-5 4v-4H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/>',
		'scan'    => '<path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M8 9h8M8 12h8M8 15h5"/>',
		'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'link'    => '<path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1"/>',
		'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'shield'  => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
		'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'palette' => '<path d="M12 3a9 9 0 1 0 0 18c1 0 1.5-.8 1.5-1.5 0-1.2-1-1.5-1-2.5s.8-1.5 2-1.5H17a4 4 0 0 0 4-4c0-4.7-4-8.5-9-8.5z"/><circle cx="7.5" cy="11" r="1"/><circle cx="10" cy="7" r="1"/><circle cx="15" cy="7.5" r="1"/>',
		'list'    => '<path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4.5" cy="6" r="1"/><circle cx="4.5" cy="12" r="1"/><circle cx="4.5" cy="18" r="1"/>',
	);
	$inner = $paths[ $name ] ?? $paths['chat'];
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
}

/**
 * Allowed SVG tags for the icons.
 *
 * @return array
 */
function chatbot_demo_svg_kses() {
	$shape = array(
		'd'  => true,
		'cx' => true,
		'cy' => true,
		'r'  => true,
	);
	return array(
		'svg'    => array(
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'aria-hidden'     => true,
			'focusable'       => true,
		),
		'path'   => $shape,
		'circle' => $shape,
	);
}

/**
 * Menu fallback: the demo pages, so the header is never empty.
 *
 * @return void
 */
function chatbot_demo_menu_fallback() {
	echo '<ul>';
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
			'number'   => 6,
		)
	);
	echo '</ul>';
}
