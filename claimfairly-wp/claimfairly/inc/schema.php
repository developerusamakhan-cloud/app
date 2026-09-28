<?php
/**
 * SEO head output: favicons, meta description, SEO title, Open Graph,
 * Twitter cards and one connected JSON-LD graph on every page.
 *
 * Graph nodes (linked by @id):
 *   Organization, WebSite, Person (founder)          every page
 *   WebPage | AboutPage | ContactPage | CollectionPage  every page
 *   BreadcrumbList                                    inner pages
 *   Article                                           guides, state, injury, insurer, settlement pages
 *   WebApplication                                    tool pages
 *   FAQPage                                           any page with an FAQ
 *   ItemList                                          homepage tools, guides index
 *
 * When Yoast SEO or Rank Math is active, they already print the title,
 * description, Open Graph tags and the base graph, so the theme only adds
 * WebApplication and FAQPage. Override with `claimfairly_seo_plugin_handles_schema`.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether an SEO plugin handles the basics.
 *
 * @return bool
 */
function claimfairly_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' );
	return (bool) apply_filters( 'claimfairly_seo_plugin_handles_schema', $active );
}

/**
 * Brand asset URL.
 *
 * @param string $file File in assets/brand.
 * @return string
 */
function claimfairly_brand_url( $file ) {
	return CLAIMFAIRLY_URI . '/assets/brand/' . $file;
}

/* Favicons ----------------------------------------------------------------- */

/**
 * Favicon links, unless a Site Icon was set in the Customizer.
 */
function claimfairly_favicons() {
	if ( has_site_icon() ) {
		return;
	}
	echo '<link rel="icon" href="' . esc_url( claimfairly_brand_url( 'favicon.ico' ) ) . '" sizes="48x48">' . "\n";
	echo '<link rel="icon" href="' . esc_url( claimfairly_brand_url( 'favicon.svg' ) ) . '" type="image/svg+xml">' . "\n";
	echo '<link rel="icon" href="' . esc_url( claimfairly_brand_url( 'favicon-32x32.png' ) ) . '" sizes="32x32" type="image/png">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( claimfairly_brand_url( 'apple-touch-icon.png' ) ) . '">' . "\n";
	echo '<link rel="manifest" href="' . esc_url( claimfairly_brand_url( 'site.webmanifest' ) ) . '">' . "\n";
}
add_action( 'wp_head', 'claimfairly_favicons', 2 );

/* Titles and descriptions --------------------------------------------------- */

/**
 * Use the page's SEO title (from the importer or the Trust & SEO box) as the <title>.
 *
 * @param string $title Title.
 * @return string
 */
function claimfairly_document_title( $title ) {
	if ( claimfairly_seo_plugin_active() || ! is_singular() ) {
		return $title;
	}
	$seo = (string) get_post_meta( get_queried_object_id(), '_cf_seo_title', true );
	return '' !== $seo ? $seo : $title;
}
add_filter( 'pre_get_document_title', 'claimfairly_document_title' );

/**
 * Description for the current view.
 *
 * @return string
 */
function claimfairly_meta_description() {
	if ( is_front_page() ) {
		$front = (int) get_option( 'page_on_front' );
		$desc  = $front ? get_post_field( 'post_excerpt', $front ) : '';
		return $desc ? $desc : get_bloginfo( 'description' );
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		return has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '' );
	}
	if ( is_home() ) {
		$page = (int) get_option( 'page_for_posts' );
		return $page ? get_post_field( 'post_excerpt', $page ) : '';
	}
	if ( is_category() ) {
		$desc = wp_strip_all_tags( category_description() );
		return $desc ? $desc : sprintf( /* translators: %s: category. */ __( 'Guides about %s from ClaimFairly: clear, sourced answers about car accident claims.', 'claimfairly' ), single_cat_title( '', false ) );
	}
	return get_bloginfo( 'description' );
}

/**
 * Share image for the current view: featured image, else the default.
 *
 * @return array{url:string,width:int,height:int}
 */
