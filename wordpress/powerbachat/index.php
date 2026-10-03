<?php
/**
 * Fallback template: blog index and anything without a more specific template.
 *
 * @package PowerBachat
 */

get_header();
?>
<div class="page-head">
	<div class="wrap">
		<p class="kicker"><?php esc_html_e( 'Guides', 'powerbachat' ); ?></p>
		<h1 class="page-head__title">
			<?php
			if ( is_home() && ! is_front_page() ) {
				single_post_title();
			} else {
				esc_html_e( 'Latest guides', 'powerbachat' );
			}
			?>
		</h1>
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
		<?php
		the_posts_pagination(
			array(
				'prev_text' => __( 'Newer', 'powerbachat' ),
				'next_text' => __( 'Older', 'powerbachat' ),
			)
		);
		?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
