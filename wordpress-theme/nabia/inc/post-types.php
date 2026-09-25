<?php
/**
 * Post types: Websites (portfolio), Testimonials and Video Reviews.
 *
 * The theme's older "Projects" type only appears if it already has posts or is chosen
 * as the portfolio in the Customizer — "Websites" is the portfolio by default.
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
	if ( nabia_projects_enabled() ) {
		nabia_register_projects();
	}

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

	register_post_type(
		'video_review',
		array(
			'labels'              => array(
				'name'          => __( 'Video Reviews', 'nabia' ),
				'singular_name' => __( 'Video Review', 'nabia' ),
				'add_new'       => __( 'Add video review', 'nabia' ),
				'add_new_item'  => __( 'Add video review', 'nabia' ),
				'edit_item'     => __( 'Edit video review', 'nabia' ),
				'all_items'     => __( 'All video reviews', 'nabia' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-video-alt3',
			'menu_position'       => 7,
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'nabia_register_post_types' );

/**
 * Show the theme's own "Projects" type only when it's needed.
 *
 * @return bool
 */
function nabia_projects_enabled() {
	if ( 'project' === get_theme_mod( 'portfolio_post_type', 'websites' ) ) {
		return true;
	}
	$count = get_transient( 'nabia_has_projects' );
	if ( false === $count ) {
		global $wpdb;
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('auto-draft','trash')", 'project' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		set_transient( 'nabia_has_projects', $count, DAY_IN_SECONDS );
	}
	return $count > 0;
}

/**
 * Register the legacy "Projects" post type and its taxonomy.
 */
function nabia_register_projects() {
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
}

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
		'video_review' => array(
			'_nabia_youtube'       => __( 'Video link: MP4 from your Media Library, or a YouTube link', 'nabia' ),
			'_nabia_video_role'    => __( 'Company / role', 'nabia' ),
			'_nabia_video_caption' => __( 'Short caption, e.g. “New store in 2 weeks”', 'nabia' ),
		),
	);
}

/**
 * Add meta boxes.
 */
