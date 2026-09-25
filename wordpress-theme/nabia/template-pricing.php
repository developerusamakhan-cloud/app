<?php
/**
 * Template Name: Pricing
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();
?>
<section class="page-hero">
	<div class="container">
		<p class="eyebrow" data-reveal><?php esc_html_e( 'Pricing', 'nabia' ); ?></p>
		<h1 class="page-title" data-split><?php the_title(); ?></h1>
	</div>
</section>
<?php
get_template_part( 'template-parts/home', 'pricing' );
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/home', 'faq' );
get_template_part( 'template-parts/home', 'audit' );
get_footer();
