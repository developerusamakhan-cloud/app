<?php
/**
 * Reusable template pieces: breadcrumbs, reviewed line, sources, author box,
 * related tools, disclaimer.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Breadcrumb trail as an array of [name, url]. Shared by the visible
 * breadcrumbs and the BreadcrumbList schema so they never disagree.
 *
 * @return array<int,array{name:string,url:string}>
 */
function claimfairly_breadcrumb_items() {
	$items = array(
		array(
			'name' => __( 'Home', 'claimfairly' ),
			'url'  => home_url( '/' ),
		),
	);

	if ( is_singular( 'page' ) ) {
		$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
		foreach ( $ancestors as $ancestor_id ) {
			$items[] = array(
				'name' => get_the_title( $ancestor_id ),
				'url'  => get_permalink( $ancestor_id ),
			);
		}
		$items[] = array(
			'name' => get_the_title(),
			'url'  => get_permalink(),
		);
	} elseif ( is_singular( 'post' ) ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			$items[] = array(
				'name' => get_the_title( $posts_page ),
				'url'  => get_permalink( $posts_page ),
			);
		}
		$items[] = array(
			'name' => get_the_title(),
			'url'  => get_permalink(),
		);
	} elseif ( is_home() && ! is_front_page() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$items[]    = array(
			'name' => $posts_page ? get_the_title( $posts_page ) : __( 'Guides', 'claimfairly' ),
			'url'  => $posts_page ? get_permalink( $posts_page ) : home_url( '/' ),
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term    = get_queried_object();
		$items[] = array(
			'name' => single_term_title( '', false ),
			'url'  => get_term_link( $term ),
		);
	}

	return $items;
}

/**
 * Visible breadcrumbs.
 */
