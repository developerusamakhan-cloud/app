<?php
/**
 * Server-side bill maths and the data tables used inside articles.
 *
 * Every number in a table comes from inc/data.php or the Customizer, so when a
 * tariff changes the articles change with it. Shortcodes:
 *
 * [powerbachat_rate_table tariff="pk-nepra" plan="standard"]
 * [powerbachat_bill_table tariff="pk-nepra" units="100,200,300" plan="standard"]
 * [powerbachat_solar_table country="pk" sizes="1,2,3,5,10"]
 * [powerbachat_price_table]
 * [powerbachat_battery]
 * [powerbachat_checked]
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Estimate a bill. Mirrors computeBill() in assets/js/main.js.
 *
 * @param string $tariff_id      Key in powerbachat_data()['tariffs'].
 * @param int    $units          Units in the billing period.
 * @param bool   $want_protected Use the protected plan when the tariff has one.
 * @return array|null
 */
function powerbachat_compute_bill( $tariff_id, $units, $want_protected = false ) {
	$data = powerbachat_data();
	if ( empty( $data['tariffs'][ $tariff_id ] ) ) {
		return null;
	}
	$tariff = $data['tariffs'][ $tariff_id ];
	$units  = max( 0, (int) round( $units ) );
	$plans  = $tariff['plans'];
	$plan   = $plans['standard'];
	if ( $want_protected && isset( $plans['protected'] ) ) {
		$max = isset( $plans['protected']['max_units'] ) ? $plans['protected']['max_units'] : null;
		if ( null === $max || $units <= $max ) {
			$plan = $plans['protected'];
		}
	}

	$schedule = end( $plan['schedules'] );
	foreach ( $plan['schedules'] as $candidate ) {
		if ( null === $candidate['max_units'] || $units <= $candidate['max_units'] ) {
			$schedule = $candidate;
			break;
		}
	}

	$lines  = array();
	$energy = 0.0;
	if ( 'flat' === $schedule['mode'] ) {
		$last = end( $schedule['slabs'] );
		$rate = $last[1];
		foreach ( $schedule['slabs'] as $slab ) {
			if ( null === $slab[0] || $units <= $slab[0] ) {
				$rate = $slab[1];
				break;
			}
		}
		$energy  = $units * $rate;
		$lines[] = array( 'label' => sprintf( 'All %d units', $units ), 'units' => $units, 'rate' => $rate, 'amount' => $energy );
	} else {
		$prev = 0;
		foreach ( $schedule['slabs'] as $slab ) {
			$cap  = null === $slab[0] ? PHP_INT_MAX : $slab[0];
			$take = min( $units, $cap ) - $prev;
			if ( $take <= 0 ) {
				break;
			}
			$amount  = $take * $slab[1];
			$energy += $amount;
			$lines[] = array(
				'label'  => null === $slab[0] ? sprintf( 'Above %d', $prev ) : sprintf( '%d to %d', $prev + 1, $slab[0] ),
				'units'  => $take,
				'rate'   => $slab[1],
				'amount' => $amount,
			);
			$prev    = $cap;
		}
	}

	$fixed = 0.0;
	foreach ( $tariff['fixed'] as $item ) {
		$fixed += $units > 0 ? $item['amount'] : 0;
	}
	$base  = $energy + $fixed;
	$taxes = 0.0;
	foreach ( $tariff['taxes'] as $tax ) {
		$taxes += $base * $tax['pct'] / 100;
	}
	$total = $base + $taxes;

	return array(
		'plan'   => $plan['label'],
		'lines'  => $lines,
		'energy' => $energy,
		'fixed'  => $fixed,
		'taxes'  => $taxes,
		'total'  => $total,
		'avg'    => $units > 0 ? $total / $units : 0,
	);
}

/**
 * Currency symbol for the country that owns a tariff.
 *
 * @param string $tariff_id Tariff key.
 * @return string
 */
