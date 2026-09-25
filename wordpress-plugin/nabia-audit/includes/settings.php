<?php
/**
 * Settings: brand, links and email options.
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a Nabia theme setting when the theme is active.
 *
 * @param string $key      Theme mod key.
 * @param string $fallback Fallback value.
 * @return string
 */
function nwa_theme_value( $key, $fallback = '' ) {
	if ( function_exists( 'nabia_mod' ) ) {
		$value = nabia_mod( $key );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return $value;
		}
	}
	return $fallback;
}

/**
 * Default settings.
 *
 * @return array
 */
function nwa_defaults() {
	$contact = home_url( '/contact/' );
	if ( function_exists( 'nabia_hire_url' ) ) {
		$contact = nabia_hire_url();
	}
	return array(
		'brand_name'    => nwa_theme_value( 'brand_name', 'Nabia Khan' ),
		'brand_role'    => 'WordPress developer & graphic designer',
		'color_primary' => '#7c3aed',
		'color_dark'    => '#1a1433',
		'color_accent'  => '#ec4899',
		'website'       => home_url( '/' ),
		'contact_url'   => $contact,
		'booking_url'   => '',
		'email'         => nwa_theme_value( 'contact_email', get_option( 'admin_email' ) ),
		'whatsapp'      => nwa_theme_value( 'contact_whatsapp', '+92 312 1305032' ),
		'fiverr'        => nwa_theme_value( 'social_fiverr', 'https://www.fiverr.com/nabia_khan' ),
		'upwork'        => nwa_theme_value( 'social_upwork', 'https://www.upwork.com/freelancers/~017b55e580768aed42' ),
		'cta_title'     => 'Want me to fix all of this for you?',
		'cta_text'      => 'Reply to the email or message me on WhatsApp. I will send you a fixed price quote for every fix in this report, usually within a few hours.',
		'notify_email'  => nwa_theme_value( 'contact_email', get_option( 'admin_email' ) ),
		'send_user'     => 1,
		'send_admin'    => 1,
		'form_title'    => 'Get your free website report',
		'button_label'  => 'Audit my website',
		'hourly_limit'  => 3,
		'psi_key'       => '',
	);
}

/**
 * Current settings merged with defaults.
 *
 * @return array
 */
function nwa_settings() {
	$saved = get_option( 'nwa_settings', array() );
	return wp_parse_args( is_array( $saved ) ? array_filter( $saved, 'nwa_not_empty' ) : array(), nwa_defaults() );
}

/**
 * Keep "0" for checkboxes, drop empty strings so defaults apply.
 *
 * @param mixed $value Value.
 * @return bool
 */
function nwa_not_empty( $value ) {
	return '' !== $value && null !== $value;
}

/**
 * One setting.
 *
 * @param string $key Key.
 * @return mixed
 */
function nwa_opt( $key ) {
	$s = nwa_settings();
	return isset( $s[ $key ] ) ? $s[ $key ] : '';
}

/**
 * WhatsApp link from the settings number.
 *
 * @return string
 */
function nwa_whatsapp_url() {
	$number = preg_replace( '/\D+/', '', (string) nwa_opt( 'whatsapp' ) );
	if ( ! $number ) {
		return '';
	}
	return 'https://wa.me/' . $number . '?text=' . rawurlencode( 'Hi ' . strtok( (string) nwa_opt( 'brand_name' ), ' ' ) . ', I just got my website audit report and would like help with the fixes.' );
}

/**
 * Settings screen.
 */
