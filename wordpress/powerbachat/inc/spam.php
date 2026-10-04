<?php
/**
 * Spam handling for the contact form, tariff alerts and the lead popup.
 *
 * Nothing is thrown away: a submission that looks like spam is still saved, marked
 * as spam with the reason, and simply not emailed. You can review it in the admin
 * (Spam view) and mark it "Not spam" if it was a real person.
 *
 * Checks:
 * - a hidden trap field (pb_hp) that people never see but bots fill in;
 * - sent faster than a person could (under 2 seconds after the page loaded);
 * - too many submissions from the same connection in an hour;
 * - a message stuffed with links.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Why a submission looks like spam, or '' when it looks fine.
 *
 * @param WP_REST_Request $request Request.
 * @param string          $bucket  Rate limit bucket.
 * @param int             $max     Submissions allowed per hour.
 * @param string          $text    Free text to scan for links.
 * @return string
 */
function powerbachat_spam_reason( $request, $bucket, $max, $text = '' ) {
	if ( '' !== trim( (string) $request['pb_hp'] ) ) {
		return __( 'Hidden trap field was filled in', 'powerbachat' );
	}
	$elapsed = (string) $request['pb_elapsed'];
	if ( '' !== $elapsed && (int) $elapsed < 2000 ) {
		return __( 'Sent too fast for a person', 'powerbachat' );
	}
	if ( preg_match_all( '#https?://|www\.#i', $text ) > 3 ) {
		return __( 'Too many links', 'powerbachat' );
	}
	if ( ! powerbachat_rate_ok( $bucket, $max ) ) {
		return __( 'Too many submissions from the same connection', 'powerbachat' );
	}
	return '';
}

/**
 * Mark a saved item as spam.
 *
 * @param int    $id     Post ID.
 * @param string $reason Reason.
 */
function powerbachat_mark_spam( $id, $reason ) {
	update_post_meta( $id, 'pb_spam', $reason ? $reason : __( 'Marked as spam', 'powerbachat' ) );
}

/**
 * Is a saved item spam?
 *
 * @param int $id Post ID.
 * @return bool
 */
function powerbachat_is_spam( $id ) {
	return '' !== (string) get_post_meta( $id, 'pb_spam', true );
}

/* ---------- Admin: Inbox / Spam views, status column, spam actions ---------- */

/**
 * Post types with an inbox.
 *
 * @return string[]
 */
function powerbachat_inbox_types() {
	return array( 'pb_message', 'pb_lead', 'pb_subscriber' );
}

/**
 * Count items in a view.
 *
 * @param string $type Post type.
 * @param bool   $spam Spam or not.
 * @return int
 */
function powerbachat_inbox_count( $type, $spam ) {
	$q = new WP_Query(
		array(
			'post_type'      => $type,
			'post_status'    => 'private',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => 'pb_spam',
					'compare' => $spam ? 'EXISTS' : 'NOT EXISTS',
				),
			),
		)
	);
	return (int) $q->found_posts;
}

/**
 * "Inbox" and "Spam" links above the list.
 *
 * @param array $views Views.
 * @return array
 */
function powerbachat_inbox_views( $views ) {
	$type    = get_current_screen() ? get_current_screen()->post_type : '';
	$current = isset( $_GET['pb_view'] ) ? sanitize_key( wp_unslash( $_GET['pb_view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$base    = admin_url( 'edit.php?post_type=' . $type );
	$views   = array(
		'inbox' => sprintf( '<a href="%s"%s>%s <span class="count">(%d)</span></a>', esc_url( $base ), '' === $current ? ' class="current"' : '', esc_html__( 'Inbox', 'powerbachat' ), powerbachat_inbox_count( $type, false ) ),
		'spam'  => sprintf( '<a href="%s"%s>%s <span class="count">(%d)</span></a>', esc_url( add_query_arg( 'pb_view', 'spam', $base ) ), 'spam' === $current ? ' class="current"' : '', esc_html__( 'Spam', 'powerbachat' ), powerbachat_inbox_count( $type, true ) ),
	);
	return $views;
}

/**
 * Filter the list to the chosen view.
 *
 * @param WP_Query $query Query.
 */
function powerbachat_inbox_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || ! in_array( $query->get( 'post_type' ), powerbachat_inbox_types(), true ) ) {
		return;
	}
	$spam = isset( $_GET['pb_view'] ) && 'spam' === sanitize_key( wp_unslash( $_GET['pb_view'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => 'pb_spam',
				'compare' => $spam ? 'EXISTS' : 'NOT EXISTS',
			),
		)
	);
}
add_action( 'pre_get_posts', 'powerbachat_inbox_query' );

/**
 * "Not spam" / "Mark as spam" row links.
 *
 * @param array   $actions Actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function powerbachat_inbox_row_actions( $actions, $post ) {
	if ( ! in_array( $post->post_type, powerbachat_inbox_types(), true ) ) {
		return $actions;
	}
	$spam = powerbachat_is_spam( $post->ID );
	$url  = wp_nonce_url( admin_url( 'admin-post.php?action=pb_spam_toggle&id=' . $post->ID ), 'pb_spam_' . $post->ID );
	$actions['pb_spam'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $spam ? esc_html__( 'Not spam', 'powerbachat' ) : esc_html__( 'Mark as spam', 'powerbachat' ) );
	return $actions;
}
add_filter( 'post_row_actions', 'powerbachat_inbox_row_actions', 20, 2 );

/**
 * Toggle spam.
 */
function powerbachat_spam_toggle() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	check_admin_referer( 'pb_spam_' . $id );
	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( esc_html__( 'Not allowed.', 'powerbachat' ) );
	}
	if ( powerbachat_is_spam( $id ) ) {
		delete_post_meta( $id, 'pb_spam' );
		// A subscriber marked "not spam" still has to confirm by email before getting alerts.
		if ( 'pb_subscriber' === get_post_type( $id ) && 'spam' === get_post_meta( $id, 'pb_status', true ) ) {
			update_post_meta( $id, 'pb_status', 'pending' );
		}
	} else {
		powerbachat_mark_spam( $id, __( 'Marked as spam by you', 'powerbachat' ) );
		if ( 'pb_subscriber' === get_post_type( $id ) ) {
			update_post_meta( $id, 'pb_status', 'spam' );
		}
	}
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=' . get_post_type( $id ) ) );
	exit;
}
add_action( 'admin_post_pb_spam_toggle', 'powerbachat_spam_toggle' );

