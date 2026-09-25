<?php
/**
 * Large featured post card (first post on the blog page).
 *
 * @package Nabia
 */

$nabia_cats    = get_the_category();
$nabia_minutes = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
?>
<article <?php post_class( 'post-featured' ); ?> data-reveal>
	<a class="post-featured-link" href="<?php the_permalink(); ?>">
		<div class="post-featured-media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'nabia-project' ); ?>
			<?php else : ?>
				<span class="post-card-fallback" aria-hidden="true"><?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="post-featured-body">
			<p class="post-kicker">
				<span class="post-badge"><?php esc_html_e( 'Latest', 'nabia' ); ?></span>
				<?php if ( $nabia_cats ) : ?>
					<span><?php echo esc_html( $nabia_cats[0]->name ); ?></span>
				<?php endif; ?>
			</p>
			<h2 class="post-featured-title"><?php the_title(); ?></h2>
			<p class="post-card-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
			<p class="post-meta">
				<?php echo esc_html( get_the_date() ); ?> ·
				<?php
				/* translators: %d: minutes */
				echo esc_html( sprintf( _n( '%d min read', '%d min read', $nabia_minutes, 'nabia' ), $nabia_minutes ) );
				?>
			</p>
			<span class="read-more"><?php esc_html_e( 'Read article', 'nabia' ); ?> <?php nabia_icon( 'arrow' ); ?></span>
		</div>
	</a>
</article>
