<?php
/**
 * Releases admin: live version per product, full history, upload, edit, roll back, delete.
 *
 * @package TalkwynHub
 */

namespace TWH\Admin;

use TWH\Domain\Markdown;
use TWH\Domain\ZipInspector;
use TWH\Download\FreeDownload;
use TWH\Repository\Products;
use TWH\Repository\Releases;
use TWH\Support\Storage;
use TWH\Support\Time;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Missing -- every admin-post handler calls Admin::guard() (capability + check_admin_referer) before reading input; an id needed to build the nonce action is read with absint() first.

/**
 * Releases screen.
 */
final class ReleasesPage {

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'admin_post_twh_upload_release', array( self::class, 'handle_upload' ) );
		add_action( 'admin_post_twh_update_release', array( self::class, 'handle_update' ) );
		add_action( 'admin_post_twh_delete_release', array( self::class, 'handle_delete' ) );
		add_action( 'admin_post_twh_make_live', array( self::class, 'handle_make_live' ) );
		add_action( 'admin_post_twh_download_release', array( self::class, 'handle_download' ) );
	}

	/**
	 * Render the live summary, the upload form and the release history, or the edit form.
	 */
	public static function render(): void {
		if ( ! current_user_can( Admin::cap() ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- routing only.
		$edit = isset( $_GET['release'] ) ? Releases::find( absint( $_GET['release'] ) ) : null;
		if ( $edit ) {
			self::render_edit( $edit );
			return;
		}
		$products  = Products::all();
		$releases  = Releases::list();
		$downloads = Releases::download_counts();
		$writable  = Storage::ensure_dir();
		$free_slug = FreeDownload::product_slug();
		$by_product = array();
		foreach ( $releases as $r ) {
			$by_product[ (int) $r['product_id'] ][] = $r;
		}
		?>
		<div class="wrap twh-wrap">
			<h1><?php esc_html_e( 'Releases', 'talkwyn-hub' ); ?></h1>

			<?php if ( ! $writable ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( sprintf( /* translators: %s: path */ __( 'The release directory is not writable: %s', 'talkwyn-hub' ), Storage::dir() ) ); ?></p></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Live now', 'talkwyn-hub' ); ?></h2>
			<p class="description"><?php esc_html_e( 'The live version is the highest active Stable release of each product. Customers get it as an update and from their account, and for the free plugin it is the file behind the website download button.', 'talkwyn-hub' ); ?></p>
			<div class="twh-live-grid">
				<?php
				if ( ! $products ) {
					echo '<div class="twh-panel"><p>' . esc_html__( 'No products yet. Create one under Products first.', 'talkwyn-hub' ) . '</p></div>';
				}
				foreach ( $products as $p ) :
					$pid    = (int) $p['id'];
					$live   = Releases::latest( $pid, 'stable' );
					$beta   = Releases::latest( $pid, 'beta' );
					$beta   = ( $beta && ( ! $live || (int) $beta['id'] !== (int) $live['id'] ) ) ? $beta : null;
					$count  = count( $by_product[ $pid ] ?? array() );
					$free   = (string) $p['slug'] === $free_slug;
					$status = $free ? FreeDownload::status() : null;
					?>
					<section class="twh-panel twh-live<?php echo $live ? '' : ' twh-live--empty'; ?>">
						<header class="twh-live__head">
							<h3><?php echo esc_html( (string) $p['name'] ); ?></h3>
							<code><?php echo esc_html( (string) $p['slug'] ); ?></code>
							<?php if ( $free ) : ?>
								<span class="twh-badge twh-badge--dev"><?php esc_html_e( 'Free plugin', 'talkwyn-hub' ); ?></span>
							<?php endif; ?>
						</header>
						<?php if ( $live ) : ?>
							<p class="twh-live__version"><span class="twh-badge twh-badge--active"><?php esc_html_e( 'Live', 'talkwyn-hub' ); ?></span> <strong><?php echo esc_html( (string) $live['version'] ); ?></strong></p>
							<p class="twh-live__meta">
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: release date, 2: file size, 3: number of downloads */
										__( 'Released %1$s · %2$s · %3$s downloads', 'talkwyn-hub' ),
										Time::human( (string) $live['released_at'] ),
										size_format( (int) $live['file_size'] ),
										number_format_i18n( (int) ( $downloads[ (int) $live['id'] ] ?? 0 ) )
									)
								);
								?>
							</p>
						<?php else : ?>
							<p class="twh-live__version"><span class="twh-badge twh-badge--revoked"><?php esc_html_e( 'Nothing live', 'talkwyn-hub' ); ?></span></p>
							<p class="twh-live__meta"><?php esc_html_e( 'No active Stable release. Customers get no download or update.', 'talkwyn-hub' ); ?></p>
						<?php endif; ?>
						<?php if ( $beta ) : ?>
							<p class="twh-live__meta"><span class="twh-badge twh-badge--trial"><?php esc_html_e( 'Beta', 'talkwyn-hub' ); ?></span> <?php echo esc_html( (string) $beta['version'] ); ?> <?php esc_html_e( '(only sites on the beta channel)', 'talkwyn-hub' ); ?></p>
						<?php endif; ?>
						<?php if ( $status ) : ?>
							<div class="twh-live__download <?php echo $status['ok'] ? 'is-ok' : 'is-bad'; ?>">
								<strong><?php echo $status['ok'] ? esc_html__( 'Website download button: working', 'talkwyn-hub' ) : esc_html__( 'Website download button: not working', 'talkwyn-hub' ); ?></strong>
								<?php if ( ! $status['ok'] ) : ?>
									<p><?php echo esc_html( $status['message'] ); ?></p>
								<?php endif; ?>
								<p><code><?php echo esc_html( FreeDownload::url() ); ?></code>
									<?php if ( $status['ok'] ) : ?>
										<a class="button button-small" href="<?php echo esc_url( FreeDownload::url() ); ?>"><?php esc_html_e( 'Test download', 'talkwyn-hub' ); ?></a>
									<?php endif; ?>
								</p>
							</div>
						<?php endif; ?>
						<p class="twh-live__foot">
							<?php
							/* translators: %s: number of releases */
							echo esc_html( sprintf( _n( '%s release in history', '%s releases in history', $count, 'talkwyn-hub' ), number_format_i18n( $count ) ) );
							?>
							<?php if ( $count ) : ?>
								· <a href="#twh-history-<?php echo (int) $pid; ?>"><?php esc_html_e( 'View history', 'talkwyn-hub' ); ?></a>
							<?php endif; ?>
						</p>
					</section>
				<?php endforeach; ?>
			</div>

			<details class="twh-panel twh-upload"<?php echo $releases ? '' : ' open'; ?>>
				<summary><strong><?php esc_html_e( 'Upload a new release', 'talkwyn-hub' ); ?></strong></summary>
				<p class="description">
					<?php
					echo esc_html(
						Storage::is_custom()
							? __( 'ZIPs are stored in TWH_RELEASES_DIR.', 'talkwyn-hub' )
							: __( 'ZIPs are stored in a protected uploads folder (.htaccess deny + random names). For maximum protection, define TWH_RELEASES_DIR in wp-config.php with a path outside the web root. On Nginx, deny access to /wp-content/uploads/talkwyn-hub-releases/ in the server config.', 'talkwyn-hub' )
					);
					?>
				</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="twh_upload_release">
					<?php wp_nonce_field( 'twh_upload_release' ); ?>
					<table class="form-table" role="presentation">
						<tr><th><label for="twh-r-product"><?php esc_html_e( 'Product', 'talkwyn-hub' ); ?></label></th>
							<td><select id="twh-r-product" name="product_id" required>
								<?php foreach ( $products as $p ) : ?>
									<option value="<?php echo (int) $p['id']; ?>"><?php echo esc_html( $p['name'] . ' (' . $p['slug'] . ')' ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php
								/* translators: %s: product slug */
								echo esc_html( sprintf( __( 'The free plugin must be uploaded to the product with the slug "%s".', 'talkwyn-hub' ), $free_slug ) );
								?>
							</p></td></tr>
						<tr><th><label for="twh-r-zip"><?php esc_html_e( 'Plugin ZIP', 'talkwyn-hub' ); ?></label></th>
							<td><input id="twh-r-zip" name="zip" type="file" accept=".zip,application/zip" required>
							<p class="description"><?php esc_html_e( 'The ZIP must contain the plugin folder (e.g. talkwyn/talkwyn.php). Its "Version" header must match the version below.', 'talkwyn-hub' ); ?></p></td></tr>
						<tr><th><label for="twh-r-version"><?php esc_html_e( 'Version', 'talkwyn-hub' ); ?></label></th>
							<td><input id="twh-r-version" name="version" type="text" required pattern="[0-9A-Za-z.+-]+" placeholder="1.2.0"></td></tr>
						<tr><th><label for="twh-r-channel"><?php esc_html_e( 'Channel', 'talkwyn-hub' ); ?></label></th>
							<td><select id="twh-r-channel" name="channel"><option value="stable"><?php esc_html_e( 'Stable', 'talkwyn-hub' ); ?></option><option value="beta"><?php esc_html_e( 'Beta', 'talkwyn-hub' ); ?></option></select></td></tr>
						<tr><th><?php esc_html_e( 'Requirements', 'talkwyn-hub' ); ?></th>
							<td>
								<label><?php esc_html_e( 'Requires WP', 'talkwyn-hub' ); ?> <input name="requires_wp" type="text" size="6" placeholder="6.4"></label>
								<label><?php esc_html_e( 'Requires PHP', 'talkwyn-hub' ); ?> <input name="requires_php" type="text" size="6" placeholder="8.0"></label>
								<label><?php esc_html_e( 'Tested up to', 'talkwyn-hub' ); ?> <input name="tested_wp" type="text" size="6" placeholder="6.7"></label>
								<p class="description"><?php esc_html_e( 'Leave empty to read them from the plugin header / readme.txt.', 'talkwyn-hub' ); ?></p>
							</td></tr>
						<tr><th><label for="twh-r-changelog"><?php esc_html_e( 'Changelog (Markdown)', 'talkwyn-hub' ); ?></label></th>
							<td><textarea id="twh-r-changelog" name="changelog" rows="8" class="large-text code" placeholder="- New: …&#10;- Fix: …"></textarea></td></tr>
						<tr><th><?php esc_html_e( 'Active', 'talkwyn-hub' ); ?></th>
							<td><label><input name="is_active" type="checkbox" value="1" checked> <?php esc_html_e( 'Offer this release as an update immediately', 'talkwyn-hub' ); ?></label></td></tr>
					</table>
					<?php submit_button( __( 'Upload release', 'talkwyn-hub' ) ); ?>
				</form>
			</details>

			<h2><?php esc_html_e( 'Release history', 'talkwyn-hub' ); ?></h2>
			<p class="twh-legend">
				<span class="twh-badge twh-badge--active"><?php esc_html_e( 'Live', 'talkwyn-hub' ); ?></span> <?php esc_html_e( 'customers get this version', 'talkwyn-hub' ); ?>
				<span class="twh-badge twh-badge--trial"><?php esc_html_e( 'Live beta', 'talkwyn-hub' ); ?></span> <?php esc_html_e( 'beta channel only', 'talkwyn-hub' ); ?>
				<span class="twh-badge"><?php esc_html_e( 'Older', 'talkwyn-hub' ); ?></span> <?php esc_html_e( 'active, but a newer version is live', 'talkwyn-hub' ); ?>
				<span class="twh-badge twh-badge--expired"><?php esc_html_e( 'Off', 'talkwyn-hub' ); ?></span> <?php esc_html_e( 'switched off, not offered to anyone', 'talkwyn-hub' ); ?>
			</p>
			<?php
			if ( ! $releases ) {
				echo '<div class="twh-panel"><p>' . esc_html__( 'No releases yet.', 'talkwyn-hub' ) . '</p></div>';
			}
			$names = array();
			foreach ( $products as $p ) {
				$names[ (int) $p['id'] ] = (string) $p['name'];
			}
			foreach ( $by_product as $pid => $rows ) :
				$live_id = (int) ( Releases::latest( (int) $pid, 'stable' )['id'] ?? 0 );
				$beta_id = (int) ( Releases::latest( (int) $pid, 'beta' )['id'] ?? 0 );
				?>
				<div class="twh-panel twh-history" id="twh-history-<?php echo (int) $pid; ?>">
					<h3><?php echo esc_html( $names[ (int) $pid ] ?? __( 'Deleted product', 'talkwyn-hub' ) ); ?></h3>
					<table class="widefat striped twh-history__table">
						<thead><tr>
							<th><?php esc_html_e( 'Version', 'talkwyn-hub' ); ?></th>
							<th><?php esc_html_e( 'Status', 'talkwyn-hub' ); ?></th>
							<th><?php esc_html_e( 'Channel', 'talkwyn-hub' ); ?></th>
							<th><?php esc_html_e( 'Released', 'talkwyn-hub' ); ?></th>
							<th><?php esc_html_e( 'Requires', 'talkwyn-hub' ); ?></th>
							<th><?php esc_html_e( 'Size', 'talkwyn-hub' ); ?></th>
							<th><?php esc_html_e( 'Downloads', 'talkwyn-hub' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'talkwyn-hub' ); ?></th>
						</tr></thead>
						<tbody>
						<?php
						foreach ( $rows as $r ) :
							$rid     = (int) $r['id'];
							$path    = Storage::path( (string) $r['zip_path'] );
							$file_ok = null !== $path && is_readable( $path );
							if ( $rid === $live_id ) {
								$badge = array( 'twh-badge--active', __( 'Live', 'talkwyn-hub' ) );
							} elseif ( $rid === $beta_id ) {
								$badge = array( 'twh-badge--trial', __( 'Live beta', 'talkwyn-hub' ) );
							} elseif ( (int) $r['is_active'] ) {
								$badge = array( '', __( 'Older', 'talkwyn-hub' ) );
							} else {
								$badge = array( 'twh-badge--expired', __( 'Off', 'talkwyn-hub' ) );
							}
							?>
							<tr class="<?php echo $rid === $live_id ? 'twh-row-live' : ''; ?>">
								<td><strong><?php echo esc_html( (string) $r['version'] ); ?></strong>
									<?php if ( '' !== trim( (string) $r['changelog'] ) ) : ?>
										<details class="twh-changelog"><summary><?php esc_html_e( 'Changelog', 'talkwyn-hub' ); ?></summary><div><?php echo wp_kses_post( Markdown::to_html( (string) $r['changelog'] ) ); ?></div></details>
									<?php endif; ?>
								</td>
								<td><span class="twh-badge <?php echo esc_attr( $badge[0] ); ?>"><?php echo esc_html( $badge[1] ); ?></span>
									<?php if ( ! $file_ok ) : ?>
										<span class="twh-badge twh-badge--revoked"><?php esc_html_e( 'file missing', 'talkwyn-hub' ); ?></span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( 'beta' === $r['channel'] ? __( 'Beta', 'talkwyn-hub' ) : __( 'Stable', 'talkwyn-hub' ) ); ?></td>
								<td><span title="<?php echo esc_attr( (string) $r['released_at'] . ' UTC' ); ?>"><?php echo esc_html( Time::human( (string) $r['released_at'] ) ); ?></span></td>
								<td><small><?php echo esc_html( sprintf( 'WP %s · PHP %s · tested %s', $r['requires_wp'] ? $r['requires_wp'] : 'n/a', $r['requires_php'] ? $r['requires_php'] : 'n/a', $r['tested_wp'] ? $r['tested_wp'] : 'n/a' ) ); ?></small></td>
								<td><?php echo esc_html( size_format( (int) $r['file_size'] ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( (int) ( $downloads[ $rid ] ?? 0 ) ) ); ?></td>
								<td class="twh-actions">
									<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=twh-releases&release=' . $rid ) ); ?>"><?php esc_html_e( 'Edit', 'talkwyn-hub' ); ?></a>
									<?php if ( $file_ok ) : ?>
										<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=twh_download_release&release=' . $rid ), 'twh_download_release_' . $rid ) ); ?>"><?php esc_html_e( 'Download', 'talkwyn-hub' ); ?></a>
									<?php endif; ?>
									<?php if ( $rid !== $live_id && $rid !== $beta_id && $file_ok ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="twh-confirm-form twh-inline-form">
											<input type="hidden" name="action" value="twh_make_live">
											<input type="hidden" name="release" value="<?php echo (int) $rid; ?>">
											<?php wp_nonce_field( 'twh_make_live_' . $rid ); ?>
											<button type="submit" class="button button-small"><?php esc_html_e( 'Make live', 'talkwyn-hub' ); ?></button>
										</form>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Edit form.
	 *
	 * @param array<string, mixed> $r Release.
	 */
	private static function render_edit( array $r ): void {
		$product = Products::find( (int) $r['product_id'] );
		?>
		<div class="wrap twh-wrap">
			<h1><?php echo esc_html( sprintf( /* translators: 1: product, 2: version */ __( 'Edit release %1$s %2$s', 'talkwyn-hub' ), $product ? $product['name'] : '', $r['version'] ) ); ?></h1>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=twh-releases' ) ); ?>">&larr; <?php esc_html_e( 'All releases', 'talkwyn-hub' ); ?></a></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="twh_update_release">
				<input type="hidden" name="release" value="<?php echo (int) $r['id']; ?>">
				<?php wp_nonce_field( 'twh_update_release_' . (int) $r['id'] ); ?>
				<table class="form-table" role="presentation">
					<tr><th><?php esc_html_e( 'Channel', 'talkwyn-hub' ); ?></th>
						<td><select name="channel"><option value="stable" <?php selected( $r['channel'], 'stable' ); ?>><?php esc_html_e( 'Stable', 'talkwyn-hub' ); ?></option><option value="beta" <?php selected( $r['channel'], 'beta' ); ?>><?php esc_html_e( 'Beta', 'talkwyn-hub' ); ?></option></select></td></tr>
					<tr><th><?php esc_html_e( 'Requirements', 'talkwyn-hub' ); ?></th>
						<td>
							<label><?php esc_html_e( 'Requires WP', 'talkwyn-hub' ); ?> <input name="requires_wp" type="text" size="6" value="<?php echo esc_attr( (string) $r['requires_wp'] ); ?>"></label>
							<label><?php esc_html_e( 'Requires PHP', 'talkwyn-hub' ); ?> <input name="requires_php" type="text" size="6" value="<?php echo esc_attr( (string) $r['requires_php'] ); ?>"></label>
							<label><?php esc_html_e( 'Tested up to', 'talkwyn-hub' ); ?> <input name="tested_wp" type="text" size="6" value="<?php echo esc_attr( (string) $r['tested_wp'] ); ?>"></label>
						</td></tr>
					<tr><th><?php esc_html_e( 'Changelog (Markdown)', 'talkwyn-hub' ); ?></th>
						<td><textarea name="changelog" rows="10" class="large-text code"><?php echo esc_textarea( (string) $r['changelog'] ); ?></textarea></td></tr>
					<tr><th><?php esc_html_e( 'Active', 'talkwyn-hub' ); ?></th>
						<td><label><input name="is_active" type="checkbox" value="1" <?php checked( (int) $r['is_active'], 1 ); ?>> <?php esc_html_e( 'Offer as update', 'talkwyn-hub' ); ?></label></td></tr>
					<tr><th><?php esc_html_e( 'File', 'talkwyn-hub' ); ?></th>
						<td><code><?php echo esc_html( (string) $r['zip_path'] ); ?></code><br><small>SHA-256: <?php echo esc_html( (string) $r['checksum'] ); ?></small></td></tr>
				</table>
				<?php submit_button( __( 'Save release', 'talkwyn-hub' ) ); ?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="twh-confirm-form">
				<input type="hidden" name="action" value="twh_delete_release">
				<input type="hidden" name="release" value="<?php echo (int) $r['id']; ?>">
				<?php wp_nonce_field( 'twh_delete_release_' . (int) $r['id'] ); ?>
				<?php submit_button( __( 'Delete release and file', 'talkwyn-hub' ), 'delete', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Upload handler.
	 */
	public static function handle_upload(): void {
		Admin::guard( 'twh_upload_release' );

		$product = Products::find( absint( $_POST['product_id'] ?? 0 ) );
		$version = (string) preg_replace( '/[^0-9A-Za-z.+-]/', '', sanitize_text_field( wp_unslash( $_POST['version'] ?? '' ) ) );
		$channel = 'beta' === sanitize_key( wp_unslash( $_POST['channel'] ?? '' ) ) ? 'beta' : 'stable';
		if ( ! $product || '' === $version ) {
			Admin::redirect( 'twh-releases', 'invalid' );
		}
		if ( Releases::exists( (int) $product['id'], $version, $channel ) ) {
			Admin::redirect( 'twh-releases', 'version_exists' );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- file array validated below.
		$file = $_FILES['zip'] ?? null;
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) || ! is_uploaded_file( (string) $file['tmp_name'] ) ) {
			Admin::redirect( 'twh-releases', 'upload_error' );
		}
		$check = wp_check_filetype( sanitize_file_name( (string) $file['name'] ), array( 'zip' => 'application/zip' ) );
		if ( 'zip' !== $check['ext'] ) {
			Admin::redirect( 'twh-releases', 'upload_error' );
		}

		$tmp    = (string) $file['tmp_name'];
		$header = ZipInspector::inspect( $tmp );
		if ( ! $header ) {
			Admin::redirect( 'twh-releases', 'zip_invalid' );
		}
		if ( 0 !== version_compare( $header['version'], $version ) || '' === $header['version'] ) {
			Admin::redirect( 'twh-releases', 'version_mismatch' );
		}

		if ( ! Storage::ensure_dir() ) {
			Admin::redirect( 'twh-releases', 'storage_error' );
		}
		$name = Storage::random_name( (string) $product['slug'], $version );
		$dest = (string) Storage::path( $name );
		if ( ! move_uploaded_file( $tmp, $dest ) ) {
			Admin::redirect( 'twh-releases', 'storage_error' );
		}
		chmod( $dest, 0640 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod

		$text = static function ( string $key, string $fallback ): string {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
			$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			return '' !== $value ? substr( $value, 0, 20 ) : substr( $fallback, 0, 20 );
		};
		Releases::create(
			array(
				'product_id'   => (int) $product['id'],
				'version'      => $version,
				'channel'      => $channel,
				'zip_path'     => $name,
				'file_size'    => (int) filesize( $dest ),
				'checksum'     => (string) hash_file( 'sha256', $dest ),
				'changelog'    => sanitize_textarea_field( wp_unslash( $_POST['changelog'] ?? '' ) ),
				'requires_wp'  => $text( 'requires_wp', $header['requires_wp'] ),
				'requires_php' => $text( 'requires_php', $header['requires_php'] ),
				'tested_wp'    => $text( 'tested_wp', $header['tested_wp'] ),
				'released_at'  => Time::now_mysql(),
				'is_active'    => ! empty( $_POST['is_active'] ),
			)
		);
		Admin::redirect( 'twh-releases', 'uploaded' );
	}

	/**
	 * Update handler.
	 */
	public static function handle_update(): void {
		$id = absint( $_POST['release'] ?? 0 );
		Admin::guard( 'twh_update_release_' . $id );
		Releases::update(
			$id,
			array(
				'channel'      => 'beta' === sanitize_key( wp_unslash( $_POST['channel'] ?? '' ) ) ? 'beta' : 'stable',
				'requires_wp'  => substr( sanitize_text_field( wp_unslash( $_POST['requires_wp'] ?? '' ) ), 0, 20 ),
				'requires_php' => substr( sanitize_text_field( wp_unslash( $_POST['requires_php'] ?? '' ) ), 0, 20 ),
				'tested_wp'    => substr( sanitize_text_field( wp_unslash( $_POST['tested_wp'] ?? '' ) ), 0, 20 ),
				'changelog'    => sanitize_textarea_field( wp_unslash( $_POST['changelog'] ?? '' ) ),
				'is_active'    => empty( $_POST['is_active'] ) ? 0 : 1,
			)
		);
		Admin::redirect( 'twh-releases', 'saved', array( 'release' => $id ) );
	}

	/**
	 * Make a release the live one (also a rollback to an older version).
	 */
	public static function handle_make_live(): void {
		$id = absint( $_POST['release'] ?? 0 );
		Admin::guard( 'twh_make_live_' . $id );
		$release = Releases::find( $id );
		if ( ! $release ) {
			Admin::redirect( 'twh-releases', 'invalid' );
		}
		$off = Releases::make_live( $id );
		Admin::redirect( 'twh-releases', 'made_live', array( 'v' => rawurlencode( (string) $release['version'] ), 'off' => $off ) );
	}

	/**
	 * Download a release ZIP from the admin (any version, active or not).
	 */
	public static function handle_download(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- guard() checks the nonce.
		$id = absint( $_GET['release'] ?? 0 );
		Admin::guard( 'twh_download_release_' . $id );
		$release = Releases::find( $id );
		$path    = $release ? Storage::path( (string) $release['zip_path'] ) : null;
		if ( ! $release || null === $path || ! is_readable( $path ) ) {
			Admin::redirect( 'twh-releases', 'file_missing' );
		}
		FreeDownload::stream_release( (array) $release, (string) $path );
		exit;
	}

	/**
	 * Delete handler.
	 */
	public static function handle_delete(): void {
		$id = absint( $_POST['release'] ?? 0 );
		Admin::guard( 'twh_delete_release_' . $id );
		$release = Releases::find( $id );
		if ( $release ) {
			$path = Storage::path( (string) $release['zip_path'] );
			if ( $path && file_exists( $path ) ) {
				wp_delete_file( $path );
			}
			Releases::delete( $id );
		}
		Admin::redirect( 'twh-releases', 'deleted' );
	}
}
