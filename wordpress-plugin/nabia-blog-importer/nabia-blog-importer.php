<?php
/**
 * Plugin Name:       Nabia Blog Importer
 * Plugin URI:        https://nabiakhan.com/
 * Description:       Import ready-made blog packs (.zip) with cover images, FAQ, categories, tags and SEO details, and publish them now, as drafts or on a schedule you choose. Posts → Blog Packs.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Nabia Khan
 * Author URI:        https://nabiakhan.com/
 * License:           GPL-2.0-or-later
 * Text Domain:       nabia-blog-importer
 *
 * @package Nabia_Blog_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NBI_VERSION', '1.1.0' );
define( 'NBI_DIR', plugin_dir_path( __FILE__ ) );
define( 'NBI_URL', plugin_dir_url( __FILE__ ) );

require NBI_DIR . 'includes/packs.php';
require NBI_DIR . 'includes/importer.php';
require NBI_DIR . 'includes/admin.php';
