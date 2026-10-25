<?php
/**
 * Review links and the review reminder.
 *
 * @package Talkwyn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Asks for a WordPress.org review: a link that is always there (plugin list, Talkwyn header
 * and footer) and a reminder that comes back a few times when the owner picks "Maybe later".
 */
class Talkwyn_Review {

	const URL = 'https://wordpress.org/support/plugin/talkwyn/reviews/#new-post';

	/**
	 * Days until each reminder: the first after install, the rest after each "Maybe later".
	 * After the last one the reminder stops for good.
	 */
	const STEPS = array( 7, 15, 30, 60 );

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
		add_filter( 'admin_footer_text', array( __CLASS__, 'footer_text' ), 20 );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Whether Talkwyn may ask for reviews on this site. A white-labelled admin (Pro renames
	 * the menu) belongs to an agency's client, so it is never asked.
	 *
	 * @return bool
	 */
	public static function allowed() {
		$branded = 'Talkwyn' === (string) apply_filters( 'talkwyn_admin_menu_title', 'Talkwyn' );
		/**
		 * Filters whether Talkwyn shows review links and the review reminder.
		 *
		 * @param bool $allowed True unless the admin is white-labelled.
		 */
		return (bool) apply_filters( 'talkwyn_review_allowed', $branded );
	}

	/**
	 * "Rate Talkwyn" and support links under the plugin on the Plugins screen.
	 *
	 * @param array  $links Links.
	 * @param string $file  Plugin file.
	 * @return array
	 */
	public static function row_meta( $links, $file ) {
		if ( plugin_basename( TALKWYN_FILE ) !== $file || ! self::allowed() ) {
			return $links;
		}
		$links[] = '<a href="https://wordpress.org/support/plugin/talkwyn/" target="_blank" rel="noopener">' . esc_html__( 'Support', 'talkwyn' ) . '</a>';
		$links[] = '<a href="' . esc_url( self::URL ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__( 'Rate Talkwyn on WordPress.org', 'talkwyn' ) . '">' . esc_html__( 'Rate Talkwyn', 'talkwyn' ) . ' <span aria-hidden="true" style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span></a>';
		return $links;
	}

	/**
	 * Footer line on Talkwyn screens.
	 *
	 * @param string $text Footer text.
	 * @return string
	 */
	public static function footer_text( $text ) {
		if ( ! self::on_talkwyn_screen() || ! self::allowed() ) {
			return $text;
		}
		return sprintf(
			/* translators: %s: link to the WordPress.org review form */
			esc_html__( 'Enjoying Talkwyn? Please leave a %s review on WordPress.org. It helps other site owners find it.', 'talkwyn' ),
			'<a href="' . esc_url( self::URL ) . '" target="_blank" rel="noopener" style="color:#ffb900;text-decoration:none" aria-label="' . esc_attr__( 'five star', 'talkwyn' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
		);
	}

	/**
	 * Whether the current admin screen is a Talkwyn screen.
	 *
	 * @return bool
	 */
	private static function on_talkwyn_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && false !== strpos( (string) $screen->id, 'talkwyn' );
	}

	/**
	 * Time the next reminder is due, or 0 when reminders are over.
	 *
	 * @return int
	 */
	public static function due_at() {
		if ( get_option( 'talkwyn_review_done' ) ) {
			return 0;
		}
		$asks = (int) get_option( 'talkwyn_review_asks', 0 );
		if ( $asks >= count( self::STEPS ) ) {
			return 0;
		}
		$snooze = (int) get_option( 'talkwyn_review_snooze', 0 );
		if ( $snooze > 0 ) {
			return $snooze;
		}
		$since = (int) get_option( 'talkwyn_installed_at', 0 );
		if ( $since <= 0 ) {
			$since = time();
			update_option( 'talkwyn_installed_at', $since, false );
		}
		return $since + self::STEPS[0] * DAY_IN_SECONDS;
	}

	/**
	 * Whether the reminder should show now: it is due, the owner can manage options and the
	 * chat has had a few real conversations, so Talkwyn only asks once it has been useful.
	 *
	 * @return bool
	 */
	public static function due() {
		$at = self::due_at();
		if ( 0 === $at || time() < $at || ! current_user_can( 'manage_options' ) || ! self::allowed() ) {
			return false;
		}
		/**
		 * Filters how many conversations a site needs before Talkwyn asks for a review.
		 *
		 * @param int $min Default 3.
		 */
		$min = (int) apply_filters( 'talkwyn_review_min_chats', 3 );
		return self::chat_count() >= $min;
	}

	/**
	 * Number of conversations ever logged (cached for an hour).
	 *
	 * @return int
	 */
	private static function chat_count() {
		$count = get_transient( 'talkwyn_chat_count' );
		if ( false === $count ) {
			global $wpdb;
			$t     = Talkwyn_DB::tables();
			$count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT session_id) FROM %i', $t['logs'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			set_transient( 'talkwyn_chat_count', $count, HOUR_IN_SECONDS );
		}
		return (int) $count;
	}

