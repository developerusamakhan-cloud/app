<?php
/**
 * Structured data (JSON-LD).
 *
 * - Organization + WebSite: front page.
 * - WebApplication: pages with type "tool".
 * - Article: guides, state, injury and insurer pages, and blog posts.
 * - BreadcrumbList: every inner page.
 * - FAQPage: any page using [cf_faq] (printed in the footer, after content renders).
 *
 * If Yoast SEO or Rank Math is active they already output Organization,
 * WebSite, Article and BreadcrumbList, so the theme only adds WebApplication
 * and FAQPage to avoid duplicates. Override with the
 * `claimfairly_seo_plugin_handles_schema` filter.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether an SEO plugin already prints the base schema graph.
 *
 * @return bool
 */
function claimfairly_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' );
	return (bool) apply_filters( 'claimfairly_seo_plugin_handles_schema', $active );
}

/**
 * Print one JSON-LD block.
 *
 * @param array $data Schema data.
 */
function claimfairly_print_jsonld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}

/**
 * Organization node.
 *
 * @return array
 */
function claimfairly_org_schema() {
	$org = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);
	$email = claimfairly_opt( 'cf_contact_email' );
	if ( $email ) {
		$org['email'] = $email;
	}
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $logo ) {
			$org['logo'] = $logo;
		}
	}
	$same_as = array_filter( preg_split( '/\r\n|\r|\n/', claimfairly_opt( 'cf_same_as' ) ) );
	if ( $same_as ) {
		$org['sameAs'] = array_values( $same_as );
	}
	return $org;
}

/**
 * Head schema.
 */
function claimfairly_head_schema() {
	$plugin = claimfairly_seo_plugin_active();
	$graph  = array();

	if ( is_front_page() && ! $plugin ) {
		$graph[] = claimfairly_org_schema();
		$graph[] = array(
			'@type'     => 'WebSite',
			'@id'       => home_url( '/#website' ),
			'url'       => home_url( '/' ),
			'name'      => get_bloginfo( 'name' ),
			'publisher' => array( '@id' => home_url( '/#organization' ) ),
		);
	}

	if ( is_singular( array( 'post', 'page' ) ) && ! is_front_page() ) {
		$post_id  = get_queried_object_id();
		$type     = claimfairly_get_page_type( $post_id );
		$reviewed = claimfairly_last_reviewed_raw( $post_id );
		$modified = $reviewed ? $reviewed : get_the_modified_date( 'c', $post_id );
		$author   = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) );

		if ( 'tool' === $type ) {
			$graph[] = array(
				'@type'               => 'WebApplication',
				'name'                => get_the_title( $post_id ),
				'url'                 => get_permalink( $post_id ),
				'description'         => claimfairly_card_summary( get_post( $post_id ) ),
				'applicationCategory' => 'FinanceApplication',
				'operatingSystem'     => 'Any',
				'browserRequirements' => 'Requires JavaScript',
				'isAccessibleForFree' => true,
				'offers'              => array(
					'@type'         => 'Offer',
					'price'         => '0',
					'priceCurrency' => 'USD',
				),
				'dateModified'        => $modified,
				'publisher'           => array(
					'@type' => 'Organization',
					'name'  => get_bloginfo( 'name' ),
					'url'   => home_url( '/' ),
				),
			);
		} elseif ( 'standard' !== $type && ! $plugin ) {
			$article = array(
				'@type'            => 'Article',
				'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
				'mainEntityOfPage' => get_permalink( $post_id ),
				'datePublished'    => get_the_date( 'c', $post_id ),
				'dateModified'     => $modified,
				'publisher'        => array(
					'@type' => 'Organization',
					'name'  => get_bloginfo( 'name' ),
					'url'   => home_url( '/' ),
				),
			);
			if ( $author ) {
				$article['author'] = array(
					'@type' => 'Person',
					'name'  => $author,
					'url'   => claimfairly_about_url(),
				);
			}
			if ( has_post_thumbnail( $post_id ) ) {
				$article['image'] = get_the_post_thumbnail_url( $post_id, 'full' );
			}
			$graph[] = $article;
		}
	}

	if ( ! is_front_page() && ! $plugin ) {
		$items = claimfairly_breadcrumb_items();
		if ( count( $items ) > 1 ) {
			$list = array();
			foreach ( $items as $i => $item ) {
				$list[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => wp_strip_all_tags( $item['name'] ),
					'item'     => $item['url'],
				);
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $list,
			);
		}
	}

	if ( $graph ) {
		claimfairly_print_jsonld(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			)
		);
	}
}
add_action( 'wp_head', 'claimfairly_head_schema', 20 );

/**
 * FAQPage schema from [cf_q] items rendered on this page.
 */
function claimfairly_faq_schema() {
	if ( ! is_singular() ) {
		return;
	}
	$seen     = array();
	$entities = array();
	foreach ( claimfairly_faq_store() as $item ) {
		if ( isset( $seen[ $item['q'] ] ) || '' === $item['a'] ) {
			continue;
		}
		$seen[ $item['q'] ] = true;
		$entities[]         = array(
			'@type'          => 'Question',
			'name'           => $item['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $item['a'],
			),
		);
	}
	if ( ! $entities ) {
		return;
	}
	claimfairly_print_jsonld(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		)
	);
}
add_action( 'wp_footer', 'claimfairly_faq_schema', 20 );
