<?php
/**
 * Public download of the free plugin (/?twh_free_zip=1).
 *
 * @package TalkwynHub
 */

namespace TWH\Download;

use TWH\Api\RateLimiter;
use TWH\Api\RestController;
use TWH\Repository\Events;
use TWH\Repository\Products;
use TWH\Repository\Releases;
use TWH\Support\Request;
use TWH\Support\Secrets;
use TWH\Support\Storage;

defined( 'ABSPATH' ) || exit;

/**
 * The free plugin is GPL and public, so anyone may download it. It is served from the
 * latest active stable release of the product with the slug "talkwyn" (filter
 * twh_free_product). Runs with or without WooCommerce.
 */
final class FreeDownload {

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'template_redirect', array( self::class, 'maybe_serve' ), 4 );
	}

	/**
	 * Slug of the free product.
	 */
	public static function product_slug(): string {
		return (string) apply_filters( 'twh_free_product', 'talkwyn' );
	}

	/**
	 * Public download URL used by the website's download page.
	 */
	public static function url(): string {
		return add_query_arg( 'twh_free_zip', '1', home_url( '/' ) );
	}

	/**
	 * Whether the free download works right now, and why not.
	 *
	 * @return array{ok: bool, code: string, message: string, product: array<string, mixed>|null, release: array<string, mixed>|null, path: string|null}
	 */
	public static function status(): array {
		$out     = array(
			'ok'      => false,
			'code'    => '',
			'message' => '',
			'product' => null,
			'release' => null,
			'path'    => null,
		);
		$product = Products::find_by_slug( self::product_slug() );
		if ( ! $product ) {
			$out['code'] = 'no_product';
			/* translators: %s: product slug */
			$out['message'] = sprintf( __( 'There is no product with the slug "%s". Create it under Products, then upload the free plugin ZIP as its release.', 'talkwyn-hub' ), self::product_slug() );
			return $out;
		}
		$out['product'] = $product;
		$release        = Releases::latest( (int) $product['id'], 'stable' );
		if ( ! $release ) {
			$out['code']    = 'no_release';
			$out['message'] = __( 'The free plugin has no active stable release. Upload one, or switch an existing release to Active and channel Stable.', 'talkwyn-hub' );
			return $out;
		}
		$out['release'] = $release;
		$path           = Storage::path( (string) $release['zip_path'] );
		if ( null === $path || ! is_readable( $path ) ) {
			$out['code'] = 'file_missing';
			/* translators: %s: version */
			$out['message'] = sprintf( __( 'The ZIP file of version %s is missing or not readable on the server. Upload the release again.', 'talkwyn-hub' ), (string) $release['version'] );
			return $out;
		}
		$out['path']    = $path;
		$out['ok']      = true;
		$out['code']    = 'ok';
		$out['message'] = __( 'Working.', 'talkwyn-hub' );
		return $out;
	}

	/**
	 * Serve the ZIP when the request asks for it.
	 */
	public static function maybe_serve(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GPL download, no state change.
		if ( empty( $_GET['twh_free_zip'] ) ) {
			return;
		}
		self::serve();
	}

	/**
	 * Stream the latest free release, or send the visitor back to the download page
	 * with a reason the page can show.
	 */
	public static function serve(): void {
		$status = self::status();
		if ( ! $status['ok'] ) {
			$fallback = (string) apply_filters( 'twh_free_download_fallback', home_url( '/download/' ) );
			wp_safe_redirect( add_query_arg( 'twh_dl', 'unavailable', $fallback ) );
			exit;
		}
		$release = (array) $status['release'];
		// Count downloads, but stop logging (not serving) when one address repeats too often.
		if ( RateLimiter::hit( 'free_download', Secrets::hash_ip( Request::ip() ) ) ) {
			Events::log(
				'free_download',
				null,
				array(
					'release_id' => (int) $release['id'],
					'version'    => (string) $release['version'],
				)
			);
		}
		self::stream_release( $release, (string) $status['path'] );
		exit;
	}

	/**
	 * Send a release ZIP to the browser.
	 *
	 * @param array<string, mixed> $release Release row.
	 * @param string               $path    Absolute file path.
	 */
	public static function stream_release( array $release, string $path ): void {
		$product = Products::find( (int) $release['product_id'] );
		$slug    = $product ? (string) $product['slug'] : 'plugin';
		// Page caches and server compression break binary downloads (wrong length, cached HTML).
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- standard constant read by caching plugins.
		}
		if ( function_exists( 'ini_get' ) && ini_get( 'zlib.output_compression' ) ) {
			ini_set( 'zlib.output_compression', 'Off' ); // phpcs:ignore WordPress.PHP.IniSet.Risky -- binary download.
		}
		if ( ! headers_sent() ) {
			header( 'X-LiteSpeed-Cache-Control: no-cache' );
			header( 'Cache-Control: no-store, no-transform' );
		}
		RestController::stream( $path, $slug . '-' . preg_replace( '/[^0-9A-Za-z.-]/', '', (string) $release['version'] ) . '.zip' );
	}
}
