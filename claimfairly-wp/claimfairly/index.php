<?php
/**
 * Listings: guides index, categories, search.
 *
 * @package ClaimFairly
 */

get_header();

if ( is_search() ) {
	/* translators: %s: search query. */
	$claimfairly_title = sprintf( __( 'Results for "%s"', 'claimfairly' ), get_search_query() );
	$claimfairly_intro = '';
} elseif ( is_archive() ) {
	$claimfairly_title = wp_strip_all_tags( get_the_archive_title() );
	$claimfairly_intro = wp_strip_all_tags( get_the_archive_description() );
} else {
	$claimfairly_title = get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'Guides', 'claimfairly' );
	$claimfairly_intro = __( 'Clear, sourced explanations of how car accident claims actually work, from diminished value to what your lawyer takes.', 'claimfairly' );
}
?>
<header class="page-head c-green">
	<div class="wrap">
		<?php claimfairly_breadcrumbs(); ?>
		<h1 class="page-head__title"><?php echo esc_html( $claimfairly_title ); ?></h1>
		<?php if ( $claimfairly_intro ) : ?>
			<p class="page-head__intro"><?php echo esc_html( $claimfairly_intro ); ?></p>
		<?php endif; ?>
		<?php
		if ( ! is_search() ) :
			$claimfairly_cats = get_categories( array( 'hide_empty' => true ) );
			if ( count( $claimfairly_cats ) > 1 ) :
				?>
				<ul class="chips chips--left">
					<?php foreach ( $claimfairly_cats as $claimfairly_cat ) : ?>
						<li><a class="chip<?php echo is_category( $claimfairly_cat->term_id ) ? ' chip--dark' : ''; ?>" href="<?php echo esc_url( get_category_link( $claimfairly_cat ) ); ?>"><?php echo esc_html( $claimfairly_cat->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php
			endif;
		endif;
		?>
	</div>
</header>

<div class="wrap section section--tight">
	<?php if ( have_posts() ) : ?>
		<ul class="guide-grid">
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
		<p><?php esc_html_e( 'Nothing matched that. Try a different word, or open one of the calculators.', 'claimfairly' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
