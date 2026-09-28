<?php
/**
 * Ready-made blog articles shipped with the theme (content/blog/*.html) and the
 * importer behind Appearance → Nabia Setup → "Import blog articles".
 *
 * Each file starts with a JSON comment (title, slug, excerpt, keyword, category, tags,
 * service, faq) followed by the article HTML. Links are written as placeholders and
 * turned into real addresses on import:
 *   {{service:web-design}}  a service page       {{page:pricing}}  pricing / audit / about ...
 *   {{post:slug}}           another article      {{contact}}  {{portfolio}}
 * A link whose page does not exist yet becomes plain text.
 *
 * Importing never touches a post you have edited. Articles that are still exactly as
 * imported are refreshed (for example with links to pages created later).
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All articles shipped with the theme, newest first.
 *
 * @return array[] Each: file, title, slug, excerpt, keyword, category, tags, service, faq, body.
 */
function nabia_blog_library() {
	$items = array();
	$files = glob( NABIA_DIR . '/content/blog/*.html' );
	if ( ! $files ) {
		return $items;
	}
	sort( $files );
	foreach ( $files as $file ) {
		$raw = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $raw || ! preg_match( '/^\s*<!--\s*(\{.*?\})\s*-->/s', $raw, $m ) ) {
			continue;
		}
		$meta = json_decode( $m[1], true );
		if ( empty( $meta['title'] ) || empty( $meta['slug'] ) ) {
			continue;
		}
		$items[] = array_merge(
			array(
				'excerpt'  => '',
				'keyword'  => '',
				'category' => '',
				'tags'     => array(),
				'service'  => '',
				'faq'      => array(),
			),
			$meta,
			array(
				'file' => $file,
				'body' => trim( substr( $raw, strlen( $m[0] ) ) ),
			)
		);
	}
	return apply_filters( 'nabia_blog_library', $items );
}

/**
 * Address for a link placeholder, or '' when that page does not exist.
 *
 * @param string $target e.g. "service:web-design", "page:pricing", "post:slug", "contact".
 * @return string
 */
function nabia_blog_target_url( $target ) {
	list( $type, $key ) = array_pad( explode( ':', $target, 2 ), 2, '' );
	switch ( $type ) {
		case 'service':
			return nabia_service_url( $key );
		case 'page':
			return nabia_page_url( $key );
		case 'post':
			$post = get_page_by_path( $key, OBJECT, 'post' );
			return ( $post && 'publish' === $post->post_status ) ? get_permalink( $post ) : '';
		case 'contact':
			$url = nabia_page_url( 'contact' );
			return $url ? $url : home_url( '/#contact' );
		case 'portfolio':
			return nabia_portfolio_url();
	}
	return '';
}

/**
 * Turn placeholders into real links (or plain text when the target is missing).
 *
 * @param string $html Article HTML.
 * @return string
 */
function nabia_blog_resolve_links( $html ) {
	return preg_replace_callback(
		'#<a href="\{\{([a-z0-9:_-]+)\}\}">(.*?)</a>#i',
		function ( $m ) {
			$url = nabia_blog_target_url( $m[1] );
			return $url ? '<a href="' . esc_url( $url ) . '">' . $m[2] . '</a>' : $m[2];
		},
		$html
	);
}

/**
 * Convert simple article HTML into editor blocks, so each paragraph, heading and list
 * is its own block in the WordPress editor.
 *
 * @param string $html Article HTML (top level p, h2, h3, ul, ol).
 * @return string
 */
function nabia_blog_to_blocks( $html ) {
	preg_match_all( '#<(p|h2|h3|ul|ol)\b[^>]*>.*?</\1>#is', $html, $found, PREG_SET_ORDER );
	$out = array();
	foreach ( $found as $el ) {
		$tag  = strtolower( $el[1] );
		$html = $el[0];
		if ( 'p' === $tag ) {
			$out[] = "<!-- wp:paragraph -->\n" . $html . "\n<!-- /wp:paragraph -->";
		} elseif ( 'h2' === $tag || 'h3' === $tag ) {
			$html  = preg_replace( '#^<(h[23])>#i', '<$1 class="wp-block-heading">', $html );
			$out[] = ( 'h2' === $tag ? '<!-- wp:heading -->' : '<!-- wp:heading {"level":3} -->' ) . "\n" . $html . "\n<!-- /wp:heading -->";
		} else {
			$items = preg_replace( '#<li>(.*?)</li>#is', "<!-- wp:list-item -->\n<li>$1</li>\n<!-- /wp:list-item -->", $html );
			$out[] = ( 'ol' === $tag ? '<!-- wp:list {"ordered":true} -->' : '<!-- wp:list -->' ) . "\n" . $items . "\n<!-- /wp:list -->";
		}
	}
	return implode( "\n\n", $out );
}

