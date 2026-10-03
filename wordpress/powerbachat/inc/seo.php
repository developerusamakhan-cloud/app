<?php
/**
 * Lightweight SEO output for pages and posts imported from the theme.
 *
 * Each imported item carries pb_seo_title, pb_meta_description and pb_focus_keyword.
 * When Yoast SEO, Rank Math, All in One SEO or SEOPress is active, the importer also
 * fills that plugin's fields and this file stays silent so nothing is printed twice.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is a dedicated SEO plugin handling titles and descriptions?
 *
 * @return bool
 */
function powerbachat_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Custom document title for singular content.
 *
 * @param string $title Title.
 * @return string
 */
function powerbachat_seo_title( $title ) {
	if ( powerbachat_seo_plugin_active() || ! is_singular() ) {
		return $title;
	}
	$custom = get_post_meta( get_queried_object_id(), 'pb_seo_title', true );
	return $custom ? $custom : $title;
}
add_filter( 'pre_get_document_title', 'powerbachat_seo_title' );

/**
 * Meta description and basic Open Graph tags.
 */
function powerbachat_seo_meta() {
	if ( powerbachat_seo_plugin_active() ) {
		return;
	}
	$desc  = '';
	$title = wp_get_document_title();
	if ( is_singular() ) {
		$id   = get_queried_object_id();
		$desc = get_post_meta( $id, 'pb_meta_description', true );
		if ( ! $desc && has_excerpt( $id ) ) {
			$desc = get_the_excerpt( $id );
		}
	} elseif ( is_front_page() ) {
		$desc = get_bloginfo( 'description' );
	}
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $desc ) ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $desc ) ) );
	}
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
	if ( is_singular() ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( get_permalink() ) );
		if ( has_post_thumbnail() ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( get_the_post_thumbnail_url( null, 'large' ) ) );
		}
	}
}
add_action( 'wp_head', 'powerbachat_seo_meta', 2 );

/**
 * Question and answer pairs from a page's FAQ section: the h3/paragraph pairs that
 * follow an h2 containing "questions" or "FAQ".
 *
 * @param string $content Post content.
 * @return array[] Each: q, a.
 */
function powerbachat_content_faqs( $content ) {
	if ( ! preg_match( '/<h2[^>]*>[^<]*(questions|faq)[^<]*<\/h2>(.*?)(?=<h2|$)/is', $content, $m ) ) {
		return array();
	}
	preg_match_all( '/<h3[^>]*>(.*?)<\/h3>\s*(?:<!--[^>]*-->\s*)*(<p[^>]*>.*?<\/p>)/is', $m[2], $pairs, PREG_SET_ORDER );
	$faqs = array();
	foreach ( $pairs as $pair ) {
		$faqs[] = array(
			'q' => trim( wp_strip_all_tags( $pair[1] ) ),
			'a' => trim( wp_strip_all_tags( do_shortcode( $pair[2] ) ) ),
		);
	}
	return $faqs;
}

/**
 * Structured data for pages and posts: WebPage or BlogPosting, breadcrumbs, FAQPage
 * when the content has an FAQ section, and WebApplication for calculator pages.
 * Page, article and breadcrumb nodes are left to an SEO plugin when one is active.
 */
function powerbachat_singular_schema() {
	if ( ! is_singular() || is_front_page() ) {
		return;
	}
	$post    = get_queried_object();
	$url     = get_permalink( $post );
	$graph   = array();
	$plugin  = powerbachat_seo_plugin_active();
	$desc    = get_post_meta( $post->ID, 'pb_meta_description', true );
	$desc    = $desc ? $desc : wp_strip_all_tags( get_the_excerpt( $post ) );
	$country = powerbachat_current_country();
	$lang    = array(
		'pk' => 'en-PK',
		'in' => 'en-IN',
		'bd' => 'en-BD',
	);

	if ( ! $plugin ) {
		$crumbs = array( array( get_bloginfo( 'name' ), home_url( '/' ) ) );
		if ( 'page' === $post->post_type ) {
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
				$crumbs[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
			}
		} else {
			$cats = get_the_category( $post->ID );
			if ( $cats ) {
				$crumbs[] = array( $cats[0]->name, get_category_link( $cats[0] ) );
			}
		}
		$crumbs[] = array( get_the_title( $post ), $url );
		$list     = array();
		foreach ( $crumbs as $i => $crumb ) {
			$list[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $crumb[0],
				'item'     => $crumb[1],
			);
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => $list,
		);

		$node = array(
			'@type'         => 'post' === $post->post_type ? 'BlogPosting' : 'WebPage',
			'@id'           => $url . '#main',
			'url'           => $url,
			'headline'      => get_the_title( $post ),
			'name'          => get_the_title( $post ),
			'description'   => $desc,
			'inLanguage'    => isset( $lang[ $country ] ) ? $lang[ $country ] : 'en',
			'datePublished' => get_the_date( DATE_W3C, $post ),
			'dateModified'  => get_the_modified_date( DATE_W3C, $post ),
			'breadcrumb'    => array( '@id' => $url . '#breadcrumb' ),
			'isPartOf'      => array( '@id' => home_url( '/#website' ) ),
			'publisher'     => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
		);
		if ( 'post' === $post->post_type ) {
			$node['author']           = array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			);
			$node['mainEntityOfPage'] = $url;
		}
		$keyword = get_post_meta( $post->ID, 'pb_focus_keyword', true );
		if ( $keyword ) {
			$node['keywords'] = $keyword;
		}
		if ( has_post_thumbnail( $post ) ) {
			$node['image'] = get_the_post_thumbnail_url( $post, 'large' );
		}
		$graph[] = $node;
	}

	$template = get_page_template_slug( $post );
	if ( 'page-templates/calculator.php' === $template || has_shortcode( $post->post_content, 'powerbachat_calculator' ) || has_shortcode( $post->post_content, 'powerbachat_solar' ) || has_shortcode( $post->post_content, 'powerbachat_battery' ) ) {
		$graph[] = array(
			'@type'               => 'WebApplication',
			'name'                => get_the_title( $post ),
			'url'                 => $url,
			'description'         => $desc,
			'applicationCategory' => 'UtilitiesApplication',
			'operatingSystem'     => 'Any',
			'browserRequirements' => 'Requires JavaScript',
			'isAccessibleForFree' => true,
			'offers'              => array(
				'@type'         => 'Offer',
				'price'         => '0',
				'priceCurrency' => 'pk' === $country ? 'PKR' : ( 'in' === $country ? 'INR' : 'BDT' ),
			),
		);
	}

	$faqs = powerbachat_content_faqs( $post->post_content );
	if ( $faqs ) {
		$graph[] = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#faq',
			'mainEntity' => array_map(
				function ( $faq ) {
					return array(
						'@type'          => 'Question',
						'name'           => $faq['q'],
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text'  => $faq['a'],
						),
					);
				},
				$faqs
			),
		);
	}

	if ( $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'powerbachat_singular_schema', 20 );
