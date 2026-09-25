<?php
/**
 * 404 page.
 *
 * @package Nabia
 */

get_header();
?>
<section class="page-hero error-404">
	<div class="container">
		<p class="error-code" aria-hidden="true" data-parallax="0.1">404</p>
		<h1 class="page-title" data-split><?php esc_html_e( 'This page went on holiday.', 'nabia' ); ?></h1>
		<p class="page-intro" data-reveal><?php esc_html_e( "The link may be broken or the page may have moved. Let's get you back on track.", 'nabia' ); ?></p>
		<div class="hero-actions" data-reveal>
			<a class="btn btn-accent btn-lg" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-magnetic><span><?php esc_html_e( 'Back home', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
		</div>
		<div class="search-wrap" data-reveal><?php get_search_form(); ?></div>
	</div>
</section>
<?php
get_footer();
