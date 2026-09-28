<?php
/**
 * Post card used in listings.
 *
 * @package ClaimFairly
 */

?>
<li class="cf-card">
	<a class="cf-card__link" href="<?php the_permalink(); ?>">
		<span class="cf-card__title"><?php the_title(); ?></span>
		<span class="cf-card__text"><?php echo esc_html( claimfairly_card_summary( get_post() ) ); ?></span>
	</a>
</li>
