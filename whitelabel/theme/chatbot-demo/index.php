<?php
/**
 * Blog, archives and search.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-head">
	<div class="wrap">
		<h1>
			<?php
			if ( is_search() ) {
				/* translators: %s: search term */
				printf( esc_html__( 'Search: %s', 'chatbot-demo' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo wp_kses_post( get_the_archive_title() );
			} else {
				esc_html_e( 'Articles', 'chatbot-demo' );
			}
			?>
		</h1>
	</div>
</section>
<div class="wrap">
	<div class="content post-list">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
			<div class="pagination"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found. Try the chat in the corner, it knows this website.', 'chatbot-demo' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
