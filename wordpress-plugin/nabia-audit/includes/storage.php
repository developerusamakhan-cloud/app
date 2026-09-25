<?php
/**
 * Audit records, protected PDF storage and secure download links.
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Private post type holding every audit request.
 */
function nwa_register_post_type() {
	register_post_type(
		'nabia_audit',
		array(
			'labels'          => array(
				'name'               => __( 'Website Audits', 'nabia-audit' ),
				'singular_name'      => __( 'Website Audit', 'nabia-audit' ),
				'menu_name'          => __( 'Website Audits', 'nabia-audit' ),
				'all_items'          => __( 'All audits', 'nabia-audit' ),
				'edit_item'          => __( 'Website audit', 'nabia-audit' ),
				'search_items'       => __( 'Search audits', 'nabia-audit' ),
				'not_found'          => __( 'No audits yet. Add the form with [nabia_website_audit].', 'nabia-audit' ),
				'not_found_in_trash' => __( 'No audits in Trash.', 'nabia-audit' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 26,
			'menu_icon'       => 'dashicons-chart-area',
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'supports'        => array( 'title' ),
		)
	);
}
add_action( 'init', 'nwa_register_post_type' );

/**
 * Folder for PDF reports, blocked from direct access.
 *
 * @return string Path with trailing slash.
 */
function nwa_reports_dir() {
	$upload = wp_upload_dir();
	$dir    = trailingslashit( $upload['basedir'] ) . 'nabia-audits/';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '.htaccess' ) ) {
		file_put_contents( $dir . '.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	if ( ! file_exists( $dir . 'index.php' ) ) {
		file_put_contents( $dir . 'index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $dir;
}

/**
 * Secret key for an audit (used in links).
 *
 * @param int $id Audit ID.
 * @return string
 */
function nwa_key( $id ) {
	$key = get_post_meta( $id, '_nwa_key', true );
	if ( ! $key ) {
		$key = wp_generate_password( 24, false );
		update_post_meta( $id, '_nwa_key', $key );
	}
	return $key;
}

/**
 * Check a key from a link.
 *
 * @param int    $id  Audit ID.
 * @param string $key Key.
 * @return bool
 */
function nwa_key_ok( $id, $key ) {
	$stored = get_post_meta( $id, '_nwa_key', true );
	return $id && $stored && is_string( $key ) && hash_equals( $stored, $key ) && 'nabia_audit' === get_post_type( $id );
}

/**
 * Audit result array.
 *
 * @param int $id Audit ID.
 * @return array
 */
function nwa_result( $id ) {
	$data = json_decode( (string) get_post_meta( $id, '_nwa_result', true ), true );
	return is_array( $data ) ? $data : array();
}

/**
 * Lead details for the PDF.
 *
 * @param int $id Audit ID.
 * @return array
 */
function nwa_lead( $id ) {
	return array(
		'name'  => get_post_meta( $id, '_nwa_name', true ),
		'email' => get_post_meta( $id, '_nwa_email', true ),
		'date'  => get_the_date( get_option( 'date_format' ), $id ),
	);
}

/**
 * Nice file name for the download.
 *
 * @param int $id Audit ID.
 * @return string
 */
function nwa_pdf_name( $id ) {
	$result = nwa_result( $id );
	$host   = preg_replace( '/^www\./', '', (string) wp_parse_url( isset( $result['final_url'] ) ? $result['final_url'] : '', PHP_URL_HOST ) );
	return sanitize_file_name( 'website-audit-' . ( $host ? $host : $id ) . '-' . get_the_date( 'Y-m-d', $id ) . '.pdf' );
}

/**
 * Path of the stored PDF, building it on the fly when missing.
 *
 * @param int  $id    Audit ID.
 * @param bool $fresh Rebuild even if it exists.
 * @return string Empty on failure.
 */
function nwa_pdf_path( $id, $fresh = false ) {
	$result = nwa_result( $id );
	if ( empty( $result['ok'] ) ) {
		return '';
	}
	$path = nwa_reports_dir() . 'audit-' . (int) $id . '-' . nwa_key( $id ) . '.pdf';
	if ( $fresh || ! file_exists( $path ) ) {
		$pdf = nwa_build_pdf( $result, nwa_lead( $id ) );
		if ( ! $pdf || false === file_put_contents( $path, $pdf ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			return '';
		}
	}
	return $path;
}

/**
 * Public download link for an audit PDF.
 *
 * @param int $id Audit ID.
 * @return string
 */
function nwa_pdf_url( $id ) {
	return add_query_arg(
		array(
			'nwa_pdf' => (int) $id,
			'key'     => nwa_key( $id ),
		),
		home_url( '/' )
	);
}

/**
 * Online report link (the page that had the form).
 *
 * @param int $id Audit ID.
 * @return string
 */
function nwa_report_url( $id ) {
	$page = get_post_meta( $id, '_nwa_source', true );
	$page = $page ? $page : home_url( '/' );
	return add_query_arg(
		array(
			'nwa_report' => (int) $id,
			'key'        => nwa_key( $id ),
		),
		remove_query_arg( array( 'nwa_report', 'key', 'nwa_error' ), $page )
	) . '#nabia-audit';
}

/**
 * Serve ?nwa_pdf=ID&key=KEY downloads (built at runtime when needed).
 */
function nwa_serve_pdf() {
	if ( empty( $_GET['nwa_pdf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$id  = absint( $_GET['nwa_pdf'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! nwa_key_ok( $id, $key ) && ! current_user_can( 'edit_post', $id ) ) {
		wp_die( esc_html__( 'This report link is not valid.', 'nabia-audit' ), '', array( 'response' => 403 ) );
	}
	$path = nwa_pdf_path( $id );
	if ( ! $path ) {
		wp_die( esc_html__( 'The report could not be created.', 'nabia-audit' ), '', array( 'response' => 500 ) );
	}
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: ' . ( isset( $_GET['view'] ) ? 'inline' : 'attachment' ) . '; filename="' . nwa_pdf_name( $id ) . '"' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Robots-Tag: noindex' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'template_redirect', 'nwa_serve_pdf', 1 );

/**
 * Delete the PDF when an audit is deleted.
 *
 * @param int $id Post ID.
 */
function nwa_delete_pdf( $id ) {
	if ( 'nabia_audit' !== get_post_type( $id ) ) {
		return;
	}
	$path = nwa_reports_dir() . 'audit-' . (int) $id . '-' . get_post_meta( $id, '_nwa_key', true ) . '.pdf';
	if ( file_exists( $path ) ) {
		wp_delete_file( $path );
	}
}
add_action( 'before_delete_post', 'nwa_delete_pdf' );
