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
get_template_part( 'template-parts/home', 'platforms' );
get_template_part( 'template-parts/home', 'work' );
get_template_part( 'template-parts/home', 'videos' );
get_template_part( 'template-parts/home', 'about' );
get_template_part( 'template-parts/home', 'process' );
get_template_part( 'template-parts/home', 'testimonials' );
get_template_part( 'template-parts/home', 'hire' );

get_template_part( 'template-parts/home', 'faq' );
get_template_part( 'template-parts/home', 'contact' );

get_footer();
