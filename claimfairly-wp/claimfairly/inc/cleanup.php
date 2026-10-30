<?php
/**
 * Remove WordPress bloat that costs speed or leaks information.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Emoji scripts and styles.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', '__return_false' );

// Head clutter.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );

// XML-RPC is a common brute-force target and is not needed here.
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Comments are off site-wide: a YMYL calculator site should not host
 * unmoderated advice. Users are pointed to the contact email instead.
 */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );

/**
 * Remove the Comments screen from the admin menu.
 */
function claimfairly_remove_comments_menu() {
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'claimfairly_remove_comments_menu' );

/**
 * Drop the classic-theme global styles that are not used by this theme.
 */
function claimfairly_dequeue_unused() {
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'claimfairly_dequeue_unused', 20 );

/**
 * Publish scheduled posts that missed their time.
 *
 * WordPress publishes scheduled posts through WP-Cron, which only runs when
 * someone visits. On a quiet site a post can sit as "Missed schedule". This
 * check runs at most every 10 minutes and publishes anything overdue.
 */
function claimfairly_publish_missed_schedule() {
	if ( wp_doing_ajax() || get_transient( 'claimfairly_missed_check' ) ) {
		return;
	}
	set_transient( 'claimfairly_missed_check', 1, 10 * MINUTE_IN_SECONDS );
	$overdue = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'future',
			'posts_per_page' => 20,
			'fields'         => 'ids',
			'date_query'     => array(
				array(
					'column' => 'post_date_gmt',
					'before' => gmdate( 'Y-m-d H:i:s' ),
				),
			),
		)
	);
	foreach ( $overdue as $post_id ) {
		wp_publish_post( $post_id );
	}
}
add_action( 'init', 'claimfairly_publish_missed_schedule', 20 );

/**
 * Pages kept out of search results: thin archives, and pages that are still
 * too close to a sibling page to stand on their own. They stay reachable for
 * visitors and keep passing links ("follow").
 *
 * @return string[] Page slugs.
 */
function claimfairly_noindex_slugs() {
	return array( 'how-allstate-handles-car-accident-claims' );
}

/**
 * Whether the current request should carry noindex.
 *
 * @return bool
 */
function claimfairly_is_noindex_request() {
	if ( is_category() || is_tag() || is_author() || is_date() ) {
		return true;
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		return $post instanceof WP_Post && in_array( $post->post_name, claimfairly_noindex_slugs(), true );
	}
	return false;
}

/**
 * Core robots meta (used when no SEO plugin controls it).
 *
 * @param array $robots Robots directives.
 * @return array
 */
function claimfairly_wp_robots_noindex( $robots ) {
	if ( claimfairly_is_noindex_request() ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'claimfairly_wp_robots_noindex', 20 );

/**
 * Rank Math robots meta.
 *
 * @param array $robots Robots directives keyed by name.
 * @return array
 */
function claimfairly_rank_math_noindex( $robots ) {
	if ( claimfairly_is_noindex_request() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'claimfairly_rank_math_noindex', 20 );

/**
 * Keep the same pages out of the Rank Math sitemap.
 *
 * @param string|false $url    Sitemap entry.
 * @param string       $type   Entry type.
 * @param object       $object Post or term.
 * @return string|false
 */
function claimfairly_rank_math_sitemap_entry( $url, $type, $object ) {
	if ( 'post' === $type && $object instanceof WP_Post && in_array( $object->post_name, claimfairly_noindex_slugs(), true ) ) {
		return false;
	}
	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'claimfairly_rank_math_sitemap_entry', 20, 3 );

/**
 * Drop category and tag archives from the Rank Math sitemap.
 *
 * @param bool   $exclude Whether to exclude.
 * @param string $type    Taxonomy name.
 * @return bool
 */
function claimfairly_rank_math_exclude_taxonomy( $exclude, $type ) {
	return in_array( $type, array( 'category', 'post_tag' ), true ) ? true : $exclude;
}
add_filter( 'rank_math/sitemap/exclude_taxonomy', 'claimfairly_rank_math_exclude_taxonomy', 20, 2 );
