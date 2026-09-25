<?php
/**
 * Branded emails: the report to the visitor and a notification to the site owner.
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Score colour for emails.
 *
 * @param int $score Score.
 * @return string
 */
function nwa_score_hex( $score ) {
	$map = array(
		'good' => '#16a34a',
		'ok'   => '#f59e0b',
		'bad'  => '#ef4444',
	);
	return $map[ nwa_state( $score ) ];
}

/**
 * HTML email with the scores and a download button.
 *
 * @param int    $id    Audit ID.
 * @param string $intro Intro paragraph.
 * @return string
 */
function nwa_email_html( $id, $intro ) {
	$s      = nwa_settings();
	$result = nwa_result( $id );
	$domain = preg_replace( '/^www\./', '', (string) wp_parse_url( $result['final_url'], PHP_URL_HOST ) );
	$dark   = esc_attr( $s['color_dark'] );
	$main   = esc_attr( $s['color_primary'] );
	$rows   = '';
	foreach ( nwa_categories() as $key => $cat ) {
		$score = (int) $result['categories'][ $key ]['score'];
		$rows .= '<tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:15px;color:#3b3552">' . esc_html( $cat['title'] ) . '</td><td style="padding:10px 0;border-bottom:1px solid #eee;text-align:right;font-size:17px;font-weight:800;color:' . esc_attr( nwa_score_hex( $score ) ) . '">' . $score . '</td></tr>';
	}
	$items = '';
	foreach ( nwa_priorities( $result, 3 ) as $n => $item ) {
		$items .= '<li style="margin:0 0 10px"><strong style="color:' . $dark . '">' . esc_html( $item['label'] ) . ':</strong> ' . esc_html( $item['fix'] ) . '</li>';
	}
	$wa    = nwa_whatsapp_url();
	$score = (int) $result['overall'];
	ob_start();
	?>
	<div style="margin:0;padding:24px 12px;background:#f1edfd;font-family:Arial,Helvetica,sans-serif">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:18px;overflow:hidden">
			<tr><td style="background:<?php echo $dark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;padding:28px 32px;color:#ffffff">
				<div style="font-size:18px;font-weight:800"><?php echo esc_html( $s['brand_name'] ); ?></div>
				<div style="font-size:13px;color:#c9b8ff"><?php echo esc_html( $s['brand_role'] ); ?></div>
				<div style="margin-top:22px;font-size:13px;letter-spacing:1px;color:#c9b8ff">WEBSITE AUDIT</div>
				<div style="font-size:26px;font-weight:800;margin-top:4px"><?php echo esc_html( $domain ); ?></div>
				<div style="margin-top:18px">
					<span style="display:inline-block;font-size:44px;font-weight:800;color:<?php echo esc_attr( nwa_score_hex( $score ) ); ?>"><?php echo (int) $score; ?></span>
					<span style="font-size:15px;color:#c9b8ff">/100 &nbsp;&middot;&nbsp; Grade <?php echo esc_html( $result['grade'] ); ?></span>
				</div>
			</td></tr>
			<tr><td style="padding:28px 32px;color:#3b3552;font-size:15px;line-height:1.6">
				<p style="margin:0 0 16px"><?php echo esc_html( $intro ); ?></p>
				<p style="margin:0 0 18px"><strong><?php echo esc_html( nwa_verdict( $score ) ); ?></strong></p>
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><?php echo $rows; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></table>
				<?php if ( $items ) : ?>
					<p style="margin:24px 0 10px;font-weight:800;color:<?php echo $dark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">Your top 3 fixes</p>
					<ol style="margin:0;padding-left:20px"><?php echo $items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></ol>
				<?php endif; ?>
				<p style="margin:26px 0 8px;text-align:center">
					<a href="<?php echo esc_url( nwa_pdf_url( $id ) ); ?>" style="display:inline-block;padding:14px 28px;border-radius:999px;background:<?php echo $main; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;color:#ffffff;font-weight:800;text-decoration:none">Download the full PDF report</a>
				</p>
				<p style="margin:0 0 4px;text-align:center;font-size:13px"><a href="<?php echo esc_url( nwa_report_url( $id ) ); ?>" style="color:<?php echo $main; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">View the report online</a></p>
			</td></tr>
			<tr><td style="padding:24px 32px;background:#fbfaff;border-top:1px solid #eee;text-align:center;color:#3b3552;font-size:15px">
				<p style="margin:0 0 14px;font-weight:800;color:<?php echo $dark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo esc_html( $s['cta_title'] ); ?></p>
				<?php if ( $wa ) : ?>
					<a href="<?php echo esc_url( $wa ); ?>" style="display:inline-block;margin:4px;padding:12px 22px;border-radius:999px;background:#1faa55;color:#ffffff;font-weight:700;text-decoration:none">WhatsApp me</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $s['contact_url'] ); ?>" style="display:inline-block;margin:4px;padding:12px 22px;border-radius:999px;background:<?php echo $dark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;color:#ffffff;font-weight:700;text-decoration:none">Get a quote</a>
				<p style="margin:16px 0 0;font-size:13px;color:#6b6780">
					<a href="<?php echo esc_url( $s['website'] ); ?>" style="color:#6b6780"><?php echo esc_html( wp_parse_url( $s['website'], PHP_URL_HOST ) ); ?></a>
					<?php if ( $s['fiverr'] ) : ?>&nbsp;&middot;&nbsp;<a href="<?php echo esc_url( $s['fiverr'] ); ?>" style="color:#6b6780">Fiverr</a><?php endif; ?>
					<?php if ( $s['upwork'] ) : ?>&nbsp;&middot;&nbsp;<a href="<?php echo esc_url( $s['upwork'] ); ?>" style="color:#6b6780">Upwork</a><?php endif; ?>
				</p>
			</td></tr>
		</table>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Send the report to the visitor and notify the owner.
 *
 * @param int $id Audit ID.
 * @return array { user: bool|null, admin: bool|null }
 */
