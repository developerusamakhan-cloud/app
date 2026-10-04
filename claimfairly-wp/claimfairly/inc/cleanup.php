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
