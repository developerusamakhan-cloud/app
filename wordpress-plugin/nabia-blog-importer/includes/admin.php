<?php
/**
 * Posts → Blog Packs: upload a pack, pick articles, choose how to publish them.
 *
 * @package Nabia_Blog_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the screen.
 */
function nbi_admin_menu() {
	add_posts_page( __( 'Blog Packs', 'nabia-blog-importer' ), __( 'Blog Packs', 'nabia-blog-importer' ), 'publish_posts', 'nabia-blog-packs', 'nbi_admin_screen' );
}
add_action( 'admin_menu', 'nbi_admin_menu' );

/**
 * Link to the screen from the Plugins list.
 *
 * @param array $links Links.
 * @return array
 */
function nbi_action_links( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit.php?page=nabia-blog-packs' ) ) . '">' . esc_html__( 'Blog Packs', 'nabia-blog-importer' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( NBI_DIR . 'nabia-blog-importer.php' ), 'nbi_action_links' );

/**
 * Can the current user use the importer?
 *
 * @return bool
 */
function nbi_can() {
	return current_user_can( 'publish_posts' ) && current_user_can( 'upload_files' ) && current_user_can( 'manage_categories' );
}

/**
 * Human label for a post status.
 *
 * @param string $status Status.
 * @return string
 */
function nbi_status_label( $status ) {
	$labels = array(
		'future'  => __( 'Scheduled', 'nabia-blog-importer' ),
		'publish' => __( 'Published', 'nabia-blog-importer' ),
		'draft'   => __( 'Draft', 'nabia-blog-importer' ),
		'pending' => __( 'Pending', 'nabia-blog-importer' ),
		'private' => __( 'Private', 'nabia-blog-importer' ),
	);
	return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
}

/**
 * Render the screen and handle its forms.
 */
function nbi_admin_screen() {
	if ( ! nbi_can() ) {
		wp_die( esc_html__( 'You need permission to publish posts, upload files and manage categories to import blog packs.', 'nabia-blog-importer' ) );
	}

	$notice = '';
	$error  = '';
	$report = array();
	$action = isset( $_POST['nbi_action'] ) ? sanitize_key( wp_unslash( $_POST['nbi_action'] ) ) : '';

	if ( $action ) {
		check_admin_referer( 'nbi_' . $action );
	}

	if ( 'upload' === $action ) {
		$result = nbi_add_pack( isset( $_FILES['nbi_pack'] ) ? $_FILES['nbi_pack'] : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( is_wp_error( $result ) ) {
			$error = $result->get_error_message();
		} else {
			$packs = nbi_get_packs();
			/* translators: 1: pack name, 2: number of articles */
			$notice = sprintf( __( 'Pack "%1$s" added with %2$d articles. Choose how to publish them below.', 'nabia-blog-importer' ), $packs[ $result ]['name'], $packs[ $result ]['count'] );
			$_GET['pack'] = $result; // Show the import form straight away.
		}
	} elseif ( 'delete' === $action && isset( $_POST['pack'] ) ) {
		nbi_delete_pack( sanitize_text_field( wp_unslash( $_POST['pack'] ) ) );
		$notice = __( 'Pack deleted. Articles already imported from it stay on your site.', 'nabia-blog-importer' );
	} elseif ( 'import' === $action && isset( $_POST['pack'] ) ) {
		$pack = sanitize_text_field( wp_unslash( $_POST['pack'] ) );
		$p    = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$opts = array(
			'slugs'    => isset( $p['slugs'] ) ? array_map( 'sanitize_title', (array) $p['slugs'] ) : array(),
			'mode'     => isset( $p['mode'] ) && in_array( $p['mode'], array( 'schedule', 'publish', 'draft' ), true ) ? $p['mode'] : 'schedule',
			'start'    => isset( $p['start'] ) ? str_replace( 'T', ' ', sanitize_text_field( $p['start'] ) ) : '',
			'every'    => isset( $p['every'] ) ? max( 1, min( 60, absint( $p['every'] ) ) ) : 2,
			'unit'     => isset( $p['unit'] ) && 'weeks' === $p['unit'] ? 'weeks' : 'days',
			'weekdays' => isset( $p['weekdays'] ) ? array_map( 'absint', (array) $p['weekdays'] ) : range( 1, 7 ),
			'vary'     => ! empty( $p['vary'] ),
			'order'    => isset( $p['order'] ) && 'shuffle' === $p['order'] ? 'shuffle' : 'pack',
			'author'   => isset( $p['author'] ) && user_can( absint( $p['author'] ), 'edit_posts' ) ? absint( $p['author'] ) : get_current_user_id(),
			'category' => isset( $p['category'] ) ? absint( $p['category'] ) : 0,
			'comments' => isset( $p['comments'] ) && 'open' === $p['comments'] ? 'open' : 'closed',
			'cover'    => ! empty( $p['cover'] ),
			'seo'      => ! empty( $p['seo'] ),
		);
		if ( ! $opts['slugs'] ) {
			$error        = __( 'Please tick at least one article.', 'nabia-blog-importer' );
			$_GET['pack'] = $pack;
		} else {
			$report = nbi_import( $pack, $opts );
		}
	}

	$packs   = nbi_get_packs();
	$current = isset( $_GET['pack'] ) ? sanitize_text_field( wp_unslash( $_GET['pack'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$base    = admin_url( 'edit.php?page=nabia-blog-packs' );
	?>
	<div class="wrap nbi">
		<style>
			.nbi .nbi-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px 24px;margin:18px 0;max-width:980px}
			.nbi .nbi-card h2{margin-top:0}
			.nbi .nbi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px 28px}
			.nbi .nbi-grid label{display:block;font-weight:600;margin-bottom:6px}
			.nbi .nbi-days label{display:inline-flex;gap:4px;align-items:center;font-weight:400;margin:0 10px 6px 0}
			.nbi .nbi-modes label{display:block;font-weight:400;margin-bottom:8px}
			.nbi .nbi-muted{color:#646970}
			.nbi .nbi-pill{display:inline-block;padding:2px 10px;border-radius:999px;background:#f0f0f1;font-size:12px}
			.nbi .nbi-pill.future{background:#efe7ff;color:#5b21b6}.nbi .nbi-pill.publish{background:#dcfce7;color:#166534}.nbi .nbi-pill.draft{background:#fef3c7;color:#92400e}
			.nbi table.widefat td,.nbi table.widefat th{vertical-align:middle}
			.nbi [data-schedule][hidden]{display:none}
		</style>

		<h1><?php esc_html_e( 'Blog Packs', 'nabia-blog-importer' ); ?></h1>
		<p class="nbi-muted"><?php esc_html_e( 'Import ready-made articles with their cover images, FAQ, categories, tags and SEO details. Publish them now, save them as drafts, or let them go live on a schedule.', 'nabia-blog-importer' ); ?></p>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<?php if ( $report ) : ?>
			<div class="nbi-card">
				<h2><?php esc_html_e( 'Done', 'nabia-blog-importer' ); ?></h2>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'Article', 'nabia-blog-importer' ); ?></th><th><?php esc_html_e( 'Status', 'nabia-blog-importer' ); ?></th><th><?php esc_html_e( 'Date', 'nabia-blog-importer' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $report as $row ) : ?>
							<tr>
								<td>
									<?php if ( $row['id'] ) : ?>
										<a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $row['title'] ); ?>
									<?php endif; ?>
									<?php if ( $row['note'] ) : ?>
										<br><small class="nbi-muted"><?php echo esc_html( $row['note'] ); ?></small>
									<?php endif; ?>
								</td>
								<td><?php if ( $row['status'] ) : ?><span class="nbi-pill <?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( nbi_status_label( $row['status'] ) ); ?></span><?php endif; ?></td>
								<td><?php echo $row['date'] && 'draft' !== $row['status'] ? esc_html( wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( get_gmt_from_date( $row['date'] ) ) ) ) : ''; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p><a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_status=future&post_type=post' ) ); ?>"><?php esc_html_e( 'See scheduled posts', 'nabia-blog-importer' ); ?></a> <a class="button" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Back to Blog Packs', 'nabia-blog-importer' ); ?></a></p>
			</div>
		<?php elseif ( $current && isset( $packs[ $current ] ) ) : ?>
			<?php nbi_import_form( $current, $packs[ $current ] ); ?>
		<?php else : ?>
			<div class="nbi-card">
				<h2><?php esc_html_e( '1. Upload a blog pack', 'nabia-blog-importer' ); ?></h2>
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'nbi_upload' ); ?>
					<input type="hidden" name="nbi_action" value="upload">
					<input type="file" name="nbi_pack" accept=".zip,application/zip" required>
					<button class="button button-primary"><?php esc_html_e( 'Upload pack', 'nabia-blog-importer' ); ?></button>
				</form>
				<p class="nbi-muted"><?php esc_html_e( 'Upload the .zip file exactly as you received it. Do not unzip it first.', 'nabia-blog-importer' ); ?></p>
			</div>

			<?php if ( $packs ) : ?>
				<div class="nbi-card">
					<h2><?php esc_html_e( '2. Your packs', 'nabia-blog-importer' ); ?></h2>
					<table class="widefat striped">
						<thead><tr><th><?php esc_html_e( 'Pack', 'nabia-blog-importer' ); ?></th><th><?php esc_html_e( 'Articles', 'nabia-blog-importer' ); ?></th><th><?php esc_html_e( 'Uploaded', 'nabia-blog-importer' ); ?></th><th></th></tr></thead>
						<tbody>
							<?php foreach ( $packs as $id => $pack ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $pack['name'] ); ?></strong><?php echo $pack['description'] ? '<br><small class="nbi-muted">' . esc_html( $pack['description'] ) . '</small>' : ''; ?></td>
									<td><?php echo (int) $pack['count']; ?></td>
									<td><?php echo esc_html( wp_date( get_option( 'date_format' ), $pack['uploaded'] ) ); ?></td>
									<td style="text-align:right;white-space:nowrap">
										<a class="button button-primary" href="<?php echo esc_url( add_query_arg( 'pack', $id, $base ) ); ?>"><?php esc_html_e( 'Import / schedule', 'nabia-blog-importer' ); ?></a>
										<form method="post" style="display:inline" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this pack? Articles you already imported stay on your site.', 'nabia-blog-importer' ) ); ?>')">
											<?php wp_nonce_field( 'nbi_delete' ); ?>
											<input type="hidden" name="nbi_action" value="delete">
											<input type="hidden" name="pack" value="<?php echo esc_attr( $id ); ?>">
											<button class="button-link button-link-delete"><?php esc_html_e( 'Delete', 'nabia-blog-importer' ); ?></button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php nbi_imported_list(); ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * The import form for one pack.
 *
 * @param string $id   Pack ID.
 * @param array  $pack Pack details.
 */
function nbi_import_form( $id, $pack ) {
	$articles = nbi_pack_articles( $id );
	$start    = new DateTimeImmutable( 'tomorrow 09:00', wp_timezone() );
	$days     = array(
		1 => __( 'Mon', 'nabia-blog-importer' ),
		2 => __( 'Tue', 'nabia-blog-importer' ),
		3 => __( 'Wed', 'nabia-blog-importer' ),
		4 => __( 'Thu', 'nabia-blog-importer' ),
		5 => __( 'Fri', 'nabia-blog-importer' ),
		6 => __( 'Sat', 'nabia-blog-importer' ),
		7 => __( 'Sun', 'nabia-blog-importer' ),
	);
	?>
	<form method="post">
		<?php wp_nonce_field( 'nbi_import' ); ?>
		<input type="hidden" name="nbi_action" value="import">
		<input type="hidden" name="pack" value="<?php echo esc_attr( $id ); ?>">

		<div class="nbi-card">
			<h2><?php echo esc_html( $pack['name'] ); ?></h2>
			<table class="widefat striped">
				<thead><tr>
					<td class="check-column" style="padding-left:12px"><input type="checkbox" data-nbi-all checked aria-label="<?php esc_attr_e( 'Select all', 'nabia-blog-importer' ); ?>"></td>
					<th><?php esc_html_e( 'Article', 'nabia-blog-importer' ); ?></th>
					<th><?php esc_html_e( 'Search term', 'nabia-blog-importer' ); ?></th>
					<th><?php esc_html_e( 'Category', 'nabia-blog-importer' ); ?></th>
					<th><?php esc_html_e( 'Cover', 'nabia-blog-importer' ); ?></th>
				</tr></thead>
				<tbody>
					<?php foreach ( $articles as $slug => $item ) : ?>
						<?php $existing = nbi_existing_post( $slug ); ?>
						<tr>
							<th class="check-column" style="padding-left:12px"><input type="checkbox" name="slugs[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( ! $existing ); ?> <?php disabled( (bool) $existing ); ?>></th>
							<td>
								<strong><?php echo esc_html( $item['title'] ); ?></strong>
								<?php if ( $existing ) : ?>
									<br><small class="nbi-muted"><?php esc_html_e( 'Already on your site:', 'nabia-blog-importer' ); ?> <a href="<?php echo esc_url( get_edit_post_link( $existing ) ); ?>"><?php echo esc_html( nbi_status_label( $existing->post_status ) ); ?></a></small>
								<?php else : ?>
									<br><small class="nbi-muted"><?php echo esc_html( wp_html_excerpt( $item['excerpt'], 120, '...' ) ); ?></small>
								<?php endif; ?>
							</td>
							<td><code><?php echo esc_html( $item['keyword'] ); ?></code></td>
							<td><?php echo esc_html( $item['category'] ); ?></td>
							<td><?php echo $item['cover'] ? '&#10003;' : ''; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="nbi-card">
			<h2><?php esc_html_e( 'How should they go live?', 'nabia-blog-importer' ); ?></h2>
			<div class="nbi-modes">
				<label><input type="radio" name="mode" value="schedule" checked> <strong><?php esc_html_e( 'Schedule', 'nabia-blog-importer' ); ?></strong> <span class="nbi-muted"><?php esc_html_e( 'one after another, on the days you choose (recommended)', 'nabia-blog-importer' ); ?></span></label>
				<label><input type="radio" name="mode" value="publish"> <strong><?php esc_html_e( 'Publish all now', 'nabia-blog-importer' ); ?></strong></label>
				<label><input type="radio" name="mode" value="draft"> <strong><?php esc_html_e( 'Save as drafts', 'nabia-blog-importer' ); ?></strong> <span class="nbi-muted"><?php esc_html_e( 'review and publish them yourself', 'nabia-blog-importer' ); ?></span></label>
			</div>

			<div class="nbi-grid" data-schedule>
				<div>
					<label for="nbi-start"><?php esc_html_e( 'First article goes live', 'nabia-blog-importer' ); ?></label>
					<input id="nbi-start" type="datetime-local" name="start" value="<?php echo esc_attr( $start->format( 'Y-m-d\TH:i' ) ); ?>">
					<p class="description"><?php echo esc_html( sprintf( /* translators: %s: timezone */ __( 'Your site time zone: %s', 'nabia-blog-importer' ), wp_timezone_string() ) ); ?></p>
				</div>
				<div>
					<label for="nbi-every"><?php esc_html_e( 'Then one article every', 'nabia-blog-importer' ); ?></label>
					<input id="nbi-every" type="number" name="every" min="1" max="60" value="2" style="width:70px">
					<select name="unit"><option value="days"><?php esc_html_e( 'days', 'nabia-blog-importer' ); ?></option><option value="weeks"><?php esc_html_e( 'weeks', 'nabia-blog-importer' ); ?></option></select>
				</div>
				<div class="nbi-days">
					<label><?php esc_html_e( 'Only on these days', 'nabia-blog-importer' ); ?></label>
					<?php foreach ( $days as $num => $label ) : ?>
						<label><input type="checkbox" name="weekdays[]" value="<?php echo (int) $num; ?>" <?php checked( $num <= 5 ); ?>> <?php echo esc_html( $label ); ?></label>
					<?php endforeach; ?>
				</div>
				<div>
					<label><input type="checkbox" name="vary" value="1" checked> <?php esc_html_e( 'Vary the time a little (up to 45 minutes) so posts look natural', 'nabia-blog-importer' ); ?></label>
				</div>
			</div>
		</div>

		<div class="nbi-card">
			<h2><?php esc_html_e( 'Details', 'nabia-blog-importer' ); ?></h2>
			<div class="nbi-grid">
				<div>
					<label for="nbi-author"><?php esc_html_e( 'Author', 'nabia-blog-importer' ); ?></label>
					<?php
					wp_dropdown_users(
						array(
							'name'       => 'author',
							'id'         => 'nbi-author',
							'capability' => array( 'edit_posts' ),
							'selected'   => get_current_user_id(),
						)
					);
					?>
				</div>
				<div>
					<label for="nbi-category"><?php esc_html_e( 'Category', 'nabia-blog-importer' ); ?></label>
					<?php
					wp_dropdown_categories(
						array(
							'name'            => 'category',
							'id'              => 'nbi-category',
							'hide_empty'      => false,
							'show_option_none' => __( 'Use each article’s own category', 'nabia-blog-importer' ),
							'option_none_value' => '0',
						)
					);
					?>
				</div>
				<div>
					<label><?php esc_html_e( 'Order', 'nabia-blog-importer' ); ?></label>
					<select name="order"><option value="pack"><?php esc_html_e( 'As listed above', 'nabia-blog-importer' ); ?></option><option value="shuffle"><?php esc_html_e( 'Random', 'nabia-blog-importer' ); ?></option></select>
				</div>
				<div>
					<label><?php esc_html_e( 'Comments', 'nabia-blog-importer' ); ?></label>
					<select name="comments"><option value="open" <?php selected( get_option( 'default_comment_status' ), 'open' ); ?>><?php esc_html_e( 'Allow comments', 'nabia-blog-importer' ); ?></option><option value="closed" <?php selected( get_option( 'default_comment_status' ), 'closed' ); ?>><?php esc_html_e( 'No comments', 'nabia-blog-importer' ); ?></option></select>
				</div>
				<div>
					<label><input type="checkbox" name="cover" value="1" checked> <?php esc_html_e( 'Add cover images as featured images', 'nabia-blog-importer' ); ?></label>
					<label><input type="checkbox" name="seo" value="1" checked> <?php esc_html_e( 'Fill in the focus keyword and meta description (Yoast SEO, Rank Math)', 'nabia-blog-importer' ); ?></label>
				</div>
			</div>
			<p style="margin-top:20px">
				<button class="button button-primary button-hero"><?php esc_html_e( 'Import articles', 'nabia-blog-importer' ); ?></button>
				<a class="button button-hero" href="<?php echo esc_url( admin_url( 'edit.php?page=nabia-blog-packs' ) ); ?>"><?php esc_html_e( 'Cancel', 'nabia-blog-importer' ); ?></a>
			</p>
		</div>
	</form>
	<script>
	( function () {
		var all = document.querySelector( '[data-nbi-all]' );
		if ( all ) {
			all.addEventListener( 'change', function () {
				document.querySelectorAll( 'input[name="slugs[]"]:not(:disabled)' ).forEach( function ( box ) { box.checked = all.checked; } );
			} );
		}
		var sched = document.querySelector( '[data-schedule]' );
		document.querySelectorAll( 'input[name="mode"]' ).forEach( function ( radio ) {
			radio.addEventListener( 'change', function () { sched.hidden = 'schedule' !== radio.value; } );
		} );
	} )();
	</script>
	<?php
}

/**
 * Articles imported so far, with their status and date.
 */
function nbi_imported_list() {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => array( 'future', 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 100,
			'meta_key'       => '_nbi_article', // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => 'date',
			'order'          => 'ASC',
		)
	);
	if ( ! $posts ) {
		return;
	}
	?>
	<div class="nbi-card">
		<h2><?php esc_html_e( 'Imported articles', 'nabia-blog-importer' ); ?></h2>
		<p class="nbi-muted"><?php esc_html_e( 'To change a date, open the article and edit "Publish" in the sidebar, or use Quick Edit in Posts.', 'nabia-blog-importer' ); ?></p>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Article', 'nabia-blog-importer' ); ?></th><th><?php esc_html_e( 'Status', 'nabia-blog-importer' ); ?></th><th><?php esc_html_e( 'Date', 'nabia-blog-importer' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( $posts as $post ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></td>
						<td><span class="nbi-pill <?php echo esc_attr( $post->post_status ); ?>"><?php echo esc_html( nbi_status_label( $post->post_status ) ); ?></span></td>
						<td><?php echo 'draft' === $post->post_status ? '' : esc_html( get_the_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $post ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
