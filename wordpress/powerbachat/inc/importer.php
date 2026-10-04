<?php
/**
 * Starter content importer: Appearance → PowerBachat content.
 *
 * Pages and guide posts ship as HTML files in /content/{country}/. Each file starts
 * with a JSON header inside an HTML comment:
 *
 * <!-- powerbachat
 * { "type": "page", "title": "…", "slug": "lesco-bill-calculator", "parent": "pk",
 *   "template": "page-templates/calculator.php", "excerpt": "…",
 *   "seo_title": "…", "meta_description": "…", "focus_keyword": "…" }
 * -->
 *
 * Nothing is overwritten unless you ask: "Create missing" only adds new items, and
 * an item you edited in WordPress is flagged so a re-import does not surprise you.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read every content file.
 *
 * @return array[] Items keyed by "type:path".
 */
function powerbachat_content_items() {
	$items = array();
	foreach ( glob( POWERBACHAT_DIR . '/content/*/*.html' ) as $file ) {
		$raw = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! preg_match( '/^\s*<!--\s*powerbachat\s*(\{.*?\})\s*-->\s*/s', $raw, $m ) ) {
			continue;
		}
		$meta = json_decode( $m[1], true );
		if ( ! is_array( $meta ) || empty( $meta['slug'] ) || empty( $meta['title'] ) ) {
			continue;
		}
		$meta = wp_parse_args(
			$meta,
			array(
				'type'             => 'page',
				'parent'           => '',
				'template'         => '',
				'excerpt'          => '',
				'seo_title'        => '',
				'meta_description' => '',
				'focus_keyword'    => '',
				'category'         => '',
				'order'            => 0,
				'secondary_keywords' => array(),
				'schema_type'      => '',
			)
		);
		$meta['body']  = trim( substr( $raw, strlen( $m[0] ) ) );
		$meta['file']  = basename( dirname( $file ) ) . '/' . basename( $file );
		$meta['path']  = trim( $meta['parent'] . '/' . $meta['slug'], '/' );
		$meta['words'] = str_word_count( wp_strip_all_tags( preg_replace( '/\[[^\]]+\]/', '', $meta['body'] ) ) );
		$items[ $meta['type'] . ':' . $meta['path'] ] = $meta;
	}
	// Parents before children.
	uasort(
		$items,
		function ( $a, $b ) {
			return substr_count( $a['path'], '/' ) - substr_count( $b['path'], '/' ) ?: strcmp( $a['path'], $b['path'] );
		}
	);
	return $items;
}

/**
 * Turn the plain HTML of a content file into block markup, one block per chunk.
 *
 * @param string $html Body HTML (top-level elements separated by blank lines).
 * @return string
 */
function powerbachat_to_blocks( $html ) {
	$out = array();
	foreach ( preg_split( '/\n\s*\n/', trim( $html ) ) as $chunk ) {
		$chunk = trim( $chunk );
		if ( '' === $chunk ) {
			continue;
		}
		if ( '[' === $chunk[0] ) {
			$out[] = "<!-- wp:shortcode -->\n" . $chunk . "\n<!-- /wp:shortcode -->";
		} elseif ( preg_match( '/^<h([2-4])/', $chunk, $m ) ) {
			$attrs = '2' === $m[1] ? '' : ' {"level":' . $m[1] . '}';
			$chunk = preg_replace( '/^<h' . $m[1] . '(?![^>]*class=)/', '<h' . $m[1] . ' class="wp-block-heading"', $chunk );
			$out[] = '<!-- wp:heading' . $attrs . " -->\n" . $chunk . "\n<!-- /wp:heading -->";
		} elseif ( preg_match( '/^<p class="([^"]+)"/', $chunk, $m ) ) {
			$out[] = '<!-- wp:paragraph {"className":"' . esc_attr( $m[1] ) . '"} -->' . "\n" . $chunk . "\n<!-- /wp:paragraph -->";
		} elseif ( 0 === strpos( $chunk, '<p' ) ) {
			$out[] = "<!-- wp:paragraph -->\n" . $chunk . "\n<!-- /wp:paragraph -->";
		} else {
			$out[] = "<!-- wp:html -->\n" . $chunk . "\n<!-- /wp:html -->";
		}
	}
	return implode( "\n\n", $out );
}

/**
 * Find an existing post for an item.
 *
 * @param array $item Content item.
 * @return WP_Post|null
 */
function powerbachat_content_existing( $item ) {
	if ( 'page' === $item['type'] ) {
		$post = get_page_by_path( $item['path'], OBJECT, 'page' );
		return $post instanceof WP_Post ? $post : null;
	}
	$found = get_posts(
		array(
			'name'           => $item['slug'],
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => 1,
		)
	);
	return $found ? $found[0] : null;
}

