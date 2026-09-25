<?php
/**
 * Single blog post.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class(); ?>>
		<section class="page-hero">
			<div class="container container-narrow">
				<p class="post-meta" data-reveal>
					<?php echo esc_html( get_the_date() ); ?>
					· <?php echo esc_html( get_the_author() ); ?>
					<?php
					$nabia_minutes = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
					/* translators: %d: minutes */
					echo ' · ' . esc_html( sprintf( _n( '%d min read', '%d min read', $nabia_minutes, 'nabia' ), $nabia_minutes ) );
					?>
				</p>
				<h1 class="page-title" data-split><?php the_title(); ?></h1>
			</div>
		</section>

		<div class="section section-tight">
			<div class="container container-narrow">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="featured-media" data-reveal><?php the_post_thumbnail( 'full' ); ?></figure>
				<?php endif; ?>

				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages();
					?>
				</div>

				<footer class="entry-footer">
					<?php the_tags( '<ul class="tags"><li>', '</li><li>', '</li></ul>' ); ?>
				</footer>

				<?php
				the_post_navigation(
					array(
						'prev_text' => '<span class="nav-label">' . esc_html__( 'Previous', 'nabia' ) . '</span><span class="nav-title">%title</span>',
						'next_text' => '<span class="nav-label">' . esc_html__( 'Next', 'nabia' ) . '</span><span class="nav-title">%title</span>',
					)
				);

				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
