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

// Author card for the header: who writes here, plus a way to ask a question.
$nabia_posts = (int) wp_count_posts( 'post' )->publish;
$nabia_aside = '<div class="aside-card author-card">'
	. '<span class="author-avatar">' . nabia_author_photo( 64 ) . '</span>'
	. '<p class="aside-card-title">' . esc_html__( 'Written by', 'nabia' ) . '</p>'
	. '<p class="author-name">' . esc_html( nabia_mod( 'brand_name' ) ) . '</p>'
	. '<p class="author-role">' . esc_html__( 'WordPress developer & graphic designer', 'nabia' ) . '</p>'
	. '<dl class="aside-rows">'
	. '<div><dt>' . esc_html__( 'Articles', 'nabia' ) . '</dt><dd>' . esc_html( number_format_i18n( $nabia_posts ) ) . '</dd></div>'
	. '<div><dt>' . esc_html__( 'Experience', 'nabia' ) . '</dt><dd>' . esc_html( nabia_mod( 'stat_1_number' ) . nabia_mod( 'stat_1_suffix' ) . ' ' . __( 'years', 'nabia' ) ) . '</dd></div>'
	. '<div><dt>' . esc_html__( 'Written for', 'nabia' ) . '</dt><dd>' . esc_html__( 'Business owners', 'nabia' ) . '</dd></div>'
	. '</dl>'
	. nabia_button( __( 'Ask me a question', 'nabia' ), nabia_hire_url() )
	. '</div>';

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
				<p><?php esc_html_e( 'Nothing found here yet. Try another search?', 'nabia' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/why' );
get_footer();
