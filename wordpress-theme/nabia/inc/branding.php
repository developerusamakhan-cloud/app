<?php
/**
 * Branding in the page head: social share (Open Graph / Twitter) tags, meta description,
 * theme colour, web app manifest and Organization schema with the logo.
 *
 * When an SEO plugin (Yoast, Rank Math, All in One SEO, SEOPress, The SEO Framework) is
 * active it stays in charge; the theme only gives it the brand share image as a fallback.
 * Brand files live in assets/brand (logos, icon, share image, profile picture).
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is an SEO plugin printing its own meta tags?
 *
 * @return bool
 */
function nabia_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || function_exists( 'the_seo_framework' );
}

/**
 * Default share image: Customizer setting, else the brand image shipped with the theme.
 *
 * @return string
 */
function nabia_share_image_url() {
	$custom = nabia_mod( 'share_image' );
	return $custom ? $custom : NABIA_URI . '/assets/brand/og-image.png';
}

/**
 * Share image for the current page: featured image on posts and pages, else the brand image.
 *
 * @return array { url, width, height, alt }
 */
function nabia_share_image() {
	$id = is_singular() ? get_queried_object_id() : 0;
	if ( $id && has_post_thumbnail( $id ) ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'full' );
		if ( $img ) {
			return array(
				'url'    => $img[0],
				'width'  => $img[1],
				'height' => $img[2],
				'alt'    => get_the_title( $id ),
			);
		}
	}
	return array(
		'url'    => nabia_share_image_url(),
		'width'  => nabia_mod( 'share_image' ) ? 0 : 1200,
		'height' => nabia_mod( 'share_image' ) ? 0 : 630,
		'alt'    => trim( nabia_mod( 'brand_name' ) . ( get_bloginfo( 'description' ) ? ' | ' . get_bloginfo( 'description' ) : ', ' . __( 'WordPress developer and graphic designer', 'nabia' ) ) ),
	);
}

/**
 * Description for the current page.
 *
 * @return string
 */
function nabia_share_description() {
	$text = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	}
	if ( is_front_page() || '' === trim( wp_strip_all_tags( $text ) ) ) {
		$text = get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) . '. ' . nabia_mod( 'footer_text' ) : nabia_mod( 'footer_text' );
	}
	return wp_html_excerpt( trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) ), 158, '...' );
}

/**
 * Theme colour and web app manifest (always), plus share tags and schema without an SEO plugin.
 */
function nabia_branding_head() {
	$colors = function_exists( 'nabia_favicon_colors' ) ? nabia_favicon_colors() : array( 'ink' => '#1a1433' );
	printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( $colors['ink'] ) );
	if ( ! has_site_icon() ) {
		printf( '<link rel="manifest" href="%s">' . "\n", esc_url( NABIA_URI . '/assets/favicon/site.webmanifest' ) );
	}

	if ( nabia_seo_plugin_active() ) {
		return;
	}

	$title = wp_get_document_title();
	$desc  = nabia_share_description();
	$image = nabia_share_image();
	$url   = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( add_query_arg( array() ) );
	$tags  = array(
		array( 'name', 'description', $desc ),
		array( 'property', 'og:site_name', nabia_mod( 'brand_name' ) ),
		array( 'property', 'og:locale', str_replace( '-', '_', get_bloginfo( 'language' ) ) ),
		array( 'property', 'og:type', is_singular( 'post' ) ? 'article' : 'website' ),
		array( 'property', 'og:title', $title ),
		array( 'property', 'og:description', $desc ),
		array( 'property', 'og:url', $url ),
		array( 'property', 'og:image', $image['url'] ),
		array( 'property', 'og:image:alt', $image['alt'] ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
		array( 'name', 'twitter:title', $title ),
		array( 'name', 'twitter:description', $desc ),
		array( 'name', 'twitter:image', $image['url'] ),
	);
	if ( $image['width'] ) {
		$tags[] = array( 'property', 'og:image:width', (string) $image['width'] );
		$tags[] = array( 'property', 'og:image:height', (string) $image['height'] );
	}
	if ( is_singular( 'post' ) ) {
		$tags[] = array( 'property', 'article:published_time', get_the_date( DATE_W3C ) );
		$tags[] = array( 'property', 'article:modified_time', get_the_modified_date( DATE_W3C ) );
	}
	foreach ( $tags as $tag ) {
		if ( '' !== (string) $tag[2] ) {
			printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $tag[0] ), esc_attr( $tag[1] ), esc_attr( $tag[2] ) );
		}
	}

	if ( is_front_page() ) {
		$same = array_values( array_filter( array( nabia_mod( 'social_linktree' ), nabia_mod( 'social_fiverr' ), nabia_mod( 'social_upwork' ) ) ) );
		$data = array(
			'@context' => 'https://schema.org',
			'@type'    => 'ProfessionalService',
			'name'     => nabia_mod( 'brand_name' ),
			'url'      => home_url( '/' ),
			'logo'     => NABIA_URI . '/assets/brand/logo-mark.png',
			'image'    => nabia_share_image_url(),
			'description' => wp_strip_all_tags( nabia_mod( 'footer_text' ) ),
			'email'    => nabia_mod( 'contact_email' ),
			'sameAs'   => $same,
		);
		if ( nabia_mod( 'contact_whatsapp' ) ) {
			$data['telephone'] = nabia_mod( 'contact_whatsapp' );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $data ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'nabia_branding_head', 3 );

/**
 * Yoast SEO: use the brand share image when a page has none.
 *
 * @param object $container Yoast image container.
 */
function nabia_yoast_share_image( $container ) {
	if ( is_object( $container ) && method_exists( $container, 'has_images' ) && ! $container->has_images() ) {
		$container->add_image_by_url( nabia_share_image_url() );
	}
}
add_action( 'wpseo_add_opengraph_images', 'nabia_yoast_share_image' );

/**
 * Rank Math: use the brand share image when a page has none.
 *
 * @param string $image Image URL.
 * @return string
 */
function nabia_rankmath_share_image( $image ) {
	return $image ? $image : nabia_share_image_url();
}
add_filter( 'rank_math/opengraph/facebook/image', 'nabia_rankmath_share_image' );
add_filter( 'rank_math/opengraph/twitter/image', 'nabia_rankmath_share_image' );