/**
 * Create or update one item.
 *
 * @param array $item      Content item.
 * @param bool  $overwrite Replace content of an existing post.
 * @return string created|updated|skipped|error
 */
function powerbachat_import_item( $item, $overwrite, $publish_at = '' ) {
	// Imported guides must not trigger "new guide" alert emails.
	$GLOBALS['powerbachat_importing'] = true;
	try {
		return powerbachat_import_item_run( $item, $overwrite, $publish_at );
	} finally {
		$GLOBALS['powerbachat_importing'] = false;
	}
}

/**
 * Worker for powerbachat_import_item().
 *
 * @param array  $item       Content item.
 * @param bool   $overwrite  Replace content of an existing post.
 * @param string $publish_at Local date to schedule a new post for, or ''.
 * @return string created|updated|skipped|error
 */
function powerbachat_import_item_run( $item, $overwrite, $publish_at ) {
	$existing = powerbachat_content_existing( $item );
	// WordPress creates a draft "Privacy Policy" page on install. Replace it.
	$wp_draft = $existing && 'page' === $item['type'] && in_array( $existing->post_status, array( 'draft', 'auto-draft' ), true ) && ! get_post_meta( $existing->ID, '_pb_content_file', true );
	if ( $wp_draft ) {
		$overwrite = true;
	}
	if ( $existing && ! $overwrite ) {
		return 'skipped';
	}

	$content = powerbachat_to_blocks( $item['body'] );
	$postarr = array(
		'post_type'    => $item['type'],
		'post_status'  => 'publish',
		'comment_status' => 'closed',
		'ping_status'  => 'closed',
		'post_title'   => $item['title'],
		'post_name'    => $item['slug'],
		'post_content' => $content,
		'post_excerpt' => $item['excerpt'],
		'menu_order'   => (int) $item['order'],
	);

	if ( 'page' === $item['type'] && $item['parent'] ) {
		$parent = get_page_by_path( $item['parent'], OBJECT, 'page' );
		if ( ! $parent ) {
			return 'error';
		}
		$postarr['post_parent'] = $parent->ID;
	}

	if ( 'post' === $item['type'] && $item['category'] ) {
		$term = get_category_by_slug( sanitize_title( $item['category'] ) );
		if ( ! $term ) {
			$made = wp_insert_term( ucwords( str_replace( '-', ' ', $item['category'] ) ), 'category', array( 'slug' => sanitize_title( $item['category'] ) ) );
			$tid  = is_wp_error( $made ) ? 0 : (int) $made['term_id'];
		} else {
			$tid = (int) $term->term_id;
		}
		if ( $tid ) {
			$postarr['post_category'] = array( $tid );
		}
	}

	if ( $existing ) {
		// Keep the status and date an existing item already has (published or scheduled).
		$postarr['post_status'] = $wp_draft ? 'publish' : $existing->post_status;
		unset( $postarr['post_date'] );
	} elseif ( $publish_at ) {
		$postarr['post_status']   = 'future';
		$postarr['post_date']     = $publish_at;
		$postarr['post_date_gmt'] = get_gmt_from_date( $publish_at );
	}

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$id            = wp_update_post( wp_slash( $postarr ), true );
	} else {
		$id = wp_insert_post( wp_slash( $postarr ), true );
	}
	if ( is_wp_error( $id ) ) {
		return 'error';
	}

	if ( 'page' === $item['type'] ) {
		update_post_meta( $id, '_wp_page_template', $item['template'] ? $item['template'] : 'default' );
	}
	$secondary = array_values( array_filter( array_map( 'sanitize_text_field', (array) $item['secondary_keywords'] ) ) );
	$all_kw    = array_merge( array( $item['focus_keyword'] ), $secondary );
	$seo       = array(
		'pb_secondary_keywords'    => implode( ', ', $secondary ),
		'pb_schema_type'           => $item['schema_type'],
		'_yoast_wpseo_focuskeywords' => $secondary ? wp_json_encode(
			array_map(
				function ( $kw ) {
					return array(
						'keyword' => $kw,
						'score'   => 0,
					);
				},
				$secondary
			)
		) : '',
		'pb_seo_title'             => $item['seo_title'],
		'pb_meta_description'      => $item['meta_description'],
		'pb_focus_keyword'         => $item['focus_keyword'],
		// Yoast SEO.
		'_yoast_wpseo_title'       => $item['seo_title'],
		'_yoast_wpseo_metadesc'    => $item['meta_description'],
		'_yoast_wpseo_focuskw'     => $item['focus_keyword'],
		// Rank Math.
		'rank_math_title'          => $item['seo_title'],
		'rank_math_description'    => $item['meta_description'],
		'rank_math_focus_keyword'  => implode( ',', array_filter( $all_kw ) ),
		// All in One SEO (legacy meta) and SEOPress.
		'_aioseo_title'            => $item['seo_title'],
		'_aioseo_description'      => $item['meta_description'],
		'_seopress_titles_title'   => $item['seo_title'],
		'_seopress_titles_desc'    => $item['meta_description'],
		'_seopress_analysis_target_kw' => implode( ',', array_filter( $all_kw ) ),
	);
	foreach ( $seo as $key => $value ) {
		if ( '' !== $value ) {
			update_post_meta( $id, $key, $value );
		}
	}
	update_post_meta( $id, '_pb_content_file', $item['file'] );
	powerbachat_import_cover( $id, $item );
	if ( 'page' === $item['type'] && 'privacy-policy' === $item['path'] ) {
		update_option( 'wp_page_for_privacy_policy', $id );
	}
	update_post_meta( $id, '_pb_content_hash', md5( $content ) );

	return $existing ? 'updated' : 'created';
}

