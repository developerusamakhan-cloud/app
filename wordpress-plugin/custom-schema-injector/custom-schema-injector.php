<?php
/**
 * Plugin Name: Custom Schema Injector
 * Description: Adds a per-page/post textarea to enter custom JSON-LD schema, which is injected into that page's <head> tag.
 * Version: 1.0.0
 * Author: developerusamakhan
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CSI_META_KEY', '_csi_custom_schema' );
define( 'CSI_NONCE_ACTION', 'csi_save_custom_schema' );
define( 'CSI_NONCE_NAME', 'csi_nonce' );

add_action( 'add_meta_boxes', 'csi_add_meta_box' );
function csi_add_meta_box() {
	$post_types = get_post_types( array( 'public' => true ) );
	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'csi_custom_schema_box',
			'Custom Schema (JSON-LD)',
			'csi_render_meta_box',
			$post_type,
			'normal',
			'default'
		);
	}
}

function csi_render_meta_box( $post ) {
	wp_nonce_field( CSI_NONCE_ACTION, CSI_NONCE_NAME );
	$value = get_post_meta( $post->ID, CSI_META_KEY, true );
	?>
	<p>
		<label for="csi_custom_schema_field">
			Paste your custom JSON-LD schema for this page. It will be added inside the
			<code>&lt;head&gt;</code> tag of this page only.
		</label>
	</p>
	<textarea
		id="csi_custom_schema_field"
		name="csi_custom_schema_field"
		rows="12"
		style="width:100%;font-family:monospace;"
		placeholder='{&#10;  "@context": "https://schema.org",&#10;  "@type": "Article",&#10;  "headline": "..."&#10;}'
	><?php echo esc_textarea( $value ); ?></textarea>
	<p class="description">Must be valid JSON. Leave empty to output nothing on this page.</p>
	<?php
}

add_action( 'save_post', 'csi_save_meta_box' );
function csi_save_meta_box( $post_id ) {
	if ( ! isset( $_POST[ CSI_NONCE_NAME ] ) || ! wp_verify_nonce( $_POST[ CSI_NONCE_NAME ], CSI_NONCE_ACTION ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['csi_custom_schema_field'] ) ) {
		$raw = wp_unslash( $_POST['csi_custom_schema_field'] );
		$raw = trim( $raw );

		if ( '' === $raw ) {
			delete_post_meta( $post_id, CSI_META_KEY );
			return;
		}

		// Validate JSON before saving; store raw text but flag invalid JSON.
		json_decode( $raw );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			set_transient( 'csi_invalid_json_' . $post_id, true, 45 );
		}

		update_post_meta( $post_id, CSI_META_KEY, $raw );
	}
}

add_action( 'admin_notices', 'csi_admin_notice_invalid_json' );
function csi_admin_notice_invalid_json() {
	global $post;
	if ( ! $post ) {
		return;
	}
	if ( get_transient( 'csi_invalid_json_' . $post->ID ) ) {
		delete_transient( 'csi_invalid_json_' . $post->ID );
		echo '<div class="notice notice-error"><p>Custom Schema Injector: the JSON-LD you entered is not valid JSON. It was saved, but please fix the syntax.</p></div>';
	}
}

add_action( 'wp_head', 'csi_output_schema' );
function csi_output_schema() {
	if ( ! is_singular() ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	$schema = get_post_meta( $post_id, CSI_META_KEY, true );
	if ( empty( $schema ) ) {
		return;
	}

	$decoded = json_decode( $schema );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return;
	}

	echo "\n<script type=\"application/ld+json\">\n" . wp_json_encode( $decoded ) . "\n</script>\n";
}
