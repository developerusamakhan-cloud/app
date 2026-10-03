<?php
/**
 * Home page.
 *
 * Each band lives in template-parts/home/ so it can be reordered or reused.
 *
 * @package PowerBachat
 */

get_header();

$powerbachat_sections = apply_filters(
	'powerbachat_home_sections',
	array( 'hero', 'ticker', 'utilities', 'slabs', 'solar', 'prices', 'guides', 'trust', 'faq', 'alerts' )
);

foreach ( $powerbachat_sections as $powerbachat_section ) {
	get_template_part( 'template-parts/home/' . $powerbachat_section );
}

get_footer();
