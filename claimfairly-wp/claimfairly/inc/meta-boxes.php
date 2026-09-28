<?php
/**
 * "Trust & SEO" meta box for posts and pages: page type, last reviewed date,
 * sources, related tools and author box toggle.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page types. Drives which trust blocks and which schema a page gets.
 *
 * @return array<string,string>
 */
function claimfairly_page_types() {
	return array(
		'standard' => __( 'Standard page (About, Privacy, Contact…)', 'claimfairly' ),
		'tool'     => __( 'Tool / calculator', 'claimfairly' ),
		'guide'    => __( 'Guide / article', 'claimfairly' ),
		'state'    => __( 'State page', 'claimfairly' ),
		'injury'   => __( 'Injury page', 'claimfairly' ),
		'insurer'  => __( 'Insurer page', 'claimfairly' ),
	);
}

/**
 * Register meta so it is also available in the REST API / block editor.
 */
function claimfairly_register_meta() {
	$keys = array(
		'_cf_page_type'     => 'string',
		'_cf_last_reviewed' => 'string',
		'_cf_sources'       => 'string',
		'_cf_related'       => 'string',
		'_cf_card_summary'  => 'string',
		'_cf_hide_author'   => 'boolean',
		'_cf_tool_key'      => 'string',
		'_cf_seo_title'     => 'string',
		'_cf_crumb'         => 'string',
	);
	foreach ( array( 'post', 'page' ) as $post_type ) {
		foreach ( $keys as $key => $type ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'          => $type,
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'claimfairly_register_meta' );

/**
 * Add the meta box.
 */
function claimfairly_add_meta_box() {
	foreach ( array( 'post', 'page' ) as $screen ) {
		add_meta_box(
			'claimfairly_trust',
			__( 'ClaimFairly: Trust & SEO', 'claimfairly' ),
			'claimfairly_render_meta_box',
			$screen,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'claimfairly_add_meta_box' );

/**
 * Meta box markup.
 *
 * @param WP_Post $post Current post.
 */
function claimfairly_render_meta_box( $post ) {
	wp_nonce_field( 'claimfairly_save_meta', 'claimfairly_meta_nonce' );

	$type     = claimfairly_get_page_type( $post->ID );
	$reviewed = get_post_meta( $post->ID, '_cf_last_reviewed', true );
	$sources  = get_post_meta( $post->ID, '_cf_sources', true );
	$related  = get_post_meta( $post->ID, '_cf_related', true );
	$summary  = get_post_meta( $post->ID, '_cf_card_summary', true );
	$hide     = (bool) get_post_meta( $post->ID, '_cf_hide_author', true );
	$tool     = (string) get_post_meta( $post->ID, '_cf_tool_key', true );
	$seo      = (string) get_post_meta( $post->ID, '_cf_seo_title', true );
	?>
	<style>
		.cf-mb p{margin:0 0 14px}.cf-mb label{display:block;font-weight:600;margin-bottom:4px}
		.cf-mb textarea,.cf-mb input[type=text]{width:100%}.cf-mb .description{color:#646970;font-size:12px;margin-top:3px}
	</style>
	<div class="cf-mb">
		<p>
			<label for="cf_page_type"><?php esc_html_e( 'Page type', 'claimfairly' ); ?></label>
			<select id="cf_page_type" name="cf_page_type">
				<?php foreach ( claimfairly_page_types() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="description"><?php esc_html_e( 'Tool pages get WebApplication schema and appear on the homepage. Tool, guide, state, injury and insurer pages show the reviewed date, sources and author box.', 'claimfairly' ); ?></span>
		</p>
		<p>
			<label for="cf_seo_title"><?php esc_html_e( 'SEO title (browser tab and Google, about 60 characters). Ignored when Rank Math or Yoast is active; use their box instead.', 'claimfairly' ); ?></label>
			<input type="text" id="cf_seo_title" name="cf_seo_title" maxlength="80" value="<?php echo esc_attr( $seo ); ?>">
		</p>
		<p>
			<label for="cf_tool_key"><?php esc_html_e( 'Which tool is on this page? (sets its color and icon)', 'claimfairly' ); ?></label>
			<select id="cf_tool_key" name="cf_tool_key">
				<option value=""><?php esc_html_e( 'None', 'claimfairly' ); ?></option>
				<?php foreach ( array_keys( claimfairly_tool_styles() ) as $key ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $tool, $key ); ?>><?php echo esc_html( $key ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="cf_last_reviewed"><?php esc_html_e( 'Last reviewed', 'claimfairly' ); ?></label>
			<input type="date" id="cf_last_reviewed" name="cf_last_reviewed" value="<?php echo esc_attr( $reviewed ); ?>">
			<span class="description"><?php esc_html_e( 'Update this every time you re-check the page against its sources (target: every 3 months).', 'claimfairly' ); ?></span>
		</p>
		<p>
			<label for="cf_sources"><?php esc_html_e( 'Sources (one per line: Title | URL)', 'claimfairly' ); ?></label>
			<textarea id="cf_sources" name="cf_sources" rows="4" placeholder="California Civil Code § 1714 | https://leginfo.legislature.ca.gov/..."><?php echo esc_textarea( $sources ); ?></textarea>
		</p>
		<p>
			<label for="cf_related"><?php esc_html_e( 'Related tools / pages (one per line: Label | URL). Leave empty to auto-pick 3 tools.', 'claimfairly' ); ?></label>
			<textarea id="cf_related" name="cf_related" rows="3" placeholder="Pain & Suffering Calculator | /pain-and-suffering-calculator/"><?php echo esc_textarea( $related ); ?></textarea>
		</p>
		<p>
			<label for="cf_card_summary"><?php esc_html_e( 'Card summary (short line used on homepage and related-tool cards)', 'claimfairly' ); ?></label>
			<input type="text" id="cf_card_summary" name="cf_card_summary" maxlength="160" value="<?php echo esc_attr( $summary ); ?>">
		</p>
		<p>
			<label><input type="checkbox" name="cf_hide_author" value="1" <?php checked( $hide ); ?>> <?php esc_html_e( 'Hide author box on this page', 'claimfairly' ); ?></label>
		</p>
	</div>
	<?php
}

/**
 * Save the meta box.
 *
 * @param int $post_id Post ID.
 */
function claimfairly_save_meta( $post_id ) {
	if ( ! isset( $_POST['claimfairly_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['claimfairly_meta_nonce'] ) ), 'claimfairly_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type = isset( $_POST['cf_page_type'] ) ? sanitize_key( wp_unslash( $_POST['cf_page_type'] ) ) : 'standard';
	if ( ! array_key_exists( $type, claimfairly_page_types() ) ) {
		$type = 'standard';
	}
	update_post_meta( $post_id, '_cf_page_type', $type );

	$reviewed = isset( $_POST['cf_last_reviewed'] ) ? sanitize_text_field( wp_unslash( $_POST['cf_last_reviewed'] ) ) : '';
	if ( $reviewed && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $reviewed ) ) {
		$reviewed = '';
	}
	update_post_meta( $post_id, '_cf_last_reviewed', $reviewed );

	foreach ( array( 'cf_sources', 'cf_related' ) as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
		update_post_meta( $post_id, '_' . $field, $value );
	}

	$seo_title = isset( $_POST['cf_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cf_seo_title'] ) ) : '';
	update_post_meta( $post_id, '_cf_seo_title', $seo_title );

	$summary = isset( $_POST['cf_card_summary'] ) ? sanitize_text_field( wp_unslash( $_POST['cf_card_summary'] ) ) : '';
	update_post_meta( $post_id, '_cf_card_summary', $summary );

	update_post_meta( $post_id, '_cf_hide_author', isset( $_POST['cf_hide_author'] ) ? 1 : 0 );

	$tool = isset( $_POST['cf_tool_key'] ) ? sanitize_key( wp_unslash( $_POST['cf_tool_key'] ) ) : '';
	update_post_meta( $post_id, '_cf_tool_key', array_key_exists( $tool, claimfairly_tool_styles() ) ? $tool : '' );
}
add_action( 'save_post', 'claimfairly_save_meta' );

/**
 * Page type for a post. Blog posts default to "guide".
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function claimfairly_get_page_type( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$type    = (string) get_post_meta( $post_id, '_cf_page_type', true );
	if ( '' === $type ) {
		$type = ( 'post' === get_post_type( $post_id ) ) ? 'guide' : 'standard';
	}
	return $type;
}

/**
 * Whether a page should show the reviewed date, sources and author box.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function claimfairly_is_trust_page( $post_id = null ) {
	return 'standard' !== claimfairly_get_page_type( $post_id );
}

/**
 * Parse "Label | URL" lines into an array of [label, url].
 *
 * @param string $raw Raw textarea value.
 * @return array<int,array{label:string,url:string}>
 */
function claimfairly_parse_link_lines( $raw ) {
	$items = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) ) {
			$label = $parts[0];
			$url   = $parts[1];
		} else {
			$label = $parts[0];
			$url   = $parts[0];
		}
		$url = esc_url_raw( $url );
		if ( $url ) {
			$items[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}
	}
	return $items;
}