function claimfairly_share_image() {
	$id = 0;
	if ( is_singular() ) {
		$id = (int) get_post_thumbnail_id( get_queried_object_id() );
	} elseif ( is_front_page() && get_option( 'page_on_front' ) ) {
		$id = (int) get_post_thumbnail_id( (int) get_option( 'page_on_front' ) );
	}
	if ( $id ) {
		$img = wp_get_attachment_image_src( $id, 'full' );
		if ( $img ) {
			return array(
				'url'    => $img[0],
				'width'  => (int) $img[1],
				'height' => (int) $img[2],
			);
		}
	}
	return array(
		'url'    => claimfairly_brand_url( 'og-default.jpg' ),
		'width'  => 1200,
		'height' => 630,
	);
}

/**
 * Canonical URL for the current view.
 *
 * @return string
 */
function claimfairly_current_url() {
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? home_url( '/' ) : $link;
	}
	return home_url( add_query_arg( array() ) );
}

/**
 * Meta description, Open Graph and Twitter tags.
 */
function claimfairly_social_meta() {
	if ( claimfairly_seo_plugin_active() || is_404() || is_search() ) {
		return;
	}
	$title = wp_get_document_title();
	$desc  = trim( claimfairly_meta_description() );
	$img   = claimfairly_share_image();
	$url   = claimfairly_current_url();
	$is_article = is_singular( 'post' ) || ( is_singular( 'page' ) && in_array( claimfairly_get_page_type( get_queried_object_id() ), array( 'guide', 'state', 'injury', 'insurer' ), true ) );

	$tags = array(
		array( 'name', 'description', $desc ),
		array( 'property', 'og:locale', 'en_US' ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:type', $is_article ? 'article' : 'website' ),
		array( 'property', 'og:title', $title ),
		array( 'property', 'og:description', $desc ),
		array( 'property', 'og:url', $url ),
		array( 'property', 'og:image', $img['url'] ),
		array( 'property', 'og:image:width', (string) $img['width'] ),
		array( 'property', 'og:image:height', (string) $img['height'] ),
		array( 'property', 'og:image:alt', $title ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
		array( 'name', 'twitter:title', $title ),
		array( 'name', 'twitter:description', $desc ),
		array( 'name', 'twitter:image', $img['url'] ),
	);
	if ( $is_article ) {
		$id     = get_queried_object_id();
		$tags[] = array( 'property', 'article:published_time', get_the_date( 'c', $id ) );
		$tags[] = array( 'property', 'article:modified_time', claimfairly_modified_iso( $id ) );
	}
	foreach ( $tags as $tag ) {
		if ( '' === (string) $tag[2] ) {
			continue;
		}
		printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $tag[0] ), esc_attr( $tag[1] ), esc_attr( $tag[2] ) );
	}
}
add_action( 'wp_head', 'claimfairly_social_meta', 3 );

/**
 * Last modified date: the reviewed date when set, else the post's modified date.
 *
 * @param int $id Post ID.
 * @return string ISO 8601.
 */
function claimfairly_modified_iso( $id ) {
	$reviewed = claimfairly_last_reviewed_raw( $id );
	return $reviewed ? gmdate( 'c', strtotime( $reviewed . ' 12:00:00' ) ) : get_the_modified_date( 'c', $id );
}

/* JSON-LD ------------------------------------------------------------------- */

/**
 * Print one JSON-LD block.
 *
 * @param array $data Schema data.
 */
function claimfairly_print_jsonld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}

/**
 * FAQ items from [cf_q] shortcodes in a post's content.
 *
 * @param string $content Post content.
 * @return array<int,array{q:string,a:string}>
 */
function claimfairly_faqs_from_content( $content ) {
	$items = array();
	if ( false === strpos( $content, '[cf_q' ) ) {
		return $items;
	}
	preg_match_all( '/' . get_shortcode_regex( array( 'cf_q' ) ) . '/s', $content, $matches, PREG_SET_ORDER );
	foreach ( $matches as $m ) {
		$atts = shortcode_parse_atts( $m[3] );
		if ( empty( $atts['q'] ) ) {
			continue;
		}
		$answer = trim( wp_strip_all_tags( do_shortcode( $m[5] ) ) );
		if ( '' !== $answer ) {
			$items[] = array(
				'q' => wp_strip_all_tags( claimfairly_fill_placeholders_safe( $atts['q'] ) ),
				'a' => $answer,
			);
		}
	}
	return $items;
}

