<?php
/**
 * White label: the name, author and icons this plugin shows everywhere.
 *
 * @package AI_Site_Assistant
 */

defined( 'ABSPATH' ) || exit;

/**
 * Brand settings. Every visible product name comes from here, so the plugin can be resold
 * under any name. Text in translatable strings uses the {brand} token, which is replaced
 * with the brand name on output.
 */
class Aisa_Brand {

	const OPTION = 'aisa_brand';

	/**
	 * Per-request cache.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'name'        => 'AI Assistant',
			'description' => 'AI chatbot that learns your website in one click, answers visitors in their language, and captures leads.',
			'author'      => '',
			'author_url'  => '',
			'menu_icon'   => 'dashicons-format-chat',
			'logo_url'    => '',
			'hide_tab'    => 0,
		);
	}

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'gettext_ai-site-assistant', array( __CLASS__, 'replace' ) );
		add_filter( 'ngettext_ai-site-assistant', array( __CLASS__, 'replace' ) );
		add_filter( 'gettext_with_context_ai-site-assistant', array( __CLASS__, 'replace' ) );
		add_filter( 'all_plugins', array( __CLASS__, 'plugin_list' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
	}

	/**
	 * All brand settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function all() {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			self::$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
			if ( '' === trim( (string) self::$cache['name'] ) ) {
				self::$cache['name'] = self::defaults()['name'];
			}
		}
		return self::$cache;
	}

	/**
	 * One brand setting.
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	public static function get( $key ) {
		$all = self::all();
		return $all[ $key ] ?? '';
	}

	/**
	 * Brand name.
	 *
	 * @return string
	 */
	public static function name() {
		/**
		 * Filters the brand name shown in the admin menu, plugin list and chat.
		 *
		 * @param string $name Name from the Branding settings.
		 */
		return (string) apply_filters( 'aisa_brand_name', (string) self::get( 'name' ) );
	}

	/**
	 * Admin menu icon: a Dashicons class or an image URL.
	 *
	 * @return string
	 */
	public static function menu_icon() {
		$icon = trim( (string) self::get( 'menu_icon' ) );
		if ( 0 === strpos( $icon, 'dashicons-' ) ) {
			return sanitize_html_class( $icon );
		}
		$url = esc_url_raw( $icon );
		return '' !== $url ? $url : 'dashicons-format-chat';
	}

	/**
	 * Replace the {brand} token in this plugin's strings.
	 *
	 * @param string $text Translated text.
	 * @return string
	 */
	public static function replace( $text ) {
		return false === strpos( (string) $text, '{brand}' ) ? $text : str_replace( '{brand}', self::name(), (string) $text );
	}

	/**
	 * Save brand settings from the Branding tab.
	 *
	 * @param array $in Raw input (unslashed).
	 * @return void
	 */
	public static function save( array $in ) {
		$icon = trim( (string) ( $in['menu_icon'] ?? '' ) );
		$out  = array(
			'name'        => sanitize_text_field( (string) ( $in['name'] ?? '' ) ),
			'description' => sanitize_textarea_field( (string) ( $in['description'] ?? '' ) ),
			'author'      => sanitize_text_field( (string) ( $in['author'] ?? '' ) ),
			'author_url'  => esc_url_raw( (string) ( $in['author_url'] ?? '' ) ),
			'menu_icon'   => 0 === strpos( $icon, 'dashicons-' ) ? sanitize_html_class( $icon ) : esc_url_raw( $icon ),
			'logo_url'    => esc_url_raw( (string) ( $in['logo_url'] ?? '' ) ),
			'hide_tab'    => empty( $in['hide_tab'] ) ? 0 : 1,
		);
		update_option( self::OPTION, $out, false );
		self::$cache = null;
	}

	/**
	 * Show the brand name, author and description on the Plugins screen.
	 *
	 * @param array $plugins Installed plugins keyed by file.
	 * @return array
	 */
	public static function plugin_list( $plugins ) {
		$file = plugin_basename( AISA_FILE );
		if ( ! isset( $plugins[ $file ] ) ) {
			return $plugins;
		}
		$b      = self::all();
		$author = '' !== trim( (string) $b['author'] ) ? (string) $b['author'] : self::name();

		$plugins[ $file ]['Name']        = self::name();
		$plugins[ $file ]['Title']       = self::name();
		$plugins[ $file ]['Description'] = '' !== trim( (string) $b['description'] ) ? (string) $b['description'] : self::defaults()['description'];
		$plugins[ $file ]['Author']      = $author;
		$plugins[ $file ]['AuthorName']  = $author;
		$plugins[ $file ]['AuthorURI']   = (string) $b['author_url'];
		$plugins[ $file ]['PluginURI']   = (string) $b['author_url'];
		return $plugins;
	}

	/**
	 * Keep the plugin row free of outside links other than the author's own site.
	 *
	 * @param array  $links Links.
	 * @param string $file  Plugin file.
	 * @return array
	 */
	public static function row_meta( $links, $file ) {
		if ( plugin_basename( AISA_FILE ) !== $file ) {
			return $links;
		}
		return array_values(
			array_filter(
				(array) $links,
				static function ( $link ) {
					return false === strpos( (string) $link, 'plugin-install.php' );
				}
			)
		);
	}
}
