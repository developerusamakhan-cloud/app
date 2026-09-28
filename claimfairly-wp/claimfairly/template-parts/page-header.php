<?php
/**
 * Tinted header band for pages and posts: breadcrumbs, icon, title, intro, byline.
 *
 * @package ClaimFairly
 */

$claimfairly_style = claimfairly_page_style();
$claimfairly_type  = claimfairly_get_page_type();
$claimfairly_intro = has_excerpt() ? get_the_excerpt() : '';
?>
<header class="page-head c-<?php echo esc_attr( $claimfairly_style['color'] ); ?>">
	<div class="wrap">
		<?php claimfairly_breadcrumbs(); ?>
		<div class="page-head__row">
			<?php if ( 'standard' !== $claimfairly_type ) : ?>
				<span class="icon-sq icon-sq--lg c-<?php echo esc_attr( $claimfairly_style['color'] ); ?>"><?php echo claimfairly_icon( $claimfairly_style['icon'], 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<?php endif; ?>
			<div>
				<h1 class="page-head__title"><?php the_title(); ?></h1>
				<?php if ( $claimfairly_intro ) : ?>
					<p class="page-head__intro"><?php echo esc_html( $claimfairly_intro ); ?></p>
				<?php endif; ?>
				<?php claimfairly_byline(); ?>
			</div>
		</div>
	</div>
</header>