function nabia_add_meta_boxes() {
	foreach ( array_keys( nabia_meta_fields() ) as $type ) {
		add_meta_box( 'nabia_details', __( 'Details', 'nabia' ), 'nabia_render_meta_box', $type, 'video_review' === $type ? 'normal' : 'side', 'high' );
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
	if ( 'video_review' === $post->post_type ) {
		echo '<p class="description">' . esc_html__( 'Title = client name. Upload the MP4 in Media → Add New, copy its “File URL” and paste it above (YouTube links work too). Use “Order” to choose which video shows first.', 'nabia' ) . '</p>';
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
		$value = in_array( $key, array( '_nabia_url', '_nabia_youtube' ), true ) ? esc_url_raw( $value ) : sanitize_text_field( $value );
		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post', 'nabia_save_meta' );

/**
 * The "Websites" portfolio post type (your ?post_type=websites posts), built into the theme
 * so it no longer depends on a plugin. If a plugin (e.g. Custom Post Type UI) still registers
 * it, that registration simply takes over — the posts are the same either way.
 */
function nabia_register_websites() {
	register_post_type(
		'websites',
		array(
			'labels'        => array(
				'name'               => __( 'Websites', 'nabia' ),
				'singular_name'      => __( 'Website', 'nabia' ),
				'menu_name'          => __( 'Websites', 'nabia' ),
				'all_items'          => __( 'All Websites', 'nabia' ),
				'add_new'            => __( 'Add new Website', 'nabia' ),
				'add_new_item'       => __( 'Add new Website', 'nabia' ),
				'edit_item'          => __( 'Edit Website', 'nabia' ),
				'new_item'           => __( 'New Website', 'nabia' ),
				'view_item'          => __( 'View Website', 'nabia' ),
				'search_items'       => __( 'Search Websites', 'nabia' ),
				'not_found'          => __( 'No websites found', 'nabia' ),
				'not_found_in_trash' => __( 'No websites found in Trash', 'nabia' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => array(
				'slug'       => 'websites',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-admin-site-alt3',
			'menu_position' => 5,
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes', 'revisions' ),
		)
	);
}
add_action( 'init', 'nabia_register_websites', 5 );

/**
 * Categories for Websites.
 *
 * Re-attaches whatever taxonomy your existing Websites posts already use (so their
 * categories keep working without the plugin); otherwise registers "website_category".
 */
function nabia_register_websites_taxonomies() {
	$taxonomies = get_transient( 'nabia_websites_taxonomies' );
	if ( false === $taxonomies ) {
		global $wpdb;
		$taxonomies = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT DISTINCT tt.taxonomy FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
				WHERE p.post_type = %s",
				'websites'
			)
		);
		$taxonomies = array_values( array_diff( (array) $taxonomies, array( 'post_format', 'post_translations', 'language' ) ) );
		set_transient( 'nabia_websites_taxonomies', $taxonomies, DAY_IN_SECONDS );
	}
	if ( ! $taxonomies ) {
		$taxonomies = array( 'website_category' );
	}

	foreach ( $taxonomies as $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			register_taxonomy_for_object_type( $taxonomy, 'websites' );
			continue;
		}
		register_taxonomy(
			$taxonomy,
			'websites',
			array(
				'labels'            => array(
					'name'          => __( 'Categories', 'nabia' ),
					'singular_name' => __( 'Category', 'nabia' ),
					'menu_name'     => __( 'Categories', 'nabia' ),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => str_replace( '_', '-', $taxonomy ) ),
			)
		);
	}
}
add_action( 'init', 'nabia_register_websites_taxonomies', 6 );

/**
 * Refresh permalinks once after each theme update so /websites/ URLs always work.
 */
function nabia_maybe_flush_rewrites() {
	if ( get_option( 'nabia_rewrite_version' ) !== NABIA_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'nabia_rewrite_version', NABIA_VERSION );
		delete_transient( 'nabia_websites_taxonomies' );
		delete_transient( 'nabia_has_projects' );
	}
}
add_action( 'init', 'nabia_maybe_flush_rewrites', 99 );

/**
 * The post type shown as the portfolio ("websites" by default, falls back to the theme's Projects).
 *
 * @return string
 */
function nabia_portfolio_type() {
	$type = nabia_mod( 'portfolio_post_type' );
	return ( $type && post_type_exists( $type ) ) ? $type : 'websites';
}

/**
 * First taxonomy attached to the portfolio post type (used for filters and card labels).
 *
 * @return string Taxonomy name or empty string.
 */
function nabia_portfolio_taxonomy() {
	$taxonomies = get_object_taxonomies( nabia_portfolio_type(), 'objects' );
	foreach ( $taxonomies as $taxonomy ) {
		if ( $taxonomy->public && $taxonomy->show_ui && 'post_format' !== $taxonomy->name ) {
			return $taxonomy->name;
		}
	}
	return '';
}

/**
 * Link to the full portfolio listing.
 *
 * @return string
 */
function nabia_portfolio_url() {
	$type = nabia_portfolio_type();
	$link = get_post_type_archive_link( $type );
	return $link ? $link : add_query_arg( 'post_type', $type, home_url( '/' ) );
}

/**
 * Live website URL of a portfolio item, from the theme field or common custom-field names.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function nabia_project_live_url( $post_id ) {
	$keys = apply_filters( 'nabia_live_url_meta_keys', array( '_nabia_url', 'website_url', 'website_link', 'live_url', 'site_url', 'project_url', 'url', 'link' ) );
	foreach ( $keys as $key ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( is_string( $value ) && preg_match( '#^https?://#i', $value ) ) {
			return $value;
		}
	}
	return '';
}

/**
 * Use the theme's portfolio templates for whichever post type is the portfolio.
 *
 * @param string[] $templates Candidate templates.
 * @return string[]
 */
function nabia_portfolio_archive_templates( $templates ) {
	if ( is_post_type_archive( nabia_portfolio_type() ) || ( nabia_portfolio_taxonomy() && is_tax( nabia_portfolio_taxonomy() ) ) ) {
		array_unshift( $templates, 'archive-project.php' );
	}
	return $templates;
}
add_filter( 'archive_template_hierarchy', 'nabia_portfolio_archive_templates' );
add_filter( 'taxonomy_template_hierarchy', 'nabia_portfolio_archive_templates' );

/**
 * Case-study layout for single portfolio items.
 *
 * @param string[] $templates Candidate templates.
 * @return string[]
 */
function nabia_portfolio_single_templates( $templates ) {
	if ( is_singular( nabia_portfolio_type() ) ) {
		array_unshift( $templates, 'single-project.php' );
	}
	return $templates;
}
add_filter( 'single_template_hierarchy', 'nabia_portfolio_single_templates' );

/**
 * "?post_type=websites" without an archive still gets the portfolio grid.
 *
 * @param string $template Template path.
 * @return string
 */
function nabia_portfolio_query_template( $template ) {
	$type = nabia_portfolio_type();
	if ( ! is_admin() && ! is_singular() && ! is_post_type_archive() && get_query_var( 'post_type' ) === $type ) {
		$archive = locate_template( 'archive-project.php' );
		if ( $archive ) {
			return $archive;
		}
	}
	return $template;
}
add_filter( 'template_include', 'nabia_portfolio_query_template' );

/**
 * Show the YouTube thumbnail in the Video Reviews list.
 *
 * @param array $columns Columns.
 * @return array
 */
function nabia_video_review_columns( $columns ) {
	return array_slice( $columns, 0, 1, true ) + array( 'nabia_thumb' => __( 'Video', 'nabia' ) ) + array_slice( $columns, 1, null, true );
}
add_filter( 'manage_video_review_posts_columns', 'nabia_video_review_columns' );

/**
 * Render the thumbnail column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function nabia_video_review_column( $column, $post_id ) {
	if ( 'nabia_thumb' !== $column ) {
		return;
	}
	$url = (string) get_post_meta( $post_id, '_nabia_youtube', true );
	if ( nabia_is_video_file( $url ) ) {
		printf( '<video src="%s#t=0.5" width="90" preload="metadata" muted style="border-radius:6px;background:#000"></video>', esc_url( $url ) );
		return;
	}
	$id = nabia_youtube_id( $url );
	if ( $id ) {
		printf( '<img src="%s" alt="" width="120" style="border-radius:6px">', esc_url( 'https://i.ytimg.com/vi/' . $id . '/mqdefault.jpg' ) );
	} else {
		echo '<em>' . esc_html__( 'No valid YouTube link', 'nabia' ) . '</em>';
	}
}
add_action( 'manage_video_review_posts_custom_column', 'nabia_video_review_column', 10, 2 );