/**
 * Hook views for each inbox type.
 */
function powerbachat_inbox_admin_hooks() {
	foreach ( powerbachat_inbox_types() as $type ) {
		add_filter( 'views_edit-' . $type, 'powerbachat_inbox_views' );
	}
}
add_action( 'admin_init', 'powerbachat_inbox_admin_hooks' );

/**
 * "New" bubbles on the Messages and Leads menus: items since you last opened the list.
 */
function powerbachat_inbox_bubbles() {
	global $menu;
	if ( ! is_array( $menu ) ) {
		return;
	}
	foreach ( array( 'pb_message', 'pb_lead' ) as $type ) {
		$seen = (int) get_user_meta( get_current_user_id(), 'pb_seen_' . $type, true );
		$q    = new WP_Query(
			array(
				'post_type'      => $type,
				'post_status'    => 'private',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'date_query'     => array( array( 'after' => gmdate( 'Y-m-d H:i:s', $seen ), 'column' => 'post_date_gmt' ) ),
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => 'pb_spam',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);
		if ( ! $q->found_posts ) {
			continue;
		}
		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=' . $type === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $q->found_posts . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	}
}
add_action( 'admin_menu', 'powerbachat_inbox_bubbles', 999 );

/**
 * Opening a list clears its bubble.
 */
function powerbachat_inbox_seen() {
	$type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( in_array( $type, array( 'pb_message', 'pb_lead' ), true ) ) {
		update_user_meta( get_current_user_id(), 'pb_seen_' . $type, time() );
	}
}
add_action( 'load-edit.php', 'powerbachat_inbox_seen' );

/**
 * Message list columns.
 *
 * @return array
 */
function powerbachat_message_columns() {
	return array(
		'cb'         => '<input type="checkbox">',
		'title'      => __( 'Message', 'powerbachat' ),
		'pb_email'   => __( 'Email', 'powerbachat' ),
		'pb_country' => __( 'Country', 'powerbachat' ),
		'pb_spam'    => __( 'Status', 'powerbachat' ),
		'date'       => __( 'Date', 'powerbachat' ),
	);
}
add_filter( 'manage_pb_message_posts_columns', 'powerbachat_message_columns' );

/**
 * Extra columns for messages, leads and subscribers.
 *
 * @param string $column  Column.
 * @param int    $post_id Post.
 */
function powerbachat_inbox_column( $column, $post_id ) {
	if ( 'pb_email' === $column ) {
		$email = get_post_meta( $post_id, 'pb_email', true );
		printf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $email ) );
	} elseif ( 'pb_country' === $column && 'pb_message' === get_post_type( $post_id ) ) {
		echo esc_html( strtoupper( get_post_meta( $post_id, 'pb_country', true ) ) );
	} elseif ( 'pb_spam' === $column ) {
		$reason = get_post_meta( $post_id, 'pb_spam', true );
		if ( $reason ) {
			printf( '<span style="color:#b32d2e;font-weight:600">%s</span><br><small>%s</small>', esc_html__( 'Spam', 'powerbachat' ), esc_html( $reason ) );
		} elseif ( get_post_meta( $post_id, 'pb_test', true ) ) {
			esc_html_e( 'Form test', 'powerbachat' );
		} else {
			echo '<span style="color:#008a20;font-weight:600">' . esc_html__( 'Received', 'powerbachat' ) . '</span>';
		}
	}
}
add_action( 'manage_pb_message_posts_custom_column', 'powerbachat_inbox_column', 10, 2 );
add_action( 'manage_pb_lead_posts_custom_column', 'powerbachat_inbox_column', 10, 2 );