/**
 * Placeholder filler that also works when the importer is not loaded.
 *
 * @param string $text Text.
 * @return string
 */
function claimfairly_fill_placeholders_safe( $text ) {
	return function_exists( 'claimfairly_fill_placeholders' ) ? claimfairly_fill_placeholders( $text ) : $text;
}

/**
 * Questions shown on the homepage (used by the template and the schema).
 *
 * @return array<int,array{0:string,1:string}>
 */
function claimfairly_home_faqs() {
	return array(
		array( __( 'Is ClaimFairly really free?', 'claimfairly' ), __( 'Yes. There is no sign-up, no trial and no paid version. The site may show ads to cover costs, but never inside a calculator.', 'claimfairly' ) ),
		array( __( 'Do you store what I type?', 'claimfairly' ), __( 'No. The math runs in your browser. Nothing you enter is sent to our server or saved anywhere.', 'claimfairly' ) ),
		array( __( 'Is this legal advice?', 'claimfairly' ), __( 'No. These are educational estimates. Claims depend on facts, evidence and state law. For advice on your situation, talk to a licensed attorney in your state.', 'claimfairly' ) ),
		array( __( 'How accurate are the estimates?', 'claimfairly' ), __( 'They use the same kinds of formulas insurers and attorneys use as a starting point, and they show every assumption. Real settlements can land above or below the range.', 'claimfairly' ) ),
		array( __( 'Are you connected to an insurance company or law firm?', 'claimfairly' ), __( 'No. ClaimFairly is independent. We are not paid by insurers, and we do not sell your details to law firms.', 'claimfairly' ) ),
		array( __( 'Which calculator should I start with?', 'claimfairly' ), __( 'If you were hurt, start with the car accident settlement calculator. If your car was repaired and is now worth less, start with diminished value.', 'claimfairly' ) ),
	);
}

/**
 * Build and print the graph.
 */
