<?php
/**
 * Uninstall: removes data only when the owner opted in (Talkwyn > Privacy).
 *
 * @package Talkwyn
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'talkwyn_daily_cleanup' );

$talkwyn_settings = get_option( 'talkwyn_settings', array() );
if ( empty( $talkwyn_settings['delete_data_on_uninstall'] ) ) {
	return;
}

global $wpdb;
foreach ( array( 'talkwyn_chunks', 'talkwyn_leads', 'talkwyn_logs' ) as $talkwyn_table ) {
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . $talkwyn_table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.SchemaChange -- removing the plugin's own tables on uninstall.
}
foreach ( array( 'talkwyn_settings', 'talkwyn_db_version', 'talkwyn_fulltext', 'talkwyn_onboarding_done', 'talkwyn_do_onboarding', 'talkwyn_installed_at', 'talkwyn_review_done', 'talkwyn_review_snooze', 'talkwyn_review_asks', 'talkwyn_subscribed' ) as $talkwyn_option ) {
	delete_option( $talkwyn_option );
}
delete_metadata( 'user', 0, 'talkwyn_dismissed', '', true );
// Conversation history, rate limits and model lists are transients.
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_talkwyn_' ) . '%', $wpdb->esc_like( '_transient_timeout_talkwyn_' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
