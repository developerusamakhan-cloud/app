<?php
/**
 * Pages and single posts.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="page-head">
		<div class="wrap">
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<div class="wrap">
		<article <?php post_class( 'content' ); ?>>
			<?php
			the_content();
			wp_link_pages();
			?>
		</article>
	</div>
	<?php
endwhile;
get_footer();
