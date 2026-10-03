<?php
/**
 * PowerBachat theme bootstrap.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'POWERBACHAT_VERSION', '1.1.1' );
define( 'POWERBACHAT_DIR', get_template_directory() );
define( 'POWERBACHAT_URI', get_template_directory_uri() );

require POWERBACHAT_DIR . '/inc/setup.php';
require POWERBACHAT_DIR . '/inc/data.php';
require POWERBACHAT_DIR . '/inc/customizer.php';
require POWERBACHAT_DIR . '/inc/geo.php';
require POWERBACHAT_DIR . '/inc/enqueue.php';
require POWERBACHAT_DIR . '/inc/template-tags.php';
require POWERBACHAT_DIR . '/inc/shortcodes.php';
require POWERBACHAT_DIR . '/inc/schema.php';
