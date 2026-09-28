<?php
/**
 * Header for pages and posts: breadcrumbs, a small kicker label with an icon,
 * the title, an intro line and (on article and tool pages) a meta card.
 *
 * The header uses the same column grid as the content below it, so the title
 * lines up exactly with the first paragraph.
 *
 * @package ClaimFairly
 */

$claimfairly_id    = get_the_ID();
$claimfairly_type  = claimfairly_get_page_type( $claimfairly_id );
$claimfairly_style = claimfairly_page_style( $claimfairly_id );
$claimfairly_side  = claimfairly_is_trust_page( $claimfairly_id );
$claimfairly_intro = has_excerpt() ? get_the_excerpt() : '';
$claimfairly_kick  = claimfairly_kicker( $claimfairly_id );
?>
<header class="page-head c-<?php echo esc_attr( $claimfairly_style['color'] ); ?>">
	<div class="wrap">
		<div class="page-head__grid<?php echo $claimfairly_side ? ' page-head__grid--side' : ''; ?>">
			<div class="page-head__main">
				<?php claimfairly_breadcrumbs(); ?>
				<?php if ( $claimfairly_kick ) : ?>
					<p class="kicker"><span class="kicker__icon"><?php echo claimfairly_icon( $claimfairly_kick['icon'], 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php echo esc_html( $claimfairly_kick['label'] ); ?></p>
				<?php endif; ?>
				<h1 class="page-head__title"><?php the_title(); ?></h1>
				<?php if ( $claimfairly_intro ) : ?>
					<p class="page-head__intro"><?php echo esc_html( $claimfairly_intro ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $claimfairly_side ) : ?>
				<aside class="page-head__meta" aria-label="<?php esc_attr_e( 'About this page', 'claimfairly' ); ?>">
					<?php claimfairly_meta_card( $claimfairly_id ); ?>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</header>
