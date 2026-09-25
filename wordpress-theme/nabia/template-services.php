<?php
/**
 * Template Name: Services overview
 * Template Post Type: page
 *
 * @package Nabia
 */

get_header();
?>
<section class="page-hero">
	<div class="container">
		<p class="eyebrow" data-reveal><?php esc_html_e( 'Services', 'nabia' ); ?></p>
		<h1 class="page-title" data-split><?php echo esc_html( nabia_mod( 'services_title' ) ); ?></h1>
		<p class="page-intro" data-reveal><?php esc_html_e( 'Design, development, e-commerce, AI and care for your website, on the platform that suits you best. Pick a service to see everything that is included.', 'nabia' ); ?></p>
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
get_template_part( 'template-parts/home', 'services' );
get_template_part( 'template-parts/home', 'platforms' );
get_template_part( 'template-parts/home', 'process' );
get_template_part( 'template-parts/home', 'pricing' );
get_template_part( 'template-parts/home', 'audit' );
get_footer();
