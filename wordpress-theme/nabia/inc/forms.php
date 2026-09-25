<?php
/**
 * Contact form with submissions stored in WordPress.
 *
 * Dashboard → Submissions lists every message (unread ones are counted in the menu).
 * Dashboard → Submissions → Form settings controls recipients, fields, messages and the
 * automatic reply. Free audit requests are listed in the same menu.
 *
 * Shortcode anywhere: [nabia_contact_form]
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default form settings.
 *
 * @return array
 */
function nabia_form_defaults() {
	return array(
		'recipients'        => '',
		'subject'           => 'New enquiry from your website',
		'form_title'        => 'Tell me about your project',
		'form_text'         => 'Share a few details and I will reply within 24 hours with ideas, a timeline and a clear quote.',
		'button_label'      => 'Send message',
		'success_message'   => 'Thank you! Your message is on its way. I will reply within 24 hours.',
		'show_phone'        => 1,
		'show_service'      => 1,
		'show_budget'       => 1,
		'budget_options'    => "Under $500\n$500 to $1,000\n$1,000 to $2,500\n$2,500+\nNot sure yet",
		'privacy_note'      => 'Your details are only used to reply to your message.',
		'autoreply'         => 1,
		'autoreply_subject' => 'Thanks for your message',
		'autoreply_message' => "Hi {name},\n\nThank you for getting in touch! I have received your message and will reply within 24 hours.\n\nBest wishes,\nNabia Khan",
	);
}

/**
 * Current form settings merged with defaults.
 *
 * @return array
 */
function nabia_form_settings() {
	$saved = get_option( 'nabia_form_settings', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), nabia_form_defaults() );
}

/**
 * Email addresses that receive new submissions.
 *
 * @return string[]
 */
function nabia_form_recipients() {
	$list = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', (string) nabia_form_settings()['recipients'] ) ) ), 'is_email' );
	if ( ! $list ) {
		$list = array( nabia_mod( 'contact_email' ) ? nabia_mod( 'contact_email' ) : get_option( 'admin_email' ) );
	}
	return $list;
}

/**
 * Register the Submissions post type (admin only) and move audit requests under it.
 */
