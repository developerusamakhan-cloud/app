<?php
/**
 * Post card.
 *
 * @package Nabia
 */

?>
<article <?php post_class( 'post-card' ); ?> data-reveal>
	<a class="post-card-link" href="<?php the_permalink(); ?>">
		<div class="post-card-media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'nabia-card', array( 'loading' => 'lazy' ) ); ?>
			<?php else : ?>
				<span class="post-card-fallback" aria-hidden="true"><?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="post-card-body">
			<p class="post-meta"><?php echo esc_html( get_the_date() ); ?> · <?php
			$nabia_min = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
			/* translators: %d: minutes */
			echo esc_html( sprintf( __( '%d min read', 'nabia' ), $nabia_min ) );
			?></p>
			<h2 class="post-card-title"><?php the_title(); ?></h2>
			<p class="post-card-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
			<span class="read-more"><?php esc_html_e( 'Read more', 'nabia' ); ?> <?php nabia_icon( 'arrow' ); ?></span>
		</div>
	</a>
</article>
