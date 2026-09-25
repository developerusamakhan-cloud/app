<?php
/**
 * Template Name: Contact / Hire me
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();

$nabia_email = nabia_mod( 'contact_email' );
$nabia_aside = nabia_aside_card(
	__( 'Quick facts', 'nabia' ),
	array(
		array( __( 'Email', 'nabia' ), antispambot( $nabia_email ) ),
		array( __( 'Reply time', 'nabia' ), __( 'Within 24 hours', 'nabia' ) ),
		array( __( 'Payment', 'nabia' ), __( '50% upfront, 50% on launch', 'nabia' ) ),
		array( __( 'Also on', 'nabia' ), 'Fiverr & Upwork' ),
	)
);

nabia_page_header(
	array(
		'eyebrow' => __( 'Contact', 'nabia' ),
		'icon'    => 'mail',
		'title'   => get_the_title() ? get_the_title() : __( 'Hire me', 'nabia' ),
		'intro'   => nabia_mod( 'cta_text' ),
		'actions' => nabia_button( __( 'Send a message', 'nabia' ), '#contact-form' ) . ( $nabia_email ? nabia_button( antispambot( $nabia_email ), 'mailto:' . antispambot( $nabia_email ), 'ghost' ) : '' ),
		'aside'   => $nabia_aside,
		'class'   => 'is-contact',
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
get_template_part( 'template-parts/contact', 'form' );
get_template_part( 'template-parts/home', 'hire' );
get_template_part( 'template-parts/home', 'process' );
get_template_part( 'template-parts/home', 'faq' );
get_footer();
