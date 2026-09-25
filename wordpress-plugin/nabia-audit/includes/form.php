<?php
/**
 * Front end: [nabia_website_audit] form with math captcha, submit handler and results.
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Styles and script, only where the form is used.
 */
function nwa_register_assets() {
	wp_register_style( 'nabia-audit', NWA_URL . 'assets/audit.css', array(), NWA_VERSION . '.' . filemtime( NWA_DIR . 'assets/audit.css' ) );
	wp_register_script( 'nabia-audit', NWA_URL . 'assets/audit.js', array(), NWA_VERSION . '.' . filemtime( NWA_DIR . 'assets/audit.js' ), true );
	wp_localize_script(
		'nabia-audit',
		'nwaSettings',
		array(
			'keys' => esc_url_raw( rest_url( 'nabia-audit/v1/keys' ) ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nwa_register_assets' );

/**
 * Signed timestamp (forms sent in under 3 seconds are bots).
 *
 * @return string
 */
function nwa_time_token() {
	$time = time();
	return $time . '.' . wp_hash( 'nwa_time_' . $time );
}

/**
 * Math question; only a signed hash of the answer is sent with the form.
 *
 * @return array { a, b, key }
 */
function nwa_captcha() {
	$a    = wp_rand( 2, 9 );
	$b    = wp_rand( 1, 9 );
	$time = time();
	return array(
		'a'   => $a,
		'b'   => $b,
		'key' => $time . '.' . wp_hash( 'nwa_math_' . $time . '|' . ( $a + $b ) ),
	);
}

/**
 * Check the math answer (valid for one day).
 *
 * @param string $key    Signed key.
 * @param string $answer Answer.
 * @return bool
 */
function nwa_captcha_ok( $key, $answer ) {
	$parts  = explode( '.', (string) $key );
	$answer = trim( (string) $answer );
	if ( 2 !== count( $parts ) || ! preg_match( '/^\d{1,2}$/', $answer ) || time() - (int) $parts[0] > DAY_IN_SECONDS ) {
		return false;
	}
	return hash_equals( wp_hash( 'nwa_math_' . $parts[0] . '|' . (int) $answer ), $parts[1] );
}

/**
 * Fresh security fields for pages served from a cache (every check still runs).
 */
function nwa_rest_routes() {
	register_rest_route(
		'nabia-audit/v1',
		'/keys',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function () {
				$math     = nwa_captcha();
				$response = new WP_REST_Response(
					array(
						'nonce' => wp_create_nonce( 'nwa_audit' ),
						'token' => nwa_time_token(),
						'a'     => $math['a'],
						'b'     => $math['b'],
						'cq'    => $math['key'],
					)
				);
				$response->header( 'Cache-Control', 'no-store, max-age=0' );
				return $response;
			},
		)
	);
}
add_action( 'rest_api_init', 'nwa_rest_routes' );

/**
 * Handle the form (posted to the page it is on).
 */
function nwa_handle_submit() {
	if ( is_admin() || ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] || empty( $_POST['nwa_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	$back = wp_validate_redirect( (string) wp_get_raw_referer(), home_url( '/' ) );
	$back = remove_query_arg( array( 'nwa_error', 'nwa_report', 'key' ), $back );
	$fail = function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'nwa_error', $code, $back ) . '#nabia-audit' );
		exit;
	};

	if ( ! isset( $_POST['nwa_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nwa_nonce'] ) ), 'nwa_audit' ) ) {
		$fail( 'expired' );
	}
	if ( ! empty( $_POST['nwa_company'] ) ) {
		$fail( 'expired' ); // Honeypot.
	}
	$token = isset( $_POST['nwa_t'] ) ? sanitize_text_field( wp_unslash( $_POST['nwa_t'] ) ) : '';
	$parts = explode( '.', $token );
	if ( 2 !== count( $parts ) || ! hash_equals( wp_hash( 'nwa_time_' . $parts[0] ), $parts[1] ) || time() - (int) $parts[0] < 3 ) {
		$fail( 'expired' );
	}
	$url   = nwa_normalize_url( isset( $_POST['nwa_url'] ) ? sanitize_text_field( wp_unslash( $_POST['nwa_url'] ) ) : '' );
	$email = isset( $_POST['nwa_email'] ) ? sanitize_email( wp_unslash( $_POST['nwa_email'] ) ) : '';
	if ( ! $url || ! is_email( $email ) ) {
		$fail( 'invalid' );
	}
	if ( ! nwa_captcha_ok(
		isset( $_POST['nwa_cq'] ) ? sanitize_text_field( wp_unslash( $_POST['nwa_cq'] ) ) : '',
		isset( $_POST['nwa_math'] ) ? sanitize_text_field( wp_unslash( $_POST['nwa_math'] ) ) : ''
	) ) {
		$fail( 'captcha' );
	}
	// Only finished reports count towards the hourly limit, and admins are never limited.
	$ip_key    = 'nwa_ip_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$count     = (int) get_transient( $ip_key );
	$unlimited = current_user_can( 'manage_options' );
	if ( ! $unlimited && $count >= max( 3, (int) nwa_opt( 'hourly_limit' ) ) ) {
		$fail( 'limit' );
	}
	$host = preg_replace( '/^www\./', '', (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$id   = wp_insert_post(
		array(
			'post_type'   => 'nabia_audit',
			'post_status' => 'private',
			'post_title'  => $host,
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		$fail( 'server' );
	}
	update_post_meta( $id, '_nwa_url', $url );
	update_post_meta( $id, '_nwa_email', $email );
	update_post_meta( $id, '_nwa_source', $back );
	update_post_meta( $id, '_nwa_read', '0' );
	nwa_key( $id );

	$result = nwa_run_audit( $url );
	if ( empty( $result['ok'] ) ) {
		$result = nwa_base_report( $url, false );
	}
	update_post_meta( $id, '_nwa_result', wp_slash( wp_json_encode( $result ) ) );
	update_post_meta( $id, '_nwa_status', 'done' );
	update_post_meta( $id, '_nwa_mode', isset( $result['mode'] ) ? $result['mode'] : 'full' );
	update_post_meta( $id, '_nwa_overall', (int) $result['overall'] );
	foreach ( $result['categories'] as $key => $cat ) {
		update_post_meta( $id, '_nwa_score_' . $key, (int) $cat['score'] );
	}
	nwa_pdf_path( $id, true );
	nwa_send_emails( $id );
	if ( ! $unlimited ) {
		set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );
	}

	wp_safe_redirect( nwa_report_url( $id ) );
	exit;
}
add_action( 'wp_loaded', 'nwa_handle_submit' );

/**
 * SVG score ring for the page.
 *
 * @param int $score Score.
 * @param int $size  Pixel size.
 * @return string
 */
function nwa_ring_svg( $score, $size = 120 ) {
	$r    = 52;
	$circ = 2 * M_PI * $r;
	$off  = $circ * ( 1 - max( 0, min( 100, $score ) ) / 100 );
	return sprintf(
		'<svg class="nwa-ring is-%1$s" viewBox="0 0 120 120" width="%2$d" height="%2$d" aria-hidden="true"><circle class="nwa-ring-track" cx="60" cy="60" r="%3$d"/><circle class="nwa-ring-fill" cx="60" cy="60" r="%3$d" stroke-dasharray="%4$.2f" stroke-dashoffset="%5$.2f" style="--nwa-dash:%4$.2f"/></svg>',
		esc_attr( nwa_state( $score ) ),
		(int) $size,
		$r,
		$circ,
		$off
	);
}

/**
 * The [nabia_website_audit] shortcode.
 *
 * @return string
 */
function nwa_shortcode() {
	wp_enqueue_style( 'nabia-audit' );
	wp_enqueue_script( 'nabia-audit' );
	$s     = nwa_settings();
	$style = sprintf( '--nwa-main:%s;--nwa-dark:%s;--nwa-accent:%s', esc_attr( $s['color_primary'] ), esc_attr( $s['color_dark'] ), esc_attr( $s['color_accent'] ) );

	$report_id = isset( $_GET['nwa_report'] ) ? absint( $_GET['nwa_report'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$key       = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	ob_start();
	echo '<div class="nwa" id="nabia-audit" style="' . esc_attr( $style ) . '">';
	if ( $report_id && nwa_key_ok( $report_id, $key ) ) {
		nwa_render_results( $report_id );
	} else {
		nwa_render_form();
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'nabia_website_audit', 'nwa_shortcode' );

/**
 * The form.
 */
function nwa_render_form() {
	$s      = nwa_settings();
	$error  = isset( $_GET['nwa_error'] ) ? sanitize_key( wp_unslash( $_GET['nwa_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$site   = isset( $_GET['nwa_site'] ) ? sanitize_text_field( wp_unslash( $_GET['nwa_site'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$errors = array(
		'invalid'     => 'Please enter your website address and a valid email.',
		'captcha'     => 'That sum was not quite right. Please try the little math question again.',
		'expired'     => 'The form expired. Please try again.',
		'limit'       => 'You have received a few reports already. Please try again in an hour, or message me directly for a free review.',
		'server'      => 'Something went wrong on our side. Please try again.',
		'unreachable' => sprintf( 'We could not open %s. Please check the address (for example yourwebsite.com) and try again. If your site blocks bots, message me and I will audit it by hand.', $site ? $site : 'this website' ),
	);
	$math   = nwa_captcha();
	$uid    = wp_unique_id( 'nwa-' );
	?>
	<form class="nwa-form" method="post" action="<?php echo esc_url( remove_query_arg( array( 'nwa_error', 'nwa_site', 'nwa_report', 'key' ) ) ); ?>" data-nwa-form novalidate>
		<p class="nwa-form-title"><?php echo esc_html( $s['form_title'] ); ?></p>
		<?php if ( isset( $errors[ $error ] ) ) : ?>
			<p class="nwa-alert" role="alert"><?php echo esc_html( $errors[ $error ] ); ?></p>
		<?php endif; ?>
		<input type="hidden" name="nwa_action" value="audit">
		<input type="hidden" name="nwa_t" value="<?php echo esc_attr( nwa_time_token() ); ?>">
		<input type="hidden" name="nwa_cq" value="<?php echo esc_attr( $math['key'] ); ?>">
		<?php wp_nonce_field( 'nwa_audit', 'nwa_nonce' ); ?>
		<p class="nwa-hp" aria-hidden="true"><label>Company <input type="text" name="nwa_company" tabindex="-1" autocomplete="off"></label></p>

		<p class="nwa-field">
			<label for="<?php echo esc_attr( $uid ); ?>-url">Website URL</label>
			<input id="<?php echo esc_attr( $uid ); ?>-url" name="nwa_url" type="text" inputmode="url" autocomplete="url" placeholder="yourwebsite.com" required>
		</p>
		<p class="nwa-field">
			<label for="<?php echo esc_attr( $uid ); ?>-email">Email for the report</label>
			<input id="<?php echo esc_attr( $uid ); ?>-email" name="nwa_email" type="email" autocomplete="email" placeholder="you@company.com" required>
		</p>
		<div class="nwa-field nwa-math">
			<label for="<?php echo esc_attr( $uid ); ?>-math">Quick check, are you human?</label>
			<div class="nwa-math-row">
				<span class="nwa-math-q" aria-hidden="true"><?php echo (int) $math['a']; ?> <b>+</b> <?php echo (int) $math['b']; ?> <b>=</b></span>
				<input id="<?php echo esc_attr( $uid ); ?>-math" name="nwa_math" type="text" inputmode="numeric" pattern="[0-9]{1,2}" maxlength="2" autocomplete="off" placeholder="?" required aria-label="<?php echo esc_attr( sprintf( 'What is %d plus %d?', $math['a'], $math['b'] ) ); ?>">
			</div>
		</div>
		<button class="nwa-submit" type="submit"><span><?php echo esc_html( $s['button_label'] ); ?></span><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
		<p class="nwa-small">Free, instant and private. You get a PDF report by email.</p>

		<div class="nwa-loading" aria-live="polite" hidden>
			<div class="nwa-spinner" aria-hidden="true"></div>
			<p class="nwa-loading-title">Analysing your website...</p>
			<ul class="nwa-steps">
				<li>Opening your homepage</li>
				<li>Checking speed and security</li>
				<li>Reading your SEO tags</li>
				<li>Reviewing design and content</li>
				<li>Building your PDF report</li>
			</ul>
			<p class="nwa-small">This takes about 10 to 30 seconds.</p>
		</div>
	</form>
	<?php
}

/**
 * Results on the page.
 *
 * @param int $id Audit ID.
 */
function nwa_render_results( $id ) {
	$s      = nwa_settings();
	$result = nwa_result( $id );
	if ( empty( $result['ok'] ) ) {
		nwa_render_form();
		return;
	}
	$score  = (int) $result['overall'];
	$domain = preg_replace( '/^www\./', '', (string) wp_parse_url( $result['final_url'], PHP_URL_HOST ) );
	$email  = get_post_meta( $id, '_nwa_email', true );
	$mailed = 'sent' === get_post_meta( $id, '_nwa_mail_user', true );
	$wa     = nwa_whatsapp_url();
	$again  = remove_query_arg( array( 'nwa_report', 'key', 'nwa_error' ) ) . '#nabia-audit';
	?>
	<div class="nwa-results">
		<div class="nwa-hero">
			<div class="nwa-score">
				<?php echo nwa_ring_svg( $score ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="nwa-score-num"><strong><?php echo (int) $score; ?></strong><small>/100</small></span>
				<span class="nwa-grade"><?php echo esc_html( $result['grade'] ); ?></span>
			</div>
			<div class="nwa-hero-text">
				<p class="nwa-eyebrow">Your website score</p>
				<p class="nwa-domain"><?php echo esc_html( $domain ); ?></p>
				<p class="nwa-verdict"><?php echo esc_html( nwa_verdict( $score ) ); ?></p>
			</div>
		</div>

		<?php if ( isset( $result['mode'] ) && 'basic' === $result['mode'] ) : ?>
			<p class="nwa-note">Quick report: your website did not let our scanner in, so this is a first look. I will review it by hand and email you the full picture within 24 hours.</p>
		<?php endif; ?>

		<ul class="nwa-bars">
			<?php foreach ( nwa_categories() as $key => $cat ) : ?>
				<?php $sc = (int) $result['categories'][ $key ]['score']; ?>
				<li class="is-<?php echo esc_attr( nwa_state( $sc ) ); ?>">
					<span class="nwa-bar-label"><?php echo esc_html( $cat['title'] ); ?></span>
					<span class="nwa-bar-score"><?php echo (int) $sc; ?></span>
					<span class="nwa-bar"><i style="--w:<?php echo (int) $sc; ?>%"></i></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php $priorities = nwa_priorities( $result, 3 ); ?>
		<?php if ( $priorities ) : ?>
			<p class="nwa-subtitle">Your top <?php echo count( $priorities ); ?> fixes</p>
			<ol class="nwa-fixes">
				<?php foreach ( $priorities as $item ) : ?>
					<li class="is-<?php echo esc_attr( $item['status'] ); ?>"><strong><?php echo esc_html( $item['label'] ); ?></strong><span><?php echo esc_html( $item['fix'] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<div class="nwa-actions">
			<a class="nwa-btn nwa-btn-main" href="<?php echo esc_url( nwa_pdf_url( $id ) ); ?>">
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M12 4v11m0 0-4.5-4.5M12 15l4.5-4.5M5 19h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				<span>Download PDF report</span>
			</a>
			<?php if ( $wa ) : ?>
				<a class="nwa-btn nwa-btn-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer"><span>Get these fixed</span></a>
			<?php else : ?>
				<a class="nwa-btn nwa-btn-dark" href="<?php echo esc_url( $s['contact_url'] ); ?>"><span>Get these fixed</span></a>
			<?php endif; ?>
		</div>
		<p class="nwa-small">
			<?php if ( $mailed ) : ?>
				We also emailed the full report to <strong><?php echo esc_html( $email ); ?></strong>.
			<?php else : ?>
				Download your report above. Tip: save it, it includes a step by step action plan.
			<?php endif; ?>
			<a href="<?php echo esc_url( $again ); ?>">Audit another website</a>
		</p>
	</div>
	<?php
}