/**
 * Publishing plan for posts: the first N (by their "order" field) go live now, the
 * rest are scheduled one every X days at 9:00 site time, starting tomorrow-plus-X.
 * Pages are never scheduled, because the home page, menus and footer link to them.
 *
 * @param array $items Content items.
 * @return array Map of item key => 'Y-m-d H:i:s' local date, or '' for publish now.
 */
function powerbachat_schedule_plan( $items ) {
	$now_count = (int) get_option( 'powerbachat_publish_now', 15 );
	$every     = max( 1, (int) get_option( 'powerbachat_publish_every', 2 ) );
	$posts     = array_filter(
		$items,
		function ( $item ) {
			return 'post' === $item['type'];
		}
	);
	uasort(
		$posts,
		function ( $a, $b ) {
			return (int) $a['order'] - (int) $b['order'] ?: strcmp( $a['file'], $b['file'] );
		}
	);
	$plan  = array();
	$i     = 0;
	$start = new DateTime( 'today 09:00', wp_timezone() );
	foreach ( array_keys( $posts ) as $key ) {
		if ( $i < $now_count ) {
			$plan[ $key ] = '';
		} else {
			$when = clone $start;
			$when->modify( '+' . ( ( $i - $now_count + 1 ) * $every ) . ' days' );
			$plan[ $key ] = $when->format( 'Y-m-d H:i:s' );
		}
		++$i;
	}
	return $plan;
}

/**
 * Admin screen.
 */
function powerbachat_content_menu() {
	add_theme_page(
		__( 'PowerBachat content', 'powerbachat' ),
		__( 'PowerBachat content', 'powerbachat' ),
		'edit_theme_options',
		'powerbachat-content',
		'powerbachat_content_screen'
	);
}
add_action( 'admin_menu', 'powerbachat_content_menu' );

/**
 * Handle the import form.
 */