function nwa_send_emails( $id ) {
	$s      = nwa_settings();
	$sent   = array(
		'user'  => null,
		'admin' => null,
	);
	$result = nwa_result( $id );
	if ( empty( $result['ok'] ) ) {
		return $sent;
	}
	$path   = nwa_pdf_path( $id );
	$files  = $path ? array( $path ) : array();
	$name   = get_post_meta( $id, '_nwa_name', true );
	$email  = get_post_meta( $id, '_nwa_email', true );
	$domain = preg_replace( '/^www\./', '', (string) wp_parse_url( $result['final_url'], PHP_URL_HOST ) );
	$html   = array( 'Content-Type: text/html; charset=UTF-8' );

	// Attachments with a friendly file name.
	$renamed = '';
	if ( $path ) {
		$renamed = trailingslashit( get_temp_dir() ) . nwa_pdf_name( $id );
		if ( @copy( $path, $renamed ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$files = array( $renamed );
		}
	}

	if ( ! empty( $s['send_user'] ) && is_email( $email ) ) {
		$intro        = sprintf( 'Hi there, thank you for requesting a free audit of %s. Your full report is attached as a PDF. Here is the summary:', $domain );
		if ( isset( $result['mode'] ) && 'basic' === $result['mode'] ) {
			$intro .= ' Your website did not let our scanner in, so this is a quick first look. I will review it by hand and email you the full picture within 24 hours.';
		}
		$headers      = $html;
		$headers[]    = 'Reply-To: ' . $s['brand_name'] . ' <' . $s['email'] . '>';
		$sent['user'] = wp_mail( $email, sprintf( 'Your website audit for %s: %d/100', $domain, $result['overall'] ), nwa_email_html( $id, $intro ), $headers, $files );
		update_post_meta( $id, '_nwa_mail_user', $sent['user'] ? 'sent' : 'failed' );
	}
	if ( ! empty( $s['send_admin'] ) && is_email( $s['notify_email'] ) ) {
		$intro         = sprintf( 'New free audit request from %s (%s) for %s. Open it in WordPress: %s', $name ? $name : 'a visitor', $email, $domain, admin_url( 'post.php?post=' . $id . '&action=edit' ) );
		$headers       = $html;
		$headers[]     = 'Reply-To: ' . ( $name ? $name : $email ) . ' <' . $email . '>';
		$manual        = isset( $result['mode'] ) && 'basic' === $result['mode'];
		if ( $manual ) {
			$intro .= ' IMPORTANT: the website could not be scanned automatically, so the visitor got a quick report and was promised a manual review within 24 hours.';
		}
		$sent['admin'] = wp_mail( $s['notify_email'], sprintf( 'New audit: %s scored %d/100%s', $domain, $result['overall'], $manual ? ' (manual review needed)' : '' ), nwa_email_html( $id, $intro ), $headers, $files );
	}
	if ( $renamed && file_exists( $renamed ) ) {
		wp_delete_file( $renamed );
	}
	return $sent;
}
