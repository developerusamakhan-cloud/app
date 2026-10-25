<?php
/**
 * Plugin Name:       AI Site Assistant
 * Description:       AI chatbot that learns your website in one click, answers visitors in their language, and captures leads.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            AI Site Assistant
 * Update URI:        false
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-site-assistant
 * Domain Path:       /languages
 *
 * @package AI_Site_Assistant
 */

defined( 'ABSPATH' ) || exit;

define( 'AISA_VERSION', '1.0.0' );
define( 'AISA_FILE', __FILE__ );
define( 'AISA_DIR', plugin_dir_path( __FILE__ ) );
define( 'AISA_URL', plugin_dir_url( __FILE__ ) );

require_once AISA_DIR . 'includes/class-aisa-text.php';
require_once AISA_DIR . 'includes/class-aisa-kses.php';
require_once AISA_DIR . 'includes/class-aisa-settings.php';
require_once AISA_DIR . 'includes/class-aisa-db.php';
require_once AISA_DIR . 'includes/class-aisa-brand.php';
require_once AISA_DIR . 'includes/class-aisa-i18n.php';
require_once AISA_DIR . 'includes/class-aisa-contrast.php';
require_once AISA_DIR . 'includes/class-aisa-markdown.php';
require_once AISA_DIR . 'includes/class-aisa-indexer.php';
require_once AISA_DIR . 'includes/class-aisa-retriever.php';
require_once AISA_DIR . 'includes/class-aisa-providers.php';
require_once AISA_DIR . 'includes/class-aisa-history.php';
require_once AISA_DIR . 'includes/class-aisa-rate-limiter.php';
require_once AISA_DIR . 'includes/class-aisa-conversation.php';
require_once AISA_DIR . 'includes/class-aisa-logs.php';
require_once AISA_DIR . 'includes/class-aisa-leads.php';
require_once AISA_DIR . 'includes/class-aisa-chat.php';
require_once AISA_DIR . 'includes/class-aisa-rest.php';
require_once AISA_DIR . 'includes/class-aisa-privacy.php';
require_once AISA_DIR . 'includes/class-aisa-frontend.php';
require_once AISA_DIR . 'includes/class-aisa-block.php';

spl_autoload_register(
	static function ( string $class_name ): void {
		if ( 0 !== strpos( $class_name, 'AisaPro\\' ) ) {
			return;
		}
		$file = AISA_DIR . 'includes/pro/' . substr( $class_name, strlen( 'AisaPro\\' ) ) . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

if ( is_admin() ) {
	require_once AISA_DIR . 'includes/admin/class-aisa-admin.php';
	require_once AISA_DIR . 'includes/admin/class-aisa-onboarding.php';
}

register_activation_hook( __FILE__, array( 'Aisa_DB', 'activate' ) );
register_activation_hook( __FILE__, array( 'AisaPro\\Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Aisa_DB', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		Aisa_DB::maybe_upgrade();
		Aisa_Brand::init();
		Aisa_I18n::init();
		Aisa_Indexer::init();
		Aisa_REST::init();
		Aisa_Privacy::init();
		Aisa_Frontend::init();
		Aisa_Block::init();
		if ( is_admin() ) {
			Aisa_Admin::init();
			Aisa_Onboarding::init();
		}
		AisaPro\Plugin::boot();

		/**
		 * Fires after the plugin has loaded. Add-ons hook in here.
		 *
		 * @param string $version the plugin version.
		 */
		do_action( 'aisa_loaded', AISA_VERSION );
	}
);

add_action( 'aisa_daily_cleanup', array( 'Aisa_DB', 'cleanup' ) );
