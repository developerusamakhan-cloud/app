<?php
/**
 * Search results.
 *
 * @package PowerBachat
 */

get_header();
?>
<div class="page-head">
	<div class="wrap">
		<p class="kicker"><?php esc_html_e( 'Search', 'powerbachat' ); ?></p>
		<h1 class="page-head__title">
			<?php
			/* translators: %s: search query */
			printf( esc_html__( 'Results for “%s”', 'powerbachat' ), esc_html( get_search_query() ) );
			?>
		</h1>
		<?php get_search_form(); ?>
	</div>
</div>
<div class="wrap section--tight">
	<?php if ( have_posts() ) : ?>
		<div class="guide-list guide-list--archive">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/card' );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
