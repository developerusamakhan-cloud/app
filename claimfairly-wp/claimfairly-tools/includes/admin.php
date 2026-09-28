<?php
/**
 * Admin screen: Tools → ClaimFairly Tools. Lists shortcodes and state
 * verification status.
 *
 * @package ClaimFairlyTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the screen.
 */
function cft_admin_menu() {
	add_management_page(
		__( 'ClaimFairly Tools', 'claimfairly-tools' ),
		__( 'ClaimFairly Tools', 'claimfairly-tools' ),
		'edit_posts',
		'claimfairly-tools',
		'cft_admin_page'
	);
}
add_action( 'admin_menu', 'cft_admin_menu' );

/**
 * Render the screen.
 */
function cft_admin_page() {
	$states     = cft_states();
	$unverified = array_filter(
		$states,
		static function ( $s ) {
			return empty( $s['verified'] );
		}
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ClaimFairly Tools', 'claimfairly-tools' ); ?></h1>
		<p><?php esc_html_e( 'Paste a shortcode into a Shortcode block to place a tool. Each tool loads its script only on pages where it is used.', 'claimfairly-tools' ); ?></p>

		<h2><?php esc_html_e( 'Tools', 'claimfairly-tools' ); ?></h2>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><th><?php esc_html_e( 'Tool', 'claimfairly-tools' ); ?></th><th><?php esc_html_e( 'Shortcode', 'claimfairly-tools' ); ?></th><th><?php esc_html_e( 'Expected page', 'claimfairly-tools' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( cft_tools() as $name => $tool ) : ?>
				<tr>
					<td><?php echo esc_html( $tool['title'] ); ?></td>
					<td><code>[cf_tool name="<?php echo esc_html( $name ); ?>"]</code></td>
					<td><a href="<?php echo esc_url( cft_tool_url( $name ) ); ?>"><?php echo esc_html( $tool['path'] ); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Presets', 'claimfairly-tools' ); ?></h2>
		<ul style="list-style:disc;padding-left:20px">
			<li><code>[cf_tool name="settlement-take-home" amount="50000"]</code></li>
			<li><code>[cf_tool name="settlement-estimator" state="CA" severity="moderate"]</code></li>
			<li><code>[cf_tool name="pain-and-suffering" severity="serious"]</code></li>
			<li><code>[cf_tool name="demand-letter" type="dv"]</code> (<?php esc_html_e( 'types: injury, property, dv', 'claimfairly-tools' ); ?>)</li>
			<li><code>[cf_state_facts state="CA"]</code> and <code>[cf_state_table]</code></li>
		</ul>

		<h2><?php esc_html_e( 'State data', 'claimfairly-tools' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: 1: unverified count, 2: total. */
				esc_html__( '%1$d of %2$d states are not marked as verified yet.', 'claimfairly-tools' ),
				count( $unverified ),
				count( $states )
			);
			?>
			<?php esc_html_e( 'Edit wp-content/plugins/claimfairly-tools/data/states.json: check each row against the official statute and insurance department site, add the links to "sources", set "verified" to true and update "reviewed". Review every 3 months.', 'claimfairly-tools' ); ?>
		</p>
	</div>
	<?php
}
