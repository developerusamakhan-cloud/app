<?php
/**
 * Single blog article.
 *
 * Reading progress bar, header, content, share buttons, trust blocks,
 * related articles and previous / next links.
 *
 * @package ClaimFairly
 */

get_header();

while ( have_posts() ) :
	the_post();
	$claimfairly_id = get_the_ID();
	?>
	<div class="read-progress" aria-hidden="true"><span></span></div>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--post' ); ?>>
		<?php get_template_part( 'template-parts/page-header' ); ?>
		<div class="wrap">
			<div class="layout layout--side">
				<div class="layout__main">
					<div class="prose">
						<?php
						the_content();
						wp_link_pages();
						?>
					</div>
					<?php get_template_part( 'template-parts/share' ); ?>
					<?php claimfairly_trust_footer(); ?>
				</div>
				<?php get_template_part( 'template-parts/sidebar' ); ?>
			</div>
		</div>

		<?php
		$claimfairly_related = claimfairly_related_posts( $claimfairly_id, 3 );
		if ( $claimfairly_related ) :
			?>
			<section class="section section--soft" aria-labelledby="more-title">
				<div class="wrap">
					<div class="section__head">
						<div>
							<p class="eyebrow"><?php esc_html_e( 'Keep reading', 'claimfairly' ); ?></p>
							<h2 id="more-title" class="section__title"><?php esc_html_e( 'More from the blog', 'claimfairly' ); ?></h2>
						</div>
						<?php if ( get_option( 'page_for_posts' ) ) : ?>
							<a class="text-link" href="<?php echo esc_url( get_permalink( (int) get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'All articles', 'claimfairly' ); ?> <?php echo claimfairly_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
						<?php endif; ?>
					</div>
					<ul class="guide-grid">
						<?php
						foreach ( $claimfairly_related as $claimfairly_rel ) {
							echo claimfairly_guide_card( $claimfairly_rel ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function.
						}
						?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$claimfairly_prev = get_previous_post();
		$claimfairly_next = get_next_post();
		if ( $claimfairly_prev || $claimfairly_next ) :
			?>
			<nav class="wrap post-nav" aria-label="<?php esc_attr_e( 'More articles', 'claimfairly' ); ?>">
				<?php if ( $claimfairly_prev ) : ?>
					<a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url( get_permalink( $claimfairly_prev ) ); ?>">
						<span class="post-nav__label"><?php esc_html_e( 'Previous article', 'claimfairly' ); ?></span>
						<span class="post-nav__title"><?php echo esc_html( get_the_title( $claimfairly_prev ) ); ?></span>
					</a>
				<?php else : ?>
					<span></span>
				<?php endif; ?>
				<?php if ( $claimfairly_next ) : ?>
					<a class="post-nav__link post-nav__link--next" href="<?php echo esc_url( get_permalink( $claimfairly_next ) ); ?>">
						<span class="post-nav__label"><?php esc_html_e( 'Next article', 'claimfairly' ); ?></span>
						<span class="post-nav__title"><?php echo esc_html( get_the_title( $claimfairly_next ) ); ?></span>
					</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