function powerbachat_tariff_currency( $tariff_id ) {
	$prefix = substr( $tariff_id, 0, 2 );
	$data   = powerbachat_data();
	return isset( $data['countries'][ $prefix ] ) ? $data['countries'][ $prefix ]['currency'] : '';
}

/**
 * Wrap a table with a caption and a "checked" note.
 *
 * @param string $caption Caption.
 * @param string $head    Header row HTML.
 * @param string $body    Body rows HTML.
 * @param string $note    Footnote (plain text).
 * @return string
 */
function powerbachat_table_html( $caption, $head, $body, $note = '' ) {
	$html  = '<figure class="data-table"><div class="data-table__scroll"><table>';
	$html .= '<caption>' . esc_html( $caption ) . '</caption>';
	$html .= '<thead><tr>' . $head . '</tr></thead><tbody>' . $body . '</tbody></table></div>';
	if ( $note ) {
		$html .= '<figcaption>' . esc_html( $note ) . '</figcaption>';
	}
	return $html . '</figure>';
}

/**
 * [powerbachat_checked] prints the month the rates were last checked.
 *
 * @return string
 */
function powerbachat_checked_shortcode() {
	return esc_html( powerbachat_mod( 'pb_tariff_checked' ) );
}
add_shortcode( 'powerbachat_checked', 'powerbachat_checked_shortcode' );

/**
 * [powerbachat_rate_table tariff="pk-nepra" plan="standard"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function powerbachat_rate_table_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'tariff'  => 'pk-nepra',
			'plan'    => 'standard',
			'caption' => '',
		),
		$atts
	);
	$data = powerbachat_data();
	if ( empty( $data['tariffs'][ $atts['tariff'] ]['plans'][ $atts['plan'] ] ) ) {
		return '';
	}
	$tariff = $data['tariffs'][ $atts['tariff'] ];
	$plan   = $tariff['plans'][ $atts['plan'] ];
	$cur    = powerbachat_tariff_currency( $atts['tariff'] );
	$multi  = count( $plan['schedules'] ) > 1;
	$body   = '';

	foreach ( $plan['schedules'] as $schedule ) {
		if ( $multi ) {
			$label = null === $schedule['max_units']
				? __( 'When usage is above the previous limit', 'powerbachat' )
				/* translators: %d: units */
				: sprintf( __( 'When total usage is up to %d units', 'powerbachat' ), $schedule['max_units'] );
			if ( 'flat' === $schedule['mode'] ) {
				$label .= ' ' . __( '(every unit at one rate)', 'powerbachat' );
			}
			$body .= '<tr class="data-table__group"><th colspan="2" scope="rowgroup">' . esc_html( $label ) . '</th></tr>';
		}
		$prev = 0;
		foreach ( $schedule['slabs'] as $slab ) {
			if ( 'flat' === $schedule['mode'] ) {
				/* translators: %d: units */
				$range = null === $slab[0] ? sprintf( __( 'Above %d units', 'powerbachat' ), $prev ) : sprintf( __( 'Up to %d units', 'powerbachat' ), $slab[0] );
			} else {
				/* translators: 1: from, 2: to */
				$range = null === $slab[0] ? sprintf( __( 'Above %d units', 'powerbachat' ), $prev ) : sprintf( __( '%1$d to %2$d units', 'powerbachat' ), $prev + 1, $slab[0] );
			}
			$body .= '<tr><td>' . esc_html( $range ) . '</td><td>' . esc_html( $cur . ' ' . number_format_i18n( $slab[1], 2 ) ) . '</td></tr>';
			$prev  = (int) $slab[0];
		}
	}

	$extras = array();
	foreach ( $tariff['taxes'] as $tax ) {
		$extras[] = $tax['label'] . ' ' . $tax['pct'] . '%';
	}
	foreach ( $tariff['fixed'] as $item ) {
		$extras[] = $item['label'] . ' ' . $cur . ' ' . $item['amount'];
	}
	$note = sprintf(
		/* translators: 1: tariff name, 2: month */
		__( '%1$s, base rates per unit, checked %2$s.', 'powerbachat' ),
		$tariff['label'],
		powerbachat_mod( 'pb_tariff_checked' )
	);
	if ( $extras ) {
		/* translators: %s: list of taxes */
		$note .= ' ' . sprintf( __( 'Added on top: %s.', 'powerbachat' ), implode( ', ', $extras ) );
	}

	$caption = $atts['caption'] ? $atts['caption'] : $plan['label'] . ' ' . __( 'rates', 'powerbachat' );
	$head    = '<th scope="col">' . esc_html__( 'Units in the month', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Rate per unit', 'powerbachat' ) . '</th>';
	return powerbachat_table_html( $caption, $head, $body, $note );
}
add_shortcode( 'powerbachat_rate_table', 'powerbachat_rate_table_shortcode' );

