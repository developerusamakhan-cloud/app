<?php
/**
 * Reusable template pieces: breadcrumbs, byline, cards, sources, author box,
 * related tools, disclaimer.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Breadcrumb trail as [name, url] pairs. Shared by the visible breadcrumbs
 * and the BreadcrumbList schema so they never disagree.
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
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor_id ) {
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
		$items[] = array(
			'name' => single_term_title( '', false ),
			'url'  => get_term_link( get_queried_object() ),
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
	echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'claimfairly' ) . '"><ol>';
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
 * Human date.
 *
 * @param string $ymd Date.
 * @return string
 */
function claimfairly_date( $ymd ) {
	return mysql2date( get_option( 'date_format' ), $ymd );
}

/**
 * About page URL (absolute).
 *
 * @return string
 */
function claimfairly_about_url() {
	$url = claimfairly_opt( 'cf_about_url' );
	return 0 === strpos( $url, '/' ) ? home_url( $url ) : $url;
}

/**
 * "By Name, reviewed date" line under the H1.
 */
function claimfairly_byline() {
	if ( ! claimfairly_is_trust_page() ) {
		return;
	}
	$post_id  = get_the_ID();
	$reviewed = claimfairly_last_reviewed_raw( $post_id );
	$author   = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) );
	$minutes  = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) ) ) / 200 ) );
	echo '<div class="byline">';
	if ( $author ) {
		echo '<span class="byline__avatar" aria-hidden="true">' . esc_html( claimfairly_initials( $author ) ) . '</span>';
		echo '<span>' . esc_html__( 'By', 'claimfairly' ) . ' <a href="' . esc_url( claimfairly_about_url() ) . '">' . esc_html( $author ) . '</a></span>';
	}
	if ( $reviewed ) {
		echo '<span class="byline__sep" aria-hidden="true"></span><span>' . esc_html__( 'Reviewed', 'claimfairly' ) . ' <time datetime="' . esc_attr( $reviewed ) . '">' . esc_html( claimfairly_date( $reviewed ) ) . '</time></span>';
	}
	if ( 'tool' !== claimfairly_get_page_type( $post_id ) ) {
		/* translators: %d: minutes. */
		echo '<span class="byline__sep" aria-hidden="true"></span><span>' . esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'claimfairly' ), $minutes ) ) . '</span>';
	}
	echo '</div>';
}

/**
 * Up to two initials from a name.
 *
 * @param string $name Name.
 * @return string
 */
function claimfairly_initials( $name ) {
	$out = '';
	foreach ( preg_split( '/\s+/', trim( $name ) ) as $part ) {
		if ( '' === $part ) {
			continue;
		}
		$out .= function_exists( 'mb_substr' ) ? mb_substr( $part, 0, 1 ) : substr( $part, 0, 1 );
		if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $out ) : strlen( $out ) ) >= 2 ) {
			break;
		}
	}
	return strtoupper( $out );
}

/**
 * Disclaimer box.
 *
 * @return string
 */
function claimfairly_get_disclaimer() {
	return '<aside class="disclaimer" role="note"><strong>' . esc_html__( 'Not legal advice.', 'claimfairly' ) . '</strong> ' . esc_html( claimfairly_opt( 'cf_disclaimer' ) ) . '</aside>';
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
	$html = '<section class="sources" aria-labelledby="sources-title"><h2 id="sources-title">' . esc_html__( 'Sources', 'claimfairly' ) . '</h2><ol>';
	foreach ( $sources as $source ) {
		$host  = wp_parse_url( $source['url'], PHP_URL_HOST );
		$html .= '<li><a href="' . esc_url( $source['url'] ) . '" rel="noopener" target="_blank">' . esc_html( $source['label'] ) . '</a>';
		if ( $host ) {
			$html .= ' <span class="sources__host">' . esc_html( preg_replace( '/^www\./', '', $host ) ) . '</span>';
		}
		$html .= '</li>';
	}
	$html    .= '</ol>';
	$reviewed = claimfairly_last_reviewed_raw( $post_id );
	if ( $reviewed ) {
		$html .= '<p class="sources__reviewed">' . esc_html__( 'Last reviewed:', 'claimfairly' ) . ' <time datetime="' . esc_attr( $reviewed ) . '">' . esc_html( claimfairly_date( $reviewed ) ) . '</time>. ' . esc_html__( 'Spotted something out of date?', 'claimfairly' ) . ' <a href="mailto:' . esc_attr( antispambot( claimfairly_opt( 'cf_contact_email' ) ) ) . '">' . esc_html__( 'Tell us', 'claimfairly' ) . '</a>.</p>';
	}
	return $html . '</section>';
}

