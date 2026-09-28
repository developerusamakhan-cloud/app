<?php
/**
 * Sticky sidebar on wide screens: "On this page" (filled by site.js) and a tool shortcut.
 *
 * @package ClaimFairly
 */

$claimfairly_tools = claimfairly_get_tools( 5, get_the_ID() );
?>
<aside class="layout__side" aria-label="<?php esc_attr_e( 'On this page', 'claimfairly' ); ?>">
	<div class="side-sticky">
		<nav class="toc" data-toc hidden>
			<p class="toc__title"><?php esc_html_e( 'On this page', 'claimfairly' ); ?></p>
			<ol></ol>
		</nav>
		<?php if ( $claimfairly_tools ) : ?>
			<div class="side-card">
				<p class="side-card__title"><?php esc_html_e( 'Free calculators', 'claimfairly' ); ?></p>
				<ul class="tool-rows tool-rows--compact">
					<?php
					foreach ( array_slice( $claimfairly_tools, 0, 4 ) as $claimfairly_tool ) {
						echo claimfairly_tool_row( $claimfairly_tool ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function.
					}
					?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</aside>