/**
 * [powerbachat_bill_table tariff="pk-nepra" units="100,200,300" plan="standard"]
 * plan="auto" uses protected rates up to the protected limit, standard above it.
 *
 * @param array $atts Attributes.
 * @return string
 */
function powerbachat_bill_table_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'tariff'  => 'pk-nepra',
			'units'   => '100,200,300,400,500,700,1000',
			'plan'    => 'standard',
			'caption' => '',
		),
		$atts
	);
	$cur  = powerbachat_tariff_currency( $atts['tariff'] );
	$body = '';
	foreach ( array_filter( array_map( 'absint', explode( ',', $atts['units'] ) ) ) as $units ) {
		$bill = powerbachat_compute_bill( $atts['tariff'], $units, 'protected' === $atts['plan'] || 'auto' === $atts['plan'] );
		if ( ! $bill ) {
			return '';
		}
		$body .= '<tr><td>' . esc_html( number_format_i18n( $units ) ) . '</td>'
			. '<td>' . esc_html( $cur . ' ' . number_format_i18n( $bill['energy'] + $bill['fixed'] ) ) . '</td>'
			. '<td>' . esc_html( $cur . ' ' . number_format_i18n( $bill['taxes'] ) ) . '</td>'
			. '<td><strong>' . esc_html( $cur . ' ' . number_format_i18n( $bill['total'] ) ) . '</strong></td>'
			. '<td>' . esc_html( $cur . ' ' . number_format_i18n( $bill['avg'], 2 ) ) . '</td></tr>';
	}
	$head    = '<th scope="col">' . esc_html__( 'Units', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Energy charge', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Taxes', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Estimated bill', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Per unit', 'powerbachat' ) . '</th>';
	$caption = $atts['caption'] ? $atts['caption'] : __( 'Estimated bill at common usage levels', 'powerbachat' );
	/* translators: %s: month */
	$note = sprintf( __( 'Estimates from the base tariff checked %s. Fuel adjustments, arrears and late fees are not included.', 'powerbachat' ), powerbachat_mod( 'pb_tariff_checked' ) );
	return powerbachat_table_html( $caption, $head, $body, $note );
}
add_shortcode( 'powerbachat_bill_table', 'powerbachat_bill_table_shortcode' );

