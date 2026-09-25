<?php
/**
 * Template Name: About me
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();

$nabia_aside = '<div class="aside-card profile-card">'
	. '<span class="profile-badge"><span class="pulse" aria-hidden="true"></span>' . esc_html( nabia_mod( 'hero_badge' ) ) . '</span>'
	. '<p class="profile-name">' . esc_html( nabia_mod( 'brand_name' ) ) . '</p>'
	. '<p class="author-role">' . esc_html__( 'WordPress developer & graphic designer', 'nabia' ) . '</p>'
	. '<dl class="aside-rows">'
	. '<div><dt>' . esc_html__( 'Experience', 'nabia' ) . '</dt><dd>' . esc_html( nabia_mod( 'stat_1_number' ) . nabia_mod( 'stat_1_suffix' ) . ' ' . __( 'years', 'nabia' ) ) . '</dd></div>'
	. '<div><dt>' . esc_html__( 'Projects', 'nabia' ) . '</dt><dd>' . esc_html( nabia_mod( 'stat_2_number' ) . nabia_mod( 'stat_2_suffix' ) ) . '</dd></div>'
	. '<div><dt>Fiverr</dt><dd>' . esc_html__( 'Level 2 seller', 'nabia' ) . '</dd></div>'
	. '<div><dt>' . esc_html__( 'Reply time', 'nabia' ) . '</dt><dd>' . esc_html__( 'Within 24 hours', 'nabia' ) . '</dd></div>'
	. '</dl></div>';

nabia_page_header(
	array(
		'eyebrow' => __( 'About', 'nabia' ),
		'icon'    => 'sparkles',
		'title'   => get_the_title() ? get_the_title() : __( 'About me', 'nabia' ),
		'intro'   => nabia_mod( 'hero_text' ),
		'actions' => nabia_button( __( 'Hire me', 'nabia' ), nabia_hire_url() ) . nabia_button( __( 'See my work', 'nabia' ), nabia_portfolio_url(), 'ghost' ),
		'aside'   => $nabia_aside,
		'class'   => 'is-about',
	)
);

get_template_part( 'template-parts/home', 'about', array( 'hide_head' => false ) );
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
// Dark and light sections alternate: videos (dark), why (light), process (dark), reviews (light).
get_template_part( 'template-parts/home', 'videos' );
get_template_part( 'template-parts/why' );
get_template_part( 'template-parts/home', 'process' );
get_template_part( 'template-parts/home', 'testimonials' );
get_template_part( 'template-parts/home', 'platforms' );
get_footer();
