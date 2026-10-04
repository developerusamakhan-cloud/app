<?php
/**
 * Contact form and the small shortcodes used by the company and legal pages.
 *
 * Messages are saved in WordPress (Messages screen) and emailed to the contact
 * address, so nothing is lost if email delivery fails. Messages older than
 * 12 months are deleted automatically, as the privacy policy promises.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Message post type (private, admin only).
 */
function powerbachat_register_messages() {
	register_post_type(
		'pb_message',
		array(
			'labels'          => array(
				'name'          => __( 'Messages', 'powerbachat' ),
				'singular_name' => __( 'Message', 'powerbachat' ),
				'menu_name'     => __( 'Messages', 'powerbachat' ),
				'not_found'     => __( 'No messages yet.', 'powerbachat' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-format-chat',
			'menu_position'   => 27,
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'powerbachat_register_messages' );

/**
 * Where contact messages go.
 *
 * @return string
 */
function powerbachat_contact_address() {
	$email = powerbachat_mod( 'pb_contact_email' );
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

/**
 * Topics offered on the form.
 *
 * @return string[]
 */
function powerbachat_contact_topics() {
	return array(
		'rate'     => __( 'A rate has changed', 'powerbachat' ),
		'wrong'    => __( 'A result does not match my bill', 'powerbachat' ),
		'solar'    => __( 'Solar or backup question', 'powerbachat' ),
		'feedback' => __( 'Feedback on the site', 'powerbachat' ),
		'other'    => __( 'Something else', 'powerbachat' ),
	);
}

/**
 * REST route.
 */
function powerbachat_contact_route() {
	register_rest_route(
		'powerbachat/v1',
		'/contact',
		array(
			'methods'             => 'POST',
			'callback'            => 'powerbachat_rest_contact',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'powerbachat_contact_route' );

/**
 * Handle a message.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function powerbachat_rest_contact( $request ) {
	$fail = function ( $message, $code = 400 ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => $message,
			),
			$code
		);
	};
	if ( '' !== trim( (string) $request['website'] ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}
	$name    = sanitize_text_field( (string) $request['name'] );
	$email   = sanitize_email( (string) $request['email'] );
	$topic   = sanitize_key( (string) $request['topic'] );
	$message = sanitize_textarea_field( (string) $request['message'] );
	$country = sanitize_key( (string) $request['country'] );
	$topics  = powerbachat_contact_topics();

	$fields = array();
	if ( ! $name ) {
		$fields['name'] = __( 'Please tell us your name.', 'powerbachat' );
	}
	if ( ! is_email( $email ) ) {
		$fields['email'] = __( 'Please enter a valid email address so we can reply.', 'powerbachat' );
	}
	if ( mb_strlen( $message ) < 10 ) {
		$fields['message'] = __( 'Please write a little more (at least 10 characters).', 'powerbachat' );
	} elseif ( mb_strlen( $message ) > 5000 ) {
		$fields['message'] = __( 'Please keep your message under 5,000 characters.', 'powerbachat' );
	}
	if ( $fields ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => __( 'Please check the highlighted fields.', 'powerbachat' ),
				'fields'  => $fields,
			),
			400
		);
	}
	if ( ! powerbachat_rate_ok( 'contact', 5 ) ) {
		return $fail( __( 'Too many messages. Please try again in an hour.', 'powerbachat' ), 429 );
	}
	$topic_label = isset( $topics[ $topic ] ) ? $topics[ $topic ] : $topics['other'];

	$id = wp_insert_post(
		array(
			'post_type'    => 'pb_message',
			'post_status'  => 'private',
			'post_title'   => $topic_label . ': ' . $name,
			'post_content' => $message,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return $fail( __( 'Something went wrong. Please try again.', 'powerbachat' ), 500 );
	}
	update_post_meta( $id, 'pb_name', $name );
	update_post_meta( $id, 'pb_email', $email );
	update_post_meta( $id, 'pb_country', in_array( $country, POWERBACHAT_COUNTRIES, true ) ? $country : '' );

	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	wp_mail(
		powerbachat_contact_address(),
		sprintf( '[%s] %s', $site, $topic_label ),
		sprintf( "%s <%s>\n%s: %s\n\n%s\n\n%s", $name, $email, __( 'Country', 'powerbachat' ), strtoupper( $country ), $message, admin_url( 'post.php?post=' . $id . '&action=edit' ) ),
		array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'message' => __( 'Thank you. Your message has reached us and we usually reply within two working days.', 'powerbachat' ),
		),
		200
	);
}

/**
 * Show the sender on the message edit screen.
 *
 * @param WP_Post $post Post.
 */
function powerbachat_message_sender_box( $post ) {
	if ( 'pb_message' !== $post->post_type ) {
		return;
	}
	$email = get_post_meta( $post->ID, 'pb_email', true );
	printf(
		'<p style="font-size:14px"><strong>%s</strong> &lt;<a href="mailto:%s">%s</a>&gt; &middot; %s</p>',
		esc_html( get_post_meta( $post->ID, 'pb_name', true ) ),
		esc_attr( $email ),
		esc_html( $email ),
		esc_html( strtoupper( get_post_meta( $post->ID, 'pb_country', true ) ) )
	);
}
add_action( 'edit_form_after_title', 'powerbachat_message_sender_box' );

/**
 * Delete messages older than 12 months.
 */
function powerbachat_messages_cleanup() {
	$old = get_posts(
		array(
			'post_type'      => 'pb_message',
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'date_query'     => array( array( 'before' => '12 months ago' ) ),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'powerbachat_daily', 'powerbachat_messages_cleanup' );

/**
 * [powerbachat_contact]: the contact form.
 *
 * @return string
 */
function powerbachat_contact_shortcode() {
	ob_start();
	?>
	<?php
	$sent  = isset( $_GET['pb_sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$error = isset( $_GET['pb_error'] ) ? sanitize_text_field( wp_unslash( $_GET['pb_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="contact-done" data-contact-done <?php echo $sent ? '' : 'hidden'; ?>>
		<span class="contact-done__icon" aria-hidden="true"><?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<p class="contact-done__title"><?php esc_html_e( 'Message sent. Thank you!', 'powerbachat' ); ?></p>
		<p><?php esc_html_e( 'It has reached us and we usually reply within two working days. Keep an eye on your inbox, and your spam folder just in case.', 'powerbachat' ); ?></p>
		<button type="button" class="btn btn--ghost btn--sm" data-contact-again><?php esc_html_e( 'Send another message', 'powerbachat' ); ?></button>
	</div>
	<form class="contact-form" id="contact-form" data-pb-form="contact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate <?php echo $sent ? 'hidden' : ''; ?>>
		<input type="hidden" name="action" value="pb_contact">
		<div class="contact-form__row">
			<p>
				<label for="pb-name"><?php esc_html_e( 'Your name', 'powerbachat' ); ?></label>
				<input id="pb-name" name="name" type="text" autocomplete="name" required maxlength="80" aria-describedby="pb-name-error">
				<span class="field-error" id="pb-name-error" data-error-for="name" hidden></span>
			</p>
			<p>
				<label for="pb-email"><?php esc_html_e( 'Email address', 'powerbachat' ); ?></label>
				<input id="pb-email" name="email" type="email" autocomplete="email" required aria-describedby="pb-email-error">
				<span class="field-error" id="pb-email-error" data-error-for="email" hidden></span>
			</p>
		</div>
		<p>
			<label for="pb-topic"><?php esc_html_e( 'What is it about?', 'powerbachat' ); ?></label>
			<select id="pb-topic" name="topic">
				<?php foreach ( powerbachat_contact_topics() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="pb-message"><?php esc_html_e( 'Message', 'powerbachat' ); ?></label>
			<textarea id="pb-message" name="message" rows="6" required minlength="10" maxlength="5000" aria-describedby="pb-message-error"></textarea>
			<span class="field-error" id="pb-message-error" data-error-for="message" hidden></span>
		</p>
		<p class="contact-form__trap" aria-hidden="true">
			<label for="pb-website"><?php esc_html_e( 'Leave this empty', 'powerbachat' ); ?></label>
			<input id="pb-website" name="website" type="text" tabindex="-1" autocomplete="off">
		</p>
		<p class="contact-form__foot">
			<button class="btn btn--ink contact-form__submit" type="submit"><span class="contact-form__label"><?php esc_html_e( 'Send message', 'powerbachat' ); ?></span><span class="contact-form__sending"><?php esc_html_e( 'Sending', 'powerbachat' ); ?></span></button>
			<span class="contact-form__note">
				<?php
				printf(
					/* translators: %s: privacy policy link */
					esc_html__( 'We use your details only to reply. See our %s.', 'powerbachat' ),
					'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '">' . esc_html__( 'privacy policy', 'powerbachat' ) . '</a>'
				);
				?>
			</span>
		</p>
		<p class="form-status<?php echo $error ? ' is-error' : ''; ?>" data-form-status role="status" aria-live="polite" <?php echo $error ? '' : 'hidden'; ?>><?php echo esc_html( $error ); ?></p>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'powerbachat_contact', 'powerbachat_contact_shortcode' );

/**
 * [powerbachat_contact_email]: the public contact address, hidden from spam bots.
 *
 * @return string
 */
function powerbachat_contact_email_shortcode() {
	$email = powerbachat_contact_address();
	return '<a href="mailto:' . esc_attr( antispambot( $email, 1 ) ) . '">' . esc_html( antispambot( $email ) ) . '</a>';
}
add_shortcode( 'powerbachat_contact_email', 'powerbachat_contact_email_shortcode' );

/**
 * [powerbachat_updated]: the date the current page was last changed.
 *
 * @return string
 */
function powerbachat_updated_shortcode() {
	return esc_html( get_the_modified_date( 'j F Y' ) );
}
add_shortcode( 'powerbachat_updated', 'powerbachat_updated_shortcode' );

/**
 * [powerbachat_legal_place]: the country whose law governs the terms.
 *
 * @return string
 */
function powerbachat_legal_place_shortcode() {
	return esc_html( powerbachat_mod( 'pb_legal_country' ) );
}
add_shortcode( 'powerbachat_legal_place', 'powerbachat_legal_place_shortcode' );
