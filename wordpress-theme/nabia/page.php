<?php
/**
 * Default page template.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	$nabia_parent = wp_get_post_parent_id( get_the_ID() );
	nabia_page_header(
		array(
			'eyebrow' => $nabia_parent ? get_the_title( $nabia_parent ) : '',
			'title'   => get_the_title(),
			'intro'   => has_excerpt() ? get_the_excerpt() : '',
			'crumbs'  => $nabia_parent ? array( array( get_the_title( $nabia_parent ), get_permalink( $nabia_parent ) ) ) : array(),
		)
	);
	?>
	<article <?php post_class( 'section section-tight' ); ?>>
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="featured-media featured-wide" data-reveal><?php the_post_thumbnail( 'full' ); ?></figure>
			<?php endif; ?>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
