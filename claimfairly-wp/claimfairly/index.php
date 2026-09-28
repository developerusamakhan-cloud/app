<?php
/**
 * Blog index, category archives and search results.
 *
 * On the first page of the blog, the newest article is shown as a large
 * featured card. Categories are shown as filter chips with post counts.
 *
 * @package ClaimFairly
 */

get_header();

$claimfairly_blog_id = (int) get_option( 'page_for_posts' );
$claimfairly_blog    = $claimfairly_blog_id ? get_permalink( $claimfairly_blog_id ) : home_url( '/' );

if ( is_search() ) {
	/* translators: %s: search query. */
	$claimfairly_title = sprintf( __( 'Results for "%s"', 'claimfairly' ), get_search_query() );
	$claimfairly_intro = '';
	$claimfairly_kick  = __( 'Search', 'claimfairly' );
} elseif ( is_category() ) {
	$claimfairly_title = single_cat_title( '', false );
	$claimfairly_intro = wp_strip_all_tags( category_description() );
	$claimfairly_kick  = __( 'Blog category', 'claimfairly' );
} elseif ( is_archive() ) {
	$claimfairly_title = wp_strip_all_tags( get_the_archive_title() );
	$claimfairly_intro = wp_strip_all_tags( get_the_archive_description() );
	$claimfairly_kick  = __( 'Blog', 'claimfairly' );
} else {
	$claimfairly_title = __( 'The ClaimFairly Blog', 'claimfairly' );
	$claimfairly_intro = $claimfairly_blog_id && has_excerpt( $claimfairly_blog_id ) ? get_the_excerpt( $claimfairly_blog_id ) : __( 'Plain-English articles on how car accident claims actually work.', 'claimfairly' );
	$claimfairly_kick  = __( 'Blog', 'claimfairly' );
}
$claimfairly_featured = is_home() && ! is_paged() && have_posts();
?>
<header class="page-head c-green">
	<div class="wrap">
		<div class="page-head__grid page-head__grid--side">
			<div class="page-head__main">
				<?php claimfairly_breadcrumbs(); ?>
				<p class="kicker"><span class="kicker__icon"><?php echo claimfairly_icon( 'book', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php echo esc_html( $claimfairly_kick ); ?></p>
				<h1 class="page-head__title"><?php echo esc_html( $claimfairly_title ); ?></h1>
				<?php if ( $claimfairly_intro ) : ?>
					<p class="page-head__intro"><?php echo esc_html( $claimfairly_intro ); ?></p>
				<?php endif; ?>
			</div>
			<div class="page-head__meta">
				<?php get_search_form( array( 'post_type' => 'post' ) ); ?>
			</div>
		</div>
		<?php
		$claimfairly_cats = get_categories(
			array(
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);
		if ( $claimfairly_cats ) :
			?>
			<nav class="blog-cats" aria-label="<?php esc_attr_e( 'Blog categories', 'claimfairly' ); ?>">
				<ul class="chips">
					<li><a class="chip<?php echo is_home() ? ' chip--dark' : ''; ?>" href="<?php echo esc_url( $claimfairly_blog ); ?>"><?php esc_html_e( 'All', 'claimfairly' ); ?> <span class="chip__count"><?php echo (int) wp_count_posts()->publish; ?></span></a></li>
					<?php foreach ( $claimfairly_cats as $claimfairly_cat ) : ?>
						<li><a class="chip<?php echo is_category( $claimfairly_cat->term_id ) ? ' chip--dark' : ''; ?>" href="<?php echo esc_url( get_category_link( $claimfairly_cat ) ); ?>"><?php echo esc_html( $claimfairly_cat->name ); ?> <span class="chip__count"><?php echo (int) $claimfairly_cat->count; ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</header>

<div class="wrap section section--tight">
	<?php if ( have_posts() ) : ?>
		<?php
		if ( $claimfairly_featured ) :
			the_post();
			$claimfairly_cats_f = get_the_category();
			$claimfairly_thumb  = (int) get_post_thumbnail_id();
			$claimfairly_look   = claimfairly_post_look( get_post() );
			?>
			<article class="featured-post">
				<a class="featured-post__link" href="<?php the_permalink(); ?>">
					<span class="featured-post__media c-<?php echo esc_attr( $claimfairly_look['color'] ); ?>">
						<?php if ( $claimfairly_thumb && ! $claimfairly_look['own_share'] ) : ?>
							<?php echo wp_get_attachment_image( $claimfairly_thumb, 'large', false, array( 'alt' => '', 'fetchpriority' => 'high' ) ); ?>
						<?php else : ?>
							<span class="featured-post__icon"><?php echo claimfairly_icon( $claimfairly_look['icon'], 56 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span class="featured-post__chip featured-post__chip--a"><?php echo claimfairly_icon( 'check', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Plain English', 'claimfairly' ); ?></span>
							<span class="featured-post__chip featured-post__chip--b"><?php echo claimfairly_icon( 'badge', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Sources cited', 'claimfairly' ); ?></span>
						<?php endif; ?>
					</span>
					<span class="featured-post__body">
						<span class="featured-post__label"><?php esc_html_e( 'Latest article', 'claimfairly' ); ?><?php echo $claimfairly_cats_f ? ' · ' . esc_html( $claimfairly_cats_f[0]->name ) : ''; ?></span>
						<span class="featured-post__title"><?php the_title(); ?></span>
						<span class="featured-post__text"><?php echo esc_html( claimfairly_card_summary( get_post() ) ); ?></span>
						<span class="featured-post__meta">
							<span class="meta-card__avatar" aria-hidden="true"><?php echo esc_html( claimfairly_initials( claimfairly_founder_name() ) ); ?></span>
							<?php echo esc_html( claimfairly_founder_name() ); ?>
							<span class="dot" aria-hidden="true"></span>
							<?php
							/* translators: %d: minutes. */
							echo esc_html( sprintf( __( '%d min read', 'claimfairly' ), claimfairly_read_minutes( get_the_ID() ) ) );
							?>
						</span>
						<span class="btn btn--dark btn--sm featured-post__cta"><?php esc_html_e( 'Read the article', 'claimfairly' ); ?></span>
					</span>
				</a>
			</article>
		<?php endif; ?>

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
		<div class="empty-state">
			<p><?php esc_html_e( 'Nothing matched that. Try a different word, or open one of the calculators.', 'claimfairly' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="blog-cta">
		<div>
			<p class="blog-cta__title"><?php esc_html_e( 'Rather see your own numbers?', 'claimfairly' ); ?></p>
			<p><?php esc_html_e( 'Our free calculators turn what you read here into a range for your own claim.', 'claimfairly' ); ?></p>
		</div>
		<a class="btn btn--dark" href="<?php echo esc_url( claimfairly_tool_page_url( 'settlement-estimator' ) ); ?>"><?php esc_html_e( 'Estimate my claim', 'claimfairly' ); ?></a>
	</div>
</div>
<?php
get_footer();
