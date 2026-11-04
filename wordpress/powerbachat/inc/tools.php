<?php
/**
 * Electrical converters under /tools/: watts to amps, kVA to kW, BTU to tons and so on.
 *
 * Every converter is linear: result = input × factor, where the factor depends on the
 * settings (volts, phase, power factor, hours...). One registry describes them all; the
 * same factor maths runs in PHP (for the reference tables search engines read) and in
 * assets/js/main.js (for the live calculator). Keep the two in step.
 *
 * Unlike the bill calculators, these pages are the same for every visitor whatever
 * their country.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common settings, reused by several tools.
 *
 * @return array
 */
function powerbachat_tool_params() {
	$phase = array(
		'type'    => 'select',
		'label'   => __( 'Circuit', 'powerbachat' ),
		'options' => array(
			'ac1' => __( 'AC single phase', 'powerbachat' ),
			'ac3' => __( 'AC three phase (line to line)', 'powerbachat' ),
			'dc'  => __( 'DC', 'powerbachat' ),
		),
		'default' => 'ac1',
	);
	$pf    = array(
		'type'    => 'number',
		'label'   => __( 'Power factor', 'powerbachat' ),
		'hint'    => __( '1 for heaters and kettles, about 0.8 to 0.95 for motors', 'powerbachat' ),
		'default' => 1,
		'min'     => 0.1,
		'max'     => 1,
		'step'    => 0.01,
		'hide_dc' => true,
	);
	return array(
		'phase' => $phase,
		'pf'    => $pf,
		'volts' => array(
			'type'    => 'number',
			'label'   => __( 'Voltage', 'powerbachat' ),
			'unit'    => 'V',
			'default' => 120,
			'min'     => 1,
			'step'    => 'any',
			'presets' => array( 12, 24, 120, 230, 240 ),
		),
	);
}

/**
 * Every converter, keyed by page slug.
 *
 * @return array
 */
