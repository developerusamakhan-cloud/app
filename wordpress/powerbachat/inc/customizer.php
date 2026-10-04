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
		'pb_hero_text_pk'    => 'Free bill calculators for LESCO, IESCO, MEPCO, FESCO, K-Electric and every other DISCO, plus honest unit rates and solar prices, checked every month. No sign-up. No app to install.',
		'pb_hero_text_in'    => 'Free bill calculators for TNEB, KSEB, UPPCL, MSEDCL and more, plus rooftop solar prices with the PM Surya Ghar subsidy worked in. No sign-up. No app to install.',
		'pb_hero_text_bd'    => 'Free bill calculators for DESCO, DPDC, BREB and every other distributor, plus solar panel and IPS prices, checked every month. No sign-up. No app to install.',
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
		'pb_double_optin'    => true,
		'pb_alert_new_posts' => true,
		'pb_contact_email'   => 'hello@powerbachat.com',
		'pb_legal_country'   => 'Pakistan',
		'pb_credit_name'     => 'Vyntic Studio',
		'pb_credit_url'      => 'https://vyntic.studio/',
		'pb_footer_note'     => 'PowerBachat is an independent site. We are not affiliated with any electricity regulator, distribution company or government body. Figures are estimates; your official bill is the final word.',
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
 * Every price board: where its rows live, its currency and how it is priced.
 * "W" boards are priced per watt (solar panels); "unit" boards per item.
 *
 * @return array[]
 */
function powerbachat_price_boards() {
	return array(
		'pk-panels'  => array(
			'mod'      => 'pb_price_rows',
			'currency' => powerbachat_mod( 'pb_price_currency' ),
			'unit'     => 'W',
			'label'    => __( 'A-grade solar panel prices in Pakistan', 'powerbachat' ),
			'default'  => "Jinko Solar | Tiger Neo N-type 585W | 27.0 | -0.5\nLongi | Hi-MO 6 580W | 26.5 | 0\nCanadian Solar | TOPBiHiKu 575W | 25.5 | -1.0\nJA Solar | DeepBlue 4.0 590W | 26.0 | 0.5\nTrina Solar | Vertex N 580W | 25.0 | 0\nAstronergy | ASTRO N5 580W | 24.5 | -0.5",
		),
		'in-panels'  => array(
			'mod'      => 'pb_price_rows_in',
			'currency' => '₹',
			'unit'     => 'W',
			'label'    => __( 'Solar panel prices in India', 'powerbachat' ),
			'default'  => "Waaree Energies | Bifacial 550W (non-DCR) | 22.0 | 0\nAdani Solar | Shine Mono PERC 545W (non-DCR) | 21.5 | -0.5\nTata Power Solar | Mono PERC 545W (DCR) | 31.0 | 0\nVikram Solar | Hypersol 550W (DCR) | 30.0 | 0.5\nPremier Energies | TOPCon 550W (DCR) | 30.5 | 0",
		),
		'bd-panels'  => array(
			'mod'      => 'pb_price_rows_bd',
			'currency' => '৳',
			'unit'     => 'W',
			'label'    => __( 'Solar panel prices in Bangladesh', 'powerbachat' ),
			'default'  => "Rahimafrooz | Mono 100W | 48.0 | 0\nWalton | Mono 150W | 46.0 | 0\nJinko Solar | Tiger Neo 580W | 36.0 | -1.0\nLongi | Hi-MO 6 575W | 35.5 | 0\nJA Solar | Mono 550W | 35.0 | -0.5",
		),
		'bd-ips'     => array(
			'mod'      => 'pb_ips_rows_bd',
			'currency' => '৳',
			'unit'     => 'unit',
			'label'    => __( 'IPS prices in Bangladesh (unit only, battery extra)', 'powerbachat' ),
			'default'  => "Rahimafrooz | Instapower 650VA | 9500 | 0\nRahimafrooz | Instapower 1000VA | 14500 | 0\nLuminous | Zelio+ 1100VA | 13500 | -500\nLuminous | Eco Volt 700VA | 8500 | 0\nMicrotek | Super Power 1050VA | 11000 | 0\nVision | IPS 1000VA | 10500 | 0",
		),
		'bd-battery' => array(
			'mod'      => 'pb_battery_rows_bd',
			'currency' => '৳',
			'unit'     => 'unit',
			'label'    => __( 'IPS and solar battery prices in Bangladesh', 'powerbachat' ),
			'default'  => "Rahimafrooz | Tubular IPS battery 12V 130Ah | 18500 | 0\nHamko | Tubular battery 12V 165Ah | 21000 | 500\nRahimafrooz | Solar battery 12V 100Ah | 15500 | 0\nVolvo | Solar battery 12V 100Ah | 13500 | 0\nLiFePO4 (various brands) | Lithium 12.8V 100Ah | 32000 | -1000",
		),
	);
}

