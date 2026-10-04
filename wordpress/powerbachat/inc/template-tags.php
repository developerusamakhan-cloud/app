<?php
/**
 * Small template helpers: logo, icons, menus, post meta.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand mark: a meter dial whose needle sits just inside the "saving" arc.
 *
 * @param string $class Extra class.
 */
function powerbachat_mark( $class = '' ) {
	?>
	<svg class="brand-mark <?php echo esc_attr( $class ); ?>" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
		<circle cx="20" cy="20" r="19" class="brand-mark__disc" />
		<path d="M8.5 25.5a12 12 0 0 1 23 0" class="brand-mark__track" />
		<path d="M8.5 25.5a12 12 0 0 1 9.2-11.3" class="brand-mark__arc" />
		<path d="M20 25.5 14.6 15.8" class="brand-mark__needle" />
		<circle cx="20" cy="25.5" r="2.4" class="brand-mark__hub" />
	</svg>
	<?php
}

/**
 * Site logo: custom logo if uploaded, otherwise the mark + wordmark.
 */
function powerbachat_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php powerbachat_mark(); ?>
		<span class="brand__word">Power<em>Bachat</em></span>
		<span class="screen-reader-text"><?php bloginfo( 'name' ); ?></span>
	</a>
	<?php
}

/**
 * Inline stroke icons (24px grid, 1.75 stroke).
 *
 * @param string $name Icon name.
 * @return string SVG markup.
 */
function powerbachat_icon( $name ) {
	$paths = array(
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-ne' => '<path d="M7 17 17 7M9 7h8v8"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h10"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',
		'up'       => '<path d="M12 19V5M6 11l6-6 6 6"/>',
		'down'     => '<path d="M12 5v14M6 13l6 6 6-6"/>',
		'flat'     => '<path d="M5 12h14"/>',
		'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'bolt'     => '<path d="M13 3 5 13.5h6L10 21l8-10.5h-6L13 3Z"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.3 5.3l1.4 1.4M17.3 17.3l1.4 1.4M5.3 18.7l1.4-1.4M17.3 6.7l1.4-1.4"/>',
		'table'    => '<rect x="3.5" y="5" width="17" height="14" rx="1.5"/><path d="M3.5 10h17M3.5 14.5h17M9.5 10v9"/>',
		'chat'     => '<path d="M4 19.5 5.3 16A8 8 0 1 1 8 18.7L4 19.5Z"/>',
		'mail'     => '<rect x="3.5" y="5.5" width="17" height="13" rx="1.5"/><path d="m4 7 8 6 8-6"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="icon icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Approximate reading time in minutes.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function powerbachat_reading_time( $post_id = null ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Date + reading time line.
 */
function powerbachat_post_meta() {
	printf(
		'<p class="post-meta"><time datetime="%1$s">%2$s</time><span aria-hidden="true">·</span><span>%3$s</span></p>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		/* translators: %d: minutes */
		esc_html( sprintf( _n( '%d min read', '%d min read', powerbachat_reading_time(), 'powerbachat' ), powerbachat_reading_time() ) )
	);
}

/**
 * Format a money amount for server-rendered snippets.
 *
 * @param float  $amount   Amount.
 * @param string $currency Symbol.
 * @param int    $decimals Decimals.
 * @return string
 */
function powerbachat_money( $amount, $currency = 'Rs', $decimals = 0 ) {
	return $currency . ' ' . number_format_i18n( $amount, $decimals );
}

/**
 * Match the current page URL to a utility in the data set, so calculator pages
 * created at the planned URLs (e.g. /pk/lesco-bill-calculator/) configure themselves.
 *
 * @param int|null $post_id Page ID.
 * @return array{country:string,utility:string}
 */
function powerbachat_context_for_page( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$country = (string) get_post_meta( $post_id, 'pb_country', true );
	$utility = (string) get_post_meta( $post_id, 'pb_utility', true );

	if ( $country && $utility ) {
		return compact( 'country', 'utility' );
	}

	$path = trailingslashit( (string) wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH ) );
	foreach ( powerbachat_data()['countries'] as $code => $data ) {
		foreach ( $data['utilities'] as $item ) {
			if ( trailingslashit( wp_parse_url( home_url( $item['url'] ), PHP_URL_PATH ) ) === $path ) {
				return array(
					'country' => $code,
					'utility' => $item['id'],
				);
			}
		}
		if ( ! $country && 0 === strpos( $path, wp_parse_url( home_url( $data['hub'] ), PHP_URL_PATH ) ) ) {
			$country = $code;
		}
	}

	return compact( 'country', 'utility' );
}

/**
 * Breadcrumb trail for pages and posts.
 */
function powerbachat_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$crumbs = array( array( __( 'Home', 'powerbachat' ), home_url( '/' ) ) );
	if ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$crumbs[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
	} elseif ( is_single() ) {
		$cats = get_the_category();
		if ( $cats ) {
			$crumbs[] = array( $cats[0]->name, get_category_link( $cats[0] ) );
		}
	}
	echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'powerbachat' ) . '"><ol>';
	foreach ( $crumbs as $crumb ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $crumb[1] ), esc_html( $crumb[0] ) );
	}
	printf( '<li aria-current="page">%s</li>', esc_html( get_the_title() ) );
	echo '</ol></nav>';
}
