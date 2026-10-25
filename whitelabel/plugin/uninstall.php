<?php
/**
 * Uninstall: removes data only when the owner opted in (Privacy tab).
 *
 * @package AI_Site_Assistant
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'aisa_daily_cleanup' );

$aisa_settings = get_option( 'aisa_settings', array() );
if ( empty( $aisa_settings['delete_data_on_uninstall'] ) ) {
	return;
}

global $wpdb;
foreach ( array( 'aisa_chunks', 'aisa_leads', 'aisa_logs' ) as $aisa_table ) {
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . $aisa_table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.SchemaChange -- removing the plugin's own tables on uninstall.
}
foreach ( array( 'aisa_settings', 'aisa_brand', 'aisa_installed_at', 'aisa_db_version', 'aisa_fulltext', 'aisa_onboarding_done', 'aisa_do_onboarding' ) as $aisa_option ) {
	delete_option( $aisa_option );
}
// Conversation history, rate limits and model lists are transients.
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_aisa_' ) . '%', $wpdb->esc_like( '_transient_timeout_aisa_' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