function claimfairly_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = claimfairly_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	$last = count( $items ) - 1;
	echo '<nav class="cf-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'claimfairly' ) . '"><ol>';
	foreach ( $items as $i => $item ) {
		if ( $i === $last ) {
			echo '<li aria-current="page">' . esc_html( $item['name'] ) . '</li>';
		} else {
			echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * Last reviewed date (Y-m-d) or empty string.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function claimfairly_last_reviewed_raw( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (string) get_post_meta( $post_id, '_cf_last_reviewed', true );
}

/**
 * "By Name · Last reviewed: date" line under the H1.
 */
function claimfairly_byline() {
	if ( ! claimfairly_is_trust_page() ) {
		return;
	}
	$reviewed = claimfairly_last_reviewed_raw();
	$author   = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', get_the_ID() ) );
	echo '<p class="cf-byline">';
	if ( $author ) {
		/* translators: %s: author name. */
		printf( esc_html__( 'By %s', 'claimfairly' ), '<a href="' . esc_url( claimfairly_about_url() ) . '">' . esc_html( $author ) . '</a>' );
	}
	if ( $reviewed ) {
		echo $author ? ' <span aria-hidden="true">·</span> ' : '';
		echo '<span class="cf-reviewed">' . esc_html__( 'Last reviewed:', 'claimfairly' ) . ' <time datetime="' . esc_attr( $reviewed ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $reviewed ) ) . '</time></span>';
	}
	echo '</p>';
}

/**
 * About page URL (absolute).
 *
 * @return string
 */
function claimfairly_about_url() {
	$url = claimfairly_opt( 'cf_about_url' );
	if ( 0 === strpos( $url, '/' ) ) {
		$url = home_url( $url );
	}
	return $url;
}

/**
 * Disclaimer box.
 *
 * @param string $extra_class Extra CSS class.
 * @return string
 */
function claimfairly_get_disclaimer( $extra_class = '' ) {
	return '<aside class="cf-disclaimer ' . esc_attr( $extra_class ) . '" role="note"><strong>' . esc_html__( 'Not legal advice.', 'claimfairly' ) . '</strong> ' . esc_html( claimfairly_opt( 'cf_disclaimer' ) ) . '</aside>';
}

/**
 * Sources box.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function claimfairly_get_sources_box( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$sources = claimfairly_parse_link_lines( get_post_meta( $post_id, '_cf_sources', true ) );
	if ( ! $sources ) {
		return '';
	}
	$html  = '<section class="cf-sources" aria-labelledby="cf-sources-title">';
	$html .= '<h2 id="cf-sources-title">' . esc_html__( 'Sources', 'claimfairly' ) . '</h2><ol>';
	foreach ( $sources as $source ) {
		$html .= '<li><a href="' . esc_url( $source['url'] ) . '" rel="noopener" target="_blank">' . esc_html( $source['label'] ) . '</a></li>';
	}
	$html .= '</ol>';
	$reviewed = claimfairly_last_reviewed_raw( $post_id );
	if ( $reviewed ) {
		$html .= '<p class="cf-sources__reviewed">' . esc_html__( 'Last reviewed:', 'claimfairly' ) . ' <time datetime="' . esc_attr( $reviewed ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $reviewed ) ) . '</time></p>';
	}
	$html .= '</section>';
	return $html;
}

/**
 * Author box. Uses the WordPress user's display name and "Biographical Info".
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function claimfairly_get_author_box( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( get_post_meta( $post_id, '_cf_hide_author', true ) ) {
		return '';
	}
	$author_id = (int) get_post_field( 'post_author', $post_id );
	$name      = get_the_author_meta( 'display_name', $author_id );
	$bio       = get_the_author_meta( 'description', $author_id );
	if ( ! $name ) {
		return '';
	}
	$initials = '';
	foreach ( preg_split( '/\s+/', trim( $name ) ) as $part ) {
		$initials .= function_exists( 'mb_substr' ) ? mb_substr( $part, 0, 1 ) : substr( $part, 0, 1 );
		if ( strlen( $initials ) >= 2 ) {
			break;
		}
	}

	$html  = '<section class="cf-author" aria-label="' . esc_attr__( 'About the author', 'claimfairly' ) . '">';
	$html .= '<div class="cf-author__avatar" aria-hidden="true">' . esc_html( strtoupper( $initials ) ) . '</div>';
	$html .= '<div class="cf-author__body"><p class="cf-author__name">' . esc_html( $name ) . '</p>';
	if ( $bio ) {
		$html .= '<p class="cf-author__bio">' . esc_html( $bio ) . '</p>';
	}
	$html .= '<p class="cf-author__links"><a href="' . esc_url( claimfairly_about_url() ) . '">' . esc_html__( 'About ClaimFairly and how our tools are built', 'claimfairly' ) . '</a></p>';
	$html .= '</div></section>';
	return $html;
}

/**
 * Tool pages (for homepage grid and related-tools fallback).
 *
 * @param int $limit   Max items (-1 for all).
 * @param int $exclude Post ID to exclude.
 * @return WP_Post[]
 */
function claimfairly_get_tools( $limit = -1, $exclude = 0 ) {
	return get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__not_in'   => $exclude ? array( $exclude ) : array(),
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'meta_key'       => '_cf_page_type', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => 'tool', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/**
 * Card summary for a page: meta field, then excerpt.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function claimfairly_card_summary( $post ) {
	$summary = (string) get_post_meta( $post->ID, '_cf_card_summary', true );
	if ( '' === $summary ) {
		$summary = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 20 );
	}
	return $summary;
}

/**
 * Related tools / pages box.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function claimfairly_get_related_box( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$links   = claimfairly_parse_link_lines( get_post_meta( $post_id, '_cf_related', true ) );
	$cards   = array();

	if ( $links ) {
		foreach ( $links as $link ) {
			$url     = 0 === strpos( $link['url'], '/' ) ? home_url( $link['url'] ) : $link['url'];
			$cards[] = array(
				'title'   => $link['label'],
				'url'     => $url,
				'summary' => '',
			);
		}
	} else {
		foreach ( claimfairly_get_tools( 3, $post_id ) as $tool ) {
			$cards[] = array(
				'title'   => get_the_title( $tool ),
				'url'     => get_permalink( $tool ),
				'summary' => claimfairly_card_summary( $tool ),
			);
		}
	}

	if ( ! $cards ) {
		return '';
	}

	$html = '<section class="cf-related" aria-labelledby="cf-related-title"><h2 id="cf-related-title">' . esc_html__( 'Related tools', 'claimfairly' ) . '</h2><ul class="cf-cards">';
	foreach ( $cards as $card ) {
		$html .= '<li class="cf-card"><a class="cf-card__link" href="' . esc_url( $card['url'] ) . '"><span class="cf-card__title">' . esc_html( $card['title'] ) . '</span>';
		if ( $card['summary'] ) {
			$html .= '<span class="cf-card__text">' . esc_html( $card['summary'] ) . '</span>';
		}
		$html .= '</a></li>';
	}
	$html .= '</ul></section>';
	return $html;
}

/**
 * Everything shown below the content on trust pages, in the order the
 * playbook asks for: sources + reviewed date, author box, related tools.
 */
function claimfairly_trust_footer() {
	if ( ! claimfairly_is_trust_page() ) {
		return;
	}
	// Tool pages already print the disclaimer inside the result box; others get it here.
	if ( 'tool' !== claimfairly_get_page_type() ) {
		echo claimfairly_get_disclaimer(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function.
	}
	echo claimfairly_get_sources_box(); // phpcs:ignore WordPress.Security.EscapeOutput
	echo claimfairly_get_author_box(); // phpcs:ignore WordPress.Security.EscapeOutput
	echo claimfairly_get_related_box(); // phpcs:ignore WordPress.Security.EscapeOutput
}
