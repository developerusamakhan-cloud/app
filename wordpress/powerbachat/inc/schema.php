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
 * Home page FAQs for one country. Answers are written to stand on their own in search results.
 *
 * @param string|null $country pk|in|bd; defaults to the visitor's country.
 * @return array[]
 */
function powerbachat_faqs( $country = null ) {
	$country = $country ? $country : powerbachat_current_country();
	$updated = array(
		'q' => 'How often are your tariff rates updated?',
		'a' => 'We check every rate against the regulator’s latest notification each month, and immediately after a new tariff is announced. The date of the last check is printed under every calculator.',
	);

	$faqs = array(
		'pk' => array(
			array(
				'q' => 'How is a LESCO, IESCO or MEPCO bill calculated?',
				'a' => 'All Pakistani DISCOs use the same NEPRA slab tariff. Your units are split into slabs (1 to 100, 101 to 200 and so on), each slab is multiplied by its rate, and then electricity duty, GST and the monthly fuel price adjustment are added. Our calculator does the slab maths and taxes for you; the fuel adjustment changes every month, so check the FPA line on your printed bill.',
			),
			array(
				'q' => 'What is a protected consumer in Pakistan?',
				'a' => 'If your household has used 200 units or fewer in each of the last six months, you are billed at the much cheaper protected rates. Use 201 units even once and you move to the non-protected tariff for the next six months, which is why the 200-unit line matters so much.',
			),
			array(
				'q' => 'What will 300 units cost me this month?',
				'a' => 'Set the slider in the calculator at the top of this page to 300 and pick your company. You will see the slab-by-slab breakdown, the taxes and an estimated total. The same tool works for 100, 150, 200, 400 or any other number of units.',
			),
			array(
				'q' => 'How big a solar system do I need for 500 units a month?',
				'a' => 'Roughly 4 kW in most of Pakistan. Enter your own monthly units in the solar sizing tool for a panel count, a cost range and a payback estimate.',
			),
			$updated,
		),
		'in' => array(
			array(
				'q' => 'How is a TNEB bill calculated?',
				'a' => 'Tamil Nadu bills domestic connections every two months. The first 100 units are free, and the remaining units are charged slab by slab. If you use more than 500 units in a cycle, a different, costlier slab table applies to the whole bill, so crossing 500 units makes a big difference.',
			),
			array(
				'q' => 'Why did my KSEB bill jump after 250 units?',
				'a' => 'Up to 250 units a month, KSEB charges each slab at its own rate. Above 250 units the bill becomes non-telescopic: every unit is charged at the single rate for your total, so one extra unit can raise the whole bill.',
			),
			array(
				'q' => 'What will 300 units cost me?',
				'a' => 'Set the slider in the calculator at the top of this page to 300 and pick your electricity board. You will see the slab-by-slab breakdown and an estimated total, including electricity duty where it applies.',
			),
			array(
				'q' => 'How much subsidy do I get under PM Surya Ghar?',
				'a' => 'The central subsidy is ₹30,000 per kW for the first 2 kW and ₹18,000 for the third kW, up to ₹78,000 in total for a 3 kW or larger system. The solar sizing tool on this page subtracts it for you.',
			),
			$updated,
		),
		'bd' => array(
			array(
				'q' => 'How is a DESCO or DPDC bill calculated?',
				'a' => 'All distributors in Bangladesh use the BERC residential slab tariff. Your units are charged slab by slab, a demand charge based on your sanctioned load is added, and 5% VAT is applied on top. Our calculator does all three steps.',
			),
			array(
				'q' => 'What is the lifeline rate?',
				'a' => 'Households that use 50 units or fewer in a month are billed every unit at the lowest lifeline rate. Go above 50 and the normal slab table applies from the first unit.',
			),
			array(
				'q' => 'What will 300 units cost me this month?',
				'a' => 'Set the slider in the calculator at the top of this page to 300 and pick your distributor. You will see each slab, the demand charge, VAT and the estimated total.',
			),
			array(
				'q' => 'How big a solar system do I need for 500 units a month?',
				'a' => 'Roughly 4.5 to 5 kW, because Bangladesh gets slightly fewer peak sun hours than Pakistan or India. Enter your own monthly units in the solar sizing tool for a panel count, cost range and payback estimate.',
			),
			$updated,
		),
	);

	$list = isset( $faqs[ $country ] ) ? $faqs[ $country ] : $faqs['pk'];
	return apply_filters( 'powerbachat_faqs', $list, $country );
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
			powerbachat_organization_node(),
			array(
				'@type'           => 'WebSite',
				'@id'             => home_url( '/#website' ),
				'url'             => home_url( '/' ),
				'name'            => get_bloginfo( 'name' ),
				'publisher'       => array( '@id' => home_url( '/#organization' ) ),
				'inLanguage'      => 'en',
				'image'           => powerbachat_share_image()[0],
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
