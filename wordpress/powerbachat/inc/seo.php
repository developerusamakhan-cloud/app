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
	} elseif ( is_front_page() ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( home_url( '/' ) ) );
	}

	// A post's featured image, otherwise the site's share image.
	$image = null;
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		if ( $src ) {
			$image = array( $src[0], $src[1], $src[2], get_the_title() );
		}
	}
	if ( ! $image ) {
		$image = powerbachat_share_image();
	}
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image[0] ) );
	if ( $image[1] && $image[2] ) {
		printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $image[1] );
		printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $image[2] );
	}
	printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $image[3] ) );
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image[0] ) );
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
	// A page can have more than one heading with "questions" in it (for example
	// "Questions to ask your installer"), so use the first one followed by Q&A pairs.
	preg_match_all( '/<h2[^>]*>[^<]*(questions|faq)[^<]*<\/h2>(.*?)(?=<h2|$)/is', $content, $sections, PREG_SET_ORDER );
	$pairs = array();
	foreach ( $sections as $section ) {
		if ( preg_match_all( '/<h3[^>]*>(.*?)<\/h3>\s*(?:<!--[^>]*-->\s*)*(<p[^>]*>.*?<\/p>)/is', $section[2], $pairs, PREG_SET_ORDER ) ) {
			break;
		}
	}
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

		$type = 'post' === $post->post_type ? 'BlogPosting' : 'WebPage';
		$custom_type = get_post_meta( $post->ID, 'pb_schema_type', true );
		if ( 'page' === $post->post_type && in_array( $custom_type, array( 'AboutPage', 'ContactPage', 'CollectionPage', 'FAQPage' ), true ) ) {
			$type = $custom_type;
		}
		$graph[] = powerbachat_organization_node();
		$node    = array(
			'@type'         => $type,
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
			'publisher'     => array( '@id' => home_url( '/#organization' ) ),
		);
		if ( 'post' === $post->post_type ) {
			$node['author']           = array( '@id' => home_url( '/#organization' ) );
			$node['mainEntityOfPage'] = $url;
			$node['wordCount']        = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
			$cats                     = get_the_category( $post->ID );
			if ( $cats ) {
				$node['articleSection'] = $cats[0]->name;
			}
		}
		$keywords = array_filter(
			array_merge(
				array( get_post_meta( $post->ID, 'pb_focus_keyword', true ) ),
				array_map( 'trim', explode( ',', (string) get_post_meta( $post->ID, 'pb_secondary_keywords', true ) ) )
			)
		);
		if ( $keywords ) {
			$node['keywords'] = implode( ', ', array_unique( $keywords ) );
		}
		if ( function_exists( 'powerbachat_post_country' ) ) {
			$names = wp_list_pluck( powerbachat_data()['countries'], 'name' );
			$where = 'post' === $post->post_type ? powerbachat_post_country( $post->ID ) : powerbachat_forced_country();
			if ( $where && isset( $names[ $where ] ) ) {
				$node['spatialCoverage'] = array(
					'@type' => 'Country',
					'name'  => $names[ $where ],
				);
			}
		}
		if ( has_post_thumbnail( $post ) ) {
			$node['image'] = get_the_post_thumbnail_url( $post, 'full' );
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

/**
 * The site's Organization node, shared by every page's structured data.
 *
 * @return array
 */
function powerbachat_organization_node() {
	$logo = has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : POWERBACHAT_URI . '/assets/img/logo-512.png';
	$node = array(
		'@type'        => 'Organization',
		'@id'          => home_url( '/#organization' ),
		'name'         => get_bloginfo( 'name' ),
		'url'          => home_url( '/' ),
		'logo'         => array(
			'@type' => 'ImageObject',
			'url'   => $logo,
		),
		'areaServed'   => array( 'PK', 'IN', 'BD' ),
		'contactPoint' => array(
			'@type'       => 'ContactPoint',
			'contactType' => 'customer support',
			'email'       => function_exists( 'powerbachat_contact_address' ) ? powerbachat_contact_address() : get_option( 'admin_email' ),
			'url'         => home_url( '/contact-us/' ),
		),
	);
	$same = array_filter( array( powerbachat_mod( 'pb_whatsapp_url' ) ) );
	if ( $same ) {
		$node['sameAs'] = array_values( $same );
	}
	return $node;
}

/**
 * Title separator: a plain bar instead of WordPress's default dash.
 *
 * @return string
 */
function powerbachat_title_separator() {
	return '|';
}
add_filter( 'document_title_separator', 'powerbachat_title_separator' );

/**
 * Title, description and schema for the country guide archives.
 *
 * @return array|null title, description, country name.
 */
function powerbachat_country_archive_meta() {
	if ( ! is_category() || ! function_exists( 'powerbachat_category_country' ) ) {
		return null;
	}
	$code = powerbachat_category_country( get_queried_object() );
	if ( ! $code ) {
		return null;
	}
	$name = powerbachat_data()['countries'][ $code ]['name'];
	return array(
		/* translators: %s: country name */
		'title' => sprintf( __( '%s Electricity Bill and Solar Guides', 'powerbachat' ), $name ),
		'desc'  => sprintf(
			/* translators: %s: country name */
			__( 'Plain-English guides for %s: how your electricity bill is worked out, unit rates, subsidies, solar, batteries and simple ways to pay less.', 'powerbachat' ),
			$name
		),
		'name'  => $name,
	);
}

/**
 * Document title parts for the country archives.
 *
 * @param array $parts Title parts.
 * @return array
 */
function powerbachat_archive_title_parts( $parts ) {
	$meta = powerbachat_country_archive_meta();
	if ( $meta && ! powerbachat_seo_plugin_active() ) {
		$parts['title'] = $meta['title'];
	}
	return $parts;
}
add_filter( 'document_title_parts', 'powerbachat_archive_title_parts' );

/**
 * Meta description and CollectionPage schema for the country archives.
 */
function powerbachat_archive_head() {
	$meta = powerbachat_country_archive_meta();
	if ( ! $meta || powerbachat_seo_plugin_active() ) {
		return;
	}
	$url = get_category_link( get_queried_object() );
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $meta['desc'] ) );
	$items = array();
	foreach ( $GLOBALS['wp_query']->posts as $i => $p ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'url'      => get_permalink( $p ),
			'name'     => get_the_title( $p ),
		);
	}
	$graph = array(
		powerbachat_organization_node(),
		array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => get_bloginfo( 'name' ),
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $meta['title'],
					'item'     => $url,
				),
			),
		),
		array(
			'@type'           => 'CollectionPage',
			'@id'             => $url . '#main',
			'url'             => $url,
			'name'            => $meta['title'],
			'description'     => $meta['desc'],
			'isPartOf'        => array( '@id' => home_url( '/#website' ) ),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'breadcrumb'      => array( '@id' => $url . '#breadcrumb' ),
			'spatialCoverage' => array(
				'@type' => 'Country',
				'name'  => $meta['name'],
			),
			'mainEntity'      => array(
				'@type'           => 'ItemList',
				'itemListElement' => $items,
			),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . '</script>' . "\n";
}
add_action( 'wp_head', 'powerbachat_archive_head', 4 );

