<?php
/**
 * "Related guides" under a post: up to 3 published guides for the same country.
 * Scheduled posts never appear here, so there are no links to pages that 404.
 *
 * @package PowerBachat
 */

$powerbachat_country = powerbachat_post_country( get_the_ID() );
$powerbachat_term    = $powerbachat_country ? powerbachat_country_category( $powerbachat_country ) : null;
$powerbachat_args    = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 3,
	'post__not_in'        => array( get_the_ID() ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $powerbachat_term ) {
	$powerbachat_args['cat'] = $powerbachat_term->term_id;
}
$powerbachat_related = new WP_Query( $powerbachat_args );
if ( ! $powerbachat_related->have_posts() ) {
	return;
}
?>
<section class="related" aria-labelledby="related-title">
	<div class="wrap">
		<h2 class="related__title" id="related-title"><?php esc_html_e( 'Related guides', 'powerbachat' ); ?></h2>
		<div class="guide-list guide-list--archive">
			<?php
			while ( $powerbachat_related->have_posts() ) :
				$powerbachat_related->the_post();
				get_template_part( 'template-parts/content/card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php if ( $powerbachat_country ) : ?>
			<p class="related__more"><a class="btn btn--ghost" href="<?php echo esc_url( powerbachat_guides_url( $powerbachat_country ) ); ?>"><?php esc_html_e( 'All guides', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
		<?php endif; ?>
	</div>
</section>
