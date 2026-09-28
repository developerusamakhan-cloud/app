<?php
/**
 * Single guide.
 *
 * @package ClaimFairly
 */

get_header();

while ( have_posts() ) :
	the_post();
	$claimfairly_type = claimfairly_get_page_type();
	$claimfairly_side = claimfairly_is_trust_page();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--' . $claimfairly_type ); ?>>
		<?php get_template_part( 'template-parts/page-header' ); ?>
		<div class="wrap">
			<div class="layout<?php echo $claimfairly_side ? ' layout--side' : ''; ?>">
				<div class="layout__main">
					<div class="prose">
						<?php
						the_content();
						wp_link_pages();
						?>
					</div>
					<?php claimfairly_trust_footer(); ?>
				</div>
				<?php if ( $claimfairly_side ) : ?>
					<?php get_template_part( 'template-parts/sidebar' ); ?>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
