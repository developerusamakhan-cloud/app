<?php
/**
 * Template Name: Services overview
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();

nabia_page_header(
	array(
		'eyebrow' => __( 'Services', 'nabia' ),
		'icon'    => 'layout',
		'title'   => get_the_title() ? get_the_title() : __( 'Services', 'nabia' ),
		'intro'   => __( 'Design, development, online stores, AI and monthly care, on the platform that suits you best. Pick a service to see exactly what is included.', 'nabia' ),
		'actions' => nabia_button( __( 'See pricing', 'nabia' ), nabia_page_url( 'pricing' ) ? nabia_page_url( 'pricing' ) : '#pricing' ) . nabia_button( __( 'Hire me', 'nabia' ), nabia_hire_url(), 'ghost' ),
		'aside'   => '<div class="stat-tiles">'
			. '<div><strong>' . esc_html( nabia_mod( 'stat_1_number' ) . nabia_mod( 'stat_1_suffix' ) ) . '</strong><span>' . esc_html__( 'Years experience', 'nabia' ) . '</span></div>'
			. '<div><strong>' . esc_html( nabia_mod( 'stat_2_number' ) . nabia_mod( 'stat_2_suffix' ) ) . '</strong><span>' . esc_html__( 'Projects delivered', 'nabia' ) . '</span></div>'
			. '<div><strong>' . count( nabia_services() ) . '</strong><span>' . esc_html__( 'Services', 'nabia' ) . '</span></div>'
			. '<div><strong>' . count( nabia_platforms() ) . '</strong><span>' . esc_html__( 'Platforms', 'nabia' ) . '</span></div>'
			. '</div>',
		'class'   => 'is-services',
	)
);

while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/home', 'services', array( 'hide_head' => true ) );
get_template_part( 'template-parts/why' );
get_template_part( 'template-parts/home', 'platforms' );
get_template_part( 'template-parts/home', 'process' );
get_template_part( 'template-parts/home', 'pricing' );
get_footer();
