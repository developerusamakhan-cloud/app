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
		'chat-live' => '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9 9 0 0 1-3.9-.9L3 20.5l1.6-4.6A8 8 0 0 1 3 11.5 8.6 8.6 0 0 1 12 3a8.6 8.6 0 0 1 9 8.5z"/><circle cx="8" cy="11.5" r=".6" fill="currentColor"/><circle cx="12" cy="11.5" r=".6" fill="currentColor"/><circle cx="16" cy="11.5" r=".6" fill="currentColor"/>',
		'gchat'     => '<path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H9l-5 4V5a1 1 0 0 1 1-1z"/><path d="M8 9h8M8 12.5h5"/>',
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
		'tiktok'    => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 3c.5 2.8 2.3 4.5 5 4.8"/>',
		'linktree'  => '<path d="M12 22v-8M5 9h14M7.5 4.5 12 9l4.5-4.5M7.5 13.5 12 9l4.5 4.5"/>',
		'grid'      => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
		'terminal'  => '<rect x="2" y="4" width="20" height="16" rx="3"/><path d="m6 9 3 3-3 3M12 15h6"/>',
		'sparkles'  => '<path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/>',
		'check'     => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'volume'    => '<path d="M11 5 6 9H2v6h4l5 4z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/>',
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
 * The "N" monogram mark as inline SVG (colours follow the active scheme).
 *
 * @return string
 */
function nabia_logo_mark() {
	return '<svg class="logo-mark" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false">'
		. '<rect class="logo-mark-bg" x="1" y="1" width="46" height="46" rx="15"/>'
		. '<path class="logo-mark-n" d="M15 34V15.5a1.5 1.5 0 0 1 2.6-1l12.8 15a1.5 1.5 0 0 0 2.6-1V14"/>'
		. '<circle class="logo-mark-dot" cx="36.5" cy="36" r="4"/>'
		. '</svg>';
}

/**
 * Site logo: the built-in animated "Nabia Khan" wordmark (or an uploaded logo if enabled).
 *
 * The first word's "i" gets an accent dot that bounces on hover; the last word is a gradient.
 *
 * @param string $variant Extra class, e.g. "logo-light" for dark backgrounds.
 */
function nabia_logo( $variant = '' ) {
	if ( has_custom_logo() && nabia_mod( 'use_custom_logo' ) ) {
		the_custom_logo();
		return;
	}
	$name = trim( (string) nabia_mod( 'brand_name' ) );
	if ( '' === $name ) {
		$name = get_bloginfo( 'name' );
	}
	$words = preg_split( '/\s+/', $name, 2 );
	$first = esc_html( $words[0] );
	$last  = isset( $words[1] ) ? esc_html( $words[1] ) : '';

	// Replace the first lowercase "i" with a dotless i + animated dot.
	$pos = strpos( $words[0], 'i' );
	if ( false !== $pos ) {
		$first = esc_html( substr( $words[0], 0, $pos ) ) . '<span class="logo-i">&#305;<span class="logo-dot" aria-hidden="true"></span></span>' . esc_html( substr( $words[0], $pos + 1 ) );
	}

	printf(
		'<a class="logo %1$s" href="%2$s" rel="home" aria-label="%3$s"><span class="logo-word" aria-hidden="true"><span class="logo-first">%4$s</span>%5$s</span><svg class="logo-swoosh" viewBox="0 0 120 10" preserveAspectRatio="none" aria-hidden="true"><path d="M2 7c30-6 80-7 116-2"/></svg></a>',
		esc_attr( $variant ),
		esc_url( home_url( '/' ) ),
		esc_attr( $name ),
		$first, // Escaped above.
		$last ? ' <span class="logo-last">' . $last . '</span>' : ''
	);
}

/**
 * Social link: one Linktree button that leads to every profile.
 *
 * @param string $class Extra class.
 */
