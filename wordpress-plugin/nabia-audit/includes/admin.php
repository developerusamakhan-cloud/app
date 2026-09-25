<?php
/**
 * Admin: Website Audits list, detail screen, actions and settings page.
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings submenu and the unread bubble.
 */
function nwa_admin_menu() {
	global $menu;
	add_submenu_page( 'edit.php?post_type=nabia_audit', __( 'Settings', 'nabia-audit' ), __( 'Settings', 'nabia-audit' ), 'manage_options', 'nwa-settings', 'nwa_settings_screen' );

	$unread = (int) ( new WP_Query(
		array(
			'post_type'      => 'nabia_audit',
			'post_status'    => 'private',
			'meta_key'       => '_nwa_read', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '0', // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	) )->found_posts;
	if ( $unread && is_array( $menu ) ) {
		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=nabia_audit' === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $unread . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	}
}
add_action( 'admin_menu', 'nwa_admin_menu' );

/**
 * Small admin styles.
 */
function nwa_admin_css() {
	$screen = get_current_screen();
	if ( ! $screen || 'nabia_audit' !== $screen->post_type ) {
		return;
	}
	echo '<style>
		.nwa-badge{display:inline-grid;place-items:center;min-width:38px;height:28px;padding:0 8px;border-radius:14px;font-weight:700;color:#fff}
		.nwa-badge.is-good{background:#16a34a}.nwa-badge.is-ok{background:#f59e0b}.nwa-badge.is-bad{background:#ef4444}.nwa-badge.is-none{background:#8c8f94}
		.nwa-mini{display:flex;gap:6px;flex-wrap:wrap}.nwa-mini span{padding:2px 7px;border-radius:10px;background:#f0f0f1;font-size:12px}
		.nwa-mini b.is-good{color:#15803d}.nwa-mini b.is-ok{color:#b45309}.nwa-mini b.is-bad{color:#b91c1c}
		.nwa-detail-head{display:flex;gap:24px;align-items:center;flex-wrap:wrap;margin-bottom:16px}
		.nwa-detail-head .nwa-badge{min-width:70px;height:56px;font-size:26px;border-radius:16px}
		.nwa-cat{margin:22px 0 8px;font-size:15px}
		.nwa-status{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
		.nwa-status.pass{background:#dcfce7;color:#15803d}.nwa-status.warn{background:#fef3c7;color:#b45309}.nwa-status.fail{background:#fee2e2;color:#b91c1c}
		.column-nwa_score{width:70px}.column-nwa_pdf{width:110px}
	</style>';
}
add_action( 'admin_head', 'nwa_admin_css' );

/**
 * List columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function nwa_columns( $columns ) {
	return array(
		'cb'         => $columns['cb'],
		'title'      => __( 'Website', 'nabia-audit' ),
		'nwa_score'  => __( 'Score', 'nabia-audit' ),
		'nwa_cats'   => __( 'Design / SEO / Content / Speed', 'nabia-audit' ),
		'nwa_person' => __( 'Requested by', 'nabia-audit' ),
		'nwa_pdf'    => __( 'Report', 'nabia-audit' ),
		'date'       => __( 'Date', 'nabia-audit' ),
	);
}
add_filter( 'manage_nabia_audit_posts_columns', 'nwa_columns' );

/**
 * Sortable score column.
 *
 * @param array $columns Columns.
 * @return array
 */
function nwa_sortable( $columns ) {
	$columns['nwa_score'] = 'nwa_score';
	return $columns;
}
add_filter( 'manage_edit-nabia_audit_sortable_columns', 'nwa_sortable' );

/**
 * Sort by score and 20 per page.
 *
 * @param WP_Query $query Query.
 */
function nwa_list_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'nabia_audit' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( 'nwa_score' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_nwa_overall' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'nwa_list_query' );

/**
 * Default 20 per page.
 *
 * @param int $per_page Per page.
 * @return int
 */
function nwa_per_page( $per_page ) {
	$saved = (int) get_user_option( 'edit_nabia_audit_per_page' );
	return $saved > 0 ? $saved : 20;
}
add_filter( 'edit_nabia_audit_per_page', 'nwa_per_page' );

/**
 * Render columns.
 *
 * @param string $column Column.
 * @param int    $id     Post ID.
 */
function nwa_column( $column, $id ) {
	$status = get_post_meta( $id, '_nwa_status', true );
	switch ( $column ) {
		case 'nwa_score':
			if ( 'done' === $status ) {
				$score = (int) get_post_meta( $id, '_nwa_overall', true );
				echo '<span class="nwa-badge is-' . esc_attr( nwa_state( $score ) ) . '">' . (int) $score . '</span>';
			} else {
				echo '<span class="nwa-badge is-none" title="' . esc_attr( get_post_meta( $id, '_nwa_error', true ) ) . '">!</span>';
			}
			break;
		case 'nwa_cats':
			if ( 'done' !== $status ) {
				echo '<em>' . esc_html__( 'Website could not be reached', 'nabia-audit' ) . '</em>';
				break;
			}
			echo '<div class="nwa-mini">';
			foreach ( nwa_categories() as $key => $cat ) {
				$score = (int) get_post_meta( $id, '_nwa_score_' . $key, true );
				echo '<span>' . esc_html( $cat['short'] ) . ' <b class="is-' . esc_attr( nwa_state( $score ) ) . '">' . (int) $score . '</b></span>';
			}
			echo '</div>';
			break;
		case 'nwa_person':
			$name  = get_post_meta( $id, '_nwa_name', true );
			$email = get_post_meta( $id, '_nwa_email', true );
			echo esc_html( $name ? $name : '' ) . ( $name ? '<br>' : '' ) . '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
			if ( '0' === get_post_meta( $id, '_nwa_read', true ) ) {
				echo ' <strong style="color:#7c3aed">&#9679; ' . esc_html__( 'New', 'nabia-audit' ) . '</strong>';
			}
			break;
		case 'nwa_pdf':
			if ( 'done' === $status ) {
				echo '<a class="button button-small" href="' . esc_url( nwa_pdf_url( $id ) ) . '">' . esc_html__( 'Download PDF', 'nabia-audit' ) . '</a>';
			}
			break;
	}
}
add_action( 'manage_nabia_audit_posts_custom_column', 'nwa_column', 10, 2 );

/**
 * Row actions.
 *
 * @param array   $actions Actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function nwa_row_actions( $actions, $post ) {
	if ( 'nabia_audit' !== $post->post_type ) {
		return $actions;
	}
	unset( $actions['inline hide-if-no-js'] );
	if ( isset( $actions['edit'] ) ) {
		$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'Open', 'nabia-audit' ) . '</a>';
	}
	$url = get_post_meta( $post->ID, '_nwa_url', true );
	if ( $url ) {
		$actions['nwa_visit'] = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Visit site', 'nabia-audit' ) . '</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'nwa_row_actions', 10, 2 );

/**
 * Hide the "Private" label.
 *
 * @param array   $states States.
 * @param WP_Post $post   Post.
 * @return array
 */
function nwa_post_states( $states, $post ) {
	if ( 'nabia_audit' === $post->post_type ) {
		unset( $states['private'] );
	}
	return $states;
}
add_filter( 'display_post_states', 'nwa_post_states', 10, 2 );

/**
 * Detail screen boxes.
 */
function nwa_meta_boxes() {
	remove_meta_box( 'submitdiv', 'nabia_audit', 'side' );
	add_meta_box( 'nwa_detail', __( 'Audit report', 'nabia-audit' ), 'nwa_detail_box', 'nabia_audit', 'normal', 'high' );
	add_meta_box( 'nwa_actions', __( 'Actions', 'nabia-audit' ), 'nwa_actions_box', 'nabia_audit', 'side', 'high' );
}
add_action( 'add_meta_boxes_nabia_audit', 'nwa_meta_boxes' );

/**
 * Full report in the admin.
 *
 * @param WP_Post $post Audit.
 */
function nwa_detail_box( $post ) {
	update_post_meta( $post->ID, '_nwa_read', '1' );
	$result = nwa_result( $post->ID );
	$name   = get_post_meta( $post->ID, '_nwa_name', true );
	$email  = get_post_meta( $post->ID, '_nwa_email', true );
	$url    = get_post_meta( $post->ID, '_nwa_url', true );
	echo '<table class="widefat striped" style="margin-bottom:16px"><tbody>';
	echo '<tr><th style="width:150px">' . esc_html__( 'Website', 'nabia-audit' ) . '</th><td><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $url ) . '</a></td></tr>';
	if ( $name ) {
		echo '<tr><th>' . esc_html__( 'Name', 'nabia-audit' ) . '</th><td>' . esc_html( $name ) . '</td></tr>';
	}
	echo '<tr><th>' . esc_html__( 'Email', 'nabia-audit' ) . '</th><td><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></td></tr>';
	echo '<tr><th>' . esc_html__( 'Report emailed', 'nabia-audit' ) . '</th><td>' . esc_html( get_post_meta( $post->ID, '_nwa_mail_user', true ) ? get_post_meta( $post->ID, '_nwa_mail_user', true ) : __( 'no', 'nabia-audit' ) ) . '</td></tr>';
	echo '<tr><th>' . esc_html__( 'Requested', 'nabia-audit' ) . '</th><td>' . esc_html( get_the_date( '', $post ) . ' ' . get_the_time( '', $post ) ) . '</td></tr>';
	echo '</tbody></table>';

	if ( empty( $result['ok'] ) ) {
		echo '<p><strong>' . esc_html__( 'The website could not be analysed:', 'nabia-audit' ) . '</strong> ' . esc_html( isset( $result['error'] ) ? $result['error'] : '' ) . '</p>';
		return;
	}
	$score = (int) $result['overall'];
	echo '<div class="nwa-detail-head"><span class="nwa-badge is-' . esc_attr( nwa_state( $score ) ) . '">' . (int) $score . '</span><div><strong style="font-size:16px">' . esc_html__( 'Grade', 'nabia-audit' ) . ' ' . esc_html( $result['grade'] ) . '</strong><br>' . esc_html( nwa_verdict( $score ) ) . '</div></div>';
	foreach ( nwa_categories() as $key => $cat ) {
		$data = $result['categories'][ $key ];
		echo '<h3 class="nwa-cat">' . esc_html( $cat['title'] ) . ' <span class="nwa-badge is-' . esc_attr( nwa_state( $data['score'] ) ) . '" style="height:22px;min-width:30px;font-size:12px">' . (int) $data['score'] . '</span></h3>';
		echo '<table class="widefat striped"><tbody>';
		foreach ( $data['checks'] as $check ) {
			echo '<tr><td style="width:80px"><span class="nwa-status ' . esc_attr( $check['status'] ) . '">' . esc_html( 'pass' === $check['status'] ? 'PASS' : ( 'warn' === $check['status'] ? 'IMPROVE' : 'FIX' ) ) . '</span></td><td style="width:190px"><strong>' . esc_html( $check['label'] ) . '</strong></td><td>' . esc_html( $check['found'] ) . ( $check['fix'] ? '<br><em style="color:#646970">' . esc_html( $check['fix'] ) . '</em>' : '' ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
}

/**
 * Action buttons.
 *
 * @param WP_Post $post Audit.
 */
function nwa_actions_box( $post ) {
	$done = 'done' === get_post_meta( $post->ID, '_nwa_status', true );
	$act  = function ( $action, $label, $primary = false ) use ( $post ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&id=' . $post->ID ), $action . '_' . $post->ID );
		echo '<p><a class="button ' . ( $primary ? 'button-primary' : '' ) . '" style="width:100%;text-align:center" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></p>';
	};
	if ( $done ) {
		echo '<p><a class="button button-primary" style="width:100%;text-align:center" href="' . esc_url( nwa_pdf_url( $post->ID ) ) . '">' . esc_html__( 'Download PDF', 'nabia-audit' ) . '</a></p>';
		echo '<p><a class="button" style="width:100%;text-align:center" href="' . esc_url( add_query_arg( 'view', '1', nwa_pdf_url( $post->ID ) ) ) . '" target="_blank">' . esc_html__( 'Open PDF in browser', 'nabia-audit' ) . '</a></p>';
		echo '<p><a class="button" style="width:100%;text-align:center" href="' . esc_url( nwa_report_url( $post->ID ) ) . '" target="_blank">' . esc_html__( 'View online report', 'nabia-audit' ) . '</a></p>';
		$act( 'nwa_resend', __( 'Email the report again', 'nabia-audit' ) );
	}
	$act( 'nwa_rerun', __( 'Run the audit again', 'nabia-audit' ) );
	echo '<p><a class="submitdelete" style="color:#b32d2e" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">' . esc_html__( 'Move to Trash', 'nabia-audit' ) . '</a></p>';
	if ( isset( $_GET['nwa_done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<p style="color:#15803d"><strong>' . esc_html( sanitize_text_field( wp_unslash( $_GET['nwa_done'] ) ) ) . '</strong></p>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}

/**
 * Admin actions: resend and re-run.
 */
function nwa_admin_action() {
	$action = current_action() === 'admin_post_nwa_resend' ? 'nwa_resend' : 'nwa_rerun';
	$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	check_admin_referer( $action . '_' . $id );
	if ( ! $id || 'nabia_audit' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'nabia-audit' ) );
	}
	if ( 'nwa_rerun' === $action ) {
		$result = nwa_run_audit( get_post_meta( $id, '_nwa_url', true ) );
		update_post_meta( $id, '_nwa_result', wp_slash( wp_json_encode( $result ) ) );
		if ( ! empty( $result['ok'] ) ) {
			update_post_meta( $id, '_nwa_status', 'done' );
			update_post_meta( $id, '_nwa_overall', (int) $result['overall'] );
			foreach ( $result['categories'] as $key => $cat ) {
				update_post_meta( $id, '_nwa_score_' . $key, (int) $cat['score'] );
			}
			nwa_pdf_path( $id, true );
			$msg = __( 'Audit updated.', 'nabia-audit' );
		} else {
			update_post_meta( $id, '_nwa_status', 'unreachable' );
			update_post_meta( $id, '_nwa_error', $result['error'] );
			$msg = __( 'The website could not be reached.', 'nabia-audit' );
		}
	} else {
		$sent = nwa_send_emails( $id );
		$msg  = $sent['user'] ? __( 'Report emailed.', 'nabia-audit' ) : __( 'The email could not be sent. Check your SMTP settings.', 'nabia-audit' );
	}
	wp_safe_redirect( add_query_arg( 'nwa_done', rawurlencode( $msg ), get_edit_post_link( $id, 'url' ) ) );
	exit;
}
add_action( 'admin_post_nwa_resend', 'nwa_admin_action' );
add_action( 'admin_post_nwa_rerun', 'nwa_admin_action' );