function powerbachat_tools() {
	$p     = powerbachat_tool_params();
	$tools = array(
		'watts-to-amps'          => array(
			'name'   => __( 'Watts to Amps', 'powerbachat' ),
			'blurb'  => __( 'Current drawn by any appliance', 'powerbachat' ),
			'group'  => 'power',
			'calc'   => 'w2a',
			'from'   => array( 'label' => __( 'Power', 'powerbachat' ), 'unit' => 'W', 'default' => 1000 ),
			'to'     => array( 'label' => __( 'Current', 'powerbachat' ), 'unit' => 'A' ),
			'params' => array( 'phase' => $p['phase'], 'volts' => $p['volts'], 'pf' => $p['pf'] ),
			'table'  => array(
				'caption' => __( 'Watts to amps at common voltages (power factor 1)', 'powerbachat' ),
				'rows'    => array( 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1200, 1500, 1800, 2000, 2500, 3000, 3500, 4000, 5000, 6000, 7500, 10000 ),
				'cols'    => array(
					array( '12 V DC', array( 'phase' => 'dc', 'volts' => 12 ) ),
					array( '120 V', array( 'phase' => 'ac1', 'volts' => 120, 'pf' => 1 ) ),
					array( '230 V', array( 'phase' => 'ac1', 'volts' => 230, 'pf' => 1 ) ),
					array( '240 V', array( 'phase' => 'ac1', 'volts' => 240, 'pf' => 1 ) ),
				),
			),
			'pair'   => 'amps-to-watts',
		),
		'amps-to-watts'          => array(
			'name'   => __( 'Amps to Watts', 'powerbachat' ),
			'blurb'  => __( 'Power from current and voltage', 'powerbachat' ),
			'group'  => 'power',
			'calc'   => 'a2w',
			'from'   => array( 'label' => __( 'Current', 'powerbachat' ), 'unit' => 'A', 'default' => 15 ),
			'to'     => array( 'label' => __( 'Power', 'powerbachat' ), 'unit' => 'W' ),
			'params' => array( 'phase' => $p['phase'], 'volts' => $p['volts'], 'pf' => $p['pf'] ),
			'table'  => array(
				'caption' => __( 'Amps to watts at common voltages (power factor 1)', 'powerbachat' ),
				'rows'    => array( 1, 2, 3, 4, 5, 6, 8, 10, 12, 15, 20, 25, 30, 40, 50, 60, 70, 80, 100, 125, 150, 200 ),
				'cols'    => array(
					array( '12 V DC', array( 'phase' => 'dc', 'volts' => 12 ) ),
					array( '120 V', array( 'phase' => 'ac1', 'volts' => 120, 'pf' => 1 ) ),
					array( '230 V', array( 'phase' => 'ac1', 'volts' => 230, 'pf' => 1 ) ),
					array( '240 V', array( 'phase' => 'ac1', 'volts' => 240, 'pf' => 1 ) ),
				),
			),
			'pair'   => 'watts-to-amps',
		),
		'volts-to-watts'         => array(
			'name'   => __( 'Volts to Watts', 'powerbachat' ),
			'blurb'  => __( 'Power from voltage and current', 'powerbachat' ),
			'group'  => 'power',
			'calc'   => 'v2w',
			'from'   => array( 'label' => __( 'Voltage', 'powerbachat' ), 'unit' => 'V', 'default' => 120 ),
			'to'     => array( 'label' => __( 'Power', 'powerbachat' ), 'unit' => 'W' ),
			'params' => array(
				'amps' => array(
					'type'    => 'number',
					'label'   => __( 'Current', 'powerbachat' ),
					'unit'    => 'A',
					'default' => 10,
					'min'     => 0,
					'step'    => 'any',
					'presets' => array( 1, 5, 10, 15, 20 ),
				),
				'pf'   => $p['pf'],
			),
			'table'  => array(
				'caption' => __( 'Watts for common voltages and currents (power factor 1)', 'powerbachat' ),
				'rows'    => array( 1.5, 3, 5, 6, 9, 12, 24, 36, 48, 110, 120, 208, 220, 230, 240, 277, 480 ),
				'cols'    => array(
					array( '1 A', array( 'amps' => 1, 'pf' => 1 ) ),
					array( '5 A', array( 'amps' => 5, 'pf' => 1 ) ),
					array( '10 A', array( 'amps' => 10, 'pf' => 1 ) ),
					array( '15 A', array( 'amps' => 15, 'pf' => 1 ) ),
					array( '20 A', array( 'amps' => 20, 'pf' => 1 ) ),
				),
			),
		),
		'hp-to-watts'            => array(
			'name'   => __( 'HP to Watts', 'powerbachat' ),
			'blurb'  => __( 'Horsepower in watts and kilowatts', 'powerbachat' ),
			'group'  => 'power',
			'calc'   => 'hp2w',
			'from'   => array( 'label' => __( 'Horsepower', 'powerbachat' ), 'unit' => 'hp', 'default' => 1 ),
			'to'     => array( 'label' => __( 'Power', 'powerbachat' ), 'unit' => 'W' ),
			'params' => array(
				'hp' => array(
					'type'    => 'select',
					'label'   => __( 'Type of horsepower', 'powerbachat' ),
					'options' => array(
						'mech'   => __( 'Mechanical (imperial), 745.7 W', 'powerbachat' ),
						'elec'   => __( 'Electrical, 746 W', 'powerbachat' ),
						'metric' => __( 'Metric (PS), 735.5 W', 'powerbachat' ),
					),
					'default' => 'mech',
				),
			),
			'table'  => array(
				'caption' => __( 'Horsepower to watts', 'powerbachat' ),
				'rows'    => array( 0.25, 0.33, 0.5, 0.75, 1, 1.5, 2, 3, 5, 7.5, 10, 15, 20, 25, 50, 100 ),
				'cols'    => array(
					array( __( 'Watts (mechanical hp)', 'powerbachat' ), array( 'hp' => 'mech' ) ),
					array( __( 'Kilowatts', 'powerbachat' ), array( 'hp' => 'mech' ), 0.001 ),
					array( __( 'Watts (metric hp)', 'powerbachat' ), array( 'hp' => 'metric' ) ),
				),
			),
		),
		'kva-to-amps'            => array(
			'name'   => __( 'kVA to Amps', 'powerbachat' ),
			'blurb'  => __( 'Full load current of a generator or transformer', 'powerbachat' ),
			'group'  => 'power',
			'calc'   => 'kva2a',
			'from'   => array( 'label' => __( 'Apparent power', 'powerbachat' ), 'unit' => 'kVA', 'default' => 10 ),
			'to'     => array( 'label' => __( 'Current', 'powerbachat' ), 'unit' => 'A' ),
			'params' => array(
				'phase' => array(
					'type'    => 'select',
					'label'   => __( 'Circuit', 'powerbachat' ),
					'options' => array(
						'ac1' => __( 'Single phase', 'powerbachat' ),
						'ac3' => __( 'Three phase (line to line)', 'powerbachat' ),
					),
					'default' => 'ac1',
				),
				'volts' => array_merge( $p['volts'], array( 'default' => 240, 'presets' => array( 120, 208, 240, 400, 480 ) ) ),
			),
			'table'  => array(
				'caption' => __( 'kVA to amps for common supplies', 'powerbachat' ),
				'rows'    => array( 1, 2, 3, 5, 7.5, 10, 15, 20, 25, 30, 37.5, 45, 50, 75, 100, 112.5, 150, 200, 225, 300, 500, 750, 1000, 1500, 2000 ),
				'cols'    => array(
					array( __( '120 V single', 'powerbachat' ), array( 'phase' => 'ac1', 'volts' => 120 ) ),
					array( __( '240 V single', 'powerbachat' ), array( 'phase' => 'ac1', 'volts' => 240 ) ),
					array( __( '208 V three', 'powerbachat' ), array( 'phase' => 'ac3', 'volts' => 208 ) ),
					array( __( '400 V three', 'powerbachat' ), array( 'phase' => 'ac3', 'volts' => 400 ) ),
					array( __( '480 V three', 'powerbachat' ), array( 'phase' => 'ac3', 'volts' => 480 ) ),
				),
			),
		),
		'kva-to-kw'              => array(
			'name'   => __( 'kVA to kW', 'powerbachat' ),
			'blurb'  => __( 'Real power from apparent power', 'powerbachat' ),
			'group'  => 'power',
			'calc'   => 'kva2kw',
			'from'   => array( 'label' => __( 'Apparent power', 'powerbachat' ), 'unit' => 'kVA', 'default' => 10 ),
			'to'     => array( 'label' => __( 'Real power', 'powerbachat' ), 'unit' => 'kW' ),
			'params' => array( 'pf' => array_merge( $p['pf'], array( 'default' => 0.8, 'hint' => __( '0.8 is the standard rating for generators', 'powerbachat' ), 'hide_dc' => false ) ) ),
			'table'  => array(
				'caption' => __( 'kVA to kW at different power factors', 'powerbachat' ),
				'rows'    => array( 1, 2, 3, 5, 7.5, 10, 15, 20, 25, 30, 40, 50, 62.5, 75, 100, 125, 150, 200, 250, 500, 1000 ),
				'cols'    => array(
					array( 'PF 0.8', array( 'pf' => 0.8 ) ),
					array( 'PF 0.85', array( 'pf' => 0.85 ) ),
					array( 'PF 0.9', array( 'pf' => 0.9 ) ),
					array( 'PF 1', array( 'pf' => 1 ) ),
				),
			),
		),
		'kwh-to-watts'           => array(
			'name'   => __( 'kWh to Watts', 'powerbachat' ),
			'blurb'  => __( 'Average power from energy and time', 'powerbachat' ),
			'group'  => 'energy',
			'calc'   => 'kwh2w',
			'from'   => array( 'label' => __( 'Energy', 'powerbachat' ), 'unit' => 'kWh', 'default' => 1 ),
			'to'     => array( 'label' => __( 'Average power', 'powerbachat' ), 'unit' => 'W' ),
			'params' => array(
				'hours' => array(
					'type'    => 'number',
					'label'   => __( 'Over this many hours', 'powerbachat' ),
					'unit'    => 'h',
					'default' => 1,
					'min'     => 0.01,
					'step'    => 'any',
					'presets' => array( 1, 8, 24, 730 ),
					'hint'    => __( '24 is a day, 730 is an average month', 'powerbachat' ),
				),
			),
			'table'  => array(
				'caption' => __( 'kWh to watts over an hour, a day and a month', 'powerbachat' ),
				'rows'    => array( 0.1, 0.25, 0.5, 1, 2, 3, 5, 10, 15, 20, 30, 50, 100 ),
				'cols'    => array(
					array( __( 'Over 1 hour', 'powerbachat' ), array( 'hours' => 1 ) ),
					array( __( 'Over a day (24 h)', 'powerbachat' ), array( 'hours' => 24 ) ),
					array( __( 'Over a month (730 h)', 'powerbachat' ), array( 'hours' => 730 ) ),
				),
			),
		),
		'watt-hours-to-mah'      => array(
			'name'   => __( 'Watt Hours to mAh', 'powerbachat' ),
			'blurb'  => __( 'Battery and power bank capacity', 'powerbachat' ),
			'group'  => 'energy',
			'calc'   => 'wh2mah',
			'from'   => array( 'label' => __( 'Energy', 'powerbachat' ), 'unit' => 'Wh', 'default' => 37 ),
			'to'     => array( 'label' => __( 'Charge', 'powerbachat' ), 'unit' => 'mAh' ),
			'params' => array( 'volts' => array_merge( $p['volts'], array( 'default' => 3.7, 'presets' => array( 3.7, 3.85, 5, 12 ), 'hint' => __( '3.7 V for most phone and power bank cells', 'powerbachat' ) ) ) ),
			'table'  => array(
				'caption' => __( 'Watt hours to mAh at common battery voltages', 'powerbachat' ),
				'rows'    => array( 1, 2, 5, 10, 15, 20, 27, 37, 50, 60, 74, 77, 99, 100, 160, 200 ),
				'cols'    => array(
					array( '3.7 V', array( 'volts' => 3.7 ) ),
					array( '3.85 V', array( 'volts' => 3.85 ) ),
					array( '5 V', array( 'volts' => 5 ) ),
					array( '12 V', array( 'volts' => 12 ) ),
				),
			),
		),
		'amp-hours-to-watt-hours' => array(
			'name'   => __( 'Amp Hours to Watt Hours', 'powerbachat' ),
			'blurb'  => __( 'How much energy a battery holds', 'powerbachat' ),
			'group'  => 'energy',
			'calc'   => 'ah2wh',
			'from'   => array( 'label' => __( 'Capacity', 'powerbachat' ), 'unit' => 'Ah', 'default' => 100 ),
			'to'     => array( 'label' => __( 'Energy', 'powerbachat' ), 'unit' => 'Wh' ),
			'params' => array( 'volts' => array_merge( $p['volts'], array( 'default' => 12, 'presets' => array( 3.7, 12, 24, 48 ) ) ) ),
			'table'  => array(
				'caption' => __( 'Amp hours to watt hours at common battery voltages', 'powerbachat' ),
				'rows'    => array( 1, 2, 5, 7, 10, 20, 35, 50, 60, 75, 100, 120, 150, 200, 300, 400 ),
				'cols'    => array(
					array( '3.7 V', array( 'volts' => 3.7 ) ),
					array( '12 V', array( 'volts' => 12 ) ),
					array( '24 V', array( 'volts' => 24 ) ),
					array( '48 V', array( 'volts' => 48 ) ),
				),
			),
		),
		'btu-to-kw'              => array(
			'name'   => __( 'BTU to kW', 'powerbachat' ),
			'blurb'  => __( 'Air conditioner and heater ratings in kilowatts', 'powerbachat' ),
			'group'  => 'heat',
			'calc'   => 'btu2kw',
			'from'   => array( 'label' => __( 'Heat rate', 'powerbachat' ), 'unit' => 'BTU/h', 'default' => 12000 ),
			'to'     => array( 'label' => __( 'Power', 'powerbachat' ), 'unit' => 'kW' ),
			'params' => array(),
			'table'  => array(
				'caption' => __( 'BTU per hour to kilowatts', 'powerbachat' ),
				'rows'    => array( 1000, 2000, 5000, 6000, 8000, 9000, 10000, 12000, 15000, 18000, 20000, 24000, 30000, 36000, 40000, 48000, 50000, 60000, 80000, 100000 ),
				'cols'    => array( array( __( 'Kilowatts', 'powerbachat' ), array() ) ),
			),
		),
		'btu-to-watts'           => array(
			'name'   => __( 'BTU to Watts', 'powerbachat' ),
			'blurb'  => __( 'Heating and cooling power in watts', 'powerbachat' ),
			'group'  => 'heat',
			'calc'   => 'btu2w',
			'from'   => array( 'label' => __( 'Heat rate', 'powerbachat' ), 'unit' => 'BTU/h', 'default' => 5000 ),
			'to'     => array( 'label' => __( 'Power', 'powerbachat' ), 'unit' => 'W' ),
			'params' => array(),
			'table'  => array(
				'caption' => __( 'BTU per hour to watts', 'powerbachat' ),
				'rows'    => array( 100, 500, 1000, 2000, 3000, 4000, 5000, 6000, 8000, 9000, 10000, 12000, 15000, 18000, 24000, 30000, 36000 ),
				'cols'    => array( array( __( 'Watts', 'powerbachat' ), array() ) ),
			),
		),
		'btu-to-tons'            => array(
			'name'   => __( 'BTU to Tons', 'powerbachat' ),
			'blurb'  => __( 'Air conditioner size in tons of refrigeration', 'powerbachat' ),
			'group'  => 'heat',
			'calc'   => 'btu2ton',
			'from'   => array( 'label' => __( 'Cooling capacity', 'powerbachat' ), 'unit' => 'BTU/h', 'default' => 24000 ),
			'to'     => array( 'label' => __( 'Capacity', 'powerbachat' ), 'unit' => 'tons' ),
			'params' => array(),
			'table'  => array(
				'caption' => __( 'BTU per hour to tons of refrigeration', 'powerbachat' ),
				'rows'    => array( 6000, 9000, 12000, 18000, 24000, 30000, 36000, 42000, 48000, 60000, 72000, 90000, 120000 ),
				'cols'    => array(
					array( __( 'Tons', 'powerbachat' ), array() ),
					array( __( 'Kilowatts', 'powerbachat' ), array(), 12000 * 0.00029307107 ),
				),
			),
		),
	);
	return apply_filters( 'powerbachat_tools', $tools );
}

