<?php
/**
 * Customizer: everything an editor should be able to change without touching code.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values for every theme mod.
 *
 * @return array
 */
function powerbachat_mod_defaults() {
	return array(
		'pb_default_country' => 'pk',
		'pb_hero_title'      => 'Your electricity bill, worked out to the last rupee.',
		'pb_hero_text_pk'    => 'Free bill calculators for LESCO, IESCO, MEPCO, FESCO, K-Electric and every other DISCO — plus honest unit rates and solar prices, checked every month. No sign-up. No app to install.',
		'pb_hero_text_in'    => 'Free bill calculators for TNEB, KSEB, UPPCL, MSEDCL and more — plus rooftop solar prices with the PM Surya Ghar subsidy worked in. No sign-up. No app to install.',
		'pb_hero_text_bd'    => 'Free bill calculators for DESCO, DPDC, BREB and every other distributor — plus solar panel and IPS prices, checked every month. No sign-up. No app to install.',
		'pb_geo_lookup'      => true,
		'pb_tariff_checked'  => 'October 2026',
		'pb_price_updated'   => '3 October 2026',
		'pb_price_rows'      => implode(
			"\n",
			array(
				'Jinko Solar | Tiger Neo N-type 585W | 27.0 | -0.5',
				'Longi | Hi-MO 6 580W | 26.5 | 0',
				'Canadian Solar | TOPBiHiKu 575W | 25.5 | -1.0',
				'JA Solar | DeepBlue 4.0 590W | 26.0 | 0.5',
				'Trina Solar | Vertex N 580W | 25.0 | 0',
				'Astronergy | ASTRO N5 580W | 24.5 | -0.5',
			)
		),
		'pb_price_currency'  => 'Rs',
		'pb_whatsapp_url'    => '',
		'pb_newsletter_url'  => '',
		'pb_footer_note'     => 'PowerBachat is an independent site. We are not affiliated with any electricity regulator, distribution company or government body. Figures are estimates — your official bill is the final word.',
	);
}

/**
 * Read a theme mod with the theme's default.
 *
 * @param string $key Mod name.
 * @return mixed
 */
function powerbachat_mod( $key ) {
	$defaults = powerbachat_mod_defaults();
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Parse the price board textarea into rows.
 *
 * @return array[] Each row: brand, model, price, change.
 */
function powerbachat_price_rows() {
	$rows  = array();
	$lines = preg_split( '/\r\n|\r|\n/', (string) powerbachat_mod( 'pb_price_rows' ) );
	foreach ( $lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) < 3 || '' === $parts[0] ) {
			continue;
		}
		$rows[] = array(
			'brand'  => $parts[0],
			'model'  => $parts[1],
			'price'  => (float) $parts[2],
			'change' => isset( $parts[3] ) ? (float) $parts[3] : 0.0,
		);
	}
	return $rows;
}

/**
 * True while the price board still shows the shipped sample rows.
 *
 * @return bool
 */
function powerbachat_prices_are_default() {
	return false === get_theme_mod( 'pb_price_rows', false );
}