/**
 * Parse a price board's Customizer textarea into rows.
 *
 * @param string $board Board key.
 * @return array[] Each row: brand, model, price, change.
 */
function powerbachat_price_rows( $board = 'pk-panels' ) {
	$boards = powerbachat_price_boards();
	if ( empty( $boards[ $board ] ) ) {
		return array();
	}
	$raw   = get_theme_mod( $boards[ $board ]['mod'], $boards[ $board ]['default'] );
	$rows  = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
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
 * True while a price board still shows the shipped sample rows.
 *
 * @param string $board Board key.
 * @return bool
 */
function powerbachat_prices_are_default( $board = 'pk-panels' ) {
	$boards = powerbachat_price_boards();
	return isset( $boards[ $board ] ) && false === get_theme_mod( $boards[ $board ]['mod'], false );
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
		$wp_customize->add_control( 'pb_hero_text_' . $code, array( 'label' => sprintf( __( 'Hero intro: %s', 'powerbachat' ), $name ), 'section' => 'powerbachat_home', 'type' => 'textarea' ) );
	}

	$wp_customize->add_setting( 'pb_share_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'pb_share_image',
			array(
				'label'       => __( 'Social share image', 'powerbachat' ),
				'description' => __( 'Shown when the home page (or a page without its own image) is shared on WhatsApp, Facebook or X. Best size 1200 x 630. Leave empty to use the built-in image.', 'powerbachat' ),
				'section'     => 'powerbachat_home',
			)
		)
	);

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

	foreach ( powerbachat_price_boards() as $key => $board ) {
		$wp_customize->add_setting( $board['mod'], array( 'default' => $board['default'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp_customize->add_control(
			$board['mod'],
			array(
				'label'       => $board['label'],
				'description' => 'W' === $board['unit']
					? __( 'One panel per line: Brand | Model | Price per watt | Change since last update. Example: Jinko Solar | Tiger Neo 585W | 27 | -0.5', 'powerbachat' )
					: __( 'One product per line: Brand | Model | Price | Change since last update. Example: Luminous | Zelio+ 1100VA | 13500 | -500', 'powerbachat' ),
				'section'     => 'powerbachat_data',
				'type'        => 'textarea',
			)
		);
	}

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

	$wp_customize->add_setting( 'pb_double_optin', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control(
		'pb_double_optin',
		array(
			'label'       => __( 'Ask new subscribers to confirm by email (recommended)', 'powerbachat' ),
			'description' => __( 'Used by the built-in alerts when no newsletter form URL is set above.', 'powerbachat' ),
			'section'     => 'powerbachat_alerts',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting( 'pb_alert_new_posts', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control(
		'pb_alert_new_posts',
		array(
			'label'       => __( 'Email subscribers when a new guide goes live', 'powerbachat' ),
			'description' => __( 'Only subscribers in that guide\'s country. Posts created by the content importer are not emailed until they go live on their scheduled date.', 'powerbachat' ),
			'section'     => 'powerbachat_alerts',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting( 'pb_contact_email', array( 'default' => $d['pb_contact_email'], 'sanitize_callback' => 'sanitize_email' ) );
	$wp_customize->add_control(
		'pb_contact_email',
		array(
			'label'       => __( 'Contact email', 'powerbachat' ),
			'description' => __( 'Shown on the contact and privacy pages and used for contact form messages. Make sure this mailbox exists.', 'powerbachat' ),
			'section'     => 'powerbachat_alerts',
			'type'        => 'email',
		)
	);

	$wp_customize->add_setting( 'pb_legal_country', array( 'default' => $d['pb_legal_country'], 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control(
		'pb_legal_country',
		array(
			'label'       => __( 'Governing law (terms of use)', 'powerbachat' ),
			'description' => __( 'The country where the site owner is based.', 'powerbachat' ),
			'section'     => 'powerbachat_alerts',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting( 'pb_credit_name', array( 'default' => $d['pb_credit_name'], 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control(
		'pb_credit_name',
		array(
			'label'       => __( 'Footer credit: made by', 'powerbachat' ),
			'description' => __( 'Shown as "Made with love by ..." in the footer. Leave empty to hide it.', 'powerbachat' ),
			'section'     => 'powerbachat_alerts',
			'type'        => 'text',
		)
	);
	$wp_customize->add_setting( 'pb_credit_url', array( 'default' => $d['pb_credit_url'], 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'pb_credit_url', array( 'label' => __( 'Footer credit link', 'powerbachat' ), 'section' => 'powerbachat_alerts', 'type' => 'url' ) );

	$wp_customize->add_setting( 'pb_footer_note', array( 'default' => $d['pb_footer_note'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'pb_footer_note', array( 'label' => __( 'Footer disclaimer', 'powerbachat' ), 'section' => 'powerbachat_alerts', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'powerbachat_customize_register' );

function powerbachat_sanitize_country( $value ) {
	return in_array( $value, array( 'pk', 'in', 'bd' ), true ) ? $value : 'pk';
}