/**
 * Tool groups, in the order the hub shows them.
 *
 * @return array
 */
function powerbachat_tool_groups() {
	return array(
		'power'  => __( 'Power and current', 'powerbachat' ),
		'energy' => __( 'Energy and batteries', 'powerbachat' ),
		'heat'   => __( 'Heating and cooling', 'powerbachat' ),
	);
}

/**
 * Multiplying factor for a tool: result = input × factor. Mirrors toolFactor() in main.js.
 *
 * @param string $calc   Calculation id.
 * @param array  $params Settings.
 * @return float
 */
function powerbachat_tool_factor( $calc, $params ) {
	$phase = isset( $params['phase'] ) ? $params['phase'] : 'ac1';
	$volts = isset( $params['volts'] ) ? (float) $params['volts'] : 0;
	$pf    = 'dc' === $phase || ! isset( $params['pf'] ) ? 1 : (float) $params['pf'];
	$root  = 'ac3' === $phase ? sqrt( 3 ) : 1;
	$hp    = array(
		'mech'   => 745.69987,
		'elec'   => 746,
		'metric' => 735.49875,
	);
	switch ( $calc ) {
		case 'w2a':
			return $volts * $pf > 0 ? 1 / ( $volts * $pf * $root ) : 0;
		case 'a2w':
			return $volts * $pf * $root;
		case 'v2w':
			return ( isset( $params['amps'] ) ? (float) $params['amps'] : 0 ) * $pf;
		case 'hp2w':
			return isset( $params['hp'], $hp[ $params['hp'] ] ) ? $hp[ $params['hp'] ] : $hp['mech'];
		case 'kva2a':
			return $volts > 0 ? 1000 / ( $volts * $root ) : 0;
		case 'kva2kw':
			return $pf;
		case 'kwh2w':
			$hours = isset( $params['hours'] ) ? (float) $params['hours'] : 0;
			return $hours > 0 ? 1000 / $hours : 0;
		case 'wh2mah':
			return $volts > 0 ? 1000 / $volts : 0;
		case 'ah2wh':
			return $volts;
		case 'btu2kw':
			return 0.00029307107;
		case 'btu2w':
			return 0.29307107;
		case 'btu2ton':
			return 1 / 12000;
	}
	return 0;
}

