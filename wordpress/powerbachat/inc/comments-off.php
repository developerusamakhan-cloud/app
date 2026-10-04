<?php
/**
 * Comments are switched off across the whole site: no comment forms, no comment
 * lists, no pingbacks or trackbacks, no comment feeds, no comment REST endpoints
 * and no Comments screens in the admin. Existing comments are hidden, not deleted.
 *
 * To bring comments back, add this to a small plugin or a child theme:
 * add_filter( 'powerbachat_disable_comments', '__return_false' );
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Are comments off?
 *
 * @return bool
 */
function powerbachat_comments_off() {
	return (bool) apply_filters( 'powerbachat_disable_comments', true );
}

/**
 * Remove comment and trackback support from every post type.
 */
function powerbachat_remove_comment_support() {
	if ( ! powerbachat_comments_off() ) {
		return;
	}
	foreach ( get_post_types() as $type ) {
		if ( post_type_supports( $type, 'comments' ) ) {
			remove_post_type_support( $type, 'comments' );
		}
		if ( post_type_supports( $type, 'trackbacks' ) ) {
			remove_post_type_support( $type, 'trackbacks' );
		}
	}
	// New posts and pages default to closed.
	add_filter( 'pre_option_default_comment_status', 'powerbachat_closed' );
	add_filter( 'pre_option_default_ping_status', 'powerbachat_closed' );
	add_filter( 'pre_option_default_pingback_flag', '__return_zero' );
}
add_action( 'init', 'powerbachat_remove_comment_support', 100 );

/**
 * Value for closed comment and ping options.
 *
 * @return string
 */
function powerbachat_closed() {
	return 'closed';
}

/**
 * Close comments and pings everywhere and hide any existing comments.
 */
function powerbachat_comment_filters() {
	if ( ! powerbachat_comments_off() ) {
		return;
	}
	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open', '__return_false', 20 );
	add_filter( 'comments_array', '__return_empty_array', 20 );
	add_filter( 'get_comments_number', '__return_zero', 20 );
	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	add_filter( 'post_comments_feed_link', '__return_empty_string' );
	add_filter( 'rest_allow_anonymous_comments', '__return_false' );
	add_filter( 'xmlrpc_methods', 'powerbachat_remove_pingback_methods' );
	add_filter( 'wp_headers', 'powerbachat_remove_pingback_header' );
	add_filter( 'rest_endpoints', 'powerbachat_remove_comment_endpoints' );
	add_filter( 'comment_reply_link', '__return_empty_string' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'after_setup_theme', 'powerbachat_comment_filters' );

/**
 * No pingbacks over XML-RPC.
 *
 * @param array $methods Methods.
 * @return array
 */
function powerbachat_remove_pingback_methods( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
}

/**
 * No X-Pingback header.
 *
 * @param array $headers Headers.
 * @return array
 */
function powerbachat_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}

/**
 * No comment endpoints in the REST API.
 *
 * @param array $endpoints Endpoints.
 * @return array
 */
function powerbachat_remove_comment_endpoints( $endpoints ) {
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/comments' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}

/**
 * Comment feeds and comment-reply URLs return the post instead of a feed.
 */
function powerbachat_block_comment_feeds() {
	if ( powerbachat_comments_off() && is_comment_feed() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'powerbachat_block_comment_feeds', 9 );

/**
 * Admin: remove the Comments menu, dashboard widget, Discussion settings page,
 * admin bar link and comment columns, and send direct visits back.
 */
function powerbachat_comments_admin() {
	if ( ! powerbachat_comments_off() ) {
		return;
	}
	global $pagenow;
	if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'options-discussion.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}
add_action( 'admin_init', 'powerbachat_comments_admin' );

/**
 * Remove comment menus.
 */
function powerbachat_comments_menus() {
	if ( powerbachat_comments_off() ) {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}
}
add_action( 'admin_menu', 'powerbachat_comments_menus', 999 );

/**
 * Remove the comments bubble from the admin bar.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function powerbachat_comments_admin_bar( $bar ) {
	if ( powerbachat_comments_off() ) {
		$bar->remove_node( 'comments' );
	}
}
add_action( 'admin_bar_menu', 'powerbachat_comments_admin_bar', 999 );

/**
 * No "Recent comments" widget.
 */
function powerbachat_comments_widget() {
	if ( powerbachat_comments_off() ) {
		unregister_widget( 'WP_Widget_Recent_Comments' );
	}
}
add_action( 'widgets_init', 'powerbachat_comments_widget', 20 );

/**
 * Close comments on everything once, when the theme is switched on, so the
 * database matches what visitors see.
 */
function powerbachat_close_existing_comments() {
	if ( ! powerbachat_comments_off() || get_option( 'powerbachat_comments_closed' ) ) {
		return;
	}
	global $wpdb;
	$wpdb->query( "UPDATE {$wpdb->posts} SET comment_status = 'closed', ping_status = 'closed' WHERE comment_status <> 'closed' OR ping_status <> 'closed'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'powerbachat_comments_closed', 1 );
	clean_post_cache( 0 );
}
add_action( 'after_switch_theme', 'powerbachat_close_existing_comments' );
add_action( 'admin_init', 'powerbachat_close_existing_comments' );
