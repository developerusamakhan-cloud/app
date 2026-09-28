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
		$post    = get_queried_object();
		$text    = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$service = nabia_head_service();
		if ( $service ) {
			$text = $service['intro'];
		} elseif ( '' === trim( wp_strip_all_tags( $text ) ) ) {
			$text = nabia_page_intro_text();
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	}
	if ( is_front_page() || '' === trim( wp_strip_all_tags( $text ) ) ) {
		$text = get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) . '. ' . nabia_mod( 'footer_text' ) : nabia_mod( 'footer_text' );
		if ( is_front_page() ) {
			$text = nabia_mod( 'hero_text' );
		}
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
		$data['founder']    = array(
			'@type'    => 'Person',
			'name'     => nabia_mod( 'brand_name' ),
			'jobTitle' => __( 'WordPress developer and graphic designer', 'nabia' ),
		);
		$data['areaServed'] = __( 'Worldwide', 'nabia' );
		$data['knowsAbout'] = wp_list_pluck( nabia_services(), 'title' );
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

/**
 * The service shown on the page being viewed (service pages only).
 *
 * @return array|null
 */
function nabia_head_service() {
	if ( ! is_page() ) {
		return null;
	}
	$id       = get_queried_object_id();
	$template = (string) get_page_template_slug( $id );
	$slug     = get_query_var( 'nabia_service' );
	if ( ! $slug ) {
		$slug = get_post_meta( $id, '_nabia_service', true );
	}
	if ( ! $slug && preg_match( '#page-templates/service-([a-z0-9-]+)\.php$#', $template, $m ) ) {
		$slug = $m[1];
	}
	if ( ! $slug && 'template-service.php' === $template ) {
		$slug = get_post_field( 'post_name', $id );
	}
	return $slug ? nabia_get_service( $slug ) : null;
}

/**
 * Intro text of the main theme pages, used as their description.
 *
 * @return string
 */
function nabia_page_intro_text() {
	$template = (string) get_page_template_slug( get_queried_object_id() );
	$map      = array(
		'template-services.php'  => __( 'Web design and development services for small businesses: WordPress, Shopify, WooCommerce, Wix, Webflow, custom code, AI chatbots, logo design, SEO and website maintenance.', 'nabia' ),
		'template-pricing.php'   => nabia_mod( 'pricing_text' ),
		'template-audit.php'     => nabia_mod( 'audit_text' ),
		'template-about.php'     => nabia_mod( 'hero_text' ),
		'template-contact.php'   => nabia_mod( 'cta_text' ),
		'template-portfolio.php' => __( 'Websites and online stores I designed and built for small businesses around the world, on WordPress, Shopify, Wix and custom code.', 'nabia' ),
	);
	return isset( $map[ $template ] ) ? $map[ $template ] : '';
}

/**
 * Search-friendly title tags for the service pages (without an SEO plugin).
 *
 * @return array slug => title.
 */
function nabia_service_title_tags() {
	return apply_filters(
		'nabia_service_title_tags',
		array(
			'web-design'              => __( 'Small Business Website Design Services', 'nabia' ),
			'wordpress-development'   => __( 'Hire a WordPress Developer', 'nabia' ),
			'shopify-woocommerce'     => __( 'Shopify & WooCommerce Developer', 'nabia' ),
			'wix-webflow-squarespace' => __( 'Wix, Squarespace & Webflow Website Design', 'nabia' ),
			'custom-websites'         => __( 'Custom Website Development', 'nabia' ),
			'ai-website-solutions'    => __( 'AI Chatbot for Your Website & AI Solutions', 'nabia' ),
			'branding-graphic-design' => __( 'Logo Design Services & Brand Identity', 'nabia' ),
			'speed-seo'               => __( 'Website Speed Optimization & SEO', 'nabia' ),
			'website-maintenance'     => __( 'Website Maintenance Services & WordPress Care', 'nabia' ),
		)
	);
}

/**
 * Use the search-friendly title on service pages and the homepage.
 *
 * @param array $parts Title parts.
 * @return array
 */