function powerbachat_content_handle() {
	if ( ! isset( $_POST['powerbachat_import'] ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	check_admin_referer( 'powerbachat_import' );
	$mode  = sanitize_key( wp_unslash( $_POST['powerbachat_import'] ) );
	if ( isset( $_POST['publish_now'] ) ) {
		update_option( 'powerbachat_publish_now', max( 0, absint( $_POST['publish_now'] ) ) );
	}
	if ( isset( $_POST['publish_every'] ) ) {
		update_option( 'powerbachat_publish_every', max( 1, absint( $_POST['publish_every'] ) ) );
	}
	$only  = isset( $_POST['item'] ) ? sanitize_text_field( wp_unslash( $_POST['item'] ) ) : '';
	$items = powerbachat_content_items();
	$tally = array(
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'error'   => 0,
	);
	$plan  = powerbachat_schedule_plan( $items );
	foreach ( $items as $key => $item ) {
		if ( $only && $only !== $key ) {
			continue;
		}
		$result = powerbachat_import_item( $item, 'overwrite' === $mode, isset( $plan[ $key ] ) ? $plan[ $key ] : '' );
		++$tally[ $result ];
	}
	flush_rewrite_rules( false );
	set_transient( 'powerbachat_import_result', $tally, 60 );
	wp_safe_redirect( admin_url( 'themes.php?page=powerbachat-content' ) );
	exit;
}
add_action( 'admin_init', 'powerbachat_content_handle' );

/**
 * Render the screen.
 */
function powerbachat_content_screen() {
	$items  = powerbachat_content_items();
	$result = get_transient( 'powerbachat_import_result' );
	delete_transient( 'powerbachat_import_result' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'PowerBachat content', 'powerbachat' ); ?></h1>
		<p><?php esc_html_e( 'Ready-written pages and guides that ship with the theme. "Create missing" only adds what is not on your site yet. Your own edits are never touched unless you re-import that item.', 'powerbachat' ); ?></p>
		<?php if ( $result ) : ?>
			<div class="notice notice-success"><p>
				<?php
				/* translators: 1: created, 2: updated, 3: skipped, 4: errors */
				printf( esc_html__( 'Done. Created %1$d, updated %2$d, skipped %3$d, errors %4$d.', 'powerbachat' ), (int) $result['created'], (int) $result['updated'], (int) $result['skipped'], (int) $result['error'] );
				?>
			</p></div>
		<?php endif; ?>
		<form method="post" style="margin:16px 0;display:flex;flex-wrap:wrap;gap:12px;align-items:center">
			<?php wp_nonce_field( 'powerbachat_import' ); ?>
			<label><?php esc_html_e( 'Publish the first', 'powerbachat' ); ?>
				<input type="number" name="publish_now" min="0" max="500" value="<?php echo esc_attr( get_option( 'powerbachat_publish_now', 15 ) ); ?>" style="width:70px">
				<?php esc_html_e( 'posts now, then schedule one every', 'powerbachat' ); ?>
				<input type="number" name="publish_every" min="1" max="30" value="<?php echo esc_attr( get_option( 'powerbachat_publish_every', 2 ) ); ?>" style="width:60px">
				<?php esc_html_e( 'days at 9:00. Pages always go live now.', 'powerbachat' ); ?>
			</label>
			<button class="button button-primary" name="powerbachat_import" value="missing"><?php esc_html_e( 'Create missing pages and posts', 'powerbachat' ); ?></button>
		</form>
		<table class="widefat striped">
			<thead><tr>
				<th><?php esc_html_e( 'Title', 'powerbachat' ); ?></th>
				<th><?php esc_html_e( 'Type', 'powerbachat' ); ?></th>
				<th><?php esc_html_e( 'URL', 'powerbachat' ); ?></th>
				<th><?php esc_html_e( 'Words', 'powerbachat' ); ?></th>
				<th><?php esc_html_e( 'Keywords (primary, secondary)', 'powerbachat' ); ?></th>
				<th><?php esc_html_e( 'Status', 'powerbachat' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php foreach ( $items as $key => $item ) : ?>
				<?php
				$post   = powerbachat_content_existing( $item );
				$status = __( 'Not created', 'powerbachat' );
				if ( $post ) {
					$status = md5( $post->post_content ) === get_post_meta( $post->ID, '_pb_content_hash', true ) ? __( 'Created', 'powerbachat' ) : __( 'Edited on site', 'powerbachat' );
					if ( 'future' === $post->post_status ) {
						/* translators: %s: date */
						$status .= ', ' . sprintf( __( 'scheduled for %s', 'powerbachat' ), get_the_date( 'j M Y', $post ) );
					}
				}
				$url = 'page' === $item['type'] ? '/' . $item['path'] . '/' : '/' . $item['slug'] . '/';
				?>
				<tr>
					<td><strong><?php echo esc_html( $item['title'] ); ?></strong></td>
					<td><?php echo esc_html( $item['type'] ); ?></td>
					<td><?php if ( $post ) : ?><a href="<?php echo esc_url( get_permalink( $post ) ); ?>" target="_blank"><?php echo esc_html( $url ); ?></a><?php else : ?><code><?php echo esc_html( $url ); ?></code><?php endif; ?></td>
					<td><?php echo esc_html( number_format_i18n( $item['words'] ) ); ?></td>
					<td><strong><?php echo esc_html( $item['focus_keyword'] ); ?></strong><?php if ( $item['secondary_keywords'] ) : ?><br><small><?php echo esc_html( implode( ', ', (array) $item['secondary_keywords'] ) ); ?></small><?php endif; ?></td>
					<td><?php echo esc_html( $status ); ?></td>
					<td>
						<form method="post" onsubmit="return <?php echo $post ? "confirm('" . esc_js( __( 'Replace this item with the theme version?', 'powerbachat' ) ) . "')" : 'true'; ?>;">
							<?php wp_nonce_field( 'powerbachat_import' ); ?>
							<input type="hidden" name="item" value="<?php echo esc_attr( $key ); ?>">
							<button class="button button-small" name="powerbachat_import" value="overwrite"><?php echo $post ? esc_html__( 'Re-import', 'powerbachat' ) : esc_html__( 'Create', 'powerbachat' ); ?></button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Attach the shipped cover image (content/images/{slug}.jpg) as the featured image,
 * unless the post already has one.
 *
 * @param int   $id   Post ID.
 * @param array $item Content item.
 */
function powerbachat_import_cover( $id, $item ) {
	$file = POWERBACHAT_DIR . '/content/images/' . sanitize_file_name( $item['slug'] ) . '.jpg';
	if ( ! file_exists( $file ) || has_post_thumbnail( $id ) ) {
		return;
	}
	$upload = wp_upload_bits( basename( $file ), null, file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! empty( $upload['error'] ) ) {
		return;
	}
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $item['title'],
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$id
	);
	if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $item['title'] );
	set_post_thumbnail( $id, $attachment_id );
}
