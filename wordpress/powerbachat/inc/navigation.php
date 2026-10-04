<?php
/**
 * Site map shared by the header menu and the footer: every page grouped by country
 * and section, plus the company and legal pages. Links only appear once the page
 * exists, so a fresh install never shows a menu full of 404s.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sections per country. Each section: label, url (section landing page) and items.
 * Bill calculator items are filled from the utility list in inc/data.php.
 *
 * @return array
 */
function powerbachat_site_map() {
	$map = array(
		'pk' => array(
			'bills' => array(
				'label' => __( 'Bill calculators', 'powerbachat' ),
				'url'   => '/pk/electricity-bill-calculator/',
				'items' => array(
					array( __( 'Electricity bill calculator', 'powerbachat' ), '/pk/electricity-bill-calculator/' ),
				),
			),
			'rates' => array(
				'label' => __( 'Unit rates', 'powerbachat' ),
				'url'   => '/pk/electricity-tariff/',
				'items' => array(
					array( __( 'Electricity price per unit', 'powerbachat' ), '/pk/electricity-tariff/' ),
					array( __( 'LESCO unit price', 'powerbachat' ), '/pk/lesco-unit-price/' ),
					array( __( 'K-Electric unit price', 'powerbachat' ), '/pk/k-electric-unit-price/' ),
				),
			),
			'solar' => array(
				'label' => __( 'Solar', 'powerbachat' ),
				'url'   => '/pk/solar-calculator/',
				'items' => array(
					array( __( 'Solar calculator', 'powerbachat' ), '/pk/solar-calculator/' ),
					array( __( 'Solar panel price today', 'powerbachat' ), '/pk/solar-panel-price-today/' ),
					array( __( 'Cost of solar panels', 'powerbachat' ), '/pk/cost-of-solar-panels/' ),
					array( __( '5kW solar system price', 'powerbachat' ), '/pk/5kw-solar-system-price/' ),
					array( __( 'Battery backup calculator', 'powerbachat' ), '/pk/battery-backup-calculator/' ),
					array( __( 'Net metering vs net billing', 'powerbachat' ), '/pk/net-metering-vs-net-billing/' ),
				),
			),
		),
		'in' => array(
			'bills' => array(
				'label' => __( 'Bill calculators', 'powerbachat' ),
				'url'   => '/in/electricity-bill-unit-rate-calculator/',
				'items' => array(),
			),
			'rates' => array(
				'label' => __( 'Unit rates', 'powerbachat' ),
				'url'   => '/in/electricity-bill-unit-rate-calculator/',
				'items' => array(
					array( __( 'Unit rate calculator', 'powerbachat' ), '/in/electricity-bill-unit-rate-calculator/' ),
					array( __( 'How your bill is calculated', 'powerbachat' ), '/in/how-electricity-bill-is-calculated/' ),
				),
			),
			'solar' => array(
				'label' => __( 'Solar', 'powerbachat' ),
				'url'   => '/in/pm-surya-ghar-calculator/',
				'items' => array(
					array( __( 'PM Surya Ghar calculator', 'powerbachat' ), '/in/pm-surya-ghar-calculator/' ),
					array( __( 'Solar subsidy calculator', 'powerbachat' ), '/in/solar-subsidy-calculator/' ),
					array( __( 'Solar power price calculator', 'powerbachat' ), '/in/solar-power-price-calculator/' ),
					array( __( 'Solar installation cost', 'powerbachat' ), '/in/solar-installation-cost-calculator/' ),
					array( __( 'Solar panel price', 'powerbachat' ), '/in/solar-panel-price/' ),
					array( __( '3kW solar system price', 'powerbachat' ), '/in/3kw-solar-system-price/' ),
					array( __( '5kW solar system price', 'powerbachat' ), '/in/5kw-solar-system-price/' ),
					array( __( 'Inverter battery backup', 'powerbachat' ), '/in/inverter-battery-backup-calculator/' ),
				),
			),
		),
		'bd' => array(
			'bills' => array(
				'label' => __( 'Bill calculators', 'powerbachat' ),
				'url'   => '/bd/electricity-bill-calculator/',
				'items' => array(
					array( __( 'Electricity bill calculator', 'powerbachat' ), '/bd/electricity-bill-calculator/' ),
				),
			),
			'rates' => array(
				'label' => __( 'Tariff', 'powerbachat' ),
				'url'   => '/bd/electricity-tariff/',
				'items' => array(
					array( __( 'Electricity tariff', 'powerbachat' ), '/bd/electricity-tariff/' ),
				),
			),
			'solar' => array(
				'label' => __( 'Solar and IPS', 'powerbachat' ),
				'url'   => '/bd/solar-system-price/',
				'items' => array(
					array( __( 'Solar system price', 'powerbachat' ), '/bd/solar-system-price/' ),
					array( __( 'Solar panel price', 'powerbachat' ), '/bd/solar-panel-price/' ),
					array( __( 'Solar battery price', 'powerbachat' ), '/bd/solar-battery-price/' ),
					array( __( 'IPS price', 'powerbachat' ), '/bd/ips-price/' ),
					array( __( 'IPS calculator', 'powerbachat' ), '/bd/ips-calculator/' ),
				),
			),
		),
	);

	// Company calculators from inc/data.php, skipping ones that share a general page.
	foreach ( powerbachat_data()['countries'] as $code => $country ) {
		if ( ! isset( $map[ $code ] ) ) {
			continue;
		}
		$seen = wp_list_pluck( $map[ $code ]['bills']['items'], 1 );
		foreach ( $country['utilities'] as $utility ) {
			if ( in_array( $utility['url'], $seen, true ) ) {
				continue;
			}
			$seen[] = $utility['url'];
			/* translators: %s: company abbreviation */
			$map[ $code ]['bills']['items'][] = array( sprintf( __( '%s bill calculator', 'powerbachat' ), $utility['abbr'] ), $utility['url'] );
		}
	}

	return apply_filters( 'powerbachat_site_map', $map );
}