function nabia_register_submissions() {
	register_post_type(
		'nabia_submission',
		array(
			'labels'          => array(
				'name'               => __( 'Submissions', 'nabia' ),
				'singular_name'      => __( 'Submission', 'nabia' ),
				'menu_name'          => __( 'Submissions', 'nabia' ),
				'all_items'          => __( 'Contact messages', 'nabia' ),
				'edit_item'          => __( 'Message', 'nabia' ),
				'search_items'       => __( 'Search messages', 'nabia' ),
				'not_found'          => __( 'No messages yet.', 'nabia' ),
				'not_found_in_trash' => __( 'No messages in Trash.', 'nabia' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 26,
			'menu_icon'       => 'dashicons-email-alt',
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'supports'        => array( 'title' ),
		)
	);
}
add_action( 'init', 'nabia_register_submissions', 5 );

/**
 * Show audit requests inside the Submissions menu.
 *
 * @param array  $args      Post type args.
 * @param string $post_type Post type.
 * @return array
 */
function nabia_audit_in_submissions( $args, $post_type ) {
	if ( 'audit_request' === $post_type ) {
		$args['show_in_menu']          = 'edit.php?post_type=nabia_submission';
		$args['labels']['all_items']   = __( 'Audit requests', 'nabia' );
		$args['labels']['menu_name']   = __( 'Audit requests', 'nabia' );
	}
	return $args;
}
add_filter( 'register_post_type_args', 'nabia_audit_in_submissions', 10, 2 );

/**
 * Unread count bubble on the Submissions menu + settings submenu.
 */
function nabia_submissions_menu() {
	global $menu;
	$unread = (int) ( new WP_Query(
		array(
			'post_type'      => 'nabia_submission',
			'post_status'    => 'private',
			'meta_key'       => '_nabia_read', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '0', // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	) )->found_posts;
	if ( $unread && is_array( $menu ) ) {
		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=nabia_submission' === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $unread . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	}

	add_submenu_page(
		'edit.php?post_type=nabia_submission',
		__( 'Form settings', 'nabia' ),
		__( 'Form settings', 'nabia' ),
		'manage_options',
		'nabia-form-settings',
		'nabia_form_settings_screen'
	);
}
add_action( 'admin_menu', 'nabia_submissions_menu' );

/**
 * Admin list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function nabia_submission_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'],
		'title'        => __( 'From', 'nabia' ),
		'nabia_email'  => __( 'Email', 'nabia' ),
		'nabia_topic'  => __( 'Service / budget', 'nabia' ),
		'nabia_status' => __( 'Status', 'nabia' ),
		'date'         => __( 'Received', 'nabia' ),
	);
}
add_filter( 'manage_nabia_submission_posts_columns', 'nabia_submission_columns' );

/**
 * Render list columns.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 */
function nabia_submission_column( $column, $post_id ) {
	switch ( $column ) {
		case 'nabia_email':
			$email = get_post_meta( $post_id, '_nabia_email', true );
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $email ) );
			break;
		case 'nabia_topic':
			echo esc_html( trim( get_post_meta( $post_id, '_nabia_service', true ) . ' / ' . get_post_meta( $post_id, '_nabia_budget', true ), ' /' ) );
			break;
		case 'nabia_status':
			echo '1' === get_post_meta( $post_id, '_nabia_read', true )
				? '<span style="color:#646970">' . esc_html__( 'Read', 'nabia' ) . '</span>'
				: '<strong style="color:#7c3aed">&#9679; ' . esc_html__( 'New', 'nabia' ) . '</strong>';
			break;
	}
}
add_action( 'manage_nabia_submission_posts_custom_column', 'nabia_submission_column', 10, 2 );

/**
 * Message details box on the edit screen; opening a message marks it as read.
 */
function nabia_submission_boxes() {
	remove_meta_box( 'submitdiv', 'nabia_submission', 'side' );
	add_meta_box( 'nabia_submission_details', __( 'Message', 'nabia' ), 'nabia_submission_details', 'nabia_submission', 'normal', 'high' );
}
add_action( 'add_meta_boxes_nabia_submission', 'nabia_submission_boxes' );

/**
 * Render the message details.
 *
 * @param WP_Post $post Submission.
 */
function nabia_submission_details( $post ) {
	update_post_meta( $post->ID, '_nabia_read', '1' );
	$rows = array(
		__( 'Name', 'nabia' )     => get_post_meta( $post->ID, '_nabia_name', true ),
		__( 'Email', 'nabia' )    => get_post_meta( $post->ID, '_nabia_email', true ),
		__( 'Phone', 'nabia' )    => get_post_meta( $post->ID, '_nabia_phone', true ),
		__( 'Service', 'nabia' )  => get_post_meta( $post->ID, '_nabia_service', true ),
		__( 'Budget', 'nabia' )   => get_post_meta( $post->ID, '_nabia_budget', true ),
		__( 'Sent from', 'nabia' ) => get_post_meta( $post->ID, '_nabia_source', true ),
		__( 'Received', 'nabia' ) => get_the_date( '', $post ) . ' ' . get_the_time( '', $post ),
	);
	echo '<table class="widefat striped" style="margin-bottom:16px"><tbody>';
	foreach ( $rows as $label => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}
		echo '<tr><th style="width:140px">' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<div style="padding:16px;background:#f6f7f7;border-radius:6px;white-space:pre-wrap;font-size:14px;line-height:1.6">' . esc_html( get_post_meta( $post->ID, '_nabia_message', true ) ) . '</div>';
	$email = get_post_meta( $post->ID, '_nabia_email', true );
	printf(
		'<p style="margin-top:16px"><a class="button button-primary" href="mailto:%1$s?subject=%2$s">%3$s</a> <a class="button" href="%4$s">%5$s</a></p>',
		esc_attr( $email ),
		rawurlencode( 'Re: ' . get_the_title( $post ) ),
		esc_html__( 'Reply by email', 'nabia' ),
		esc_url( get_delete_post_link( $post->ID ) ),
		esc_html__( 'Move to Trash', 'nabia' )
	);
}

/**
 * Settings screen.
 */
function nabia_form_settings_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$saved = false;
	if ( isset( $_POST['nabia_form_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nabia_form_settings_nonce'] ) ), 'nabia_form_settings' ) ) {
		$in   = isset( $_POST['nabia_form'] ) && is_array( $_POST['nabia_form'] ) ? wp_unslash( $_POST['nabia_form'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
		$data = array();
		foreach ( nabia_form_defaults() as $key => $default ) {
			if ( is_int( $default ) ) {
				$data[ $key ] = empty( $in[ $key ] ) ? 0 : 1;
			} elseif ( in_array( $key, array( 'budget_options', 'autoreply_message', 'form_text' ), true ) ) {
				$data[ $key ] = isset( $in[ $key ] ) ? sanitize_textarea_field( $in[ $key ] ) : '';
			} else {
				$data[ $key ] = isset( $in[ $key ] ) ? sanitize_text_field( $in[ $key ] ) : '';
			}
		}
		update_option( 'nabia_form_settings', $data, false );
		$saved = true;
	}
	$s = nabia_form_settings();

	$text = function ( $key, $label, $help = '' ) use ( $s ) {
		printf(
			'<tr><th scope="row"><label for="nf-%1$s">%2$s</label></th><td><input class="regular-text" id="nf-%1$s" name="nabia_form[%1$s]" value="%3$s">%4$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $s[ $key ] ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
		);
	};
	$area = function ( $key, $label, $help = '' ) use ( $s ) {
		printf(
			'<tr><th scope="row"><label for="nf-%1$s">%2$s</label></th><td><textarea class="large-text" rows="5" id="nf-%1$s" name="nabia_form[%1$s]">%3$s</textarea>%4$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_textarea( $s[ $key ] ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
		);
	};
	$check = function ( $key, $label ) use ( $s ) {
		printf(
			'<label style="display:block;margin-bottom:6px"><input type="checkbox" name="nabia_form[%1$s]" value="1" %2$s> %3$s</label>',
			esc_attr( $key ),
			checked( 1, (int) $s[ $key ], false ),
			esc_html( $label )
		);
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Form settings', 'nabia' ); ?></h1>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'nabia' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'The contact form appears on pages using the “Contact / Hire me” template. Add it anywhere else with the shortcode', 'nabia' ); ?> <code>[nabia_contact_form]</code>.</p>
		<form method="post">
			<?php wp_nonce_field( 'nabia_form_settings', 'nabia_form_settings_nonce' ); ?>

			<h2><?php esc_html_e( 'Notifications', 'nabia' ); ?></h2>
			<table class="form-table" role="presentation"><tbody>
				<?php
				/* translators: %s: default email */
				$text( 'recipients', __( 'Send new messages to', 'nabia' ), sprintf( __( 'One or more emails, separated by commas. Empty = %s', 'nabia' ), implode( ', ', nabia_form_recipients() ) ) );
				$text( 'subject', __( 'Email subject', 'nabia' ) );
				?>
			</tbody></table>

			<h2><?php esc_html_e( 'Form', 'nabia' ); ?></h2>
			<table class="form-table" role="presentation"><tbody>
				<?php
				$text( 'form_title', __( 'Title', 'nabia' ) );
				$area( 'form_text', __( 'Intro text', 'nabia' ) );
				?>
				<tr><th scope="row"><?php esc_html_e( 'Optional fields', 'nabia' ); ?></th><td>
					<?php
					$check( 'show_phone', __( 'Phone / WhatsApp', 'nabia' ) );
					$check( 'show_service', __( 'Service (list of your services)', 'nabia' ) );
					$check( 'show_budget', __( 'Budget', 'nabia' ) );
					?>
					<p class="description"><?php esc_html_e( 'Name, email and message are always included.', 'nabia' ); ?></p>
				</td></tr>
				<?php
				$area( 'budget_options', __( 'Budget options', 'nabia' ), __( 'One option per line.', 'nabia' ) );
				$text( 'button_label', __( 'Button text', 'nabia' ) );
				$text( 'success_message', __( 'Thank-you message', 'nabia' ) );
				$text( 'privacy_note', __( 'Privacy note under the button', 'nabia' ) );
				?>
			</tbody></table>

			<h2><?php esc_html_e( 'Automatic reply', 'nabia' ); ?></h2>
			<table class="form-table" role="presentation"><tbody>
				<tr><th scope="row"><?php esc_html_e( 'Enable', 'nabia' ); ?></th><td><?php $check( 'autoreply', __( 'Send a confirmation email to the person who wrote', 'nabia' ) ); ?></td></tr>
				<?php
				$text( 'autoreply_subject', __( 'Subject', 'nabia' ) );
				$area( 'autoreply_message', __( 'Message', 'nabia' ), __( 'Use {name} for their name.', 'nabia' ) );
				?>
			</tbody></table>

			<?php submit_button( __( 'Save settings', 'nabia' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Signed timestamp so very fast (bot) submissions can be rejected.
 *
 * @return string
 */
function nabia_form_token() {
	$time = time();
	return $time . '.' . wp_hash( 'nabia_form_' . $time );
}

/**
 * Handle a contact form submission.
 */
function nabia_handle_contact() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( 'contact', $back );
	$go   = function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'contact', $code, $back ) . '#contact-form' );
		exit;
	};

	if ( ! isset( $_POST['nabia_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nabia_contact_nonce'] ) ), 'nabia_contact' ) ) {
		$go( 'expired' );
	}
	if ( ! empty( $_POST['company_website'] ) ) {
		$go( 'sent' ); // Honeypot.
	}

	// Reject forms sent less than 3 seconds after loading (bots).
	$token = isset( $_POST['nabia_t'] ) ? sanitize_text_field( wp_unslash( $_POST['nabia_t'] ) ) : '';
	$parts = explode( '.', $token );
	if ( 2 !== count( $parts ) || ! hash_equals( wp_hash( 'nabia_form_' . $parts[0] ), $parts[1] ) || time() - (int) $parts[0] < 3 ) {
		$go( 'expired' );
	}

	$settings = nabia_form_settings();
	$name     = isset( $_POST['cf_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cf_name'] ) ) : '';
	$email    = isset( $_POST['cf_email'] ) ? sanitize_email( wp_unslash( $_POST['cf_email'] ) ) : '';
	$phone    = isset( $_POST['cf_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['cf_phone'] ) ) : '';
	$service  = isset( $_POST['cf_service'] ) ? sanitize_text_field( wp_unslash( $_POST['cf_service'] ) ) : '';
	$budget   = isset( $_POST['cf_budget'] ) ? sanitize_text_field( wp_unslash( $_POST['cf_budget'] ) ) : '';
	$message  = isset( $_POST['cf_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cf_message'] ) ) : '';
	$source   = isset( $_POST['cf_source'] ) ? esc_url_raw( wp_unslash( $_POST['cf_source'] ) ) : '';

	if ( '' === $name || ! is_email( $email ) || strlen( $message ) < 5 ) {
		$go( 'invalid' );
	}

	$ip_key = 'nabia_cf_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) {
		$go( 'limit' );
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	$id = wp_insert_post(
		array(
			'post_type'   => 'nabia_submission',
			'post_status' => 'private',
			/* translators: %s: sender name */
			'post_title'  => sprintf( __( 'Message from %s', 'nabia' ), $name ),
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		foreach ( compact( 'name', 'email', 'phone', 'service', 'budget', 'message', 'source' ) as $key => $value ) {
			update_post_meta( $id, '_nabia_' . $key, $value );
		}
		update_post_meta( $id, '_nabia_read', '0' );
	}

	$lines = array(
		__( 'Name', 'nabia' )    => $name,
		__( 'Email', 'nabia' )   => $email,
		__( 'Phone', 'nabia' )   => $phone,
		__( 'Service', 'nabia' ) => $service,
		__( 'Budget', 'nabia' )  => $budget,
		__( 'Page', 'nabia' )    => $source,
	);
	$body = '';
	foreach ( $lines as $label => $value ) {
		if ( '' !== $value ) {
			$body .= $label . ': ' . $value . "\n";
		}
	}
	$body .= "\n" . $message . "\n\n" . __( 'See all messages:', 'nabia' ) . ' ' . admin_url( 'edit.php?post_type=nabia_submission' );

	wp_mail( nabia_form_recipients(), $settings['subject'] . ': ' . $name, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );

	if ( ! empty( $settings['autoreply'] ) ) {
		wp_mail( $email, $settings['autoreply_subject'], str_replace( '{name}', $name, $settings['autoreply_message'] ) );
	}

	$go( 'sent' );
}
add_action( 'admin_post_nopriv_nabia_contact', 'nabia_handle_contact' );
add_action( 'admin_post_nabia_contact', 'nabia_handle_contact' );

/**
 * Shortcode: [nabia_contact_form]
 *
 * @return string
 */
function nabia_contact_form_shortcode() {
	ob_start();
	get_template_part( 'template-parts/contact', 'form' );
	return ob_get_clean();
}
add_shortcode( 'nabia_contact_form', 'nabia_contact_form_shortcode' );

/**
 * Tidy the Submissions list: no "Private" label and no Quick Edit for messages.
 *
 * @param array   $states Post states.
 * @param WP_Post $post   Post.
 * @return array
 */
function nabia_submission_states( $states, $post ) {
	if ( in_array( $post->post_type, array( 'nabia_submission', 'audit_request' ), true ) ) {
		unset( $states['private'] );
	}
	return $states;
}
add_filter( 'display_post_states', 'nabia_submission_states', 10, 2 );

/**
 * Remove Quick Edit from message rows.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function nabia_submission_row_actions( $actions, $post ) {
	if ( in_array( $post->post_type, array( 'nabia_submission', 'audit_request' ), true ) ) {
		unset( $actions['inline hide-if-no-js'] );
		if ( isset( $actions['edit'] ) ) {
			$actions['edit'] = str_replace( '>Edit<', '>' . esc_html__( 'Open', 'nabia' ) . '<', $actions['edit'] );
		}
	}
	return $actions;
}
add_filter( 'post_row_actions', 'nabia_submission_row_actions', 10, 2 );
