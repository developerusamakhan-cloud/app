<?php
/**
 * Template Name: Free audit
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();

nabia_page_header(
	array(
		'eyebrow' => __( 'Free audit', 'nabia' ),
		'icon'    => 'search',
		'title'   => get_the_title() ? get_the_title() : nabia_mod( 'audit_title' ),
		'intro'   => nabia_mod( 'audit_text' ),
		'aside'   => '<div class="promise-card"><strong>60s</strong><span>' . esc_html__( 'Instant score for design, SEO, content and speed, plus a PDF report in your inbox. Free, no obligation.', 'nabia' ) . '</span></div>',
		'class'   => 'is-audit',
	)
);

get_template_part( 'template-parts/home', 'audit', array( 'hide_head' => true ) );
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/home', 'testimonials' );
get_template_part( 'template-parts/home', 'videos' );
get_template_part( 'template-parts/why' );
get_footer();
