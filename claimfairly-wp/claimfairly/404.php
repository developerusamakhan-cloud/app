<?php
/**
 * 404 page.
 *
 * @package ClaimFairly
 */

get_header();
$claimfairly_tools = claimfairly_get_tools();
?>
<header class="page-head c-orange">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'Error 404', 'claimfairly' ); ?></p>
		<h1 class="page-head__title"><?php esc_html_e( 'This page took a wrong turn', 'claimfairly' ); ?></h1>
		<p class="page-head__intro"><?php esc_html_e( 'It may have moved, or the link had a typo. One of these is probably what you were after.', 'claimfairly' ); ?></p>
	</div>
</header>
<div class="wrap section section--tight">
	<?php if ( $claimfairly_tools ) : ?>
		<ul class="tool-rows tool-rows--grid">
			<?php
			foreach ( $claimfairly_tools as $claimfairly_tool ) {
				echo claimfairly_tool_row( $claimfairly_tool ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function.
			}
			?>
		</ul>
	<?php endif; ?>
	<?php get_search_form(); ?>
</div>
<?php
get_footer();
