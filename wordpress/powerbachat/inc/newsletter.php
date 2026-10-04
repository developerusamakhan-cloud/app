<?php
/**
 * Tariff alerts: a built-in newsletter.
 *
 * - Sign-ups are stored in WordPress (Alert subscribers screen), with the country
 *   the visitor was viewing.
 * - Double opt-in: a confirmation email is sent first (can be switched off).
 * - Every email has a one-click unsubscribe link and a List-Unsubscribe header.
 * - When a guide post goes live (including scheduled posts), subscribers in that
 *   country get one short email listing the new guides. Emails go out in small
 *   batches through WP-Cron so the server is never flooded.
 * - Admins can send their own alert (e.g. "new tariff notified") per country.
 * - Unconfirmed and unsubscribed addresses are deleted after 30 days.
 *
 * If a Mailchimp/Brevo form URL is set in the Customizer, the form posts there
 * instead and none of this runs for new sign-ups.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const POWERBACHAT_ALERT_BATCH = 40;

/**
 * Subscriber post type (private, admin only).
 */
function powerbachat_register_subscribers() {
	register_post_type(
		'pb_subscriber',
		array(
			'labels'          => array(
				'name'          => __( 'Alert subscribers', 'powerbachat' ),
				'singular_name' => __( 'Subscriber', 'powerbachat' ),
				'menu_name'     => __( 'Tariff alerts', 'powerbachat' ),
				'all_items'     => __( 'Subscribers', 'powerbachat' ),
				'search_items'  => __( 'Search subscribers', 'powerbachat' ),
				'not_found'     => __( 'No subscribers yet.', 'powerbachat' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 26,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'powerbachat_register_subscribers' );

/**
 * Find a subscriber by email.
 *
 * @param string $email Email.
 * @return WP_Post|null
 */
function powerbachat_find_subscriber( $email ) {
	$found = get_posts(
		array(
			'post_type'      => 'pb_subscriber',
			'post_status'    => 'private',
			'title'          => strtolower( $email ),
			'posts_per_page' => 1,
			'no_found_rows'  => true,
		)
	);
	return $found ? $found[0] : null;
}

/**
 * A simple per-IP rate limit (hashed, never stored raw).
 *
 * @param string $bucket Name.
 * @param int    $max    Allowed requests per hour.
 * @return bool True when allowed.
 */
function powerbachat_rate_ok( $bucket, $max ) {
	$ip  = function_exists( 'powerbachat_visitor_ip' ) ? powerbachat_visitor_ip() : '';
	$key = 'pb_rl_' . $bucket . '_' . substr( hash( 'sha256', $ip . wp_salt() ), 0, 20 );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return false;
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
	return true;
}

/**
 * REST routes.
 */
function powerbachat_alerts_routes() {
	register_rest_route(
		'powerbachat/v1',
		'/subscribe',
		array(
			'methods'             => 'POST',
			'callback'            => 'powerbachat_rest_subscribe',
			'permission_callback' => '__return_true',
			'args'                => array(
				'email'   => array(
					'type'     => 'string',
					'required' => true,
				),
				'country' => array(
					'type'    => 'string',
					'default' => '',
				),
				'website' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'powerbachat_alerts_routes' );

/**
 * Handle a sign-up.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function powerbachat_rest_subscribe( $request ) {
	$fail = function ( $message, $code = 400 ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => $message,
			),
			$code
		);
	};

	// Bots fill the hidden "website" field; pretend all is well.
	if ( '' !== trim( (string) $request['website'] ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}
	$email = sanitize_email( strtolower( trim( (string) $request['email'] ) ) );
	if ( ! $email || ! is_email( $email ) ) {
		return $fail( __( 'Please enter a valid email address.', 'powerbachat' ) );
	}
	if ( ! powerbachat_rate_ok( 'sub', 6 ) ) {
		return $fail( __( 'Too many attempts. Please try again in an hour.', 'powerbachat' ), 429 );
	}
	$country = sanitize_key( (string) $request['country'] );
	if ( ! in_array( $country, POWERBACHAT_COUNTRIES, true ) ) {
		$country = powerbachat_current_country();
	}

	$double = (bool) powerbachat_mod( 'pb_double_optin' );
	$sub    = powerbachat_find_subscriber( $email );
	if ( $sub && 'confirmed' === get_post_meta( $sub->ID, 'pb_status', true ) ) {
		update_post_meta( $sub->ID, 'pb_country', $country );
		return new WP_REST_Response(
			array(
				'ok'      => true,
				'message' => __( 'You are already on the list. We will be in touch when rates move.', 'powerbachat' ),
			),
			200
		);
	}
	if ( ! $sub ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'pb_subscriber',
				'post_status' => 'private',
				'post_title'  => $email,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $fail( __( 'Something went wrong. Please try again.', 'powerbachat' ), 500 );
		}
	} else {
		$id = $sub->ID;
	}
	update_post_meta( $id, 'pb_country', $country );
	update_post_meta( $id, 'pb_token', wp_generate_password( 32, false ) );
	update_post_meta( $id, 'pb_signed_up', time() );

	if ( $double ) {
		update_post_meta( $id, 'pb_status', 'pending' );
		powerbachat_send_confirmation( $id );
		$message = __( 'Almost done. Check your inbox and tap the link to confirm.', 'powerbachat' );
	} else {
		update_post_meta( $id, 'pb_status', 'confirmed' );
		update_post_meta( $id, 'pb_confirmed', time() );
		$message = __( 'Noted. You will hear from us when the next tariff lands.', 'powerbachat' );
	}
	return new WP_REST_Response(
		array(
			'ok'      => true,
			'message' => $message,
		),
		200
	);
}

/**
 * Signed link for confirm or unsubscribe.
 *
 * @param int    $id     Subscriber ID.
 * @param string $action confirm|unsubscribe.
 * @return string
 */
function powerbachat_alert_link( $id, $action ) {
	return add_query_arg(
		array(
			'pb_alert' => $action,
			'sid'      => $id,
			'token'    => get_post_meta( $id, 'pb_token', true ),
		),
		home_url( '/' )
	);
}

/**
 * Send an email to one subscriber, with unsubscribe link and header.
 *
 * @param int    $id      Subscriber ID.
 * @param string $subject Subject.
 * @param string $body    Plain text body (without the footer).
 * @param bool   $footer  Add the unsubscribe footer.
 * @return bool
 */
function powerbachat_mail_subscriber( $id, $subject, $body, $footer = true ) {
	$to    = get_post_field( 'post_title', $id );
	$unsub = powerbachat_alert_link( $id, 'unsubscribe' );
	$site  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	if ( $footer ) {
		$body .= "\n\n--\n" . sprintf(
			/* translators: 1: site name, 2: unsubscribe URL */
			__( "You are getting this because you joined %1\$s tariff alerts.\nUnsubscribe with one click: %2\$s", 'powerbachat' ),
			$site,
			$unsub
		);
	}
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'List-Unsubscribe: <' . $unsub . '>',
		'List-Unsubscribe-Post: List-Unsubscribe=One-Click',
	);
	return wp_mail( $to, $subject, $body, $headers );
}

/**
 * Confirmation email.
 *
 * @param int $id Subscriber ID.
 */
function powerbachat_send_confirmation( $id ) {
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	/* translators: %s: site name */
	$subject = sprintf( __( 'Confirm your %s tariff alerts', 'powerbachat' ), $site );
	$body    = sprintf(
		/* translators: 1: site name, 2: confirm URL */
		__( "Hello,\n\nPlease confirm that you want %1\$s to email you when electricity rates, fuel adjustments or solar prices change:\n\n%2\$s\n\nIf you did not sign up, ignore this email and you will not hear from us again.\n\nThe %1\$s team", 'powerbachat' ),
		$site,
		powerbachat_alert_link( $id, 'confirm' )
	);
	powerbachat_mail_subscriber( $id, $subject, $body, false );
}

/**
 * Handle confirm and unsubscribe links (and one-click POST unsubscribe).
 */
function powerbachat_alert_links() {
	if ( empty( $_GET['pb_alert'] ) || empty( $_GET['sid'] ) || empty( $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$action = sanitize_key( wp_unslash( $_GET['pb_alert'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$id     = absint( $_GET['sid'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$token  = sanitize_text_field( wp_unslash( $_GET['token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$post   = get_post( $id );
	$valid  = $post && 'pb_subscriber' === $post->post_type && hash_equals( (string) get_post_meta( $id, 'pb_token', true ), $token );

	nocache_headers();
	if ( ! $valid ) {
		powerbachat_message_page( __( 'This link has expired', 'powerbachat' ), __( 'The link is no longer valid. If you still want tariff alerts, sign up again at the bottom of any page.', 'powerbachat' ) );
	}
	if ( 'confirm' === $action ) {
		if ( 'confirmed' !== get_post_meta( $id, 'pb_status', true ) ) {
			update_post_meta( $id, 'pb_status', 'confirmed' );
			update_post_meta( $id, 'pb_confirmed', time() );
		}
		powerbachat_message_page( __( 'You are on the list', 'powerbachat' ), __( 'Thank you for confirming. We will email you when rates, fuel adjustments or solar prices move in your country, and when we publish a new guide. Nothing else.', 'powerbachat' ) );
	}
	if ( 'unsubscribe' === $action ) {
		update_post_meta( $id, 'pb_status', 'unsubscribed' );
		update_post_meta( $id, 'pb_unsubscribed', time() );
		if ( 'POST' === ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			status_header( 200 );
			exit;
		}
		powerbachat_message_page( __( 'You have been unsubscribed', 'powerbachat' ), __( 'You will not get any more tariff alerts from us. Your address will be deleted within 30 days. Changed your mind? You can sign up again at the bottom of any page.', 'powerbachat' ) );
	}
}
add_action( 'template_redirect', 'powerbachat_alert_links', 1 );

/**
 * A simple themed message page, then exit.
 *
 * @param string $title Heading.
 * @param string $text  Message.
 */
function powerbachat_message_page( $title, $text ) {
	add_filter(
		'pre_get_document_title',
		function () use ( $title ) {
			return $title;
		},
		99
	);
	add_filter( 'wp_robots', 'wp_robots_no_robots' );
	status_header( 200 );
	get_header();
	?>
	<div class="page-head">
		<div class="wrap">
			<h1 class="page-head__title"><?php echo esc_html( $title ); ?></h1>
			<p class="page-head__lede"><?php echo esc_html( $text ); ?></p>
			<p><a class="btn btn--ink" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the calculators', 'powerbachat' ); ?></a></p>
		</div>
	</div>
	<?php
	get_footer();
	exit;
}

/**
 * Country of a post from its category ('' when it has none, i.e. for everyone).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function powerbachat_post_country( $post_id ) {
	foreach ( get_the_category( $post_id ) as $term ) {
		$code = powerbachat_category_country( $term );
		if ( $code ) {
			return $code;
		}
	}
	return '';
}

/**
 * Queue a guide for the next alert when it goes live.
 *
 * @param string  $new  New status.
 * @param string  $old  Old status.
 * @param WP_Post $post Post.
 */
function powerbachat_queue_new_post( $new, $old, $post ) {
	if ( 'publish' !== $new || 'publish' === $old || 'post' !== $post->post_type ) {
		return;
	}
	if ( ! empty( $GLOBALS['powerbachat_importing'] ) || ! powerbachat_mod( 'pb_alert_new_posts' ) ) {
		return;
	}
	$queue   = (array) get_option( 'powerbachat_alert_queue', array() );
	$queue[] = (int) $post->ID;
	update_option( 'powerbachat_alert_queue', array_values( array_unique( $queue ) ), false );
	if ( ! wp_next_scheduled( 'powerbachat_send_alerts' ) ) {
		wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'powerbachat_send_alerts' );
	}
}
add_action( 'transition_post_status', 'powerbachat_queue_new_post', 10, 3 );

/**
 * Start a job: either the queued guides or a custom message.
 *
 * @param array $job Job: type, posts|subject+body, country.
 */
function powerbachat_start_alert_job( $job ) {
	$jobs   = (array) get_option( 'powerbachat_alert_jobs', array() );
	$jobs[] = wp_parse_args(
		$job,
		array(
			'type'    => 'posts',
			'posts'   => array(),
			'subject' => '',
			'body'    => '',
			'country' => '',
			'last_id' => 0,
			'sent'    => 0,
			'started' => time(),
		)
	);
	update_option( 'powerbachat_alert_jobs', $jobs, false );
	if ( ! wp_next_scheduled( 'powerbachat_send_alerts' ) ) {
		wp_schedule_single_event( time() + 60, 'powerbachat_send_alerts' );
	}
}

/**
 * Cron: move the queue into a job and send one batch.
 */
function powerbachat_send_alerts() {
	$queue = (array) get_option( 'powerbachat_alert_queue', array() );
	if ( $queue ) {
		delete_option( 'powerbachat_alert_queue' );
		$live = array_values(
			array_filter(
				$queue,
				function ( $id ) {
					return 'publish' === get_post_status( $id );
				}
			)
		);
		if ( $live ) {
			powerbachat_start_alert_job(
				array(
					'type'  => 'posts',
					'posts' => $live,
				)
			);
		}
	}

	$jobs = (array) get_option( 'powerbachat_alert_jobs', array() );
	if ( ! $jobs ) {
		return;
	}
	$job  = $jobs[0];
	global $wpdb;
	$subs = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = 'pb_status' AND m.meta_value = 'confirmed'
			WHERE p.post_type = 'pb_subscriber' AND p.post_status = 'private' AND p.ID > %d ORDER BY p.ID ASC LIMIT %d",
			$job['last_id'],
			POWERBACHAT_ALERT_BATCH
		)
	);

	foreach ( $subs as $sid ) {
		$sid            = (int) $sid;
		$country        = get_post_meta( $sid, 'pb_country', true );
		$job['last_id'] = $sid;
		if ( $job['country'] && $job['country'] !== $country ) {
			continue;
		}
		if ( 'posts' === $job['type'] ) {
			$mine = array_filter(
				$job['posts'],
				function ( $pid ) use ( $country ) {
					$c = powerbachat_post_country( $pid );
					return '' === $c || $c === $country;
				}
			);
			if ( ! $mine ) {
				continue;
			}
			list( $subject, $body ) = powerbachat_posts_email( $mine );
		} else {
			$subject = $job['subject'];
			$body    = $job['body'];
		}
		if ( powerbachat_mail_subscriber( $sid, $subject, $body ) ) {
			++$job['sent'];
		}
	}

	if ( count( $subs ) < POWERBACHAT_ALERT_BATCH ) {
		array_shift( $jobs );
		$log   = (array) get_option( 'powerbachat_alert_log', array() );
		$log[] = array(
			'when'    => time(),
			'sent'    => $job['sent'],
			'subject' => 'posts' === $job['type'] ? __( 'New guides', 'powerbachat' ) : $job['subject'],
			'country' => $job['country'],
		);
		update_option( 'powerbachat_alert_log', array_slice( $log, -20 ), false );
	} else {
		$jobs[0] = $job;
	}
	update_option( 'powerbachat_alert_jobs', $jobs, false );
	if ( $jobs ) {
		wp_schedule_single_event( time() + 2 * MINUTE_IN_SECONDS, 'powerbachat_send_alerts' );
	}
}
add_action( 'powerbachat_send_alerts', 'powerbachat_send_alerts' );

/**
 * Subject and body for a "new guides" email.
 *
 * @param int[] $post_ids Posts.
 * @return string[] Subject, body.
 */
function powerbachat_posts_email( $post_ids ) {
	$site  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$first = get_the_title( reset( $post_ids ) );
	$subject = 1 === count( $post_ids )
		/* translators: %s: post title */
		? sprintf( __( 'New guide: %s', 'powerbachat' ), wp_specialchars_decode( $first, ENT_QUOTES ) )
		/* translators: %d: number of guides */
		: sprintf( __( '%d new guides on your electricity bill', 'powerbachat' ), count( $post_ids ) );
	$lines = array();
	foreach ( $post_ids as $pid ) {
		$lines[] = wp_specialchars_decode( get_the_title( $pid ), ENT_QUOTES ) . "\n" . wp_strip_all_tags( get_the_excerpt( $pid ) ) . "\n" . get_permalink( $pid );
	}
	/* translators: %s: site name */
	$body = __( 'Hello,', 'powerbachat' ) . "\n\n" . __( 'We have just published:', 'powerbachat' ) . "\n\n" . implode( "\n\n", $lines ) . "\n\n" . sprintf( __( 'The %s team', 'powerbachat' ), $site );
	return array( $subject, $body );
}

/**
 * Daily clean-up: unconfirmed and unsubscribed addresses older than 30 days.
 */
function powerbachat_alerts_cleanup() {
	foreach ( array( 'pending' => 'pb_signed_up', 'unsubscribed' => 'pb_unsubscribed' ) as $status => $date_key ) {
		$old = get_posts(
			array(
				'post_type'      => 'pb_subscriber',
				'post_status'    => 'private',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					array(
						'key'   => 'pb_status',
						'value' => $status,
					),
					array(
						'key'     => $date_key,
						'value'   => time() - 30 * DAY_IN_SECONDS,
						'compare' => '<',
						'type'    => 'NUMERIC',
					),
				),
			)
		);
		foreach ( $old as $id ) {
			wp_delete_post( $id, true );
		}
	}
}
add_action( 'powerbachat_daily', 'powerbachat_alerts_cleanup' );

/**
 * Daily housekeeping event.
 */
function powerbachat_schedule_daily() {
	if ( ! wp_next_scheduled( 'powerbachat_daily' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'powerbachat_daily' );
	}
}
add_action( 'init', 'powerbachat_schedule_daily' );

/* ---------- Admin: list columns, export and "Send an alert" ---------- */

/**
 * Columns.
 *
 * @return array
 */
function powerbachat_subscriber_columns() {
	return array(
		'cb'         => '<input type="checkbox">',
		'title'      => __( 'Email', 'powerbachat' ),
		'pb_country' => __( 'Country', 'powerbachat' ),
		'pb_status'  => __( 'Status', 'powerbachat' ),
		'pb_date'    => __( 'Signed up', 'powerbachat' ),
	);
}
add_filter( 'manage_pb_subscriber_posts_columns', 'powerbachat_subscriber_columns' );

/**
 * Column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Post.
 */
function powerbachat_subscriber_column( $column, $post_id ) {
	$names = wp_list_pluck( powerbachat_data()['countries'], 'name' );
	if ( 'pb_country' === $column ) {
		$c = get_post_meta( $post_id, 'pb_country', true );
		echo esc_html( isset( $names[ $c ] ) ? $names[ $c ] : $c );
	} elseif ( 'pb_status' === $column ) {
		$labels = array(
			'pending'      => __( 'Waiting for confirmation', 'powerbachat' ),
			'confirmed'    => __( 'Confirmed', 'powerbachat' ),
			'unsubscribed' => __( 'Unsubscribed', 'powerbachat' ),
		);
		$s = get_post_meta( $post_id, 'pb_status', true );
		echo esc_html( isset( $labels[ $s ] ) ? $labels[ $s ] : $s );
	} elseif ( 'pb_date' === $column ) {
		$t = (int) get_post_meta( $post_id, 'pb_signed_up', true );
		echo esc_html( $t ? wp_date( get_option( 'date_format' ), $t ) : '' );
	}
}
add_action( 'manage_pb_subscriber_posts_custom_column', 'powerbachat_subscriber_column', 10, 2 );

/**
 * Remove "Edit" style row actions that make no sense here.
 *
 * @param array   $actions Actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function powerbachat_subscriber_row_actions( $actions, $post ) {
	if ( 'pb_subscriber' === $post->post_type ) {
		unset( $actions['edit'], $actions['inline hide-if-no-js'], $actions['view'] );
	}
	return $actions;
}
add_filter( 'post_row_actions', 'powerbachat_subscriber_row_actions', 10, 2 );

/**
 * "Send an alert" screen.
 */
function powerbachat_alerts_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=pb_subscriber',
		__( 'Send an alert', 'powerbachat' ),
		__( 'Send an alert', 'powerbachat' ),
		'edit_theme_options',
		'powerbachat-send-alert',
		'powerbachat_alerts_admin_screen'
	);
}
add_action( 'admin_menu', 'powerbachat_alerts_admin_menu' );

/**
 * Count confirmed subscribers per country.
 *
 * @return int[]
 */
function powerbachat_subscriber_counts() {
	global $wpdb;
	$rows   = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"SELECT c.meta_value AS country, COUNT(*) AS n FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = 'pb_status' AND s.meta_value = 'confirmed'
		LEFT JOIN {$wpdb->postmeta} c ON c.post_id = p.ID AND c.meta_key = 'pb_country'
		WHERE p.post_type = 'pb_subscriber' GROUP BY c.meta_value"
	);
	$counts = array_fill_keys( POWERBACHAT_COUNTRIES, 0 );
	foreach ( $rows as $row ) {
		$counts[ $row->country ] = (int) $row->n;
	}
	return $counts;
}

/**
 * Handle the send form and CSV export.
 */
function powerbachat_alerts_admin_actions() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	if ( isset( $_GET['powerbachat_export'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		check_admin_referer( 'powerbachat_export' );
		$ids = get_posts(
			array(
				'post_type'      => 'pb_subscriber',
				'post_status'    => 'private',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=powerbachat-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fputcsv( $out, array( 'email', 'country', 'status', 'signed_up', 'confirmed' ) );
		foreach ( $ids as $id ) {
			$c = (int) get_post_meta( $id, 'pb_confirmed', true );
			fputcsv(
				$out,
				array(
					get_post_field( 'post_title', $id ),
					get_post_meta( $id, 'pb_country', true ),
					get_post_meta( $id, 'pb_status', true ),
					gmdate( 'Y-m-d H:i', (int) get_post_meta( $id, 'pb_signed_up', true ) ),
					$c ? gmdate( 'Y-m-d H:i', $c ) : '',
				)
			);
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
	if ( isset( $_POST['powerbachat_send_alert'] ) ) {
		check_admin_referer( 'powerbachat_send_alert' );
		$subject = sanitize_text_field( wp_unslash( isset( $_POST['subject'] ) ? $_POST['subject'] : '' ) );
		$body    = sanitize_textarea_field( wp_unslash( isset( $_POST['body'] ) ? $_POST['body'] : '' ) );
		$country = sanitize_key( wp_unslash( isset( $_POST['country'] ) ? $_POST['country'] : '' ) );
		$country = in_array( $country, POWERBACHAT_COUNTRIES, true ) ? $country : '';
		$result  = 'empty';
		if ( $subject && $body ) {
			if ( ! empty( $_POST['test'] ) ) {
				wp_mail( wp_get_current_user()->user_email, '[Test] ' . $subject, $body );
				$result = 'test';
			} else {
				powerbachat_start_alert_job(
					array(
						'type'    => 'custom',
						'subject' => $subject,
						'body'    => $body,
						'country' => $country,
					)
				);
				$result = 'queued';
			}
		}
		wp_safe_redirect( admin_url( 'edit.php?post_type=pb_subscriber&page=powerbachat-send-alert&result=' . $result ) );
		exit;
	}
}
add_action( 'admin_init', 'powerbachat_alerts_admin_actions' );

/**
 * Render the send screen.
 */
function powerbachat_alerts_admin_screen() {
	$counts = powerbachat_subscriber_counts();
	$names  = wp_list_pluck( powerbachat_data()['countries'], 'name' );
	$result = isset( $_GET['result'] ) ? sanitize_key( wp_unslash( $_GET['result'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$jobs   = (array) get_option( 'powerbachat_alert_jobs', array() );
	$log    = array_reverse( (array) get_option( 'powerbachat_alert_log', array() ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Send a tariff alert', 'powerbachat' ); ?></h1>
		<?php if ( 'queued' === $result ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Queued. Emails go out in small batches every couple of minutes.', 'powerbachat' ); ?></p></div>
		<?php elseif ( 'test' === $result ) : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'Test sent to your own email address.', 'powerbachat' ); ?></p></div>
		<?php elseif ( 'empty' === $result ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Add a subject and a message.', 'powerbachat' ); ?></p></div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'Confirmed subscribers:', 'powerbachat' ); ?>
			<?php foreach ( $counts as $code => $n ) : ?>
				<strong><?php echo esc_html( isset( $names[ $code ] ) ? $names[ $code ] : $code ); ?></strong> <?php echo esc_html( number_format_i18n( $n ) ); ?>&nbsp;&nbsp;
			<?php endforeach; ?>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'edit.php?post_type=pb_subscriber&powerbachat_export=1' ), 'powerbachat_export' ) ); ?>"><?php esc_html_e( 'Export CSV', 'powerbachat' ); ?></a>
		</p>
		<p><?php esc_html_e( 'New guide posts are emailed automatically to subscribers in that country when they go live (switch this off in Customize, PowerBachat, Alerts and footer). Use this form for anything else, such as a new tariff notification.', 'powerbachat' ); ?></p>

		<form method="post" style="max-width:720px">
			<?php wp_nonce_field( 'powerbachat_send_alert' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="pb-country"><?php esc_html_e( 'Send to', 'powerbachat' ); ?></label></th>
					<td><select id="pb-country" name="country">
						<option value=""><?php esc_html_e( 'All countries', 'powerbachat' ); ?></option>
						<?php foreach ( $names as $code => $name ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select></td>
				</tr>
				<tr>
					<th><label for="pb-subject"><?php esc_html_e( 'Subject', 'powerbachat' ); ?></label></th>
					<td><input id="pb-subject" class="large-text" name="subject" maxlength="120" placeholder="<?php esc_attr_e( 'New NEPRA tariff from 1 July: what changes for your bill', 'powerbachat' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="pb-body"><?php esc_html_e( 'Message', 'powerbachat' ); ?></label></th>
					<td><textarea id="pb-body" class="large-text" rows="10" name="body"></textarea>
					<p class="description"><?php esc_html_e( 'Plain text. Keep it short and add a link to the page with the details. An unsubscribe link is added automatically.', 'powerbachat' ); ?></p></td>
				</tr>
			</table>
			<p>
				<button class="button" name="test" value="1"><?php esc_html_e( 'Send me a test', 'powerbachat' ); ?></button>
				<button class="button button-primary" name="powerbachat_send_alert" value="1" onclick="return confirm('<?php echo esc_js( __( 'Send this alert to every confirmed subscriber selected?', 'powerbachat' ) ); ?>')"><?php esc_html_e( 'Send alert', 'powerbachat' ); ?></button>
			</p>
			<input type="hidden" name="powerbachat_send_alert" value="1">
		</form>

		<?php if ( $jobs ) : ?>
			<h2><?php esc_html_e( 'Sending now', 'powerbachat' ); ?></h2>
			<ul>
				<?php foreach ( $jobs as $job ) : ?>
					<li><?php echo esc_html( 'posts' === $job['type'] ? __( 'New guides', 'powerbachat' ) : $job['subject'] ); ?>: <?php echo esc_html( number_format_i18n( $job['sent'] ) ); ?> <?php esc_html_e( 'sent so far', 'powerbachat' ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( $log ) : ?>
			<h2><?php esc_html_e( 'Recent alerts', 'powerbachat' ); ?></h2>
			<ul>
				<?php foreach ( $log as $entry ) : ?>
					<li><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' H:i', $entry['when'] ) . ': ' . $entry['subject'] . ' (' . number_format_i18n( $entry['sent'] ) . ')' ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<p class="description"><?php esc_html_e( 'Emails are sent with wp_mail(). For reliable delivery, install an SMTP plugin (for example WP Mail SMTP) connected to a sending service such as Brevo, Amazon SES or your host\'s mail server.', 'powerbachat' ); ?></p>
	</div>
	<?php
}

/**
 * No "Private" label next to every subscriber and message in the admin lists.
 *
 * @param array   $states Post states.
 * @param WP_Post $post   Post.
 * @return array
 */
function powerbachat_hide_private_state( $states, $post ) {
	if ( in_array( $post->post_type, array( 'pb_subscriber', 'pb_message' ), true ) ) {
		unset( $states['private'] );
	}
	return $states;
}
add_filter( 'display_post_states', 'powerbachat_hide_private_state', 10, 2 );