/**
 * The site-wide share image: used for the home page and for any page without a
 * featured image. Replace it in Customize > PowerBachat > Home page.
 *
 * @return array URL, width, height, alt text.
 */
function powerbachat_share_image() {
	$custom = powerbachat_mod( 'pb_share_image' );
	if ( $custom ) {
		$id = attachment_url_to_postid( $custom );
		$src = $id ? wp_get_attachment_image_src( $id, 'full' ) : null;
		return array( $custom, $src ? $src[1] : 0, $src ? $src[2] : 0, get_bloginfo( 'name' ) );
	}
	return array(
		POWERBACHAT_URI . '/assets/img/share-home.jpg?v=' . rawurlencode( POWERBACHAT_VERSION ),
		1200,
		630,
		/* translators: %s: site name */
		sprintf( __( '%s: electricity bill calculators, unit rates and solar prices', 'powerbachat' ), get_bloginfo( 'name' ) ),
	);
}

/**
 * Give Yoast SEO and Rank Math the same share image on the home page, and as the
 * fallback for pages that have no image of their own.
 */
function powerbachat_seo_plugin_share_image() {
	if ( defined( 'WPSEO_VERSION' ) ) {
		add_action(
			'wpseo_add_opengraph_additional_images',
			function ( $images ) {
				if ( is_front_page() || ! ( is_singular() && has_post_thumbnail() ) ) {
					$images->add_image( powerbachat_share_image()[0] );
				}
			}
		);
	}
	if ( class_exists( 'RankMath' ) ) {
		add_filter(
			'rank_math/opengraph/facebook/image',
			function ( $url ) {
				return $url ? $url : powerbachat_share_image()[0];
			}
		);
		add_filter(
			'rank_math/opengraph/twitter/image',
			function ( $url ) {
				return $url ? $url : powerbachat_share_image()[0];
			}
		);
	}
}
add_action( 'wp', 'powerbachat_seo_plugin_share_image' );

/**
 * The XML sitemap address: Rank Math's or Yoast's sitemap index when one of them
 * is active, otherwise the sitemap WordPress builds in.
 *
 * @return string
 */
function powerbachat_sitemap_url() {
	if ( class_exists( 'RankMath' ) || defined( 'WPSEO_VERSION' ) ) {
		$url = home_url( '/sitemap_index.xml' );
	} elseif ( defined( 'SEOPRESS_VERSION' ) ) {
		$url = home_url( '/sitemaps.xml' );
	} elseif ( function_exists( 'get_sitemap_url' ) && get_sitemap_url( 'index' ) ) {
		$url = get_sitemap_url( 'index' );
	} else {
		$url = home_url( '/sitemap_index.xml' );
	}
	return apply_filters( 'powerbachat_sitemap_url', $url );
}

/**
 * Name the sitemap in robots.txt if no plugin has already done so, so crawlers find it.
 *
 * @param string $output Robots.txt content.
 * @param bool   $public Whether the site is visible to search engines.
 * @return string
 */
function powerbachat_robots_sitemap( $output, $public ) {
	if ( $public && false === stripos( $output, powerbachat_sitemap_url() ) ) {
		$output = rtrim( $output ) . "\nSitemap: " . powerbachat_sitemap_url() . "\n";
	}
	return $output;
}
add_filter( 'robots_txt', 'powerbachat_robots_sitemap', 99, 2 );