function nabia_document_title( $parts ) {
	if ( nabia_seo_plugin_active() ) {
		return $parts;
	}
	$service = nabia_head_service();
	$tags    = nabia_service_title_tags();
	if ( $service && isset( $tags[ $service['slug'] ] ) ) {
		$parts['title'] = $tags[ $service['slug'] ];
	} elseif ( is_front_page() && ! get_bloginfo( 'description' ) ) {
		$parts['tagline'] = __( 'WordPress Developer & Website Designer', 'nabia' );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'nabia_document_title' );

/**
 * Title separator: a pipe instead of the default dash (SEO plugins keep their own setting).
 *
 * @param string $sep Separator.
 * @return string
 */
function nabia_title_separator( $sep ) {
	return nabia_seo_plugin_active() ? $sep : '|';
}
add_filter( 'document_title_separator', 'nabia_title_separator' );

/**
 * FAQPage data from question / answer pairs.
 *
 * @param array $pairs Each: array( question, answer ).
 * @return array
 */
function nabia_faq_schema( $pairs ) {
	$items = array();
	foreach ( $pairs as $pair ) {
		$pair = array_values( (array) $pair );
		if ( empty( $pair[0] ) || empty( $pair[1] ) ) {
			continue;
		}
		$items[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $pair[0] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $pair[1] ),
			),
		);
	}
	return $items ? array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $items,
	) : array();
}

/**
 * Structured data that helps Google and AI assistants understand each page:
 * FAQ answers (homepage, service pages, articles), the service itself and blog posts.
 * FAQ and Service data are printed even with an SEO plugin (they do not add these).
 */
function nabia_structured_data() {
	$blocks = array();
	$brand  = array(
		'@type' => 'ProfessionalService',
		'name'  => nabia_mod( 'brand_name' ),
		'url'   => home_url( '/' ),
	);

	if ( is_front_page() ) {
		$blocks[] = nabia_faq_schema( nabia_faq() );
	}

	$service = nabia_head_service();
	if ( $service ) {
		$tags     = nabia_service_title_tags();
		$blocks[] = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Service',
			'name'        => isset( $tags[ $service['slug'] ] ) ? $tags[ $service['slug'] ] : $service['title'],
			'serviceType' => $service['title'],
			'description' => wp_strip_all_tags( $service['intro'] ),
			'url'         => get_permalink( get_queried_object_id() ),
			'provider'    => $brand,
			'areaServed'  => __( 'Worldwide', 'nabia' ),
		);
		$blocks[] = nabia_faq_schema( array_merge( $service['faq'], array_slice( nabia_faq(), 0, 2 ) ) );
	}

	if ( is_singular( 'post' ) ) {
		$id  = get_queried_object_id();
		$faq = get_post_meta( $id, '_nabia_faq', true );
		if ( is_array( $faq ) ) {
			$blocks[] = nabia_faq_schema( $faq );
		}
		if ( ! nabia_seo_plugin_active() ) {
			$image    = nabia_share_image();
			$blocks[] = array(
				'@context'         => 'https://schema.org',
				'@type'            => 'BlogPosting',
				'headline'         => wp_strip_all_tags( get_the_title( $id ) ),
				'description'      => nabia_share_description(),
				'image'            => $image['url'],
				'datePublished'    => get_the_date( DATE_W3C, $id ),
				'dateModified'     => get_the_modified_date( DATE_W3C, $id ),
				'mainEntityOfPage' => get_permalink( $id ),
				'author'           => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $id ) ),
					'url'   => nabia_page_url( 'about' ) ? nabia_page_url( 'about' ) : home_url( '/' ),
				),
				'publisher'        => array(
					'@type' => 'Organization',
					'name'  => nabia_mod( 'brand_name' ),
					'logo'  => array(
						'@type' => 'ImageObject',
						'url'   => NABIA_URI . '/assets/brand/logo-mark.png',
					),
				),
			);
		}
	}

	foreach ( array_filter( $blocks ) as $block ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'nabia_structured_data', 4 );