/**
 * Full post content for an article: body plus a FAQ section, links resolved, as blocks.
 *
 * @param array $item Article from nabia_blog_library().
 * @return string
 */
function nabia_blog_content( $item ) {
	$html = $item['body'];
	if ( $item['faq'] ) {
		$html .= "\n<h2>" . esc_html__( 'Frequently asked questions', 'nabia' ) . '</h2>';
		foreach ( $item['faq'] as $pair ) {
			$html .= "\n<h3>" . esc_html( $pair[0] ) . "</h3>\n<p>" . esc_html( $pair[1] ) . '</p>';
		}
	}
	return nabia_blog_to_blocks( nabia_blog_resolve_links( $html ) );
}

/**
 * Category or tag IDs, created when missing.
 *
 * @param array  $names    Term names.
 * @param string $taxonomy Taxonomy.
 * @return int[]
 */
function nabia_blog_terms( $names, $taxonomy ) {
	$ids = array();
	foreach ( array_filter( (array) $names ) as $name ) {
		$term = term_exists( $name, $taxonomy );
		if ( ! $term ) {
			$term = wp_insert_term( $name, $taxonomy );
		}
		if ( ! is_wp_error( $term ) && $term ) {
			$ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}
	return $ids;
}

/**
 * Add the article's cover image (content/blog/covers/slug.jpg) as its featured image.
 *
 * @param int   $post_id Post ID.
 * @param array $item    Article.
 */
function nabia_blog_cover( $post_id, $item ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return;
	}
	$file = NABIA_DIR . '/content/blog/covers/' . $item['slug'] . '.jpg';
	if ( ! file_exists( $file ) ) {
		return;
	}
	$upload = wp_upload_bits( $item['slug'] . '.jpg', null, file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		return;
	}
	$attachment = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $item['title'],
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$post_id
	);
	if ( ! $attachment || is_wp_error( $attachment ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment, wp_generate_attachment_metadata( $attachment, $upload['file'] ) );
	update_post_meta( $attachment, '_wp_attachment_image_alt', wp_strip_all_tags( $item['title'] ) );
	set_post_thumbnail( $post_id, $attachment );
}

/**
 * Save SEO details (focus keyword, description, FAQ) on the post.
 *
 * @param int   $post_id Post ID.
 * @param array $item    Article.
 */
function nabia_blog_meta( $post_id, $item ) {
	update_post_meta( $post_id, '_nabia_article', $item['slug'] );
	update_post_meta( $post_id, '_nabia_faq', $item['faq'] );
	if ( $item['service'] ) {
		update_post_meta( $post_id, '_nabia_service', $item['service'] );
	}
	if ( $item['keyword'] ) {
		update_post_meta( $post_id, '_nabia_keyword', $item['keyword'] );
		// Picked up by Yoast SEO and Rank Math when they are installed.
		update_post_meta( $post_id, '_yoast_wpseo_focuskw', $item['keyword'] );
		update_post_meta( $post_id, 'rank_math_focus_keyword', $item['keyword'] );
	}
	if ( $item['excerpt'] ) {
		update_post_meta( $post_id, '_yoast_wpseo_metadesc', $item['excerpt'] );
		update_post_meta( $post_id, 'rank_math_description', $item['excerpt'] );
	}
}

/**
 * Status of every article: not imported, imported (unchanged) or edited by you.
 *
 * @return array slug => array( post, state ).
 */
