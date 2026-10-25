<?php
/**
 * Advanced admin tabs: extra knowledge, analytics, unanswered questions and advanced settings.
 *
 * @package AI_Site_Assistant
 */

namespace AisaPro;

defined( 'ABSPATH' ) || exit;

use Aisa_Admin as A;

/**
 * Admin.
 */
final class Admin {

	/**
	 * Hooks.
	 */
	public static function init(): void {
		add_filter( 'aisa_admin_tabs', array( self::class, 'tabs' ) );
		foreach ( array( 'extra', 'analytics', 'inbox', 'advanced' ) as $tab ) {
			add_action( 'aisa_admin_tab_' . $tab, array( self::class, 'tab_' . $tab ) );
		}
		add_action( 'admin_notices', array( self::class, 'notices' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'assets' ), 20 );
		add_action( 'aisa_admin_dashboard', array( self::class, 'dashboard' ) );
	}

	/**
	 * Tabs.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function tabs( array $tabs ): array {
		$privacy  = $tabs['privacy'] ?? null;
		$branding = $tabs['branding'] ?? null;
		unset( $tabs['privacy'], $tabs['branding'] );
		$tabs['extra']     = __( 'Extra knowledge', 'ai-site-assistant' );
		$tabs['analytics'] = __( 'Analytics', 'ai-site-assistant' );
		$tabs['inbox']     = __( 'Unanswered', 'ai-site-assistant' );
		$tabs['advanced']       = __( 'Advanced', 'ai-site-assistant' );
		if ( $privacy ) {
			$tabs['privacy'] = $privacy;
		}
		if ( $branding ) {
			$tabs['branding'] = $branding;
		}
		return $tabs;
	}

	/**
	 * Assets.
	 *
	 * @param string $hook Hook.
	 */
	public static function assets( $hook ): void {
		if ( false === strpos( (string) $hook, 'aisa' ) ) {
			return;
		}
		wp_enqueue_style( 'aisa-pro-admin', AISA_URL . 'assets/pro/css/pro-admin.css', array( 'aisa-admin' ), AISA_VERSION );
		wp_enqueue_script( 'aisa-pro-admin', AISA_URL . 'assets/pro/js/pro-admin.js', array( 'aisa-admin' ), AISA_VERSION, true );
	}

