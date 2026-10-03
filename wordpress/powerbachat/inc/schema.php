<?php
/**
 * FAQ content and structured data.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Home page FAQs. Answers are written to stand on their own in search results.
 *
 * @return array[]
 */
function powerbachat_faqs() {
	$faqs = array(
		array(
			'q' => 'How is a LESCO, IESCO or MEPCO bill calculated?',
			'a' => 'All Pakistani DISCOs use the same NEPRA slab tariff. Your units are split into slabs (1–100, 101–200 and so on), each slab is multiplied by its rate, and then electricity duty, GST and the monthly fuel price adjustment are added. Our calculator does the slab maths and taxes for you; the fuel adjustment changes every month, so check the FPA line on your printed bill.',
		),
		array(
			'q' => 'What is a protected consumer in Pakistan?',
			'a' => 'If your household has used 200 units or fewer in each of the last six months, you are billed at the much cheaper protected rates. Use 201 units even once and you move to the non-protected tariff for the next six months — which is why the 200-unit line matters so much.',
		),
		array(
			'q' => 'What will 300 units cost me this month?',
			'a' => 'Set the slider in the calculator at the top of this page to 300 and pick your company. You will see the slab-by-slab breakdown, the taxes and an estimated total. The same tool works for 100, 150, 200, 400 or any other number of units.',
		),
		array(
			'q' => 'How big a solar system do I need for 500 units a month?',
			'a' => 'Roughly 3.5–4 kW in most of Pakistan and India, and a little more in Bangladesh where there are fewer peak sun hours. Enter your own monthly units in the solar sizing tool for a panel count, a cost range and a payback estimate.',
		),
		array(
			'q' => 'How often are your tariff rates updated?',
			'a' => 'We check every rate against the regulator’s latest notification each month, and immediately after a new tariff is announced. The date of the last check is printed under every calculator.',
		),
	);

	return apply_filters( 'powerbachat_faqs', $faqs );
}

/**
 * Organization + WebSite JSON-LD on the front page.
 */
function powerbachat_site_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$graph = array(
		'@context' => 'https://schema.org',
		'@graph'   => array(
			array(
				'@type' => 'Organization',
				'@id'   => home_url( '/#organization' ),
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
			array(
				'@type'           => 'WebSite',
				'@id'             => home_url( '/#website' ),
				'url'             => home_url( '/' ),
				'name'            => get_bloginfo( 'name' ),
				'publisher'       => array( '@id' => home_url( '/#organization' ) ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => home_url( '/?s={search_term_string}' ),
					'query-input' => 'required name=search_term_string',
				),
			),
			array(
				'@type'      => 'FAQPage',
				'mainEntity' => array_map(
					function ( $faq ) {
						return array(
							'@type'          => 'Question',
							'name'           => $faq['q'],
							'acceptedAnswer' => array(
								'@type' => 'Answer',
								'text'  => $faq['a'],
							),
						);
					},
					powerbachat_faqs()
				),
			),
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'powerbachat_site_schema' );