/**
 * Format a converted number the way people read them: fewer decimals for big
 * values, thousands separators, no trailing zeros. Mirrors toolNum() in main.js.
 *
 * @param float $n Number.
 * @return string
 */
function powerbachat_tool_num( $n ) {
	$abs = abs( $n );
	if ( $abs >= 100 ) {
		$d = 1;
	} elseif ( $abs >= 1 ) {
		$d = 2;
	} elseif ( $abs >= 0.01 ) {
		$d = 3;
	} else {
		$d = 5;
	}
	$out = number_format( $n, $d, '.', ',' );
	if ( false !== strpos( $out, '.' ) ) {
		$out = rtrim( rtrim( $out, '0' ), '.' );
	}
	return '-0' === $out ? '0' : $out;
}

/**
 * The tool for a page, from its slug.
 *
 * @param int|WP_Post|null $post Page.
 * @return array|null Tool with its slug under "id".
 */
function powerbachat_tool_for( $post = null ) {
	$post  = get_post( $post );
	$tools = powerbachat_tools();
	if ( ! $post || ! isset( $tools[ $post->post_name ] ) ) {
		return null;
	}
	return array_merge( $tools[ $post->post_name ], array( 'id' => $post->post_name ) );
}

/**
 * Settings as the browser needs them, without the table.
 *
 * @param array $tool Tool.
 * @return array
 */