function nwa_settings_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$saved = false;
	if ( isset( $_POST['nwa_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nwa_settings_nonce'] ) ), 'nwa_settings' ) ) {
		$in   = isset( $_POST['nwa'] ) && is_array( $_POST['nwa'] ) ? wp_unslash( $_POST['nwa'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
		$data = array();
		foreach ( nwa_defaults() as $key => $default ) {
			if ( in_array( $key, array( 'send_user', 'send_admin' ), true ) ) {
				$data[ $key ] = empty( $in[ $key ] ) ? 0 : 1;
			} elseif ( 'hourly_limit' === $key ) {
				$data[ $key ] = isset( $in[ $key ] ) ? max( 1, absint( $in[ $key ] ) ) : $default;
			} elseif ( 0 === strpos( $key, 'color_' ) ) {
				$data[ $key ] = isset( $in[ $key ] ) ? (string) sanitize_hex_color( $in[ $key ] ) : '';
			} elseif ( in_array( $key, array( 'website', 'contact_url', 'booking_url', 'fiverr', 'upwork' ), true ) ) {
				$data[ $key ] = isset( $in[ $key ] ) ? esc_url_raw( $in[ $key ] ) : '';
			} elseif ( 'cta_text' === $key ) {
				$data[ $key ] = isset( $in[ $key ] ) ? sanitize_textarea_field( $in[ $key ] ) : '';
			} else {
				$data[ $key ] = isset( $in[ $key ] ) ? sanitize_text_field( $in[ $key ] ) : '';
			}
		}
		update_option( 'nwa_settings', $data, false );
		$saved = true;
	}
	$s   = nwa_settings();
	$row = function ( $key, $label, $type = 'text', $help = '' ) use ( $s ) {
		echo '<tr><th scope="row"><label for="nwa-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( 'textarea' === $type ) {
			echo '<textarea class="large-text" rows="3" id="nwa-' . esc_attr( $key ) . '" name="nwa[' . esc_attr( $key ) . ']">' . esc_textarea( $s[ $key ] ) . '</textarea>';
		} elseif ( 'checkbox' === $type ) {
			echo '<label><input type="checkbox" id="nwa-' . esc_attr( $key ) . '" name="nwa[' . esc_attr( $key ) . ']" value="1" ' . checked( 1, (int) $s[ $key ], false ) . '> ' . esc_html( $help ) . '</label>';
			$help = '';
		} else {
			echo '<input class="regular-text" type="' . esc_attr( $type ) . '" id="nwa-' . esc_attr( $key ) . '" name="nwa[' . esc_attr( $key ) . ']" value="' . esc_attr( $s[ $key ] ) . '">';
		}
		if ( $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</td></tr>';
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Website Audit settings', 'nabia-audit' ); ?></h1>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'nabia-audit' ); ?></p></div>
		<?php endif; ?>
		<?php
		$test = get_transient( 'nwa_psi_test_' . get_current_user_id() );
		if ( $test ) :
			delete_transient( 'nwa_psi_test_' . get_current_user_id() );
			?>
			<div class="notice notice-<?php echo 'ok' === $test['status'] ? 'success' : 'error'; ?>"><p><?php echo esc_html( $test['message'] ); ?></p></div>
		<?php endif; ?>
		<p>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nwa_test_psi' ), 'nwa_test_psi' ) ); ?>"><?php esc_html_e( 'Test the Google PageSpeed API key', 'nabia-audit' ); ?></a>
			<span class="description"><?php esc_html_e( 'Takes 10 to 40 seconds. Save your key first.', 'nabia-audit' ); ?></span>
			<br><span class="description">
				<?php
				/* translators: %d: seconds */
				echo esc_html( sprintf( __( 'Your server allows each audit about %d seconds.', 'nabia-audit' ), nwa_time_budget() ) );
				?>
			</span>
		</p>
		<p><?php esc_html_e( 'Show the audit form on any page with the shortcode', 'nabia-audit' ); ?> <code>[nabia_website_audit]</code>. <?php esc_html_e( 'With the Nabia theme it replaces the built in free audit form automatically.', 'nabia-audit' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'nwa_settings', 'nwa_settings_nonce' ); ?>
			<h2><?php esc_html_e( 'Brand', 'nabia-audit' ); ?></h2>
			<table class="form-table" role="presentation"><tbody>
				<?php
				$row( 'brand_name', __( 'Name on the report', 'nabia-audit' ) );
				$row( 'brand_role', __( 'Your role', 'nabia-audit' ) );
				$row( 'color_primary', __( 'Main colour', 'nabia-audit' ), 'color' );
				$row( 'color_dark', __( 'Dark colour', 'nabia-audit' ), 'color' );
				$row( 'color_accent', __( 'Accent colour', 'nabia-audit' ), 'color' );
				?>
			</tbody></table>
			<h2><?php esc_html_e( 'Links in the report', 'nabia-audit' ); ?></h2>
			<table class="form-table" role="presentation"><tbody>
				<?php
				$row( 'website', __( 'Website', 'nabia-audit' ), 'url' );
				$row( 'contact_url', __( 'Contact page', 'nabia-audit' ), 'url' );
				$row( 'booking_url', __( 'Booking link (optional)', 'nabia-audit' ), 'url', __( 'Calendly or similar. Shown as "Book a free call".', 'nabia-audit' ) );
				$row( 'email', __( 'Email', 'nabia-audit' ), 'email' );
				$row( 'whatsapp', __( 'WhatsApp number', 'nabia-audit' ) );
				$row( 'fiverr', __( 'Fiverr profile', 'nabia-audit' ), 'url' );
				$row( 'upwork', __( 'Upwork profile', 'nabia-audit' ), 'url' );
				$row( 'cta_title', __( 'Last page title', 'nabia-audit' ) );
				$row( 'cta_text', __( 'Last page text', 'nabia-audit' ), 'textarea' );
				?>
			</tbody></table>
			<h2><?php esc_html_e( 'Form and emails', 'nabia-audit' ); ?></h2>
			<table class="form-table" role="presentation"><tbody>
				<?php
				$row( 'form_title', __( 'Form title', 'nabia-audit' ) );
				$row( 'button_label', __( 'Button text', 'nabia-audit' ) );
				$row( 'send_user', __( 'Email the report', 'nabia-audit' ), 'checkbox', __( 'Send the PDF report to the visitor', 'nabia-audit' ) );
				$row( 'send_admin', __( 'Notify me', 'nabia-audit' ), 'checkbox', __( 'Email me every new audit with the PDF attached', 'nabia-audit' ) );
				$row( 'notify_email', __( 'Send notifications to', 'nabia-audit' ), 'email' );
				$row( 'hourly_limit', __( 'Reports per visitor per hour', 'nabia-audit' ), 'number', __( 'Only finished reports count, the minimum is 3. You (admins) are never limited.', 'nabia-audit' ) );
				$row( 'psi_key', __( 'Google PageSpeed API key (recommended)', 'nabia-audit' ), 'text', __( 'Used when a website blocks the direct scan. Free key: Google Cloud Console, enable "PageSpeed Insights API", create an API key. Without a key Google allows only a few requests.', 'nabia-audit' ) );
				?>
			</tbody></table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Test the PageSpeed key on this website and show the result.
 */
function nwa_test_psi() {
	check_admin_referer( 'nwa_test_psi' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'nabia-audit' ) );
	}
	nwa_time_left( nwa_time_budget() );
	if ( ! trim( (string) nwa_opt( 'psi_key' ) ) ) {
		$test = array(
			'status'  => 'error',
			'message' => __( 'No API key saved yet. Paste your key, save the settings and test again.', 'nabia-audit' ),
		);
	} else {
		$lh   = nwa_pagespeed( home_url( '/' ) );
		$test = is_wp_error( $lh )
			? array(
				'status'  => 'error',
				'message' => __( 'Google PageSpeed did not work:', 'nabia-audit' ) . ' ' . $lh->get_error_message() . ' ' . __( 'Check that the "PageSpeed Insights API" is enabled and that the key has no website (HTTP referrer) restriction, because the request comes from your server.', 'nabia-audit' ),
			)
			: array(
				'status'  => 'ok',
				/* translators: %d: score */
				'message' => sprintf( __( 'It works! Google scored your homepage %d/100 for mobile speed.', 'nabia-audit' ), isset( $lh['categories']['performance']['score'] ) ? (int) round( $lh['categories']['performance']['score'] * 100 ) : 0 ),
			);
	}
	set_transient( 'nwa_psi_test_' . get_current_user_id(), $test, 300 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=nabia_audit&page=nwa-settings' ) );
	exit;
}
add_action( 'admin_post_nwa_test_psi', 'nwa_test_psi' );
