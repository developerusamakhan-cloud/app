<?php
/**
 * Template Name: Pricing
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();

$nabia_included = '<div class="aside-card"><p class="aside-card-title">' . esc_html__( 'Every website includes', 'nabia' ) . '</p><ul class="check-list">';
foreach ( array( __( 'Custom design for your brand', 'nabia' ), __( 'Mobile-friendly on every screen', 'nabia' ), __( 'Speed and SEO basics', 'nabia' ), __( 'Contact form to your inbox', 'nabia' ), __( 'Video walkthrough of your site', 'nabia' ), __( 'One month of free support', 'nabia' ) ) as $nabia_item ) {
	$nabia_included .= '<li><span class="tick" aria-hidden="true">' . nabia_get_icon( 'check' ) . '</span>' . esc_html( $nabia_item ) . '</li>';
}
$nabia_included .= '</ul></div>';

nabia_page_header(
	array(
		'eyebrow' => __( 'Pricing', 'nabia' ),
		'icon'    => 'star',
		'title'   => get_the_title() ? get_the_title() : __( 'Pricing', 'nabia' ),
		'intro'   => nabia_mod( 'pricing_text' ),
		'aside'   => $nabia_included,
		'class'   => 'is-pricing',
	)
);

get_template_part( 'template-parts/home', 'pricing', array( 'hide_head' => true ) );
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/why' );
get_template_part( 'template-parts/home', 'faq' );
get_footer();
