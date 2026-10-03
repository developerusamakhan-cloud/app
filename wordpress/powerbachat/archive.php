<?php
/**
 * Category, tag, date and author archives.
 *
 * @package PowerBachat
 */

get_header();
?>
<div class="page-head">
	<div class="wrap">
		<p class="kicker"><?php esc_html_e( 'Archive', 'powerbachat' ); ?></p>
		<h1 class="page-head__title"><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
		<?php the_archive_description( '<div class="page-head__lede">', '</div>' ); ?>
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
