<?php
/**
 * Plugin Name:       Nabia Website Audit
 * Plugin URI:        https://nabiakhan.com/
 * Description:       Free website audit form with a math captcha. Scores a website for Design, SEO, Content and Speed & Security in real time, shows the results on the page, builds a branded PDF report and emails it. Every request is saved under Website Audits.
 * Version:           1.2.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Nabia Khan
 * Author URI:        https://nabiakhan.com/
 * License:           GPL-2.0-or-later
 * Text Domain:       nabia-audit
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NWA_VERSION', '1.2.1' );
define( 'NWA_FILE', __FILE__ );
define( 'NWA_DIR', plugin_dir_path( __FILE__ ) );
define( 'NWA_URL', plugin_dir_url( __FILE__ ) );

require_once NWA_DIR . 'includes/settings.php';
require_once NWA_DIR . 'includes/analyzer.php';
require_once NWA_DIR . 'includes/report-pdf.php';
require_once NWA_DIR . 'includes/storage.php';
require_once NWA_DIR . 'includes/mailer.php';
require_once NWA_DIR . 'includes/form.php';
require_once NWA_DIR . 'includes/admin.php';

/**
 * Create the protected folder for PDF reports on activation.
 */
function nwa_activate() {
	nwa_register_post_type();
	nwa_reports_dir();
	flush_rewrite_rules( false );
}
register_activation_hook( __FILE__, 'nwa_activate' );

/**
 * Settings link on the Plugins screen.
 *
 * @param array $links Links.
 * @return array
 */
function nwa_plugin_links( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit.php?post_type=nabia_audit&page=nwa-settings' ) ) . '">' . esc_html__( 'Settings', 'nabia-audit' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'nwa_plugin_links' );
