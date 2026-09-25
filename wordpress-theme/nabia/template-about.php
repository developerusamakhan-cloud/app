<?php
/**
 * Template Name: About me
 * Template Post Type: page
 *
 * About page: intro video + story, skills, process, video reviews, reviews and contact.
 *
 * @package Nabia
 */

get_header();
?>
<section class="page-hero">
	<div class="container">
		<p class="eyebrow" data-reveal><?php esc_html_e( 'About', 'nabia' ); ?></p>
		<h1 class="page-title" data-split><?php the_title(); ?></h1>
	</div>
</section>
<?php
get_template_part( 'template-parts/home', 'about' );
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/home', 'platforms' );
get_template_part( 'template-parts/home', 'process' );
get_template_part( 'template-parts/home', 'videos' );
get_template_part( 'template-parts/home', 'testimonials' );
get_footer();
