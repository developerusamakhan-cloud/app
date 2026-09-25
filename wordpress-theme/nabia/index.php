<?php
/**
 * Blog overview, category/tag archives and search results.
 *
 * @package Nabia
 */

get_header();

$nabia_blog_url = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/' );

if ( is_search() ) {
	/* translators: %s: search query */
	$nabia_title = sprintf( __( 'Results for “%s”', 'nabia' ), get_search_query() );
	$nabia_intro = '';
} elseif ( is_archive() ) {
	$nabia_title = wp_strip_all_tags( get_the_archive_title() );
	$nabia_intro = wp_strip_all_tags( get_the_archive_description() );
} else {
	$nabia_title = get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'Blog', 'nabia' );
	$nabia_intro = '';
}
if ( ! $nabia_intro ) {
	$nabia_intro = __( 'Practical tips on WordPress, web design, SEO, e-commerce and AI, to help your website work harder for your business.', 'nabia' );
}

// "Browse topics" card for the header.
$nabia_cats  = get_categories(
	array(
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 6,
		'hide_empty' => true,
	)
);
$nabia_aside = '';
if ( $nabia_cats ) {
	$nabia_aside = '<div class="aside-card"><p class="aside-card-title">' . esc_html__( 'Browse topics', 'nabia' ) . '</p><ul class="topic-list">';
	foreach ( $nabia_cats as $nabia_cat ) {
		$nabia_aside .= '<li><a href="' . esc_url( get_category_link( $nabia_cat ) ) . '"><span>' . esc_html( $nabia_cat->name ) . '</span><em>' . (int) $nabia_cat->count . '</em></a></li>';
	}
	$nabia_aside .= '</ul></div>';
}

nabia_page_header(
	array(
		'eyebrow' => is_search() ? __( 'Search', 'nabia' ) : __( 'Blog', 'nabia' ),
		'icon'    => is_search() ? 'search' : 'pen',
		'title'   => $nabia_title,
		'intro'   => $nabia_intro,
		'crumbs'  => ( is_archive() || is_search() ) ? array( array( __( 'Blog', 'nabia' ), $nabia_blog_url ) ) : array(),
		'aside'   => $nabia_aside,
		'class'   => 'is-blog',
	)
);
?>

<section class="section section-tight blog-list">
	<div class="container">
		<?php if ( ( is_home() || is_category() ) && $nabia_cats ) : ?>
			<ul class="filter-pills">
				<li><a class="<?php echo is_home() ? 'is-active' : ''; ?>" href="<?php echo esc_url( $nabia_blog_url ); ?>"><?php esc_html_e( 'All', 'nabia' ); ?></a></li>
				<?php foreach ( $nabia_cats as $nabia_cat ) : ?>
					<li><a class="<?php echo is_category( $nabia_cat->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $nabia_cat ) ); ?>"><?php echo esc_html( $nabia_cat->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<?php
			$nabia_first = is_home() && ! is_paged();
			if ( $nabia_first ) {
				the_post();
				get_template_part( 'template-parts/content', 'featured' );
			}
			?>
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
				<p><?php esc_html_e( 'Nothing found here yet. Try another search?', 'nabia' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/why' );
get_footer();