function powerbachat_customize_register( $wp_customize ) {
	$d = powerbachat_mod_defaults();

	$wp_customize->add_panel(
		'powerbachat',
		array(
			'title'    => __( 'PowerBachat', 'powerbachat' ),
			'priority' => 30,
		)
	);

	// Home page copy.
	$wp_customize->add_section(
		'powerbachat_home',
		array(
			'title' => __( 'Home page', 'powerbachat' ),
			'panel' => 'powerbachat',
		)
	);

	$wp_customize->add_setting( 'pb_default_country', array( 'default' => $d['pb_default_country'], 'sanitize_callback' => 'powerbachat_sanitize_country' ) );
	$wp_customize->add_control(
		'pb_default_country',
		array(
			'label'       => __( 'Country for visitors outside Pakistan, India and Bangladesh', 'powerbachat' ),
			'description' => __( 'Everyone else sees their own country automatically, based on their location. Pages under /pk/, /in/ and /bd/ always show that country.', 'powerbachat' ),
			'section'     => 'powerbachat_home',
			'type'        => 'select',
			'choices'     => array(
				'pk' => 'Pakistan',
				'in' => 'India',
				'bd' => 'Bangladesh',
			),
		)
	);

	$wp_customize->add_setting( 'pb_hero_title', array( 'default' => $d['pb_hero_title'], 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'pb_hero_title', array( 'label' => __( 'Hero headline', 'powerbachat' ), 'section' => 'powerbachat_home', 'type' => 'text' ) );

	foreach ( array( 'pk' => 'Pakistan', 'in' => 'India', 'bd' => 'Bangladesh' ) as $code => $name ) {
		$wp_customize->add_setting( 'pb_hero_text_' . $code, array( 'default' => $d[ 'pb_hero_text_' . $code ], 'sanitize_callback' => 'sanitize_textarea_field' ) );
		/* translators: %s: country name */
		$wp_customize->add_control( 'pb_hero_text_' . $code, array( 'label' => sprintf( __( 'Hero intro — %s', 'powerbachat' ), $name ), 'section' => 'powerbachat_home', 'type' => 'textarea' ) );
	}

	$wp_customize->add_setting( 'pb_geo_lookup', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control(
		'pb_geo_lookup',
		array(
			'label'       => __( 'Look up visitor location online', 'powerbachat' ),
			'description' => __( 'Used only when your host or Cloudflare does not already send the visitor\'s country. Sends the visitor IP to api.country.is; results are cached for a week.', 'powerbachat' ),
			'section'     => 'powerbachat_home',
			'type'        => 'checkbox',
		)
	);

	// Rates and prices.
	$wp_customize->add_section(
		'powerbachat_data',
		array(
			'title'       => __( 'Rates & prices', 'powerbachat' ),
			'panel'       => 'powerbachat',
			'description' => __( 'Tariff slabs live in inc/data.php (or the powerbachat_data filter). The solar price board is edited here.', 'powerbachat' ),
		)
	);

	$wp_customize->add_setting( 'pb_tariff_checked', array( 'default' => $d['pb_tariff_checked'], 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'pb_tariff_checked', array( 'label' => __( 'Tariffs last checked (shown on site)', 'powerbachat' ), 'section' => 'powerbachat_data', 'type' => 'text' ) );

	$wp_customize->add_setting( 'pb_price_updated', array( 'default' => $d['pb_price_updated'], 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'pb_price_updated', array( 'label' => __( 'Price board updated on', 'powerbachat' ), 'section' => 'powerbachat_data', 'type' => 'text' ) );

	$wp_customize->add_setting( 'pb_price_currency', array( 'default' => $d['pb_price_currency'], 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'pb_price_currency', array( 'label' => __( 'Price board currency', 'powerbachat' ), 'section' => 'powerbachat_data', 'type' => 'text' ) );

	$wp_customize->add_setting( 'pb_price_rows', array( 'default' => $d['pb_price_rows'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control(
		'pb_price_rows',
		array(
			'label'       => __( 'Solar panel price board', 'powerbachat' ),
			'description' => __( 'One panel per line: Brand | Model | Price per watt | Change since last update. Example: Jinko Solar | Tiger Neo 585W | 27 | -0.5', 'powerbachat' ),
			'section'     => 'powerbachat_data',
			'type'        => 'textarea',
		)
	);

	// Alerts and footer.
	$wp_customize->add_section(
		'powerbachat_alerts',
		array(
			'title' => __( 'Alerts & footer', 'powerbachat' ),
			'panel' => 'powerbachat',
		)
	);

	$wp_customize->add_setting( 'pb_whatsapp_url', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'pb_whatsapp_url', array( 'label' => __( 'WhatsApp channel URL', 'powerbachat' ), 'section' => 'powerbachat_alerts', 'type' => 'url' ) );

	$wp_customize->add_setting( 'pb_newsletter_url', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control(
		'pb_newsletter_url',
		array(
			'label'       => __( 'Newsletter form action URL', 'powerbachat' ),
			'description' => __( 'Paste the form action from Mailchimp, Brevo, ConvertKit etc. The field is posted as "EMAIL" and "email".', 'powerbachat' ),
			'section'     => 'powerbachat_alerts',
			'type'        => 'url',
		)
	);

	$wp_customize->add_setting( 'pb_footer_note', array( 'default' => $d['pb_footer_note'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'pb_footer_note', array( 'label' => __( 'Footer disclaimer', 'powerbachat' ), 'section' => 'powerbachat_alerts', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'powerbachat_customize_register' );

function powerbachat_sanitize_country( $value ) {
	return in_array( $value, array( 'pk', 'in', 'bd' ), true ) ? $value : 'pk';
}
