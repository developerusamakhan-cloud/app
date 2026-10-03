<?php
/**
 * Reference data: countries, utilities, tariff tables and solar assumptions.
 *
 * IMPORTANT: before launch, check every rate below against the latest official
 * notification (NEPRA for Pakistan, the state regulator / DISCOM tariff order for
 * India, BERC for Bangladesh) and update `verified`. Everything here can also be
 * overridden from a plugin or child theme with the `powerbachat_data` filter.
 *
 * Tariff model
 * ------------
 * Each tariff has one or more plans (e.g. "standard" and "protected"). A plan holds
 * schedules; the first schedule whose `max_units` covers the consumption is used.
 * A schedule bills its slabs either:
 *   - telescopic: every slab is charged at its own rate (first 100 at A, next 100 at B…)
 *   - flat:       all units are charged at the rate of the slab the total falls in.
 * Slabs are [ upper_limit_in_units|null, rate_per_unit ].
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full data set used by the templates and the front-end scripts.
 *
 * @return array
 */
function powerbachat_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}

	$data = array(
		'countries' => array(
			'pk' => array(
				'name'       => 'Pakistan',
				'currency'   => 'Rs',
				'locale'     => 'en-PK',
				'regulator'  => 'NEPRA',
				'period'     => 'month',
				'hub'        => '/pk/',
				'default'    => 'lesco',
				'chart_note' => 'Cross 200 units and you lose protected status, and the rate on every unit jumps, not just the extra ones.',
				'utilities'  => array(
					array( 'id' => 'lesco', 'abbr' => 'LESCO', 'name' => 'Lahore Electric Supply Co.', 'area' => 'Lahore, Kasur, Okara, Sheikhupura', 'tariff' => 'pk-nepra', 'url' => '/pk/lesco-bill-calculator/', 'rates_url' => '/pk/lesco-unit-price/' ),
					array( 'id' => 'iesco', 'abbr' => 'IESCO', 'name' => 'Islamabad Electric Supply Co.', 'area' => 'Islamabad, Rawalpindi, Attock, Jhelum', 'tariff' => 'pk-nepra', 'url' => '/pk/iesco-bill-calculator/' ),
					array( 'id' => 'mepco', 'abbr' => 'MEPCO', 'name' => 'Multan Electric Power Co.', 'area' => 'Multan, Bahawalpur, D.G. Khan', 'tariff' => 'pk-nepra', 'url' => '/pk/mepco-bill-calculator/' ),
					array( 'id' => 'fesco', 'abbr' => 'FESCO', 'name' => 'Faisalabad Electric Supply Co.', 'area' => 'Faisalabad, Sargodha, Jhang', 'tariff' => 'pk-nepra', 'url' => '/pk/fesco-bill-calculator/' ),
					array( 'id' => 'gepco', 'abbr' => 'GEPCO', 'name' => 'Gujranwala Electric Power Co.', 'area' => 'Gujranwala, Sialkot, Gujrat', 'tariff' => 'pk-nepra', 'url' => '/pk/gepco-bill-calculator/' ),
					array( 'id' => 'pesco', 'abbr' => 'PESCO', 'name' => 'Peshawar Electric Supply Co.', 'area' => 'Peshawar, Mardan, Swat', 'tariff' => 'pk-nepra', 'url' => '/pk/pesco-bill-calculator/' ),
					array( 'id' => 'hesco', 'abbr' => 'HESCO', 'name' => 'Hyderabad Electric Supply Co.', 'area' => 'Hyderabad, Mirpurkhas, Thatta', 'tariff' => 'pk-nepra', 'url' => '/pk/hesco-bill-calculator/' ),
					array( 'id' => 'sepco', 'abbr' => 'SEPCO', 'name' => 'Sukkur Electric Power Co.', 'area' => 'Sukkur, Larkana, Nawabshah', 'tariff' => 'pk-nepra', 'url' => '/pk/sepco-bill-calculator/' ),
					array( 'id' => 'qesco', 'abbr' => 'QESCO', 'name' => 'Quetta Electric Supply Co.', 'area' => 'Quetta and Balochistan', 'tariff' => 'pk-nepra', 'url' => '/pk/qesco-bill-calculator/' ),
					array( 'id' => 'tesco', 'abbr' => 'TESCO', 'name' => 'Tribal Areas Electric Supply Co.', 'area' => 'Merged districts, KP', 'tariff' => 'pk-nepra', 'url' => '/pk/tesco-bill-calculator/' ),
					array( 'id' => 'ke', 'abbr' => 'K-Electric', 'name' => 'K-Electric', 'area' => 'Karachi', 'tariff' => 'pk-nepra', 'url' => '/pk/k-electric-bill-calculator/', 'rates_url' => '/pk/k-electric-unit-price/' ),
				),
			),
			'in' => array(
				'name'       => 'India',
				'currency'   => '₹',
				'locale'     => 'en-IN',
				'regulator'  => 'State ERCs',
				'period'     => 'billing cycle',
				'hub'        => '/in/',
				'default'    => 'tneb',
				'chart_note' => 'In Tamil Nadu, the first 100 units are free, but go past 500 in a two-month cycle and the cheaper 101 to 200 slab disappears.',
				'utilities'  => array(
					array( 'id' => 'tneb', 'abbr' => 'TNEB', 'name' => 'Tamil Nadu (TANGEDCO)', 'area' => 'Bi-monthly billing', 'tariff' => 'in-tneb', 'url' => '/in/tneb-bill-calculator/' ),
					array( 'id' => 'kseb', 'abbr' => 'KSEB', 'name' => 'Kerala State Electricity Board', 'area' => 'Monthly slab rates', 'tariff' => 'in-kseb', 'url' => '/in/kseb-bill-calculator/' ),
					array( 'id' => 'uppcl', 'abbr' => 'UPPCL', 'name' => 'Uttar Pradesh Power Corp.', 'area' => 'Lucknow, Kanpur, Noida…', 'tariff' => '', 'url' => '/in/uppcl-bill-calculator/' ),
					array( 'id' => 'msedcl', 'abbr' => 'MSEDCL', 'name' => 'Maharashtra (Mahadiscom)', 'area' => 'Mumbai suburbs, Pune, Nagpur', 'tariff' => '', 'url' => '/in/mahadiscom-bill-calculator/' ),
					array( 'id' => 'bescom', 'abbr' => 'BESCOM', 'name' => 'Bangalore Electricity Supply Co.', 'area' => 'Bengaluru and 8 districts', 'tariff' => '', 'url' => '/in/bescom-bill-calculator/' ),
					array( 'id' => 'delhi', 'abbr' => 'Delhi', 'name' => 'BSES / Tata Power-DDL', 'area' => 'National Capital Territory', 'tariff' => '', 'url' => '/in/delhi-electricity-bill-calculator/' ),
				),
			),
			'bd' => array(
				'name'       => 'Bangladesh',
				'currency'   => '৳',
				'locale'     => 'en-BD',
				'regulator'  => 'BERC',
				'period'     => 'month',
				'hub'        => '/bd/',
				'default'    => 'desco',
				'chart_note' => 'Watch the step at 400 units: the rate per unit jumps by more than half in a single slab.',
				'utilities'  => array(
					array( 'id' => 'desco', 'abbr' => 'DESCO', 'name' => 'Dhaka Electric Supply Co.', 'area' => 'Dhaka North, Gazipur (part)', 'tariff' => 'bd-berc', 'url' => '/bd/desco-bill-calculator/' ),
					array( 'id' => 'dpdc', 'abbr' => 'DPDC', 'name' => 'Dhaka Power Distribution Co.', 'area' => 'Dhaka South, Narayanganj', 'tariff' => 'bd-berc', 'url' => '/bd/dpdc-bill-calculator/' ),
					array( 'id' => 'breb', 'abbr' => 'BREB', 'name' => 'Bangladesh Rural Electrification Board', 'area' => 'Palli Bidyut, nationwide', 'tariff' => 'bd-berc', 'url' => '/bd/electricity-bill-calculator/' ),
					array( 'id' => 'nesco', 'abbr' => 'NESCO', 'name' => 'Northern Electricity Supply Co.', 'area' => 'Rajshahi, Rangpur', 'tariff' => 'bd-berc', 'url' => '/bd/electricity-bill-calculator/' ),
					array( 'id' => 'wzpdco', 'abbr' => 'WZPDCO', 'name' => 'West Zone Power Distribution Co.', 'area' => 'Khulna, Barishal', 'tariff' => 'bd-berc', 'url' => '/bd/electricity-bill-calculator/' ),
				),
			),
		),

		/*
		 * Tariff tables. VERIFY BEFORE LAUNCH. These are residential reference rates and
		 * exclude fuel-cost adjustments (FPA/FCA), quarterly adjustments and arrears.
		 */
		'tariffs'   => array(
			'pk-nepra' => array(
				'label'    => 'NEPRA uniform residential tariff',
				'verified' => '2026-10',
				'fixed'    => array(),
				'taxes'    => array(
					array( 'label' => 'Electricity duty', 'pct' => 1.5 ),
					array( 'label' => 'GST', 'pct' => 18 ),
				),
				'plans'    => array(
					'standard'  => array(
						'label'     => 'Non-protected',
						'schedules' => array(
							array(
								'max_units' => null,
								'mode'      => 'telescopic',
								'slabs'     => array( array( 100, 22.44 ), array( 200, 28.91 ), array( 300, 33.10 ), array( 400, 37.99 ), array( 500, 40.22 ), array( 600, 41.62 ), array( 700, 42.76 ), array( null, 47.69 ) ),
							),
						),
					),
					'protected' => array(
						'label'     => 'Protected (≤200 units for 6 months)',
						'max_units' => 200,
						'schedules' => array(
							array(
								'max_units' => 200,
								'mode'      => 'telescopic',
								'slabs'     => array( array( 100, 7.74 ), array( 200, 10.06 ) ),
							),
						),
					),
				),
			),
			'in-tneb'  => array(
				'label'    => 'TNERC domestic tariff (LA1A), per two-month cycle',
				'verified' => '2026-10',
				'cycle'    => 'bimonthly',
				'fixed'    => array(),
				'taxes'    => array(),
				'plans'    => array(
					'standard' => array(
						'label'     => 'Domestic',
						'schedules' => array(
							array(
								'max_units' => 500,
								'mode'      => 'telescopic',
								'slabs'     => array( array( 100, 0 ), array( 200, 2.35 ), array( 400, 4.70 ), array( 500, 6.30 ) ),
							),
							array(
								'max_units' => null,
								'mode'      => 'telescopic',
								'slabs'     => array( array( 100, 0 ), array( 400, 4.70 ), array( 500, 6.30 ), array( 600, 8.40 ), array( 800, 9.45 ), array( 1000, 10.50 ), array( null, 11.55 ) ),
							),
						),
					),
				),
			),
			'in-kseb'  => array(
				'label'    => 'KSERC domestic tariff (LT-1A), monthly',
				'verified' => '2026-10',
				'fixed'    => array(),
				'taxes'    => array(
					array( 'label' => 'Electricity duty', 'pct' => 10 ),
				),
				'plans'    => array(
					'standard' => array(
						'label'     => 'Domestic',
						'schedules' => array(
							array(
								'max_units' => 250,
								'mode'      => 'telescopic',
								'slabs'     => array( array( 50, 3.35 ), array( 100, 4.25 ), array( 150, 5.35 ), array( 200, 7.20 ), array( 250, 8.50 ) ),
							),
							array(
								'max_units' => null,
								'mode'      => 'flat',
								'slabs'     => array( array( 300, 6.75 ), array( 350, 7.60 ), array( 400, 7.95 ), array( 500, 8.25 ), array( null, 9.20 ) ),
							),
						),
					),
				),
			),
			'bd-berc'  => array(
				'label'    => 'BERC residential (LT-A) tariff',
				'verified' => '2026-10',
				'fixed'    => array(
					array( 'label' => 'Demand charge (2 kW)', 'amount' => 84 ),
				),
				'taxes'    => array(
					array( 'label' => 'VAT', 'pct' => 5 ),
				),
				'plans'    => array(
					'standard' => array(
						'label'     => 'Residential',
						'schedules' => array(
							array(
								'max_units' => 50,
								'mode'      => 'flat',
								'slabs'     => array( array( 50, 4.63 ) ),
							),
							array(
								'max_units' => null,
								'mode'      => 'telescopic',
								'slabs'     => array( array( 75, 5.26 ), array( 200, 7.20 ), array( 300, 7.59 ), array( 400, 8.02 ), array( 600, 12.67 ), array( null, 14.61 ) ),
							),
						),
					),
				),
			),
		),

		/*
		 * Solar sizing assumptions per country. VERIFY prices before launch.
		 * per_kw: installed on-grid cost range per kW (panels + inverter + structure + labour).
		 */
		'solar'     => array(
			'pk' => array( 'sun_hours' => 5.3, 'per_kw' => array( 115000, 150000 ), 'panel_watt' => 585, 'subsidy' => null ),
			'in' => array( 'sun_hours' => 5.0, 'per_kw' => array( 55000, 70000 ), 'panel_watt' => 545, 'subsidy' => 'pm-surya-ghar' ),
			'bd' => array( 'sun_hours' => 4.5, 'per_kw' => array( 85000, 115000 ), 'panel_watt' => 550, 'subsidy' => null ),
		),
	);

	/**
	 * Filter the PowerBachat data set.
	 *
	 * @param array $data Countries, tariffs and solar assumptions.
	 */
	$data = apply_filters( 'powerbachat_data', $data );

	return $data;
}

