<?php
/**
 * ClaimFairly theme bootstrap.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLAIMFAIRLY_VERSION', '2.5.0' );
// Bump when the logo, favicon or share images change, so setup replaces the old ones.
define( 'CLAIMFAIRLY_BRAND_VERSION', 'navy' );
define( 'CLAIMFAIRLY_DIR', get_template_directory() );
define( 'CLAIMFAIRLY_URI', get_template_directory_uri() );

require CLAIMFAIRLY_DIR . '/inc/icons.php';
require CLAIMFAIRLY_DIR . '/inc/setup.php';
require CLAIMFAIRLY_DIR . '/inc/cleanup.php';
require CLAIMFAIRLY_DIR . '/inc/customizer.php';
require CLAIMFAIRLY_DIR . '/inc/meta-boxes.php';
require CLAIMFAIRLY_DIR . '/inc/template-tags.php';
require CLAIMFAIRLY_DIR . '/inc/shortcodes.php';
require CLAIMFAIRLY_DIR . '/inc/schema.php';
require CLAIMFAIRLY_DIR . '/inc/importer.php';