function powerbachat_tool_client_config( $tool ) {
	$params = array();
	foreach ( $tool['params'] as $key => $param ) {
		$params[ $key ] = array(
			'default' => $param['default'],
			'hideDc'  => ! empty( $param['hide_dc'] ),
		);
	}
	return array(
		'calc'   => $tool['calc'],
		'from'   => $tool['from'],
		'to'     => $tool['to'],
		'params' => $params,
	);
}

/**
 * URL of a tool page.
 *
 * @param string $slug Tool slug.
 * @return string
 */
function powerbachat_tool_url( $slug ) {
	return home_url( '/tools/' . $slug . '/' );
}

/**
 * [powerbachat_tool_table] prints the reference table for the page's tool.
 *
 * @return string
 */
function powerbachat_tool_table_shortcode() {
	$tool = powerbachat_tool_for();
	if ( ! $tool || empty( $tool['table'] ) ) {
		return '';
	}
	$table = $tool['table'];
	$head  = '<th scope="col">' . esc_html( $tool['from']['unit'] ) . '</th>';
	foreach ( $table['cols'] as $col ) {
		$head .= '<th scope="col">' . esc_html( $col[0] ) . '</th>';
	}
	$body = '';
	foreach ( $table['rows'] as $value ) {
		$body .= '<tr><th scope="row">' . esc_html( powerbachat_tool_num( $value ) . ' ' . $tool['from']['unit'] ) . '</th>';
		foreach ( $table['cols'] as $col ) {
			$scale = isset( $col[2] ) ? $col[2] : 1;
			$unit  = 1 === $scale ? $tool['to']['unit'] : '';
			$body .= '<td>' . esc_html( trim( powerbachat_tool_num( $value * powerbachat_tool_factor( $tool['calc'], $col[1] ) * $scale ) . ' ' . $unit ) ) . '</td>';
		}
		$body .= '</tr>';
	}
	return powerbachat_table_html( $table['caption'], $head, $body, __( 'Values are rounded. Use the calculator above for any other number.', 'powerbachat' ) );
}
add_shortcode( 'powerbachat_tool_table', 'powerbachat_tool_table_shortcode' );

