<?php
/**
 * Plugin Name:       ClaimFairly Tools
 * Plugin URI:        https://claimfairly.com/
 * Description:       Free car accident and insurance claim calculators for ClaimFairly.com. Every tool runs in the visitor's browser and stores nothing. Use [cf_tool name="..."] to place a tool.
 * Version:           1.2.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            ClaimFairly
 * License:           GPL-2.0-or-later
 * Text Domain:       claimfairly-tools
 *
 * @package ClaimFairlyTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CFT_VERSION', '1.2.1' );
define( 'CFT_DIR', plugin_dir_path( __FILE__ ) );
define( 'CFT_URL', plugin_dir_url( __FILE__ ) );

require CFT_DIR . 'includes/states.php';
require CFT_DIR . 'includes/tools.php';
require CFT_DIR . 'includes/shortcodes.php';
require CFT_DIR . 'includes/admin.php';
