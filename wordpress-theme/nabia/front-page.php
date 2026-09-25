<?php
/**
 * Front page: one-page portfolio.
 *
 * @package Nabia
 */

get_header();

get_template_part( 'template-parts/home', 'hero' );
get_template_part( 'template-parts/home', 'marquee' );
get_template_part( 'template-parts/home', 'services' );
get_template_part( 'template-parts/home', 'work' );
get_template_part( 'template-parts/home', 'videos' );
get_template_part( 'template-parts/home', 'about' );
get_template_part( 'template-parts/home', 'process' );
get_template_part( 'template-parts/home', 'testimonials' );

// If a static front page has content, show it between the sections.
if ( 'page' === get_option( 'show_on_front' ) ) {
	while ( have_posts() ) {
		the_post();
		if ( '' !== trim( get_the_content() ) ) {
			echo '<section class="section home-content"><div class="container entry-content">';
			the_content();
			echo '</div></section>';
		}
	}
}

get_template_part( 'template-parts/home', 'faq' );
get_template_part( 'template-parts/home', 'contact' );

get_footer();
