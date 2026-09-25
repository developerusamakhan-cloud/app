<?php
/**
 * Template Name: Free audit
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();
get_template_part( 'template-parts/home', 'audit' );
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/home', 'videos' );
get_template_part( 'template-parts/home', 'testimonials' );
get_footer();