/**
 * Data trimmed for the front-end (keys renamed to camelCase-free JSON-friendly arrays).
 *
 * @return array
 */
function powerbachat_js_data() {
	$data      = powerbachat_data();
	$countries = array();

	foreach ( $data['countries'] as $code => $country ) {
		$utilities = array();
		foreach ( $country['utilities'] as $utility ) {
			$utilities[] = array(
				'id'     => $utility['id'],
				'abbr'   => $utility['abbr'],
				'name'   => $utility['name'],
				'tariff' => $utility['tariff'],
				'url'    => home_url( $utility['url'] ),
			);
		}
		$countries[ $code ] = array(
			'name'      => $country['name'],
			'currency'  => $country['currency'],
			'locale'    => $country['locale'],
			'period'    => $country['period'],
			'default'   => $country['default'],
			'chartNote' => $country['chart_note'],
			'utilities' => $utilities,
		);
	}

	return array(
		'countries' => $countries,
		'tariffs'   => $data['tariffs'],
		'solar'     => $data['solar'],
	);
}

/**
 * Number of utilities that have a calculator page.
 *
 * @return int
 */
function powerbachat_utility_count() {
	$count = 0;
	foreach ( powerbachat_data()['countries'] as $country ) {
		$count += count( $country['utilities'] );
	}
	return $count;
}
