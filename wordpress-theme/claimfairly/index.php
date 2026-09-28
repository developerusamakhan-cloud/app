<?php
/**
 * Fallback listing template (blog index, archives, search).
 *
 * @package ClaimFairly
 */

get_header();
?>
<div class="container container--narrow">
	<?php claimfairly_breadcrumbs(); ?>
	<header class="entry__header">
		<h1 class="entry__title">
			<?php
			if ( is_search() ) {
				/* translators: %s: search query. */
				printf( esc_html__( 'Search results for "%s"', 'claimfairly' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
				echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
			} else {
				esc_html_e( 'Guides', 'claimfairly' );
			}
			?>
		</h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<div class="entry__intro"><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<ul class="cf-cards cf-cards--list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
			?>
		</ul>
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => __( 'Previous', 'claimfairly' ),
				'next_text' => __( 'Next', 'claimfairly' ),
			)
		);
		?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found. Try a different search.', 'claimfairly' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
