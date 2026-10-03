<?php
/**
 * Default page.
 *
 * @package PowerBachat
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="page-head">
			<div class="wrap">
				<?php powerbachat_breadcrumbs(); ?>
				<h1 class="page-head__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="page-head__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</header>
		<div class="wrap">
			<div class="prose entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
		</div>
	</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="wrap"><div class="prose">';
		comments_template();
		echo '</div></div>';
	}
endwhile;

get_footer();
