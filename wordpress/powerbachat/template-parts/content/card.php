<?php
/**
 * Post card used on archives and search.
 *
 * @package PowerBachat
 */

$powerbachat_cat = get_the_category();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'guide reveal' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<span class="guide__img"><?php the_post_thumbnail( 'powerbachat-card' ); ?></span>
		<?php endif; ?>
		<span class="guide__tag"><?php echo esc_html( $powerbachat_cat ? $powerbachat_cat[0]->name : get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
		<span class="guide__title"><?php the_title(); ?></span>
		<span class="guide__text"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></span>
		<span class="guide__meta">
			<?php
			/* translators: %d: minutes */
			printf( esc_html__( '%d min read', 'powerbachat' ), (int) powerbachat_reading_time() );
			?>
		</span>
	</a>
</article>