function nabia_social_links( $class = '' ) {
	$url = nabia_mod( 'social_linktree' );
	if ( ! $url ) {
		return;
	}
	printf(
		'<a class="linktree-btn %1$s" href="%2$s" target="_blank" rel="noopener noreferrer" data-magnetic><span class="linktree-icon" aria-hidden="true">%3$s</span><span>%4$s</span><span class="linktree-arrow" aria-hidden="true">%5$s</span></a>',
		esc_attr( $class ),
		esc_url( $url ),
		nabia_get_icon( 'linktree' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'All my links', 'nabia' ),
		nabia_get_icon( 'arrow-up' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Next section number on the front page ("01", "02", …). Hidden sections don't use one.
 *
 * @return string Empty string outside the front page.
 */
function nabia_next_section_number() {
	static $count = 0;
	if ( ! is_front_page() ) {
		return '';
	}
	++$count;
	return sprintf( '%02d', $count );
}

/**
 * Eyebrow label with the automatic section number.
 *
 * @param string $label Small label.
 */
function nabia_eyebrow( $label ) {
	$number = nabia_next_section_number();
	?>
	<p class="eyebrow" data-reveal>
		<?php if ( $number ) : ?>
			<span class="eyebrow-num"><?php echo esc_html( $number ); ?></span>
		<?php endif; ?>
		<?php echo esc_html( $label ); ?>
	</p>
	<?php
}

/**
 * Section heading: numbered eyebrow + big title.
 *
 * @param string $eyebrow Small label.
 * @param string $title   Big title.
 */
function nabia_section_head( $eyebrow, $title ) {
	?>
	<header class="section-head">
		<?php nabia_eyebrow( $eyebrow ); ?>
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
	if ( ! $number ) {
		return '';
	}
	$text = trim( (string) nabia_mod( 'whatsapp_message' ) );
	return 'https://wa.me/' . $number . ( $text ? '?text=' . rawurlencode( $text ) : '' );
}

/**
 * Google Chat link (empty when no Google Chat email is set).
 *
 * @return string
 */
function nabia_gchat_url() {
	return nabia_mod( 'gchat_email' ) ? ( nabia_mod( 'gchat_url' ) ? nabia_mod( 'gchat_url' ) : 'https://mail.google.com/chat/' ) : '';
}

/**
 * "Live chat" button that opens the Tawk.to chat window (installed separately).
 *
 * @param string $class Button classes.
 * @param bool   $echo  Print (true) or return.
 * @return string
 */
function nabia_livechat_button( $class = 'btn btn-ghost btn-lg', $echo = true ) {
	if ( ! nabia_mod( 'enable_livechat' ) ) {
		return '';
	}
	$html = sprintf(
		'<button type="button" class="%1$s" data-livechat>%2$s<span>%3$s</span></button>',
		esc_attr( $class ),
		nabia_get_icon( 'chat-live' ),
		esc_html( nabia_mod( 'livechat_label' ) ? nabia_mod( 'livechat_label' ) : __( 'Live chat', 'nabia' ) )
	);
	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $html;
}

/**
 * WhatsApp + Google Chat buttons for a faster reply.
 *
 * @param string $title Optional line above the buttons.
 * @param string $class Extra class.
 */
function nabia_quick_contact( $title = '', $class = '' ) {
	$wa    = nabia_whatsapp_url();
	$gchat = nabia_gchat_url();
	$live  = (bool) nabia_mod( 'enable_livechat' );
	if ( ! $wa && ! $gchat && ! $live ) {
		return;
	}
	?>
	<div class="quick-contact <?php echo esc_attr( $class ); ?>">
		<?php if ( $title ) : ?>
			<p class="quick-contact-title"><?php nabia_icon( 'bolt' ); ?><span><?php echo esc_html( $title ); ?></span></p>
		<?php endif; ?>
		<div class="quick-contact-buttons">
			<?php if ( $wa ) : ?>
				<a class="qc-btn qc-whatsapp" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer">
					<?php nabia_icon( 'whatsapp' ); ?>
					<span><strong><?php esc_html_e( 'WhatsApp', 'nabia' ); ?></strong></span>
				</a>
			<?php endif; ?>
			<?php if ( $gchat ) : ?>
				<a class="qc-btn qc-gchat" href="<?php echo esc_url( $gchat ); ?>" target="_blank" rel="noopener noreferrer" data-copy="<?php echo esc_attr( nabia_mod( 'gchat_email' ) ); ?>" title="<?php esc_attr_e( 'Chat with me on Google Chat', 'nabia' ); ?>">
					<?php nabia_icon( 'gchat' ); ?>
					<span><strong><?php esc_html_e( 'Google Chat', 'nabia' ); ?></strong></span>
				</a>
			<?php endif; ?>
			<?php if ( $live ) : ?>
				<button type="button" class="qc-btn qc-live" data-livechat>
					<?php nabia_icon( 'chat-live' ); ?>
					<span><strong><?php echo esc_html( nabia_mod( 'livechat_label' ) ? nabia_mod( 'livechat_label' ) : __( 'Live chat', 'nabia' ) ); ?></strong></span>
				</button>
			<?php endif; ?>
		</div>
	</div>
	<?php
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
	$page  = function ( $role, $hash ) use ( $base ) {
		$url = function_exists( 'nabia_page_url' ) ? nabia_page_url( $role ) : '';
		return $url ? $url : $base . $hash;
	};
	$items = array(
		array( $page( 'services', '#services' ), __( 'Services', 'nabia' ) ),
		array( function_exists( 'nabia_portfolio_url' ) ? nabia_portfolio_url() : $base . '#work', __( 'Work', 'nabia' ) ),
		array( $page( 'pricing', '#pricing' ), __( 'Pricing', 'nabia' ) ),
		array( $page( 'about', '#about' ), __( 'About', 'nabia' ) ),
		array( $page( 'audit', '#audit' ), __( 'Free audit', 'nabia' ) ),
		array( function_exists( 'nabia_hire_url' ) ? nabia_hire_url() : $base . '#contact', __( 'Contact', 'nabia' ) ),
	);
	$class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'menu';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $item ) {
		printf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( $item[0] ), esc_html( $item[1] ) );
	}
	echo '</ul>';
}

/**
 * Colour schemes offered in the Customizer.
 *
 * Each: label, bg, surface, ink, accent, accent_ink (text on accent), second, third.
 *
 * @return array
 */
function nabia_color_schemes() {
	return apply_filters(
		'nabia_color_schemes',
		array(
			'violet'   => array(
				'label'       => __( 'Violet & Pink (default)', 'nabia' ),
				'bg'          => '#fbfaff',
				'bg_alt'      => '#f1edfd',
				'ink'         => '#1a1433',
				'accent'      => '#7c3aed',
				'accent_ink'  => '#ffffff',
				'accent_dark' => '#b9a4ff',
				'second'      => '#ec4899',
				'third'       => '#fbbf24',
			),
			'navy'     => array(
				'label'       => __( 'Navy & Gold', 'nabia' ),
				'bg'          => '#f8f7f4',
				'bg_alt'      => '#eeebe4',
				'ink'         => '#0f1b2d',
				'accent'      => '#1d4ed8',
				'accent_ink'  => '#ffffff',
				'accent_dark' => '#8fb0ff',
				'second'      => '#f59e0b',
				'third'       => '#10b981',
			),
			'coral'    => array(
				'label'       => __( 'Coral & Indigo', 'nabia' ),
				'bg'          => '#f7f2ec',
				'bg_alt'      => '#efe6dc',
				'ink'         => '#16121a',
				'accent'      => '#ff5a36',
				'accent_ink'  => '#16121a',
				'accent_dark' => '#ff7a5c',
				'second'      => '#4f46e5',
				'third'       => '#ffc83d',
			),
			'ocean'    => array(
				'label'       => __( 'Mint & Ocean', 'nabia' ),
				'bg'          => '#f1f5f4',
				'bg_alt'      => '#e2ebe9',
				'ink'         => '#0b1a24',
				'accent'      => '#0f9d84',
				'accent_ink'  => '#ffffff',
				'accent_dark' => '#3ee0bf',
				'second'      => '#2457ff',
				'third'       => '#ffb547',
			),
			'lime'     => array(
				'label'       => __( 'Electric Lime', 'nabia' ),
				'bg'          => '#f4f1ea',
				'bg_alt'      => '#ebe6db',
				'ink'         => '#0e0e10',
				'accent'      => '#c6ff3d',
				'accent_ink'  => '#0e0e10',
				'accent_dark' => '#c6ff3d',
				'second'      => '#7c5cff',
				'third'       => '#ff8fab',
			),
			'sunshine' => array(
				'label'       => __( 'Sunshine & Pink', 'nabia' ),
				'bg'          => '#fbf8f1',
				'bg_alt'      => '#f3ecdc',
				'ink'         => '#141414',
				'accent'      => '#ffd23f',
				'accent_ink'  => '#141414',
				'accent_dark' => '#ffd23f',
				'second'      => '#ff3d7f',
				'third'       => '#2ec4b6',
			),
		)
	);
}

/**
 * Build the CSS custom properties for the chosen scheme (+ optional accent override).
 *
 * @return string
 */
function nabia_scheme_css() {
	$schemes = nabia_color_schemes();
	$key     = nabia_mod( 'color_scheme' );
	$scheme  = isset( $schemes[ $key ] ) ? $schemes[ $key ] : reset( $schemes );

	$override = sanitize_hex_color( nabia_mod( 'accent_color' ) );
	if ( $override ) {
		$scheme['accent']      = $override;
		$scheme['accent_dark'] = $override;
	}

	$map = array(
		'bg'         => '--bg',
		'bg_alt'     => '--bg-alt',
		'ink'        => '--ink',
		'accent'     => '--accent',
		'accent_ink' => '--accent-ink',
		'accent_dark' => '--accent-dk',
		'second'     => '--second',
		'third'      => '--third',
	);
	$css = '';
	foreach ( $map as $field => $var ) {
		$value = isset( $scheme[ $field ] ) ? sanitize_hex_color( $scheme[ $field ] ) : '';
		if ( $value ) {
			$css .= $var . ':' . $value . ';';
		}
	}
	return ':root{' . $css . '}';
}

/**
 * Extract a YouTube video ID from any common URL format (watch, youtu.be, shorts, embed, live).
 *
 * @param string $url URL or bare ID.
 * @return string Video ID or empty string.
 */
function nabia_youtube_id( $url ) {
	$url = trim( $url );
	if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $url ) ) {
		return $url;
	}
	if ( preg_match( '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/|v/))([A-Za-z0-9_-]{11})~', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Is this a direct link to a video file (self-hosted MP4 etc.)?
 *
 * @param string $url URL.
 * @return bool
 */
function nabia_is_video_file( $url ) {
	return (bool) preg_match( '#^https?://\S+\.(mp4|m4v|webm|mov|ogv)(\?\S*)?$#i', trim( $url ) );
}

/**
 * Guess a client name from a video file name.
 * "vidssave.com-Dr-Craig-Duncan-_-Happy-Client-_-Nabia-Khan-480P.mp4" → "Dr Craig Duncan".
 *
 * @param string $url Video URL.
 * @return string
 */
function nabia_name_from_video_url( $url ) {
	$name = rawurldecode( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_FILENAME ) );
	$name = preg_replace( '/^[a-z0-9]+\.(com|net|org|io|app|co)[-_]/i', '', $name ); // Downloader prefixes like "vidssave.com-".
	$parts = preg_split( '/-_-|_-_|__/', $name );
	$name  = $parts[0];
	$name  = preg_replace( '/[-_](\d{3,4}p|hd|final|v\d+)$/i', '', $name );
	$name  = trim( preg_replace( '/[-_\s]+/', ' ', $name ) );
	return ucwords( $name );
}

/**
 * Build one video entry from a link.
 *
 * @param string $url     YouTube link or video file URL.
 * @param string $name    Client name (optional; guessed from the file name for MP4s).
 * @param string $role    Company / role.
 * @param string $caption Short caption.
 * @return array|null
 */
function nabia_video_entry( $url, $name = '', $role = '', $caption = '' ) {
	$url = trim( $url );
	if ( nabia_is_video_file( $url ) ) {
		return array(
			'type'     => 'file',
			'id'       => 'file-' . md5( $url ),
			'src'      => $url,
			'name'     => $name ? $name : nabia_name_from_video_url( $url ),
			'role'     => $role,
			'caption'  => $caption,
			'vertical' => true,
		);
	}
	$id = nabia_youtube_id( $url );
	if ( ! $id ) {
		return null;
	}
	return array(
		'type'     => 'youtube',
		'id'       => $id,
		'src'      => '',
		'name'     => $name,
		'role'     => $role,
		'caption'  => $caption,
		'vertical' => false !== strpos( $url, '/shorts/' ),
	);
}

/**
 * All video reviews: from Dashboard → Video Reviews first, then the Customizer list.
 *
 * Customizer format, one per line: link | Client name | Short caption.
 * Links can be self-hosted MP4 files or YouTube videos/Shorts.
 *
 * @return array[] Each: type (file|youtube), id, src, name, role, caption, vertical.
 */
function nabia_video_reviews() {
	$videos = array();

	$posts = get_posts(
		array(
			'post_type'      => 'video_review',
			'posts_per_page' => 50,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		)
	);
	foreach ( $posts as $post ) {
		$entry = nabia_video_entry(
			(string) get_post_meta( $post->ID, '_nabia_youtube', true ),
			get_the_title( $post ),
			(string) get_post_meta( $post->ID, '_nabia_video_role', true ),
			(string) get_post_meta( $post->ID, '_nabia_video_caption', true )
		);
		if ( $entry ) {
			$videos[] = $entry;
		}
	}

	$lines = preg_split( '/\r\n|\r|\n/', (string) nabia_mod( 'video_reviews' ) );
	foreach ( $lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		$entry = nabia_video_entry( $parts[0], isset( $parts[1] ) ? $parts[1] : '', '', isset( $parts[2] ) ? $parts[2] : '' );
		if ( $entry ) {
			$videos[] = $entry;
		}
	}
	return $videos;
}

/**
 * Escape text for a large headline, pulling trailing punctuation closer.
 *
 * @param string $text Text.
 * @return string Safe HTML.
 */
function nabia_tight( $text ) {
	$out = '';
	foreach ( preg_split( '/([.,?!]+)/', (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ) as $part ) {
		$out .= preg_match( '/^[.,?!]+$/', $part ) ? '<span class="punct">' . esc_html( $part ) . '</span>' : esc_html( $part );
	}
	return $out;
}

/**
 * Use the author photo from the Customizer for every registered user's avatar
 * (post bylines, author box, comments by the site owner, admin bar).
 *
 * @param array $args        Avatar data.
 * @param mixed $id_or_email User ID, email, WP_User, WP_Post or WP_Comment.
 * @return array
 */
function nabia_author_avatar( $args, $id_or_email ) {
	$photo = nabia_author_photo_url();
	if ( ! $photo ) {
		return $args;
	}
	$user_id = 0;
	if ( is_numeric( $id_or_email ) ) {
		$user_id = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user_id = (int) $id_or_email->post_author;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$user_id = (int) $id_or_email->user_id;
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$user    = get_user_by( 'email', $id_or_email );
		$user_id = $user ? $user->ID : 0;
	}
	if ( $user_id && get_userdata( $user_id ) ) {
		$args['url']          = $photo;
		$args['found_avatar'] = true;
	}
	return $args;
}
add_filter( 'pre_get_avatar_data', 'nabia_author_avatar', 99, 2 );

/**
 * Author photo URL: the Customizer setting, or the built in default when it is empty.
 *
 * @return string
 */
function nabia_author_photo_url() {
	$photo = get_theme_mod( 'author_image', '' );
	if ( ! $photo ) {
		$defaults = nabia_defaults();
		$photo    = $defaults['author_image'];
	}
	return apply_filters( 'nabia_author_photo', $photo );
}

/**
 * The author photo as an <img>, printed directly so avatar plugins or the
 * "Show Avatars" setting can never swap it for the grey default.
 *
 * @param int $size Size in pixels.
 * @return string
 */
function nabia_author_photo( $size = 64 ) {
	return sprintf(
		'<img class="avatar avatar-%1$d photo" src="%2$s" alt="%3$s" width="%1$d" height="%1$d" loading="lazy" decoding="async">',
		(int) $size,
		esc_url( nabia_author_photo_url() ),
		esc_attr( get_the_author() ? get_the_author() : nabia_mod( 'brand_name' ) )
	);
}