/**
 * Add a Status column to leads.
 *
 * @param array $columns Columns.
 * @return array
 */
function powerbachat_lead_spam_column( $columns ) {
	$date = isset( $columns['date'] ) ? $columns['date'] : __( 'Date', 'powerbachat' );
	unset( $columns['date'] );
	$columns['pb_spam'] = __( 'Status', 'powerbachat' );
	$columns['date']    = $date;
	return $columns;
}
add_filter( 'manage_pb_lead_posts_columns', 'powerbachat_lead_spam_column', 20 );

/**
 * Delete spam after 30 days.
 */
function powerbachat_spam_cleanup() {
	$old = get_posts(
		array(
			'post_type'      => powerbachat_inbox_types(),
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'date_query'     => array( array( 'before' => '30 days ago' ) ),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => 'pb_spam',
					'compare' => 'EXISTS',
				),
			),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'powerbachat_daily', 'powerbachat_spam_cleanup' );

/* ---------- "Run form test" on the Messages screen ---------- */

/**
 * The test box above the Messages list.
 */
function powerbachat_form_test_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-pb_message' !== $screen->id ) {
		return;
	}
	$result = get_transient( 'pb_form_test_' . get_current_user_id() );
	delete_transient( 'pb_form_test_' . get_current_user_id() );
	if ( $result ) {
		printf( '<div class="notice notice-%s"><p>%s</p></div>', esc_attr( $result['ok'] ? 'success' : 'error' ), wp_kses_post( $result['text'] ) );
	}
	?>
	<div class="notice notice-info">
		<p>
			<?php esc_html_e( 'Not sure the contact form works on your server? Run a test: the site sends a message through the form the same way a visitor does, then tells you whether it was saved and whether the email went out.', 'powerbachat' ); ?>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pb_form_test' ), 'pb_form_test' ) ); ?>"><?php esc_html_e( 'Run form test', 'powerbachat' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'powerbachat_form_test_notice' );

/**
 * Run the test: post to admin-ajax.php from the server, like a visitor would.
 */
function powerbachat_form_test() {
	check_admin_referer( 'pb_form_test' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'powerbachat' ) );
	}
	$token = wp_generate_password( 20, false );
	set_transient( 'pb_form_test_token', $token, 5 * MINUTE_IN_SECONDS );
	$response = wp_remote_post(
		admin_url( 'admin-ajax.php' ),
		array(
			'timeout'   => 20,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			'body'      => array(
				'action'     => 'pb_contact',
				'name'       => __( 'Form test', 'powerbachat' ),
				'email'      => get_option( 'admin_email' ),
				'topic'      => 'feedback',
				'message'    => __( 'Automatic test from the admin. You can delete this message.', 'powerbachat' ),
				'pb_elapsed' => '9000',
				'pb_test'    => $token,
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		$result = array(
			'ok'   => false,
			/* translators: %s: error */
			'text' => sprintf( __( '<strong>The server could not reach its own admin-ajax.php</strong> (%s). Your host or a security plugin may block it. Ask your host to allow requests to /wp-admin/admin-ajax.php, or check your firewall plugin.', 'powerbachat' ), esc_html( $response->get_error_message() ) ),
		);
	} else {
		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 200 === $code && ! empty( $data['ok'] ) ) {
			$mail   = ! empty( $data['mail'] );
			$result = array(
				'ok'   => $mail,
				'text' => $mail
					/* translators: %s: email address */
					? sprintf( __( '<strong>All good.</strong> The test message was saved below and an email was sent to %s. Check that it arrived (look in spam too).', 'powerbachat' ), esc_html( powerbachat_contact_address() ) )
					/* translators: %s: email address */
					: sprintf( __( '<strong>The form works and saves messages</strong>, but WordPress could not send the email to %s. Install and set up WP Mail SMTP so notifications reach you. Messages are always kept here either way.', 'powerbachat' ), esc_html( powerbachat_contact_address() ) ) . ( ! empty( $data['mail_error'] ) ? ' ' . esc_html( $data['mail_error'] ) : '' ),
			);
		} else {
			$result = array(
				'ok'   => false,
				/* translators: %d: HTTP status */
				'text' => sprintf( __( '<strong>The form request was refused</strong> (HTTP %d). A security plugin or your host is probably blocking admin-ajax.php for visitors. Allow it in your firewall settings and run the test again.', 'powerbachat' ), (int) $code ),
			);
		}
	}
	set_transient( 'pb_form_test_' . get_current_user_id(), $result, MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'edit.php?post_type=pb_message' ) );
	exit;
}
add_action( 'admin_post_pb_form_test', 'powerbachat_form_test' );
