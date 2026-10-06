<?php
/**
 * Blog packs: upload, store, read and delete.
 *
 * A pack is a .zip with:
 *   pack.json            optional: { "name": "...", "description": "..." }
 *   posts/*.html         one article per file (JSON details in a comment at the top)
 *   covers/<slug>.jpg    optional cover image per article (jpg, png or webp)
 *
 * Only .html, .json and image files are kept; everything else in the zip is ignored.
 * Packs are stored in wp-content/uploads/nabia-blog-packs/<pack-id>/.
 *
 * @package Nabia_Blog_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Folder that holds the packs.
 *
 * @return string
 */
function nbi_packs_dir() {
	$upload = wp_upload_dir( null, false );
	return trailingslashit( $upload['basedir'] ) . 'nabia-blog-packs';
}

/**
 * Saved packs.
 *
 * @return array id => { name, description, uploaded, count }
 */
function nbi_get_packs() {
	$packs = get_option( 'nbi_packs', array() );
	return is_array( $packs ) ? $packs : array();
}

/**
 * Store an uploaded .zip as a pack.
 *
 * @param array $file Entry from $_FILES.
 * @return string|WP_Error Pack ID.
 */
function nbi_add_pack( $file ) {
	if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return new WP_Error( 'nbi_upload', __( 'No file was uploaded.', 'nabia-blog-importer' ) );
	}
	if ( ! preg_match( '/\.zip$/i', (string) $file['name'] ) ) {
		return new WP_Error( 'nbi_type', __( 'Please upload the blog pack as a .zip file.', 'nabia-blog-importer' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	$base = nbi_packs_dir();
	$id   = sanitize_key( preg_replace( '/\.zip$/i', '', wp_basename( $file['name'] ) ) ) . '-' . gmdate( 'Ymd-His' );
	$tmp  = $base . '/tmp-' . $id;
	$dest = $base . '/' . $id;
	wp_mkdir_p( $tmp );

	$unzipped = unzip_file( $file['tmp_name'], $tmp );
	if ( is_wp_error( $unzipped ) ) {
		nbi_rmdir( $tmp );
		return $unzipped;
	}

	// Copy only the files we use, flattened into posts/ and covers/.
	wp_mkdir_p( $dest . '/posts' );
	wp_mkdir_p( $dest . '/covers' );
	file_put_contents( $dest . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$meta  = array();
	$count = 0;
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $tmp, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $files as $path => $info ) {
		if ( ! $info->isFile() || $info->isLink() ) {
			continue;
		}
		$name = sanitize_file_name( $info->getFilename() );
		if ( 0 === strpos( $name, '.' ) || false !== strpos( $path, '__MACOSX' ) ) {
			continue;
		}
		if ( preg_match( '/\.html?$/i', $name ) ) {
			$article = nbi_parse_article( file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( $article ) {
				copy( $path, $dest . '/posts/' . preg_replace( '/\.htm$/i', '.html', $name ) );
				++$count;
			}
		} elseif ( preg_match( '/\.(jpe?g|png|webp)$/i', $name ) && wp_get_image_mime( $path ) ) {
			copy( $path, $dest . '/covers/' . strtolower( $name ) );
		} elseif ( 'pack.json' === strtolower( $name ) ) {
			$meta = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	}
	nbi_rmdir( $tmp );

	if ( ! $count ) {
		nbi_rmdir( $dest );
		return new WP_Error( 'nbi_empty', __( 'No articles were found in this zip. Is it a Nabia blog pack?', 'nabia-blog-importer' ) );
	}

	$packs        = nbi_get_packs();
	$packs[ $id ] = array(
		'name'        => ! empty( $meta['name'] ) ? sanitize_text_field( $meta['name'] ) : sanitize_text_field( preg_replace( '/\.zip$/i', '', $file['name'] ) ),
		'description' => ! empty( $meta['description'] ) ? sanitize_text_field( $meta['description'] ) : '',
		'uploaded'    => time(),
		'count'       => $count,
	);
	update_option( 'nbi_packs', $packs, false );
	return $id;
}

/**
 * Delete a pack's files (posts made from it stay).
 *
 * @param string $id Pack ID.
 */
function nbi_delete_pack( $id ) {
	$packs = nbi_get_packs();
	if ( isset( $packs[ $id ] ) ) {
		nbi_rmdir( nbi_packs_dir() . '/' . $id );
		unset( $packs[ $id ] );
		update_option( 'nbi_packs', $packs, false );
	}
}

/**
 * Remove a folder and everything in it (only inside the packs folder).
 *
 * @param string $dir Folder.
 */
function nbi_rmdir( $dir ) {
	$base = wp_normalize_path( nbi_packs_dir() );
	$dir  = wp_normalize_path( $dir );
	if ( 0 !== strpos( $dir, $base . '/' ) || ! is_dir( $dir ) ) {
		return;
	}
	global $wp_filesystem;
	if ( ! $wp_filesystem ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
	}
	$wp_filesystem->delete( $dir, true );
}

/**
 * Read one article file.
 *
 * @param string $raw File contents.
 * @return array|null
 */
function nbi_parse_article( $raw ) {
	if ( ! $raw || ! preg_match( '/^\s*<!--\s*(\{.*?\})\s*-->/s', $raw, $m ) ) {
		return null;
	}
	$meta = json_decode( $m[1], true );
	if ( empty( $meta['title'] ) || empty( $meta['slug'] ) ) {
		return null;
	}
	return array_merge(
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
			'slug' => sanitize_title( $meta['slug'] ),
			'body' => trim( substr( $raw, strlen( $m[0] ) ) ),
		)
	);
}

/**
 * Articles in a pack, in file order.
 *
 * @param string $id Pack ID.
 * @return array[] Each article plus 'cover' (path or '').
 */
function nbi_pack_articles( $id ) {
	$dir   = nbi_packs_dir() . '/' . sanitize_file_name( $id );
	$files = glob( $dir . '/posts/*.html' );
	$items = array();
	if ( ! $files ) {
		return $items;
	}
	sort( $files );
	foreach ( $files as $file ) {
		$item = nbi_parse_article( file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $item ) {
			continue;
		}
		$item['cover'] = '';
		foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
			if ( file_exists( $dir . '/covers/' . $item['slug'] . '.' . $ext ) ) {
				$item['cover'] = $dir . '/covers/' . $item['slug'] . '.' . $ext;
				break;
			}
		}
		$items[ $item['slug'] ] = $item;
	}
	return $items;
}