/**
 * Cards linking to tools.
 *
 * @param string[] $slugs Tool slugs, in order.
 * @param string   $label Accessible label for the list.
 * @return string
 */
function powerbachat_tool_cards( $slugs, $label ) {
	$tools = powerbachat_tools();
	$html  = '<ul class="util-grid tool-grid" aria-label="' . esc_attr( $label ) . '">';
	foreach ( $slugs as $slug ) {
		if ( ! isset( $tools[ $slug ] ) ) {
			continue;
		}
		$tool  = $tools[ $slug ];
		$html .= '<li class="util-card tool-card"><a href="' . esc_url( powerbachat_tool_url( $slug ) ) . '">';
		$html .= '<span class="tool-card__units">' . esc_html( $tool['from']['unit'] ) . ' ' . powerbachat_icon( 'arrow' ) . ' ' . esc_html( $tool['to']['unit'] ) . '</span>';
		$html .= '<span class="util-card__name">' . esc_html( $tool['name'] ) . '</span>';
		$html .= '<span class="util-card__area">' . esc_html( $tool['blurb'] ) . '</span>';
		$html .= '<span class="util-card__go">' . esc_html__( 'Convert', 'powerbachat' ) . ' ' . powerbachat_icon( 'arrow' ) . '</span>';
		$html .= '</a></li>';
	}
	return $html . '</ul>';
}