function claimfairly_schema_graph() {
	if ( is_404() || is_search() ) {
		return;
	}
	$plugin  = claimfairly_seo_plugin_active();
	$home    = home_url( '/' );
	$org_id  = $home . '#organization';
	$site_id = $home . '#website';
	$url     = claimfairly_current_url();
	$page_id = $url . '#webpage';
	$graph   = array();
	$img     = claimfairly_share_image();
	$founder = claimfairly_founder_name();
	$person  = $founder ? array( '@id' => $home . '#founder' ) : null;

	if ( ! $plugin ) {
		// Organization.
		$org = array(
			'@type'       => 'Organization',
			'@id'         => $org_id,
			'name'        => get_bloginfo( 'name' ),
			'url'         => $home,
			'description' => claimfairly_opt( 'cf_footer_about' ),
			'logo'        => array(
				'@type'  => 'ImageObject',
				'@id'    => $home . '#logo',
				'url'    => claimfairly_brand_url( 'logo.png' ),
				'width'  => 1276,
				'height' => 220,
			),
			'image'       => array( '@id' => $home . '#logo' ),
		);
		if ( claimfairly_opt( 'cf_contact_email' ) ) {
			$org['email']        = claimfairly_opt( 'cf_contact_email' );
			$org['contactPoint'] = array(
				'@type'       => 'ContactPoint',
				'contactType' => 'customer support',
				'email'       => claimfairly_opt( 'cf_contact_email' ),
			);
		}
		$same_as = array_values( array_filter( preg_split( '/\r\n|\r|\n/', claimfairly_opt( 'cf_same_as' ) ) ) );
		if ( $same_as ) {
			$org['sameAs'] = $same_as;
		}
		if ( $person ) {
			$org['founder'] = $person;
		}
		$graph[] = $org;

		// WebSite.
		$graph[] = array(
			'@type'       => 'WebSite',
			'@id'         => $site_id,
			'url'         => $home,
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'publisher'   => array( '@id' => $org_id ),
			'inLanguage'  => 'en-US',
		);

		// Founder.
		if ( $person ) {
			$admins  = get_users(
				array(
					'role'    => 'administrator',
					'number'  => 1,
					'orderby' => 'ID',
				)
			);
			$p       = array(
				'@type'    => 'Person',
				'@id'      => $home . '#founder',
				'name'     => $founder,
				'url'      => claimfairly_about_url(),
				'jobTitle' => 'Founder',
				'worksFor' => array( '@id' => $org_id ),
			);
			$bio     = $admins ? get_the_author_meta( 'description', $admins[0]->ID ) : '';
			if ( $bio ) {
				$p['description'] = $bio;
			}
			$photo = (int) claimfairly_opt( 'cf_founder_photo' );
			if ( $photo && wp_get_attachment_image_url( $photo, 'medium' ) ) {
				$p['image'] = wp_get_attachment_image_url( $photo, 'medium' );
			}
			if ( $same_as ) {
				$p['sameAs'] = $same_as;
			}
			$graph[] = $p;
		}
	}

	$post_id = is_singular() ? get_queried_object_id() : ( is_front_page() ? (int) get_option( 'page_on_front' ) : 0 );
	$type    = $post_id ? claimfairly_get_page_type( $post_id ) : 'standard';
	$slug    = $post_id ? get_post_field( 'post_name', $post_id ) : '';
	$faqs    = array();

	if ( is_front_page() ) {
		foreach ( claimfairly_home_faqs() as $f ) {
			$faqs[] = array(
				'q' => $f[0],
				'a' => $f[1],
			);
		}
	} elseif ( $post_id ) {
		$faqs = claimfairly_faqs_from_content( get_post_field( 'post_content', $post_id ) );
	}

	if ( ! $plugin ) {
		// WebPage (typed).
		$page_type = 'WebPage';
		if ( 'about' === $slug ) {
			$page_type = 'AboutPage';
		} elseif ( 'contact' === $slug ) {
			$page_type = 'ContactPage';
		} elseif ( is_home() || is_category() || 'states' === $slug || 'injuries' === $slug || 'insurers' === $slug ) {
			$page_type = 'CollectionPage';
		}
		if ( $faqs && ! in_array( $page_type, array( 'AboutPage', 'ContactPage', 'CollectionPage' ), true ) ) {
			$page_type = array( 'WebPage', 'FAQPage' );
		}
		$webpage = array(
			'@type'              => $page_type,
			'@id'                => $page_id,
			'url'                => $url,
			'name'               => wp_get_document_title(),
			'description'        => trim( claimfairly_meta_description() ),
			'isPartOf'           => array( '@id' => $site_id ),
			'inLanguage'         => 'en-US',
			'primaryImageOfPage' => array(
				'@type'  => 'ImageObject',
				'url'    => $img['url'],
				'width'  => $img['width'],
				'height' => $img['height'],
			),
		);
		if ( $post_id ) {
			$webpage['datePublished'] = get_the_date( 'c', $post_id );
			$webpage['dateModified']  = claimfairly_modified_iso( $post_id );
			$reviewed                 = claimfairly_last_reviewed_raw( $post_id );
			if ( $reviewed ) {
				$webpage['lastReviewed'] = $reviewed;
				if ( $person ) {
					$webpage['reviewedBy'] = $person;
				}
			}
		}
		if ( ! is_front_page() ) {
			$webpage['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
		}
		if ( 'state' === $type && function_exists( 'cft_state' ) ) {
			$state = cft_state( $slug );
			if ( $state ) {
				$webpage['about'] = array(
					'@type' => 'State',
					'name'  => $state['name'],
				);
			}
		}
		$questions = array();
		foreach ( $faqs as $f ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $f['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $f['a'],
				),
			);
		}
		if ( $questions && is_array( $page_type ) ) {
			$webpage['mainEntity'] = $questions;
		}
		$graph[] = $webpage;
		if ( $questions && ! is_array( $page_type ) ) {
			// About, contact and collection pages keep their own type; the FAQ gets its own node.
			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $url . '#faq',
				'url'        => $url,
				'isPartOf'   => array( '@id' => $page_id ),
				'mainEntity' => $questions,
			);
		}

		// Breadcrumbs.
		if ( ! is_front_page() ) {
			$list = array();
			foreach ( claimfairly_breadcrumb_items() as $i => $item ) {
				$list[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => wp_strip_all_tags( $item['name'] ),
					'item'     => $item['url'],
				);
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => $url . '#breadcrumb',
				'itemListElement' => $list,
			);
		}

		// Article.
		if ( $post_id && ( is_singular( 'post' ) || in_array( $type, array( 'guide', 'state', 'injury', 'insurer' ), true ) ) ) {
			$content = get_post_field( 'post_content', $post_id );
			$article = array(
				'@type'            => 'Article',
				'@id'              => $url . '#article',
				'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
				'description'      => trim( claimfairly_meta_description() ),
				'image'            => $img['url'],
				'datePublished'    => get_the_date( 'c', $post_id ),
				'dateModified'     => claimfairly_modified_iso( $post_id ),
				'mainEntityOfPage' => array( '@id' => $page_id ),
				'isPartOf'         => array( '@id' => $site_id ),
				'publisher'        => array( '@id' => $org_id ),
				'inLanguage'       => 'en-US',
				'wordCount'        => str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) ),
			);
			$author_name = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) );
			if ( $person && $author_name === $founder ) {
				$article['author'] = $person;
			} elseif ( $author_name ) {
				$article['author'] = array(
					'@type' => 'Person',
					'name'  => $author_name,
				);
			}
			$cats = get_the_category( $post_id );
			if ( $cats ) {
				$article['articleSection'] = $cats[0]->name;
			}
			$graph[] = $article;
		}

		// Lists: homepage tools and guides index.
		if ( is_front_page() || is_home() ) {
			$items = is_front_page() ? claimfairly_get_tools() : $GLOBALS['wp_query']->posts;
			if ( $items ) {
				$list = array();
				foreach ( array_values( $items ) as $i => $item ) {
					$list[] = array(
						'@type'    => 'ListItem',
						'position' => $i + 1,
						'url'      => get_permalink( $item ),
						'name'     => get_the_title( $item ),
					);
				}
				$graph[] = array(
					'@type'           => 'ItemList',
					'@id'             => $url . '#list',
					'name'            => is_front_page() ? __( 'Free claim calculators', 'claimfairly' ) : __( 'Car accident claim guides', 'claimfairly' ),
					'itemListElement' => $list,
				);
			}
		}
	}

	// Tool pages: WebApplication (always, SEO plugins do not generate it).
	if ( $post_id && 'tool' === $type ) {
		$app = array(
			'@type'               => 'WebApplication',
			'@id'                 => $url . '#app',
			'name'                => wp_strip_all_tags( get_the_title( $post_id ) ),
			'url'                 => $url,
			'description'         => claimfairly_card_summary( get_post( $post_id ) ),
			'image'               => $img['url'],
			'applicationCategory' => 'FinanceApplication',
			'operatingSystem'     => 'Any',
			'browserRequirements' => 'Requires JavaScript',
			'isAccessibleForFree' => true,
			'offers'              => array(
				'@type'         => 'Offer',
				'price'         => '0',
				'priceCurrency' => 'USD',
			),
			'dateModified'        => claimfairly_modified_iso( $post_id ),
		);
		if ( ! $plugin ) {
			$app['publisher']        = array( '@id' => $org_id );
			$app['mainEntityOfPage'] = array( '@id' => $page_id );
		} else {
			$app['publisher'] = array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => $home,
			);
		}
		$graph[] = $app;
	}

	// With an SEO plugin, FAQ still needs its own node.
	if ( $plugin && $faqs ) {
		$faq = array(
			'@type'      => 'FAQPage',
			'mainEntity' => array(),
		);
		foreach ( $faqs as $f ) {
			$faq['mainEntity'][] = array(
				'@type'          => 'Question',
				'name'           => $f['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $f['a'],
				),
			);
		}
		$graph[] = $faq;
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
add_action( 'wp_head', 'claimfairly_schema_graph', 20 );
