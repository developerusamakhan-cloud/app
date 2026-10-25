<?php
/**
 * First-run setup wizard: AI connection, scan, colours, badge and updates, go live.
 *
 * @package AI_Site_Assistant
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setup wizard.
 */
class Aisa_Onboarding {

	const SLUG = 'aisa-setup';

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect' ) );
		add_action( 'admin_post_aisa_wizard', array( __CLASS__, 'save' ) );
	}

	/**
	 * Hidden page.
	 *
	 * @return void
	 */
	public static function menu() {
		add_submenu_page( 'options.php', __( '{brand} setup', 'ai-site-assistant' ), __( '{brand} setup', 'ai-site-assistant' ), 'manage_options', self::SLUG, array( __CLASS__, 'page' ) );
	}

	/**
	 * Open the wizard once after the first activation.
	 *
	 * @return void
	 */
	public static function maybe_redirect() {
		if ( ! get_option( 'aisa_do_onboarding' ) || ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
			return;
		}
		delete_option( 'aisa_do_onboarding' );
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG ) );
		exit;
	}

	/**
	 * Steps.
	 *
	 * @return array<int, array{0:string,1:string}>
	 */
	private static function steps() {
		return array(
			1 => array( __( 'AI', 'ai-site-assistant' ), 'providers' ),
			2 => array( __( 'Scan', 'ai-site-assistant' ), 'scan' ),
			3 => array( __( 'Colours', 'ai-site-assistant' ), 'appearance' ),
			4 => array( __( 'Badge and news', 'ai-site-assistant' ), 'star' ),
			5 => array( __( 'Go live', 'ai-site-assistant' ), 'rocket' ),
		);
	}

	/**
	 * URL of a step.
	 *
	 * @param int $step Step.
	 * @return string
	 */
	private static function url( $step ) {
		return admin_url( 'admin.php?page=' . self::SLUG . '&step=' . absint( $step ) );
	}

	/**
	 * Page.
	 *
	 * @return void
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$step  = isset( $_GET['step'] ) ? min( 6, max( 1, absint( $_GET['step'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$steps = self::steps();
		echo '<div class="wrap twa twa-wizard">';
		Aisa_Admin::hero( __( 'Set up {brand}', 'ai-site-assistant' ), __( 'About five minutes. You can change everything later.', 'ai-site-assistant' ), '<a class="twa-btn twa-btn--light" href="' . esc_url( admin_url( 'admin.php?page=aisa' ) ) . '">' . esc_html__( 'Skip setup', 'ai-site-assistant' ) . '</a>' );
		if ( $step <= 5 ) {
			$pct = round( 100 * ( $step - 1 ) / 4 );
			echo '<ol class="twa-wizard__steps" style="--twa-progress:' . esc_attr( (string) $pct ) . '%">';
			foreach ( $steps as $n => $info ) {
				$class = $n < $step ? 'is-done' : ( $n === $step ? 'is-current' : '' );
				echo wp_kses( '<li class="' . esc_attr( $class ) . '"' . ( $n === $step ? ' aria-current="step"' : '' ) . '><a href="' . esc_url( self::url( $n ) ) . '"><span class="twa-wizard__dot">' . ( $n < $step ? Aisa_Admin::icon( 'check' ) : esc_html( (string) $n ) ) . '</span><span class="twa-wizard__name">' . esc_html( $info[0] ) . '</span></a></li>', Aisa_Kses::html() );
			}
			echo '</ol>';
		}
		echo '<section class="twa-card twa-wizard__card">';
		call_user_func( array( __CLASS__, 'step_' . $step ) );
		echo '</section></div>';
	}

	/**
	 * Step heading.
	 *
	 * @param string $icon  Icon.
	 * @param string $title Title.
	 * @param string $text  Intro.
	 * @return void
	 */
	private static function heading( $icon, $title, $text ) {
		echo wp_kses( '<div class="twa-wizard__head"><span class="twa-chip-icon twa-chip-icon--lg">' . Aisa_Admin::icon( $icon ) . '</span><h2>' . esc_html( $title ) . '</h2><p class="twa-intro">' . esc_html( $text ) . '</p></div>', Aisa_Kses::html() );
	}

	/**
	 * Wizard form open.
	 *
	 * @param int $step Step.
	 * @return void
	 */
	private static function form_open( $step ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aisa_wizard"><input type="hidden" name="step" value="' . esc_attr( (string) $step ) . '">';
		wp_nonce_field( 'aisa_wizard' );
	}

	/**
	 * Navigation.
	 *
	 * @param int    $step       Step.
	 * @param bool   $submit     Next submits the form.
	 * @param string $next_label Label.
	 * @return void
	 */
	private static function nav( $step, $submit = false, $next_label = '' ) {
		$next_label = '' !== $next_label ? $next_label : __( 'Continue', 'ai-site-assistant' );
		echo '<div class="twa-wizard__nav">';
		if ( $step > 1 ) {
			echo '<a class="twa-btn twa-btn--light twa-btn--lg" href="' . esc_url( self::url( $step - 1 ) ) . '">' . esc_html__( 'Back', 'ai-site-assistant' ) . '</a>';
		} else {
			echo '<span></span>';
		}
		if ( $submit ) {
			echo wp_kses( '<button class="twa-btn twa-btn--ink twa-btn--lg">' . esc_html( $next_label ) . Aisa_Admin::icon( 'arrow' ) . '</button>', Aisa_Kses::html() );
		} else {
			echo wp_kses( '<a class="twa-btn twa-btn--ink twa-btn--lg" href="' . esc_url( self::url( $step + 1 ) ) . '">' . esc_html( $next_label ) . Aisa_Admin::icon( 'arrow' ) . '</a>', Aisa_Kses::html() );
		}
		echo '</div>';
	}

	/**
	 * Step 1: provider key, with a test button.
	 *
	 * @return void
	 */
	private static function step_1() {
		$s        = Aisa_Settings::all();
		$registry = Aisa_Providers::registry();
		if ( isset( $registry[ Aisa_Providers::WPAI ] ) ) {
			self::heading( 'providers', __( 'Connect an AI provider', 'ai-site-assistant' ), __( '{brand} uses the AI connection you set up for the whole site in Settings > Connectors. You can also add your own free key from a provider below, as the main connection or as a fallback.', 'ai-site-assistant' ) );
		} else {
			self::heading( 'providers', __( 'Add a free AI key', 'ai-site-assistant' ), __( '{brand} works with free tiers from these providers. Groq is the quickest to set up. Add more later for automatic fallback.', 'ai-site-assistant' ) );
		}
		self::form_open( 1 );
		if ( isset( $registry[ Aisa_Providers::WPAI ] ) ) {
			$site_ready = Aisa_Providers::wpai_ready();
			echo '<div class="twa-key-row twa-key-row--site" data-provider="' . esc_attr( Aisa_Providers::WPAI ) . '"><div class="twa-key-row__head"><span class="twa-avatar">W</span><strong>' . esc_html( $registry[ Aisa_Providers::WPAI ]['label'] ) . '</strong><span class="twa-status ' . ( $site_ready ? 'is-on' : '' ) . '">' . esc_html( $site_ready ? __( 'Connected', 'ai-site-assistant' ) : __( 'Not connected', 'ai-site-assistant' ) ) . '</span><a class="twa-link" href="' . esc_url( admin_url( 'options-connectors.php' ) ) . '">' . esc_html__( 'Open Connectors', 'ai-site-assistant' ) . '</a></div><div class="twa-key-row__fields"><div class="twa-key-row__test"><button type="button" class="twa-btn twa-btn--light twa-btn--sm twa-test">' . esc_html__( 'Test', 'ai-site-assistant' ) . '</button><span class="twa-test-status" aria-live="polite"></span></div></div></div>';
			echo '<p class="twa-intro">' . esc_html__( 'Optional: direct API connections', 'ai-site-assistant' ) . '</p>';
		}
		echo '<div class="twa-keys">';
		foreach ( $registry as $id => $p ) {
			if ( empty( $p['free'] ) ) {
				continue;
			}
			echo '<div class="twa-key-row" data-provider="' . esc_attr( $id ) . '"><div class="twa-key-row__head"><span class="twa-avatar">' . esc_html( strtoupper( substr( (string) $p['label'], 0, 1 ) ) ) . '</span><strong>' . esc_html( $p['label'] ) . '</strong>' . ( ! empty( $p['signup'] ) ? '<a class="twa-link" href="' . esc_url( $p['signup'] ) . '" target="_blank" rel="noopener">' . esc_html__( 'Get a free key', 'ai-site-assistant' ) . '</a>' : '' ) . '</div><div class="twa-key-row__fields">';
			foreach ( (array) $p['fields'] as $field ) {
				if ( ( $p['model_field'] ?? '' ) === $field ) {
					echo '<input type="hidden" name="aisa[' . esc_attr( $field ) . ']" value="' . esc_attr( (string) $s[ $field ] ) . '">';
					continue;
				}
				$label = ( $p['key_field'] ?? '' ) === $field ? __( 'API key', 'ai-site-assistant' ) : __( 'Account ID', 'ai-site-assistant' );
				echo '<label class="twa-field"><span>' . esc_html( $label ) . '</span><input type="' . ( ( $p['key_field'] ?? '' ) === $field ? 'password' : 'text' ) . '" name="aisa[' . esc_attr( $field ) . ']" value="' . esc_attr( (string) $s[ $field ] ) . '" autocomplete="off" spellcheck="false"></label>';
			}
			echo '<div class="twa-key-row__test"><button type="button" class="twa-btn twa-btn--light twa-btn--sm twa-test">' . esc_html__( 'Test', 'ai-site-assistant' ) . '</button><span class="twa-test-status" aria-live="polite"></span></div></div></div>';
		}
		echo '</div>';
		self::nav( 1, true, __( 'Save and continue', 'ai-site-assistant' ) );
		echo '</form>';
	}

	/**
	 * Step 2: scan.
	 *
	 * @return void
	 */
	private static function step_2() {
		self::heading( 'scan', __( 'Let {brand} read your website', 'ai-site-assistant' ), __( '{brand} scans your published pages, posts and products and keeps the text in your own database. It updates by itself when you edit content.', 'ai-site-assistant' ) );
		echo wp_kses( '<div class="twa-scan twa-scan--center"><button type="button" class="twa-btn twa-btn--red twa-btn--lg" id="twa-scan">' . Aisa_Admin::icon( 'scan' ) . esc_html__( 'Scan my site', 'ai-site-assistant' ) . '</button><span id="twa-scan-status" aria-live="polite">', Aisa_Kses::html() );
		/* translators: %s: number of chunks */
		printf( esc_html__( '%s chunks indexed', 'ai-site-assistant' ), esc_html( number_format_i18n( Aisa_Indexer::count() ) ) );
		echo '</span></div><div class="twa-progress" aria-hidden="true"><span></span></div>';
		self::nav( 2 );
	}

	/**
	 * Step 3: colour, name and welcome.
	 *
	 * @return void
	 */
	private static function step_3() {
		$s     = Aisa_Settings::all();
		$on    = Aisa_Contrast::text_on( (string) $s['brand_color'] );
		$ratio = Aisa_Contrast::ratio( (string) $s['brand_color'], $on );
		self::heading( 'appearance', __( 'Make it yours', 'ai-site-assistant' ), __( 'Pick your colour. Smart Contrast chooses readable text for it automatically.', 'ai-site-assistant' ) );
		self::form_open( 3 );
		echo '<div class="twa-field"><label for="twa-brand_color">' . esc_html__( 'Brand colour', 'ai-site-assistant' ) . '</label><div class="twa-colorrow"><input type="color" id="twa-brand_color" class="twa-color" name="aisa[brand_color]" value="' . esc_attr( (string) $s['brand_color'] ) . '"><div class="twa-contrast" data-ink="' . esc_attr( Aisa_Contrast::INK ) . '"><span class="twa-contrast__sample" style="background:' . esc_attr( (string) $s['brand_color'] ) . ';color:' . esc_attr( $on ) . '">' . esc_html__( 'Visitor message', 'ai-site-assistant' ) . '</span><p class="twa-contrast__warn"' . ( $ratio >= 4.5 ? ' hidden' : '' ) . '>' . esc_html__( 'Text on this colour is hard to read. Pick a darker or lighter shade.', 'ai-site-assistant' ) . '</p></div></div></div>';
		echo '<label class="twa-field"><span>' . esc_html__( 'Assistant name', 'ai-site-assistant' ) . '</span><input type="text" name="aisa[bot_name]" value="' . esc_attr( (string) $s['bot_name'] ) . '"></label>';
		echo '<label class="twa-field"><span>' . esc_html__( 'Welcome message', 'ai-site-assistant' ) . '</span><textarea name="aisa[welcome_message]" rows="3">' . esc_textarea( (string) $s['welcome_message'] ) . '</textarea></label>';
		self::nav( 3, true, __( 'Save and continue', 'ai-site-assistant' ) );
		echo '</form>';
	}

	/**
	 * Step 4: the optional badge.
	 *
	 * @return void
	 */
	private static function step_4() {
		$s = Aisa_Settings::all();
		self::heading( 'star', __( 'One quick question', 'ai-site-assistant' ), __( 'Optional and off unless you turn it on.', 'ai-site-assistant' ) );
		self::form_open( 4 );
		echo '<input type="hidden" name="aisa_bools[]" value="show_badge">';
		echo '<div class="twa-choice"><label class="twa-toggle"><input type="checkbox" role="switch" name="aisa[show_badge]" value="1"' . checked( ! empty( $s['show_badge'] ), true, false ) . '> <span class="twa-toggle__text"><span class="twa-toggle__label">' . esc_html__( 'Show a small "Powered by {brand}" line in the chat', 'ai-site-assistant' ) . '</span><span class="twa-toggle__help">' . esc_html__( 'A small line under the chat. You can switch it off any time.', 'ai-site-assistant' ) . '</span></span></label><div class="twa-badge-demo"><span class="twc"><a class="twc-badge" href="#" tabindex="-1"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 4.5h16A5.5 5.5 0 0 1 29.5 10v10A5.5 5.5 0 0 1 24 25.5H13.5L7.5 30v-4.6A5.5 5.5 0 0 1 2.5 20V10A5.5 5.5 0 0 1 8 4.5Z"/></svg><span>' . esc_html__( 'Powered by {brand}', 'ai-site-assistant' ) . '</span></a></span></div></div>';
		self::nav( 4, true, __( 'Save and continue', 'ai-site-assistant' ) );
		echo '</form>';
	}

	/**
	 * Step 5: go live.
	 *
	 * @return void
	 */
	private static function step_5() {
		$s = Aisa_Settings::all();
		self::heading( 'rocket', __( 'Go live', 'ai-site-assistant' ), __( 'Turn the chat on and open your site to try it. Leads and conversations appear in the {brand} menu.', 'ai-site-assistant' ) );
		self::form_open( 5 );
		echo '<input type="hidden" name="aisa_bools[]" value="enabled"><div class="twa-choice"><label class="twa-toggle"><input type="checkbox" role="switch" name="aisa[enabled]" value="1"' . checked( ! empty( $s['enabled'] ), true, false ) . '> <span class="twa-toggle__text"><span class="twa-toggle__label">' . esc_html__( 'Show the chat on my site', 'ai-site-assistant' ) . '</span></span></label></div>';
		self::nav( 5, true, __( 'Finish', 'ai-site-assistant' ) );
		echo '</form>';
	}

	/**
	 * Done screen.
	 *
	 * @return void
	 */
	private static function step_6() {
		echo wp_kses( '<div class="twa-done"><span class="twa-done__mark">' . Aisa_Admin::icon( 'check' ) . '</span><h2>' . esc_html__( 'Your chat is live', 'ai-site-assistant' ) . '</h2><p class="twa-intro">' . esc_html__( 'Open your site and ask a question your pages answer. New leads and conversations show up in {brand}.', 'ai-site-assistant' ) . '</p><div class="twa-actions twa-actions--center"><a class="twa-btn twa-btn--red twa-btn--lg" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open my site', 'ai-site-assistant' ) . '</a><a class="twa-btn twa-btn--light twa-btn--lg" href="' . esc_url( admin_url( 'admin.php?page=aisa' ) ) . '">' . esc_html__( 'Go to the dashboard', 'ai-site-assistant' ) . '</a></div>', Aisa_Kses::html() );
		echo '</div>';
	}

	/**
	 * Save a step.
	 *
	 * @return void
	 */
	public static function save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'ai-site-assistant' ) );
		}
		check_admin_referer( 'aisa_wizard' );
		$step  = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;
		$input = isset( $_POST['aisa'] ) && is_array( $_POST['aisa'] ) ? wp_unslash( $_POST['aisa'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
		$bools = isset( $_POST['aisa_bools'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['aisa_bools'] ) ) : array();
		Aisa_Settings::update( Aisa_Settings::sanitize( $input, $bools ) );
		if ( $step >= 5 ) {
			update_option( 'aisa_onboarding_done', time(), false );
		}
		wp_safe_redirect( self::url( $step + 1 ) );
		exit;
	}
}