	/**
	 * "Maybe later": wait for the next step, or stop after the last one.
	 *
	 * @return void
	 */
	public static function later() {
		$asks = (int) get_option( 'talkwyn_review_asks', 0 ) + 1;
		update_option( 'talkwyn_review_asks', $asks, false );
		if ( $asks < count( self::STEPS ) ) {
			update_option( 'talkwyn_review_snooze', time() + self::STEPS[ $asks ] * DAY_IN_SECONDS, false );
		}
	}

	/**
	 * Reviewed, or "Do not ask again".
	 *
	 * @return void
	 */
	public static function done() {
		update_option( 'talkwyn_review_done', time(), false );
	}

	/**
	 * Action links for the reminder.
	 *
	 * @return array{done:string,later:string}
	 */
	private static function links() {
		return array(
			'done'  => wp_nonce_url( admin_url( 'admin-post.php?action=talkwyn_dismiss&id=review_done' ), 'talkwyn_dismiss' ),
			'later' => wp_nonce_url( admin_url( 'admin-post.php?action=talkwyn_dismiss&id=review_later' ), 'talkwyn_dismiss' ),
		);
	}

	/**
	 * Reminder on the Dashboard and the Plugins screen. Talkwyn screens show their own box.
	 *
	 * @return void
	 */
	public static function notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) || ! self::due() ) {
			return;
		}
		$l = self::links();
		echo wp_kses(
			'<div class="notice notice-info is-dismissible talkwyn-review-notice" data-later="' . esc_url( $l['later'] ) . '">'
			. '<p><strong>' . esc_html__( 'Is Talkwyn helping your visitors?', 'talkwyn' ) . '</strong> '
			. esc_html__( 'A short review on WordPress.org helps other site owners find it, and it keeps Talkwyn free. It takes a minute.', 'talkwyn' ) . '</p>'
			. '<p><a class="button button-primary" href="' . esc_url( self::URL ) . '" target="_blank" rel="noopener" data-talkwyn-review="' . esc_url( $l['done'] ) . '">' . esc_html__( 'Write a review', 'talkwyn' ) . '</a> '
			. '<a class="button" href="' . esc_url( $l['later'] ) . '">' . esc_html__( 'Maybe later', 'talkwyn' ) . '</a> '
			. '<a class="button-link" href="' . esc_url( $l['done'] ) . '">' . esc_html__( 'I already did', 'talkwyn' ) . '</a></p>'
			. '</div>',
			Talkwyn_Kses::html()
		);
	}

	/**
	 * Reminder box at the top of Talkwyn screens.
	 *
	 * @param string $icon Star icon markup.
	 * @return string
	 */
	public static function box( $icon ) {
		if ( ! self::due() ) {
			return '';
		}
		$l = self::links();
		return '<aside class="twa-review"><span class="twa-chip-icon">' . $icon . '</span><div><strong>' . esc_html__( 'Is Talkwyn helping your visitors?', 'talkwyn' ) . '</strong><p>' . esc_html__( 'A short review on WordPress.org helps other site owners find it. It takes a minute.', 'talkwyn' ) . '</p></div>'
			. '<div class="twa-review__actions"><a class="twa-btn twa-btn--ink twa-btn--sm" href="' . esc_url( self::URL ) . '" target="_blank" rel="noopener" data-twa-review="done" data-twa-href="' . esc_url( $l['done'] ) . '">' . esc_html__( 'Write a review', 'talkwyn' ) . '</a>'
			. '<a class="twa-btn twa-btn--light twa-btn--sm" href="' . esc_url( $l['later'] ) . '">' . esc_html__( 'Maybe later', 'talkwyn' ) . '</a>'
			. '<a class="twa-link" href="' . esc_url( $l['done'] ) . '">' . esc_html__( 'I already did', 'talkwyn' ) . '</a></div></aside>';
	}

	/**
	 * Small script for the Dashboard and Plugins reminder (marks it done after the review link
	 * opens, and treats the close button as "Maybe later").
	 *
	 * @param string $hook Screen hook.
	 * @return void
	 */
	public static function assets( $hook ) {
		if ( ! in_array( $hook, array( 'index.php', 'plugins.php' ), true ) || ! self::due() ) {
			return;
		}
		wp_enqueue_script( 'talkwyn-review', TALKWYN_URL . 'assets/js/review.js', array(), TALKWYN_VERSION, true );
	}
}
