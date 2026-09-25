<?php
/**
 * Template Name: Contact / Hire me
 * Template Post Type: page
 *
 * Contact page: big contact block, Fiverr/Upwork, free audit form and FAQ.
 *
 * @package Nabia
 */

get_header();
?>
<section class="page-hero">
	<div class="container">
		<p class="eyebrow" data-reveal><?php esc_html_e( 'Contact', 'nabia' ); ?></p>
		<h1 class="page-title" data-split><?php the_title(); ?></h1>
		<p class="page-intro" data-reveal><?php echo esc_html( nabia_mod( 'cta_text' ) ); ?></p>
	</div>
</section>
<?php
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/home', 'contact' );
get_template_part( 'template-parts/home', 'hire' );
get_template_part( 'template-parts/home', 'audit' );
get_template_part( 'template-parts/home', 'faq' );
get_footer();