/**
 * Company and legal pages, the same in every country.
 *
 * @return array[] label, path.
 */
function powerbachat_company_pages() {
	return apply_filters(
		'powerbachat_company_pages',
		array(
			array( __( 'About us', 'powerbachat' ), '/about-us/' ),
			array( __( 'How we check rates', 'powerbachat' ), '/editorial-policy/' ),
			array( __( 'Contact us', 'powerbachat' ), '/contact-us/' ),
			array( __( 'Privacy policy', 'powerbachat' ), '/privacy-policy/' ),
			array( __( 'Terms of use', 'powerbachat' ), '/terms-of-use/' ),
			array( __( 'Disclaimer', 'powerbachat' ), '/disclaimer/' ),
			array( __( 'Cookie policy', 'powerbachat' ), '/cookie-policy/' ),
		)
	);
}

/**
 * Paths of every published page, read once per request.
 *
 * @return array<string,bool>
 */
function powerbachat_published_paths() {
	static $paths = null;
	if ( null === $paths ) {
		$paths = array();
		$ids   = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $ids as $id ) {
			$paths[ '/' . get_page_uri( $id ) . '/' ] = true;
		}
	}
	return $paths;
}

/**
 * Does a published page live at this path?
 *
 * @param string $path Path such as /pk/solar-calculator/.
 * @return bool
 */
function powerbachat_page_exists( $path ) {
	return isset( powerbachat_published_paths()[ $path ] );
}

/**
 * Keep only links whose page exists.
 *
 * @param array[] $items label, path pairs.
 * @return array[]
 */
function powerbachat_live_links( $items ) {
	return array_values(
		array_filter(
			$items,
			function ( $item ) {
				return powerbachat_page_exists( $item[1] );
			}
		)
	);
}

/**
 * The visitor's country category, for "Guides" links.
 *
 * @param string $code pk, in or bd.
 * @return WP_Term|null
 */
function powerbachat_country_category( $code ) {
	$slugs = array(
		'pk' => array( 'pakistan', 'pk' ),
		'in' => array( 'india', 'in' ),
		'bd' => array( 'bangladesh', 'bd' ),
	);
	foreach ( isset( $slugs[ $code ] ) ? $slugs[ $code ] : array() as $slug ) {
		$term = get_category_by_slug( $slug );
		if ( $term ) {
			return $term;
		}
	}
	return null;
}