/**
 * [powerbachat_tools] lists every converter, grouped. Used on the /tools/ hub.
 *
 * @return string
 */
function powerbachat_tools_shortcode() {
	$tools = powerbachat_tools();
	$html  = '';
	foreach ( powerbachat_tool_groups() as $group => $label ) {
		$slugs = array_keys( wp_list_filter( $tools, array( 'group' => $group ) ) );
		if ( ! $slugs ) {
			continue;
		}
		$html .= '<h2 class="wp-block-heading">' . esc_html( $label ) . '</h2>' . powerbachat_tool_cards( $slugs, $label );
	}
	return '<div class="tool-hub">' . $html . '</div>';
}
add_shortcode( 'powerbachat_tools', 'powerbachat_tools_shortcode' );

/**
 * Other tools to show under a tool: the reverse converter first, then its group.
 *
 * @param array $tool Current tool.
 * @param int   $max  How many.
 * @return string[]
 */
function powerbachat_related_tools( $tool, $max = 4 ) {
	$tools = powerbachat_tools();
	$out   = array();
	if ( ! empty( $tool['pair'] ) ) {
		$out[] = $tool['pair'];
	}
	foreach ( $tools as $slug => $other ) {
		if ( $slug !== $tool['id'] && $other['group'] === $tool['group'] && ! in_array( $slug, $out, true ) ) {
			$out[] = $slug;
		}
	}
	foreach ( array_keys( $tools ) as $slug ) {
		if ( $slug !== $tool['id'] && ! in_array( $slug, $out, true ) ) {
			$out[] = $slug;
		}
	}
	return array_slice( $out, 0, $max );
}

/**
 * Tool pages for the menus, once they exist.
 *
 * @return array[] label, path.
 */
function powerbachat_tool_links() {
	$links = array();
	foreach ( powerbachat_tools() as $slug => $tool ) {
		$links[] = array( $tool['name'], '/tools/' . $slug . '/' );
	}
	return powerbachat_live_links( $links );
}

/**
 * Is this the /tools/ hub or one of the converters?
 *
 * @param int|WP_Post|null $post Page.
 * @return bool
 */
function powerbachat_is_tools_page( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}
	return 'tools' === get_page_uri( $post ) || 0 === strpos( get_page_uri( $post ), 'tools/' );
}