	/**
	 * Result notices.
	 */
	public static function notices(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( (string) $screen->id, 'aisa' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$code = isset( $_GET['aisa_notice'] ) ? sanitize_key( wp_unslash( $_GET['aisa_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' === $code ) {
			return;
		}
		$messages = array(
			'saved'     => array( 'success', __( 'Answer saved.', 'ai-site-assistant' ) ),
			'answered'  => array( 'success', __( 'Answer saved. The question is marked as done.', 'ai-site-assistant' ) ),
			'deleted'   => array( 'success', __( 'Deleted.', 'ai-site-assistant' ) ),
			'missing'   => array( 'error', __( 'Add both a question and an answer.', 'ai-site-assistant' ) ),
			'nofile'    => array( 'error', __( 'Choose a file first.', 'ai-site-assistant' ) ),
			'upload'    => array( 'error', __( 'Upload failed.', 'ai-site-assistant' ) ),
			'uploaded'  => array( 'success', __( 'File added to the knowledge.', 'ai-site-assistant' ) ),
			'badurl'    => array( 'error', __( 'Enter a full URL that starts with https://', 'ai-site-assistant' ) ),
			'crawled'   => array( 'success', __( 'Page added to the knowledge.', 'ai-site-assistant' ) ),
			'queued'    => array( 'success', __( 'The first pages were added. The rest are read in the background, 10 every minute.', 'ai-site-assistant' ) ),
			'refreshed' => array( 'success', __( 'Source read again.', 'ai-site-assistant' ) ),
			'imported'  => array( 'success', __( 'Settings imported.', 'ai-site-assistant' ) ),
			'badfile'   => array( 'error', __( 'That file is not a {brand} settings export.', 'ai-site-assistant' ) ),
		);
		if ( isset( $messages[ $code ] ) ) {
			$detail = isset( $_GET['detail'] ) ? sanitize_text_field( wp_unslash( $_GET['detail'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-' . esc_attr( $messages[ $code ][0] ) . ' is-dismissible"><p>' . esc_html( $messages[ $code ][1] . ( '' !== $detail ? ' ' . $detail : '' ) ) . '</p></div>';
		}
	}

	/**
	 * Dashboard card with the unanswered count.
	 */
	public static function dashboard(): void {
		$inbox = count( Insights::inbox( 100 ) );
		A::card( __( '{brand}', 'ai-site-assistant' ) );
		echo '<p>' . esc_html( sprintf( /* translators: %d: count */ _n( '%d question needs an answer.', '%d questions need an answer.', $inbox, 'ai-site-assistant' ), $inbox ) ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=aisa&tab=inbox' ) ) . '">' . esc_html__( 'Open the inbox', 'ai-site-assistant' ) . '</a> · <a href="' . esc_url( admin_url( 'admin.php?page=aisa&tab=analytics' ) ) . '">' . esc_html__( 'See analytics', 'ai-site-assistant' ) . '</a></p></section>';
	}

	/**
	 * Extra knowledge tab.
	 */
	public static function tab_extra(): void {
		$edit = null;
		if ( isset( $_GET['edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			foreach ( Knowledge::qa_all() as $row ) {
				if ( (int) $row['id'] === absint( $_GET['edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$edit = $row;
				}
			}
		}
		echo '<div class="twa-grid">';
		A::card( $edit ? __( 'Edit answer', 'ai-site-assistant' ) : __( 'Custom answers', 'ai-site-assistant' ), __( 'When a visitor asks something close to one of these questions, {brand} replies with your answer word for word.', 'ai-site-assistant' ) );
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aisa_pro_qa_save"><input type="hidden" name="id" value="' . esc_attr( $edit ? (string) $edit['id'] : '0' ) . '">';
		wp_nonce_field( 'aisa_pro_qa_save' );
		echo '<div class="twa-field"><label for="twp-q">' . esc_html__( 'Question', 'ai-site-assistant' ) . '</label><input id="twp-q" type="text" name="question" required value="' . esc_attr( $edit ? (string) $edit['question'] : '' ) . '"></div>';
		echo '<div class="twa-field"><label for="twp-a">' . esc_html__( 'Answer', 'ai-site-assistant' ) . '</label><textarea id="twp-a" name="answer" rows="5" required>' . esc_textarea( $edit ? (string) $edit['answer'] : '' ) . '</textarea><p class="description">' . esc_html__( 'Markdown works: **bold**, lists and [links](https://example.com).', 'ai-site-assistant' ) . '</p></div>';
		echo '<div class="twa-field"><label for="twp-k">' . esc_html__( 'Also match these words (optional)', 'ai-site-assistant' ) . '</label><input id="twp-k" type="text" name="keywords" value="' . esc_attr( $edit ? (string) $edit['keywords'] : '' ) . '" placeholder="refund, money back"></div>';
		echo '<p><button class="twa-btn twa-btn--ink">' . esc_html__( 'Save answer', 'ai-site-assistant' ) . '</button></p></form></section>';

		A::card( __( 'Add files and pages', 'ai-site-assistant' ), __( 'PDF, DOCX and TXT files, or any public page or sitemap. {brand} reads the text and adds it to the knowledge.', 'ai-site-assistant' ) );
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="twp-inline-form"><input type="hidden" name="action" value="aisa_pro_upload">';
		wp_nonce_field( 'aisa_pro_upload' );
		echo '<label for="twp-file" class="twp-label">' . esc_html__( 'File (PDF, DOCX or TXT)', 'ai-site-assistant' ) . '</label><input id="twp-file" type="file" name="file" accept=".pdf,.docx,.txt" required> <button class="twa-btn twa-btn--light twa-btn--sm">' . esc_html__( 'Upload', 'ai-site-assistant' ) . '</button></form>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="twp-inline-form"><input type="hidden" name="action" value="aisa_pro_crawl">';
		wp_nonce_field( 'aisa_pro_crawl' );
		echo '<label for="twp-url" class="twp-label">' . esc_html__( 'Page or sitemap URL', 'ai-site-assistant' ) . '</label><input id="twp-url" type="url" name="url" placeholder="https://example.com/sitemap.xml" required> <button class="twa-btn twa-btn--light twa-btn--sm">' . esc_html__( 'Read', 'ai-site-assistant' ) . '</button></form>';
		$queue = (array) get_option( Knowledge::CRAWL_QUEUE, array() );
		if ( $queue ) {
			echo '<p class="description">' . esc_html( sprintf( /* translators: %d: pages */ _n( '%d page waiting to be read.', '%d pages waiting to be read.', count( $queue ), 'ai-site-assistant' ), count( $queue ) ) ) . '</p>';
		}
		echo '</section></div>';

		$qa = Knowledge::qa_all();
		A::card( __( 'Your answers', 'ai-site-assistant' ) );
		if ( ! $qa ) {
			echo '<p>' . esc_html__( 'No custom answers yet.', 'ai-site-assistant' ) . '</p>';
		} else {
			echo '<div class="twa-table"><table class="widefat striped"><thead><tr><th>' . esc_html__( 'Question', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Answer', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Used', 'ai-site-assistant' ) . '</th><th></th></tr></thead><tbody>';
			foreach ( $qa as $row ) {
				$del = wp_nonce_url( admin_url( 'admin-post.php?action=aisa_pro_qa_delete&id=' . (int) $row['id'] ), 'aisa_pro_qa_delete' );
				echo '<tr><td>' . esc_html( $row['question'] ) . '</td><td>' . esc_html( wp_trim_words( (string) $row['answer'], 20 ) ) . '</td><td>' . esc_html( number_format_i18n( (int) $row['hits'] ) ) . '</td><td class="twp-actions"><a href="' . esc_url( admin_url( 'admin.php?page=aisa&tab=extra&edit=' . (int) $row['id'] ) ) . '">' . esc_html__( 'Edit', 'ai-site-assistant' ) . '</a> <a class="twa-danger" href="' . esc_url( $del ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this answer?', 'ai-site-assistant' ) ) . '\')">' . esc_html__( 'Delete', 'ai-site-assistant' ) . '</a></td></tr>';
			}
			echo '</tbody></table></div>';
		}
		echo '</section>';

		$sources = Knowledge::sources();
		A::card( __( 'Files and pages', 'ai-site-assistant' ) );
		if ( ! $sources ) {
			echo '<p>' . esc_html__( 'Nothing added yet.', 'ai-site-assistant' ) . '</p>';
		} else {
			echo '<div class="twa-table"><table class="widefat striped"><thead><tr><th>' . esc_html__( 'Source', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Type', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Chunks', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Status', 'ai-site-assistant' ) . '</th><th></th></tr></thead><tbody>';
			foreach ( $sources as $row ) {
				$del     = wp_nonce_url( admin_url( 'admin-post.php?action=aisa_pro_source_delete&id=' . (int) $row['id'] ), 'aisa_pro_source_delete' );
				$refresh = wp_nonce_url( admin_url( 'admin-post.php?action=aisa_pro_source_refresh&id=' . (int) $row['id'] ), 'aisa_pro_source_refresh' );
				$label   = 'url' === $row['type'] ? '<a href="' . esc_url( $row['ref'] ) . '" target="_blank" rel="noopener">' . esc_html( (string) $row['title'] ) . '</a>' : esc_html( (string) $row['title'] );
				echo '<tr><td>' . $label . '</td><td>' . esc_html( 'url' === $row['type'] ? __( 'Page', 'ai-site-assistant' ) : __( 'File', 'ai-site-assistant' ) ) . '</td><td>' . esc_html( number_format_i18n( (int) $row['chunks'] ) ) . '</td><td>' . esc_html( 'ok' === $row['status'] ? __( 'Ready', 'ai-site-assistant' ) : ( (string) $row['message'] ? (string) $row['message'] : __( 'No text', 'ai-site-assistant' ) ) ) . '</td><td class="twp-actions"><a href="' . esc_url( $refresh ) . '">' . esc_html__( 'Read again', 'ai-site-assistant' ) . '</a> <a class="twa-danger" href="' . esc_url( $del ) . '">' . esc_html__( 'Remove', 'ai-site-assistant' ) . '</a></td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label escaped above.
			}
			echo '</tbody></table></div>';
		}
		echo '</section>';
	}

	/**
	 * Analytics tab.
	 */
	public static function tab_analytics(): void {
		$d     = Insights::stats( 30 );
		$stats = array(
			array( __( 'Chats, 30 days', 'ai-site-assistant' ), number_format_i18n( $d['chats'] ) ),
			array( __( 'Leads, 30 days', 'ai-site-assistant' ), number_format_i18n( $d['leads'] ) ),
			array( __( 'Lead rate', 'ai-site-assistant' ), number_format_i18n( $d['lead_rate'] * 100, 1 ) . '%' ),
			array( __( 'Average reply time', 'ai-site-assistant' ), $d['avg_ms'] ? number_format_i18n( $d['avg_ms'] / 1000, 1 ) . ' s' : '0 s' ),
		);
		echo '<div class="twa-stats">';
		foreach ( $stats as $s ) {
			echo '<div class="twa-stat"><span>' . esc_html( $s[0] ) . '</span><strong>' . esc_html( $s[1] ) . '</strong></div>';
		}
		echo '</div>';

		A::card( __( 'Chats per day', 'ai-site-assistant' ), __( 'Conversations started in the last 30 days.', 'ai-site-assistant' ) );
		$max = max( 1, max( $d['days'] ) );
		echo '<div class="twp-bars" role="img" aria-label="' . esc_attr__( 'Chats per day', 'ai-site-assistant' ) . '">';
		foreach ( $d['days'] as $day => $count ) {
			echo '<span class="twp-bar" style="--h:' . esc_attr( (string) round( 100 * $count / $max ) ) . '%" title="' . esc_attr( wp_date( 'M j', strtotime( $day ) ) . ': ' . $count ) . '"><i></i></span>';
		}
		echo '</div></section><div class="twa-grid">';

		A::card( __( 'Top questions', 'ai-site-assistant' ) );
		self::list_rows( $d['top'], 'text', 'count', __( 'No questions yet.', 'ai-site-assistant' ) );
		echo '</section>';
		A::card( __( 'Pages where chats start', 'ai-site-assistant' ) );
		$pages = array_map(
			static function ( $row ) {
				$path = (string) wp_parse_url( (string) $row['url'], PHP_URL_PATH );
				return array(
					'text'  => '' !== $path ? $path : __( '(unknown)', 'ai-site-assistant' ),
					'count' => (int) $row['c'],
				);
			},
			$d['pages']
		);
		self::list_rows( $pages, 'text', 'count', __( 'No chats yet.', 'ai-site-assistant' ) );
		echo '</section>';

		A::card( __( 'AI providers', 'ai-site-assistant' ), sprintf( /* translators: %s: percent */ __( '%s of answers came from an AI provider; the rest used local fallback answers.', 'ai-site-assistant' ), number_format_i18n( $d['ai_rate'] * 100, 0 ) . '%' ) );
		if ( $d['providers'] ) {
			$reg = \Aisa_Providers::registry();
			echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Provider', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Answers', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Failures', 'ai-site-assistant' ) . '</th><th>' . esc_html__( 'Success rate', 'ai-site-assistant' ) . '</th></tr></thead><tbody>';
			foreach ( $d['providers'] as $id => $p ) {
				echo '<tr><td>' . esc_html( $reg[ $id ]['label'] ?? $id ) . '</td><td>' . esc_html( number_format_i18n( $p['served'] ) ) . '</td><td>' . esc_html( number_format_i18n( $p['failed'] ) ) . '</td><td>' . esc_html( number_format_i18n( $p['rate'] * 100, 0 ) ) . '%</td></tr>';
			}
			echo '</tbody></table>';
		} else {
			echo '<p>' . esc_html__( 'No AI answers yet.', 'ai-site-assistant' ) . '</p>';
		}
		echo '</section>';
		A::card( __( 'Feedback', 'ai-site-assistant' ) );
		echo '<p>' . esc_html( sprintf( /* translators: 1: helpful count, 2: not helpful count */ __( '%1$s helpful, %2$s not helpful.', 'ai-site-assistant' ), number_format_i18n( $d['helpful'] ), number_format_i18n( $d['unhelpful'] ) ) ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=aisa&tab=inbox' ) ) . '">' . esc_html__( 'Review unanswered questions', 'ai-site-assistant' ) . '</a></p></section></div>';
	}

	/**
	 * Ranked list.
	 *
	 * @param array  $rows  Rows.
	 * @param string $label Label key.
	 * @param string $count Count key.
	 * @param string $empty Empty text.
	 */
	private static function list_rows( array $rows, string $label, string $count, string $empty ): void {
		if ( ! $rows ) {
			echo '<p>' . esc_html( $empty ) . '</p>';
			return;
		}
		echo '<ol class="twp-rank">';
		foreach ( $rows as $row ) {
			echo '<li><span dir="auto">' . esc_html( (string) $row[ $label ] ) . '</span><b>' . esc_html( number_format_i18n( (int) $row[ $count ] ) ) . '</b></li>';
		}
		echo '</ol>';
	}

	/**
	 * Inbox tab.
	 */
	public static function tab_inbox(): void {
		$items = Insights::inbox( 100 );
		A::card( __( 'Unanswered questions', 'ai-site-assistant' ), __( 'Questions the assistant could not answer from your site, and answers visitors marked as not helpful. Add an answer once and {brand} uses it from then on.', 'ai-site-assistant' ) );
		if ( ! $items ) {
			echo '<p class="twp-empty">' . esc_html__( 'All caught up. New items appear here as visitors chat.', 'ai-site-assistant' ) . '</p></section>';
			return;
		}
		foreach ( $items as $item ) {
			$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=aisa_pro_dismiss&id=' . $item['id'] ), 'aisa_pro_dismiss_' . $item['id'] );
			echo '<article class="twp-inbox"><header><span class="twa-badge' . ( 'not_helpful' === $item['reason'] ? ' twp-badge--warn' : '' ) . '">' . esc_html( 'not_helpful' === $item['reason'] ? __( 'Not helpful', 'ai-site-assistant' ) : __( 'Not answered', 'ai-site-assistant' ) ) . '</span> <time>' . esc_html( get_date_from_gmt( $item['date'], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . '</time>';
			if ( $item['page'] ) {
				echo ' <a href="' . esc_url( $item['page'] ) . '" target="_blank" rel="noopener">' . esc_html( (string) wp_parse_url( $item['page'], PHP_URL_PATH ) ) . '</a>';
			}
			echo '</header><p class="twp-inbox__q" dir="auto"><strong>' . esc_html( $item['question'] ) . '</strong></p><p class="twp-inbox__a" dir="auto">' . esc_html( wp_trim_words( $item['reply'], 40 ) ) . '</p>';
			echo '<details><summary class="twa-btn twa-btn--ink twa-btn--sm">' . esc_html__( 'Add answer', 'ai-site-assistant' ) . '</summary><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aisa_pro_qa_save"><input type="hidden" name="resolve_log" value="' . esc_attr( (string) $item['id'] ) . '">';
			wp_nonce_field( 'aisa_pro_qa_save' );
			echo '<div class="twa-field"><label>' . esc_html__( 'Question', 'ai-site-assistant' ) . '<input type="text" name="question" value="' . esc_attr( $item['question'] ) . '" required></label></div><div class="twa-field"><label>' . esc_html__( 'Answer', 'ai-site-assistant' ) . '<textarea name="answer" rows="4" required></textarea></label></div><p><button class="twa-btn twa-btn--ink">' . esc_html__( 'Save answer', 'ai-site-assistant' ) . '</button></p></form></details> <a class="twa-btn twa-btn--light twa-btn--sm" href="' . esc_url( $dismiss ) . '">' . esc_html__( 'Dismiss', 'ai-site-assistant' ) . '</a></article>';
		}
		echo '</section>';
	}

	/**
	 * Advanced settings tab.
	 */
	public static function tab_advanced(): void {
		$s = \Aisa_Settings::all();
		A::form_open( 'advanced' );
		echo '<div class="twa-grid">';

		A::card( __( 'Smart search', 'ai-site-assistant' ), __( 'Finds answers by meaning, not only matching words. Uses the embedding API of a provider you already have a key for.', 'ai-site-assistant' ) );
		A::select(
			'pro_embed_provider',
			__( 'Embedding provider', 'ai-site-assistant' ),
			array(
				''        => __( 'Off (keyword search)', 'ai-site-assistant' ),
				'openai'  => 'OpenAI',
				'mistral' => 'Mistral',
				'gemini'  => 'Google Gemini',
			)
		);
		A::field( 'pro_embed_model', __( 'Embedding model (optional)', 'ai-site-assistant' ), 'text', __( 'Leave empty for the provider default.', 'ai-site-assistant' ) );
		A::field( 'pro_semantic_weight', __( 'Meaning vs keywords (0 to 100)', 'ai-site-assistant' ), 'number', __( 'Higher trusts meaning more. 60 works well for most sites.', 'ai-site-assistant' ), array( 'min' => 0, 'max' => 100 ) );
		$st = Search::stats();
		echo '<div class="twp-embed"><button type="button" class="twa-btn twa-btn--light twa-btn--sm" id="twp-embed">' . esc_html__( 'Build smart search now', 'ai-site-assistant' ) . '</button> <span id="twp-embed-status" aria-live="polite">' . esc_html( sprintf( /* translators: 1: vectors, 2: chunks */ __( '%1$s of %2$s chunks ready', 'ai-site-assistant' ), number_format_i18n( $st['vectors'] ), number_format_i18n( $st['chunks'] ) ) ) . '</span></div><p class="description">' . esc_html__( 'New and changed content is added in the background every 10 minutes.', 'ai-site-assistant' ) . '</p>';
		echo '</section>';

		A::card( __( 'Replies and WooCommerce', 'ai-site-assistant' ) );
		A::toggle( 'pro_stream', __( 'Stream replies word by word', 'ai-site-assistant' ), __( 'Falls back to normal replies when your host buffers responses.', 'ai-site-assistant' ) );
		A::toggle( 'pro_woo_cards', __( 'Show product cards with price and Add to cart', 'ai-site-assistant' ) );
		A::toggle( 'pro_woo_orders', __( 'Order status lookup (order number plus billing email)', 'ai-site-assistant' ) );
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<p class="description">' . esc_html__( 'WooCommerce is not active on this site.', 'ai-site-assistant' ) . '</p>';
		}
		echo '</section>';

		A::card( __( 'Lead alerts', 'ai-site-assistant' ), __( 'Email alerts are set under Answers and leads. Add Slack or Telegram for instant alerts.', 'ai-site-assistant' ) );
		A::field( 'pro_slack_webhook', __( 'Slack incoming webhook URL', 'ai-site-assistant' ), 'url', '', array( 'placeholder' => 'https://hooks.slack.com/services/...' ) );
		A::field( 'pro_telegram_token', __( 'Telegram bot token', 'ai-site-assistant' ), 'password', '', array( 'autocomplete' => 'off' ) );
		A::field( 'pro_telegram_chat', __( 'Telegram chat ID', 'ai-site-assistant' ) );
		echo '<p><button type="button" class="twa-btn twa-btn--light twa-btn--sm" id="twp-test-alert">' . esc_html__( 'Send a test alert', 'ai-site-assistant' ) . '</button> <span id="twp-alert-status" aria-live="polite"></span></p>';
		echo '</section>';

		A::card( __( 'Proactive messages', 'ai-site-assistant' ), __( 'Greet visitors with a short message near the chat button. Each message shows once per visit.', 'ai-site-assistant' ), 'twa-card--wide' );
		$rules = array_values( (array) $s['pro_proactive'] );
		echo '<div class="twp-rules"><div class="twp-rules__head"><span>' . esc_html__( 'Message', 'ai-site-assistant' ) . '</span><span>' . esc_html__( 'When', 'ai-site-assistant' ) . '</span><span>' . esc_html__( 'Value', 'ai-site-assistant' ) . '</span><span>' . esc_html__( 'Only on URLs containing', 'ai-site-assistant' ) . '</span><span>' . esc_html__( 'Open chat', 'ai-site-assistant' ) . '</span></div>';
		for ( $i = 0; $i < 5; $i++ ) {
			$r = $rules[ $i ] ?? array(
				'message' => '',
				'trigger' => 'time',
				'value'   => 15,
				'url'     => '',
				'open'    => 0,
			);
			$n = 'aisa[pro_proactive][' . $i . ']';
			echo '<div class="twp-rules__row"><input type="text" name="' . esc_attr( $n ) . '[message]" value="' . esc_attr( $r['message'] ) . '" aria-label="' . esc_attr__( 'Message', 'ai-site-assistant' ) . '" placeholder="' . esc_attr__( 'Questions about pricing? Ask me.', 'ai-site-assistant' ) . '">';
			echo '<select name="' . esc_attr( $n ) . '[trigger]" aria-label="' . esc_attr__( 'When', 'ai-site-assistant' ) . '">';
			foreach ( array(
				'time'   => __( 'After seconds on page', 'ai-site-assistant' ),
				'scroll' => __( 'After scrolling percent', 'ai-site-assistant' ),
				'exit'   => __( 'When leaving the page', 'ai-site-assistant' ),
			) as $k => $label ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $r['trigger'], $k, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select><input type="number" min="0" max="600" name="' . esc_attr( $n ) . '[value]" value="' . esc_attr( (string) $r['value'] ) . '" aria-label="' . esc_attr__( 'Value', 'ai-site-assistant' ) . '"><input type="text" name="' . esc_attr( $n ) . '[url]" value="' . esc_attr( $r['url'] ) . '" placeholder="/pricing/" aria-label="' . esc_attr__( 'URL contains', 'ai-site-assistant' ) . '"><label class="twp-center"><input type="checkbox" name="' . esc_attr( $n ) . '[open]" value="1"' . checked( ! empty( $r['open'] ), true, false ) . '><span class="screen-reader-text">' . esc_html__( 'Open chat', 'ai-site-assistant' ) . '</span></label></div>';
		}
		echo '<input type="hidden" name="aisa[pro_proactive][99][message]" value=""></div></section>';

		A::card( __( 'Business hours', 'ai-site-assistant' ), sprintf( /* translators: %s: time zone */ __( 'Outside these hours the chat shows as away. Times use the site time zone (%s).', 'ai-site-assistant' ), wp_timezone_string() ), 'twa-card--wide' );
		A::toggle( 'pro_hours_enabled', __( 'Use business hours', 'ai-site-assistant' ) );
		$days  = array(
			'mon' => __( 'Monday', 'ai-site-assistant' ),
			'tue' => __( 'Tuesday', 'ai-site-assistant' ),
			'wed' => __( 'Wednesday', 'ai-site-assistant' ),
			'thu' => __( 'Thursday', 'ai-site-assistant' ),
			'fri' => __( 'Friday', 'ai-site-assistant' ),
			'sat' => __( 'Saturday', 'ai-site-assistant' ),
			'sun' => __( 'Sunday', 'ai-site-assistant' ),
		);
		$hours = (array) $s['pro_hours'];
		echo '<div class="twp-hours">';
		foreach ( $days as $key => $label ) {
			$h = $hours[ $key ] ?? Settings::default_hours()[ $key ];
			$n = 'aisa[pro_hours][' . $key . ']';
			echo '<div class="twp-hours__row"><label><input type="checkbox" name="' . esc_attr( $n ) . '[open]" value="1"' . checked( ! empty( $h['open'] ), true, false ) . '> ' . esc_html( $label ) . '</label><input type="time" name="' . esc_attr( $n ) . '[from]" value="' . esc_attr( $h['from'] ) . '" aria-label="' . esc_attr( $label . ' ' . __( 'opens', 'ai-site-assistant' ) ) . '"><span>' . esc_html__( 'to', 'ai-site-assistant' ) . '</span><input type="time" name="' . esc_attr( $n ) . '[until]" value="' . esc_attr( $h['until'] ) . '" aria-label="' . esc_attr( $label . ' ' . __( 'closes', 'ai-site-assistant' ) ) . '"></div>';
		}
		echo '</div>';
		A::field( 'pro_away_label', __( 'Status text when away', 'ai-site-assistant' ) );
		A::toggle( 'pro_away_lead_only', __( 'When away, collect contact details instead of chatting', 'ai-site-assistant' ) );
		A::field( 'pro_away_message', __( 'Away reply', 'ai-site-assistant' ), 'textarea' );
		echo '</section></div>';
		A::form_close( __( 'Save advanced settings', 'ai-site-assistant' ) );

		A::card( __( 'Export and import settings', 'ai-site-assistant' ), __( 'Move a setup to another site. Exports include settings and custom answers. API keys are left out unless you tick the box.', 'ai-site-assistant' ) );
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="twp-inline-form"><input type="hidden" name="action" value="aisa_pro_export">';
		wp_nonce_field( 'aisa_pro_export' );
		echo '<label><input type="checkbox" name="with_secrets" value="1"> ' . esc_html__( 'Include API keys', 'ai-site-assistant' ) . '</label> <button class="twa-btn twa-btn--light twa-btn--sm">' . esc_html__( 'Download export', 'ai-site-assistant' ) . '</button></form>';
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="twp-inline-form"><input type="hidden" name="action" value="aisa_pro_import">';
		wp_nonce_field( 'aisa_pro_import' );
		echo '<input type="file" name="file" accept=".json,application/json" required aria-label="' . esc_attr__( 'Settings file', 'ai-site-assistant' ) . '"> <label><input type="checkbox" name="with_answers" value="1" checked> ' . esc_html__( 'Also import custom answers', 'ai-site-assistant' ) . '</label> <button class="twa-btn twa-btn--light twa-btn--sm">' . esc_html__( 'Import', 'ai-site-assistant' ) . '</button></form></section>';
	}
}