function nabia_blog_status() {
	$status = array();
	foreach ( nabia_blog_library() as $item ) {
		$post  = get_page_by_path( $item['slug'], OBJECT, 'post' );
		$state = 'missing';
		if ( $post && 'trash' !== $post->post_status ) {
			$hash  = get_post_meta( $post->ID, '_nabia_import_hash', true );
			$state = ( $hash && md5( $post->post_content ) === $hash ) ? 'imported' : 'edited';
		} else {
			$post = null;
		}
		$status[ $item['slug'] ] = array( $post, $state );
	}
	return $status;
}

/**
 * Import the articles. New ones are published; unchanged imported ones are refreshed;
 * posts you edited (or your own posts with the same address) are left alone.
 *
 * @return array Log lines.
 */
function nabia_setup_import_posts() {
	$log     = array();
	$library = nabia_blog_library();
	$status  = nabia_blog_status();
	$now     = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
	$created = array();

	// Pass 1: create missing posts (so articles can link to each other in pass 2).
	foreach ( $library as $index => $item ) {
		list( $post, $state ) = $status[ $item['slug'] ];
		if ( 'missing' !== $state ) {
			continue;
		}
		// Newest first in the order of the files, one day apart.
		$date = gmdate( 'Y-m-d H:i:s', $now - $index * DAY_IN_SECONDS );
		$id   = wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_title'    => $item['title'],
				'post_name'     => $item['slug'],
				'post_excerpt'  => $item['excerpt'],
				'post_content'  => '',
				'post_date'     => $date,
				'post_category' => nabia_blog_terms( array( $item['category'] ), 'category' ),
				'tags_input'    => $item['tags'],
			),
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			/* translators: %s: article title */
			$log[] = sprintf( __( 'Could not create article: %s', 'nabia' ), $item['title'] );
			continue;
		}
		$created[ $item['slug'] ] = $id;
	}

	// Pass 2: write content with resolved links, SEO details and cover images.
	foreach ( $library as $item ) {
		list( $post, $state ) = $status[ $item['slug'] ];
		if ( isset( $created[ $item['slug'] ] ) ) {
			$id = $created[ $item['slug'] ];
		} elseif ( 'imported' === $state ) {
			$id = $post->ID;
		} else {
			/* translators: %s: article title */
			$log[] = sprintf( __( 'Kept your version: %s', 'nabia' ), $item['title'] );
			continue;
		}
		$content = nabia_blog_content( $item );
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => $content,
			)
		);
		update_post_meta( $id, '_nabia_import_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
		nabia_blog_meta( $id, $item );
		nabia_blog_cover( $id, $item );
		$log[] = isset( $created[ $item['slug'] ] )
			/* translators: %s: article title */
			? sprintf( __( 'Published article: %s', 'nabia' ), $item['title'] )
			/* translators: %s: article title */
			: sprintf( __( 'Refreshed article: %s', 'nabia' ), $item['title'] );
	}

	$blog = nabia_setup_blog_page();
	if ( $blog ) {
		$log[] = $blog;
	}
	return $log;
}

/**
 * Make sure there is a Blog page that lists the posts.
 *
 * @return string Log line, or '' when nothing changed.
 */
function nabia_setup_blog_page() {
	if ( (int) get_option( 'page_for_posts' ) ) {
		return '';
	}
	$page = nabia_setup_find( 'blog' );
	if ( ! $page ) {
		$page = get_page_by_path( 'blog' );
	}
	$id = $page ? $page->ID : wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => __( 'Blog', 'nabia' ),
			'post_name'   => 'blog',
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return '';
	}
	update_post_meta( $id, '_nabia_page', 'blog' );

	// The homepage design is front-page.php either way; a static front page lets the
	// Blog page list the articles.
	if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
		$home = nabia_setup_find( 'home' );
		if ( ! $home ) {
			$home_id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => __( 'Home', 'nabia' ),
					'post_name'   => 'home',
				)
			);
			if ( $home_id && ! is_wp_error( $home_id ) ) {
				update_post_meta( $home_id, '_nabia_page', 'home' );
			}
		} else {
			$home_id = $home->ID;
		}
		if ( ! empty( $home_id ) && ! is_wp_error( $home_id ) ) {
			update_option( 'page_on_front', (int) $home_id );
			update_option( 'show_on_front', 'page' );
		}
	}
	update_option( 'page_for_posts', (int) $id );
	return __( 'Blog page created and set as your posts page (Settings → Reading).', 'nabia' );
}
