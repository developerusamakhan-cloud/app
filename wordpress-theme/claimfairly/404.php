<?php
/**
 * 404 page.
 *
 * @package ClaimFairly
 */

get_header();
?>
<div class="container container--narrow">
	<header class="entry__header">
		<h1 class="entry__title"><?php esc_html_e( 'Page not found', 'claimfairly' ); ?></h1>
	</header>
	<p><?php esc_html_e( 'That page does not exist or has moved. These free tools might be what you were looking for:', 'claimfairly' ); ?></p>
	<?php
	$claimfairly_tools = claimfairly_get_tools();
	if ( $claimfairly_tools ) :
		?>
		<ul class="cf-cards">
			<?php foreach ( $claimfairly_tools as $claimfairly_tool ) : ?>
				<li class="cf-card">
					<a class="cf-card__link" href="<?php echo esc_url( get_permalink( $claimfairly_tool ) ); ?>">
						<span class="cf-card__title"><?php echo esc_html( get_the_title( $claimfairly_tool ) ); ?></span>
						<span class="cf-card__text"><?php echo esc_html( claimfairly_card_summary( $claimfairly_tool ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php get_search_form(); ?>
</div>
<?php
get_footer();