/**
 * [powerbachat_solar_table country="pk" sizes="1,2,3,5,10"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function powerbachat_solar_table_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'country' => 'pk',
			'sizes'   => '1,2,3,5,7,10,15',
			'caption' => '',
		),
		$atts
	);
	$data = powerbachat_data();
	if ( empty( $data['solar'][ $atts['country'] ] ) ) {
		return '';
	}
	$cfg  = $data['solar'][ $atts['country'] ];
	$cur  = $data['countries'][ $atts['country'] ]['currency'];
	$gen  = $cfg['sun_hours'] * 30 * 0.78;
	$body = '';
	foreach ( array_filter( array_map( 'floatval', explode( ',', $atts['sizes'] ) ) ) as $kw ) {
		$panels = (int) ceil( $kw * 1000 / $cfg['panel_watt'] );
		$body  .= '<tr><td>' . esc_html( rtrim( rtrim( number_format( $kw, 1 ), '0' ), '.' ) . ' kW' ) . '</td>'
			. '<td>' . esc_html( $panels . ' × ' . $cfg['panel_watt'] . ' W' ) . '</td>'
			. '<td>' . esc_html( number_format_i18n( round( $kw * $gen, -1 ) ) ) . '</td>'
			. '<td>' . esc_html( $cur . ' ' . number_format_i18n( round( $kw * $cfg['per_kw'][0], -3 ) ) . ' to ' . number_format_i18n( round( $kw * $cfg['per_kw'][1], -3 ) ) ) . '</td></tr>';
	}
	$head    = '<th scope="col">' . esc_html__( 'System', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Panels', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Units per month', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Installed cost (on-grid)', 'powerbachat' ) . '</th>';
	$caption = $atts['caption'] ? $atts['caption'] : __( 'Solar system sizes, output and cost', 'powerbachat' );
	/* translators: 1: sun hours, 2: month */
	$note = sprintf( __( 'Output assumes %1$s peak sun hours and 22%% system losses. Cost covers panels, inverter, structure, wiring and fitting, without batteries. Price range checked %2$s.', 'powerbachat' ), $cfg['sun_hours'], powerbachat_mod( 'pb_price_updated' ) );
	return powerbachat_table_html( $caption, $head, $body, $note );
}
add_shortcode( 'powerbachat_solar_table', 'powerbachat_solar_table_shortcode' );

/**
 * [powerbachat_price_table] is the Customizer price board as an article table.
 *
 * @return string
 */
function powerbachat_price_table_shortcode() {
	$rows = powerbachat_price_rows();
	if ( ! $rows ) {
		return '';
	}
	$cur  = powerbachat_mod( 'pb_price_currency' );
	$body = '';
	foreach ( $rows as $row ) {
		preg_match( '/(\d{3,4})\s?W/i', $row['model'], $watt );
		$panel = ! empty( $watt[1] ) ? $cur . ' ' . number_format_i18n( round( $row['price'] * (int) $watt[1], -2 ) ) : '';
		$move  = 0.0 === $row['change'] ? __( 'No change', 'powerbachat' ) : ( $row['change'] < 0 ? __( 'Down', 'powerbachat' ) : __( 'Up', 'powerbachat' ) ) . ' ' . number_format( abs( $row['change'] ), 1 );
		$body .= '<tr><td><strong>' . esc_html( $row['brand'] ) . '</strong><br><small>' . esc_html( $row['model'] ) . '</small></td>'
			. '<td>' . esc_html( $cur . ' ' . number_format( $row['price'], 1 ) ) . '</td>'
			. '<td>' . esc_html( $panel ) . '</td>'
			. '<td>' . esc_html( $move ) . '</td></tr>';
	}
	$head = '<th scope="col">' . esc_html__( 'Panel', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Per watt', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Per panel', 'powerbachat' ) . '</th><th scope="col">' . esc_html__( 'Since last update', 'powerbachat' ) . '</th>';
	/* translators: %s: date */
	$note = sprintf( __( 'Market rates updated %s, ex-warehouse, before delivery and fitting.', 'powerbachat' ), powerbachat_mod( 'pb_price_updated' ) );
	return powerbachat_table_html( __( 'A-grade solar panel prices', 'powerbachat' ), $head, $body, $note );
}
add_shortcode( 'powerbachat_price_table', 'powerbachat_price_table_shortcode' );

/**
 * [powerbachat_battery] is the battery backup calculator.
 *
 * @return string
 */
function powerbachat_battery_shortcode() {
	return powerbachat_render_part( 'template-parts/battery' );
}
add_shortcode( 'powerbachat_battery', 'powerbachat_battery_shortcode' );