/**
 * URL of the guides list for a country: its category archive, else the posts page.
 *
 * @param string $code Country code.
 * @return string
 */
function powerbachat_guides_url( $code ) {
	$term = powerbachat_country_category( $code );
	if ( $term && $term->count ) {
		return get_category_link( $term );
	}
	$page = (int) get_option( 'page_for_posts' );
	return $page ? get_permalink( $page ) : home_url( '/#guides' );
}

/**
 * Main bill calculator page for a country, used by the "Check my bill" button.
 *
 * @param string $code Country code.
 * @return string
 */
function powerbachat_bill_url( $code ) {
	$map = powerbachat_site_map();
	$url = isset( $map[ $code ] ) ? $map[ $code ]['bills']['url'] : '';
	return $url && powerbachat_page_exists( $url ) ? home_url( $url ) : home_url( '/#calculator' );
}

/**
 * Primary menu fallback: real pages for each country, with dropdowns.
 * Used until a menu is assigned to the "Primary menu" location.
 */
function powerbachat_primary_fallback() {
	$current = home_url( add_query_arg( array() ) );
	$current = trailingslashit( strtok( $current, '?' ) );
	$anchors = array(
		'bills' => '/#utilities',
		'rates' => '/#slabs',
		'solar' => '/#solar',
	);

	echo '<ul class="nav__list">';
	foreach ( powerbachat_site_map() as $code => $sections ) {
		foreach ( $sections as $key => $section ) {
			$items = powerbachat_live_links( $section['items'] );
			$url   = powerbachat_page_exists( $section['url'] ) ? home_url( $section['url'] ) : home_url( $anchors[ $key ] );
			$open  = false;
			foreach ( $items as $item ) {
				if ( home_url( $item[1] ) === $current ) {
					$open = true;
				}
			}
			$classes = array( 'menu-item' );
			if ( count( $items ) > 1 ) {
				$classes[] = 'menu-item-has-children';
			}
			if ( $open || $url === $current ) {
				$classes[] = 'current-menu-ancestor';
			}
			printf( '<li class="%s" data-only="%s"><a href="%s">%s</a>', esc_attr( implode( ' ', $classes ) ), esc_attr( $code ), esc_url( $url ), esc_html( $section['label'] ) );
			if ( count( $items ) > 1 ) {
				printf( '<ul class="sub-menu%s">', count( $items ) > 7 ? ' sub-menu--wide' : '' );
				foreach ( $items as $item ) {
					$link = home_url( $item[1] );
					printf(
						'<li class="menu-item%s"><a href="%s"%s>%s</a></li>',
						$link === $current ? ' current-menu-item' : '',
						esc_url( $link ),
						$link === $current ? ' aria-current="page"' : '',
						esc_html( $item[0] )
					);
				}
				echo '</ul>';
			}
			echo '</li>';
		}
		printf( '<li class="menu-item" data-only="%s"><a href="%s">%s</a></li>', esc_attr( $code ), esc_url( powerbachat_guides_url( $code ) ), esc_html__( 'Guides', 'powerbachat' ) );
	}
	echo '</ul>';
}

/**
 * Nicer titles for the country guide archives.
 *
 * @param string $title Archive title.
 * @return string
 */
function powerbachat_archive_title( $title ) {
	if ( is_category() ) {
		$code = powerbachat_category_country( get_queried_object() );
		if ( $code ) {
			/* translators: %s: country name */
			return sprintf( __( '%s electricity and solar guides', 'powerbachat' ), powerbachat_data()['countries'][ $code ]['name'] );
		}
		return single_cat_title( '', false );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'powerbachat_archive_title' );

/**
 * Country code for a country category.
 *
 * @param WP_Term|null $term Category.
 * @return string
 */
function powerbachat_category_country( $term ) {
	if ( ! $term || empty( $term->slug ) ) {
		return '';
	}
	$map = array(
		'pakistan'   => 'pk',
		'pk'         => 'pk',
		'india'      => 'in',
		'in'         => 'in',
		'bangladesh' => 'bd',
		'bd'         => 'bd',
	);
	return isset( $map[ $term->slug ] ) ? $map[ $term->slug ] : '';
}
