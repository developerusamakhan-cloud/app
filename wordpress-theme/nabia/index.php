<?php
/**
 * Blog index, archives and search results.
 *
 * @package Nabia
 */

get_header();
?>
<section class="page-hero">
	<div class="container">
		<p class="eyebrow" data-reveal><?php echo is_search() ? esc_html__( 'Search', 'nabia' ) : esc_html__( 'Journal', 'nabia' ); ?></p>
		<h1 class="page-title" data-split>
			<?php
			if ( is_search() ) {
				/* translators: %s: search query */
				printf( esc_html__( 'Results for “%s”', 'nabia' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			} elseif ( is_home() && ! is_front_page() ) {
				echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
			} else {
				esc_html_e( 'Latest articles', 'nabia' );
			}
			?>
		</h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<div class="page-intro" data-reveal><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</div>
</section>

<section class="section section-tight">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>
			<?php nabia_pagination(); ?>
		<?php else : ?>
			<div class="empty-state">
				<p><?php esc_html_e( 'Nothing found here yet. Try a search?', 'nabia' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
