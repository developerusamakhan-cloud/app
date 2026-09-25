<?php
/**
 * Portfolio projects & testimonials.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register post types and taxonomy.
 */
function nabia_register_post_types() {
	register_post_type(
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Projects', 'nabia' ),
				'singular_name' => __( 'Project', 'nabia' ),
				'add_new_item'  => __( 'Add new project', 'nabia' ),
				'edit_item'     => __( 'Edit project', 'nabia' ),
				'all_items'     => __( 'All projects', 'nabia' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'work' ),
			'menu_icon'    => 'dashicons-portfolio',
			'menu_position' => 5,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		)
	);

	register_taxonomy(
		'project_type',
		'project',
		array(
			'labels'            => array(
				'name'          => __( 'Project types', 'nabia' ),
				'singular_name' => __( 'Project type', 'nabia' ),
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'work-type' ),
		)
	);

	register_post_type(
		'testimonial',
		array(
			'labels'       => array(
				'name'          => __( 'Testimonials', 'nabia' ),
				'singular_name' => __( 'Testimonial', 'nabia' ),
				'add_new_item'  => __( 'Add new testimonial', 'nabia' ),
				'edit_item'     => __( 'Edit testimonial', 'nabia' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-format-quote',
			'menu_position'       => 6,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'nabia_register_post_types' );

/**
 * Flush rewrite rules once when the theme is activated so /work/ works immediately.
 */
function nabia_activate() {
	nabia_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'nabia_activate' );

/**
 * Meta fields per post type: key => label.
 *
 * @return array
 */
function nabia_meta_fields() {
	return array(
		'project'     => array(
			'_nabia_client' => __( 'Client', 'nabia' ),
			'_nabia_year'   => __( 'Year', 'nabia' ),
			'_nabia_role'   => __( 'Services (e.g. Design, WordPress, SEO)', 'nabia' ),
			'_nabia_url'    => __( 'Live website URL', 'nabia' ),
		),
		'testimonial' => array(
			'_nabia_author_role' => __( 'Role / company', 'nabia' ),
			'_nabia_rating'      => __( 'Rating (1–5)', 'nabia' ),
		),
	);
}

/**
 * Add meta boxes.
 */
function nabia_add_meta_boxes() {
	foreach ( array_keys( nabia_meta_fields() ) as $type ) {
		add_meta_box( 'nabia_details', __( 'Details', 'nabia' ), 'nabia_render_meta_box', $type, 'side' );
	}
}
add_action( 'add_meta_boxes', 'nabia_add_meta_boxes' );

/**
 * Render meta box.
 *
 * @param WP_Post $post Current post.
 */
function nabia_render_meta_box( $post ) {
	$fields = nabia_meta_fields();
	if ( empty( $fields[ $post->post_type ] ) ) {
		return;
	}
	wp_nonce_field( 'nabia_save_meta', 'nabia_meta_nonce' );
	foreach ( $fields[ $post->post_type ] as $key => $label ) {
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( get_post_meta( $post->ID, $key, true ) )
		);
	}
	if ( 'testimonial' === $post->post_type ) {
		echo '<p class="description">' . esc_html__( 'Title = client name, content = the quote, featured image = avatar.', 'nabia' ) . '</p>';
	}
}

/**
 * Save meta.
 *
 * @param int $post_id Post ID.
 */
function nabia_save_meta( $post_id ) {
	if ( ! isset( $_POST['nabia_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nabia_meta_nonce'] ) ), 'nabia_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = nabia_meta_fields();
	$type   = get_post_type( $post_id );
	if ( empty( $fields[ $type ] ) ) {
		return;
	}
	foreach ( array_keys( $fields[ $type ] ) as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
		$value = '_nabia_url' === $key ? esc_url_raw( $value ) : sanitize_text_field( $value );
		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post', 'nabia_save_meta' );
