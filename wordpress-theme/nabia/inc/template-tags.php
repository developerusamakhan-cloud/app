<?php
/**
 * Template helpers.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG icon.
 *
 * @param string $name Icon name.
 * @return string
 */
function nabia_get_icon( $name ) {
	$paths = array(
		'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-up'  => '<path d="M7 17 17 7M8 7h9v9"/>',
		'layout'    => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/>',
		'code'      => '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
		'cart'      => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>',
		'pen'       => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/><circle cx="11" cy="11" r="2"/>',
		'bolt'      => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
		'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		'star'      => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
		'mail'      => '<rect x="2" y="4" width="20" height="16" rx="3"/><path d="m22 7-10 6L2 7"/>',
		'whatsapp'  => '<path d="M21 12a9 9 0 0 1-13.3 7.9L3 21l1.1-4.7A9 9 0 1 1 21 12z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5"/>',
		'plus'      => '<path d="M12 5v14M5 12h14"/>',
		'menu'      => '<path d="M4 8h16M4 16h16"/>',
		'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
		'linkedin'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/>',
		'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".5"/>',
		'behance'   => '<path d="M3 6h5.5a3 3 0 0 1 0 6H3zM3 12h6a3 3 0 0 1 0 6H3zM15 7h5M14 15h7a3.5 3.5 0 1 0-1 2.5"/>',
		'dribbble'  => '<circle cx="12" cy="12" r="10"/><path d="M8.6 2.6c4 5.4 6.6 11.8 7.6 18.6M19 5c-3.5 4-9 5.5-16.8 5.2M21.8 13.3c-6.2-1.3-11.8.6-16 6"/>',
		'github'    => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.9a3.4 3.4 0 0 0-.9-2.6c3.1-.4 6.4-1.5 6.4-7A5.4 5.4 0 0 0 20 4.8 5 5 0 0 0 19.9 1S18.7.7 16 2.5a13.4 13.4 0 0 0-7 0C6.3.7 5.1 1 5.1 1A5 5 0 0 0 5 4.8a5.4 5.4 0 0 0-1.5 3.7c0 5.4 3.3 6.6 6.4 7a3.4 3.4 0 0 0-.9 2.6V22"/>',
		'youtube'   => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3z"/>',
		'upwork'    => '<path d="M3 5v6a4 4 0 0 0 8 0V5M11 11c1.5 4 3 6 5.5 6a4 4 0 0 0 0-8c-2.5 0-4 2-5 5l-2 8"/>',
		'fiverr'    => '<path d="M9 21V9h6v12M9 9V7a3 3 0 0 1 3-3h1M6 9h9"/><circle cx="18" cy="5" r="1"/>',
		'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return '<svg class="icon icon-' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Echo an icon (markup is static and trusted).
 *
 * @param string $name Icon name.
 */
function nabia_icon( $name ) {
	echo nabia_get_icon( $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Site logo or text wordmark.
 */
function nabia_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$name = nabia_mod( 'brand_name' );
	if ( ! $name ) {
		$name = get_bloginfo( 'name' );
	}
	printf(
		'<a class="wordmark" href="%1$s" rel="home"><span class="wordmark-dot" aria-hidden="true"></span>%2$s</a>',
		esc_url( home_url( '/' ) ),
		esc_html( $name )
	);
}

/**
 * Configured social links.
 *
 * @return array network => url
 */
function nabia_socials() {
	$out = array();
	foreach ( array( 'linkedin', 'instagram', 'behance', 'dribbble', 'github', 'youtube', 'upwork', 'fiverr' ) as $network ) {
		$url = nabia_mod( 'social_' . $network );
		if ( $url ) {
			$out[ $network ] = $url;
		}
	}
	return $out;
}

/**
 * Print social link list.
 *
 * @param string $class Extra class.
 */
function nabia_social_links( $class = '' ) {
	$socials = nabia_socials();
	if ( ! $socials ) {
		return;
	}
	echo '<ul class="socials ' . esc_attr( $class ) . '">';
	foreach ( $socials as $network => $url ) {
		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s" data-magnetic>%3$s</a></li>',
			esc_url( $url ),
			esc_attr( ucfirst( $network ) ),
			nabia_get_icon( $network ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
	echo '</ul>';
}

/**
 * Section heading with eyebrow + number.
 *
 * @param string $number  Section number e.g. "01".
 * @param string $eyebrow Small label.
 * @param string $title   Big title.
 */
function nabia_section_head( $number, $eyebrow, $title ) {
	?>
	<header class="section-head">
		<p class="eyebrow" data-reveal><span class="eyebrow-num"><?php echo esc_html( $number ); ?></span><?php echo esc_html( $eyebrow ); ?></p>
		<h2 class="section-title" data-split><?php echo esc_html( $title ); ?></h2>
	</header>
	<?php
}

/**
 * WhatsApp link, if configured.
 *
 * @return string
 */
function nabia_whatsapp_url() {
	$number = preg_replace( '/\D+/', '', (string) nabia_mod( 'contact_whatsapp' ) );
	return $number ? 'https://wa.me/' . $number : '';
}

/**
 * Placeholder projects shown until real Projects are added.
 *
 * @return array
 */
function nabia_demo_projects() {
	return array(
		array(
			'title' => 'Bloom Botanics',
			'type'  => 'WooCommerce store',
			'year'  => '2025',
			'hue'   => '#ff8fab',
		),
		array(
			'title' => 'Northline Consulting',
			'type'  => 'Corporate website',
			'year'  => '2025',
			'hue'   => '#7c5cff',
		),
		array(
			'title' => 'Saffron Kitchen',
			'type'  => 'Restaurant & ordering',
			'year'  => '2024',
			'hue'   => '#ffb347',
		),
		array(
			'title' => 'Pixel Pulse Studio',
			'type'  => 'Branding + website',
			'year'  => '2024',
			'hue'   => '#3dd6c6',
		),
	);
}

/**
 * Placeholder testimonials shown until real Testimonials are added.
 *
 * @return array
 */
function nabia_demo_testimonials() {
	return array(
		array(
			'name'  => __( 'Happy client', 'nabia' ),
			'role'  => __( 'Business owner', 'nabia' ),
			'quote' => __( 'Nabia Khan has been a pleasure to work with for my website! She understands how to translate the needs of your business into a website that resonates with your audience. Her professionalism, creativity and technical proficiency are displayed throughout the whole process.', 'nabia' ),
		),
		array(
			'name'  => __( 'Your next client', 'nabia' ),
			'role'  => __( 'Add real reviews in Testimonials', 'nabia' ),
			'quote' => __( 'Fast communication, clean design and a website that finally feels like our brand. Every small request was handled the same day.', 'nabia' ),
		),
		array(
			'name'  => __( 'Another client', 'nabia' ),
			'role'  => __( 'Edit me in the dashboard', 'nabia' ),
			'quote' => __( 'Our online store is faster, prettier and converting better than ever. I would recommend Nabia to anyone who needs WordPress done right.', 'nabia' ),
		),
	);
}

/**
 * Pagination wrapper.
 */
function nabia_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( '← Prev', 'nabia' ),
			'next_text' => __( 'Next →', 'nabia' ),
		)
	);
}

/**
 * Menu shown until a Primary menu is assigned: links to the one-page sections.
 *
 * @param array $args wp_nav_menu() arguments.
 */
function nabia_menu_fallback( $args ) {
	$base  = is_front_page() ? '' : home_url( '/' );
	$items = array(
		'#services' => __( 'Services', 'nabia' ),
		'#work'     => __( 'Work', 'nabia' ),
		'#about'    => __( 'About', 'nabia' ),
		'#process'  => __( 'Process', 'nabia' ),
		'#contact'  => __( 'Contact', 'nabia' ),
	);
	$class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'menu';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $hash => $label ) {
		printf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( $base . $hash ), esc_html( $label ) );
	}
	echo '</ul>';
}
