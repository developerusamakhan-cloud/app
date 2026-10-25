<?php
/**
 * Boot: loads the advanced modules (always on, no license).
 *
 * @package AI_Site_Assistant
 */

namespace AisaPro;

defined( 'ABSPATH' ) || exit;

/**
 * Bootstrap.
 */
final class Plugin {

	/**
	 * Boot on plugins_loaded, after the core classes are loaded.
	 */
	public static function boot(): void {
		Settings::init();
		Installer::maybe_upgrade();
		Admin::init();
		Providers::init();
		Search::init();
		Knowledge::init();
		Stream::init();
		Insights::init();
		Woo::init();
		Alerts::init();
		Engage::init();
		Transfer::init();
		Frontend::init();

		/**
		 * Fires after the advanced modules loaded.
		 */
		do_action( 'aisa_pro_loaded' );
	}
}
