<?php
/**
 * Single page (tools, state pages, policy pages…).
 *
 * @package ClaimFairly
 */

get_header();

while ( have_posts() ) :
	the_post();
	$claimfairly_type = claimfairly_get_page_type();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--' . $claimfairly_type ); ?>>
		<div class="container container--narrow">
			<?php claimfairly_breadcrumbs(); ?>
			<header class="entry__header">
				<h1 class="entry__title"><?php the_title(); ?></h1>
				<?php claimfairly_byline(); ?>
			</header>
			<div class="entry__content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
			<?php claimfairly_trust_footer(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
