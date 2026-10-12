<?php
/**
 * Uninstall Talkwyn Pro. Data is removed only when "Delete all Talkwyn data"
 * is on (Talkwyn > Privacy). Uninstalling does not free the license slot on
 * talkwyn.com: deactivate the license in Talkwyn > License first, or deactivate
 * the site later from My Account on talkwyn.com.
 *
 * @package TalkwynPro
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

foreach ( array( 'talkwyn_pro_embed', 'talkwyn_pro_crawl', 'talkwyn_license_daily_check' ) as $talkwyn_pro_hook ) {
	wp_clear_scheduled_hook( $talkwyn_pro_hook );
}

global $wpdb;

// Custom answers and PDF, document and web page sources live in the free plugin's index as
// qa:N and src:N. Only Pro can manage them, so they leave with Pro.
$talkwyn_pro_chunks = $wpdb->prefix . 'talkwyn_chunks';
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $talkwyn_pro_chunks ) ) === $talkwyn_pro_chunks ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE source_key LIKE %s OR source_key LIKE %s', $talkwyn_pro_chunks, $wpdb->esc_like( 'qa:' ) . '%', $wpdb->esc_like( 'src:' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
foreach ( array( 'talkwyn_license_update_info', 'talkwyn_license_catch_up', 'talkwyn_license_catch_up_late' ) as $talkwyn_pro_transient ) {
	delete_transient( $talkwyn_pro_transient );
}

$talkwyn_pro_settings = get_option( 'talkwyn_settings', array() );
if ( empty( $talkwyn_pro_settings['delete_data_on_uninstall'] ) ) {
	return;
}

// Pro API keys and alert channels stored in the shared settings.
if ( is_array( $talkwyn_pro_settings ) ) {
	foreach ( array( 'openai_key', 'anthropic_key', 'mistral_key', 'deepseek_key', 'pro_slack_webhook', 'pro_telegram_token', 'pro_telegram_chat' ) as $talkwyn_pro_secret ) {
		unset( $talkwyn_pro_settings[ $talkwyn_pro_secret ] );
	}
	update_option( 'talkwyn_settings', $talkwyn_pro_settings );
}
foreach ( array( 'talkwyn_pro_vectors', 'talkwyn_pro_qa', 'talkwyn_pro_sources' ) as $talkwyn_pro_table ) {
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . $talkwyn_pro_table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.SchemaChange
}
foreach ( array( 'talkwyn_pro_db_version', 'talkwyn_pro_crawl_queue', 'talkwyn_license_key', 'talkwyn_license_state', 'talkwyn_license_instance' ) as $talkwyn_pro_option ) {
	delete_option( $talkwyn_pro_option );
}
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_talkwyn_pro_' ) . '%', $wpdb->esc_like( '_transient_timeout_talkwyn_pro_' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