/**
 * Author box. Uses the user's display name and Biographical Info.
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
	$photo = (int) claimfairly_opt( 'cf_founder_photo' );
	$html  = '<section class="author" aria-label="' . esc_attr__( 'About the author', 'claimfairly' ) . '">';
	if ( $photo && user_can( $author_id, 'manage_options' ) ) {
		$html .= wp_get_attachment_image( $photo, 'thumbnail', false, array( 'class' => 'author__photo', 'alt' => '' ) );
	} else {
		$html .= '<div class="author__avatar" aria-hidden="true">' . esc_html( claimfairly_initials( $name ) ) . '</div>';
	}
	$html .= '<div><p class="author__label">' . esc_html__( 'Written and checked by', 'claimfairly' ) . '</p><p class="author__name">' . esc_html( $name ) . '</p>';
	if ( $bio ) {
		$html .= '<p class="author__bio">' . esc_html( $bio ) . '</p>';
	}
	$html .= '<p class="author__links"><a href="' . esc_url( claimfairly_about_url() ) . '">' . esc_html__( 'About ClaimFairly', 'claimfairly' ) . '</a> <span aria-hidden="true">·</span> <a href="' . esc_url( home_url( '/editorial-policy/' ) ) . '">' . esc_html__( 'How we check our facts', 'claimfairly' ) . '</a></p>';
	return $html . '</div></section>';
}

/**
 * Published tool pages, ordered by menu order.
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
 * Card summary: meta field, then excerpt.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function claimfairly_card_summary( $post ) {
	$summary = (string) get_post_meta( $post->ID, '_cf_card_summary', true );
	if ( '' === $summary ) {
		$summary = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 22, '.' );
	}
	return $summary;
}

/**
 * Compact tool card (icon square + title + summary), used in related boxes and menus.
 *
 * @param WP_Post $post Tool page.
 * @return string
 */
function claimfairly_tool_row( $post ) {
	$style = claimfairly_page_style( $post->ID );
	return '<li><a class="tool-row" href="' . esc_url( get_permalink( $post ) ) . '"><span class="icon-sq c-' . esc_attr( $style['color'] ) . '">' . claimfairly_icon( $style['icon'], 20 ) . '</span><span><span class="tool-row__title">' . esc_html( get_the_title( $post ) ) . '</span><span class="tool-row__text">' . esc_html( claimfairly_card_summary( $post ) ) . '</span></span></a></li>';
}

/**
 * Guide card with a colored cover.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function claimfairly_guide_card( $post ) {
	$cats  = get_the_category( $post->ID );
	$cat   = $cats ? $cats[0]->name : __( 'Guide', 'claimfairly' );
	$slug  = $cats ? $cats[0]->slug : '';
	$map   = array(
		'diminished-value'  => array( 'violet', 'car-down' ),
		'settlements'       => array( 'orange', 'pie' ),
		'fault-and-states'  => array( 'blue', 'pin' ),
		'after-a-crash'     => array( 'rose', 'doc' ),
		'insurance-claims'  => array( 'navy', 'shield' ),
	);
	$look  = isset( $map[ $slug ] ) ? $map[ $slug ] : array( 'green', 'book' );
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	$mins  = max( 1, (int) ceil( $words / 200 ) );

	$html  = '<li class="guide-card"><a href="' . esc_url( get_permalink( $post ) ) . '">';
	$html .= '<span class="guide-card__cover c-' . esc_attr( $look[0] ) . '">';
	if ( has_post_thumbnail( $post ) ) {
		$html .= get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) );
	} else {
		$html .= '<span class="guide-card__icon">' . claimfairly_icon( $look[1], 30 ) . '</span><span class="guide-card__cover-title">' . esc_html( $cat ) . '</span>';
	}
	$html .= '</span><span class="guide-card__body">';
	$html .= '<span class="guide-card__title">' . esc_html( get_the_title( $post ) ) . '</span>';
	$html .= '<span class="guide-card__text">' . esc_html( claimfairly_card_summary( $post ) ) . '</span>';
	/* translators: %d: minutes. */
	$html .= '<span class="guide-card__meta">' . esc_html( sprintf( __( '%d min read', 'claimfairly' ), $mins ) ) . '</span>';
	return $html . '</span></a></li>';
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
	$items   = '';

	if ( $links ) {
		foreach ( $links as $link ) {
			$url  = 0 === strpos( $link['url'], '/' ) ? home_url( $link['url'] ) : $link['url'];
			$page = 0 === strpos( $link['url'], '/' ) ? get_page_by_path( trim( $link['url'], '/' ) ) : null;
			if ( $page && 'publish' === $page->post_status ) {
				$items .= claimfairly_tool_row( $page );
			} else {
				$items .= '<li><a class="tool-row" href="' . esc_url( $url ) . '"><span class="icon-sq c-green">' . claimfairly_icon( 'arrow', 20 ) . '</span><span><span class="tool-row__title">' . esc_html( $link['label'] ) . '</span></span></a></li>';
			}
		}
	} else {
		foreach ( claimfairly_get_tools( 3, $post_id ) as $tool ) {
			$items .= claimfairly_tool_row( $tool );
		}
	}

	if ( '' === $items ) {
		return '';
	}
	return '<section class="related" aria-labelledby="related-title"><h2 id="related-title">' . esc_html__( 'Keep going', 'claimfairly' ) . '</h2><ul class="tool-rows">' . $items . '</ul></section>';
}

/**
 * Everything below the content on trust pages: disclaimer, sources, author, related.
 */
function claimfairly_trust_footer() {
	if ( ! claimfairly_is_trust_page() ) {
		return;
	}
	if ( 'tool' !== claimfairly_get_page_type() ) {
		echo claimfairly_get_disclaimer(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function.
	}
	echo claimfairly_get_sources_box(); // phpcs:ignore WordPress.Security.EscapeOutput
	echo claimfairly_get_author_box(); // phpcs:ignore WordPress.Security.EscapeOutput
	echo claimfairly_get_related_box(); // phpcs:ignore WordPress.Security.EscapeOutput
}
