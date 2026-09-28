<?php
/**
 * Site-wide settings (Appearance → Customize → ClaimFairly settings).
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values for every theme option.
 *
 * @return array<string,string>
 */
function claimfairly_option_defaults() {
	return array(
		'cf_disclaimer'     => __( 'Educational estimate, not legal advice. Results vary. Consult a licensed attorney in your state.', 'claimfairly' ),
		'cf_footer_about'   => __( 'Free, honest calculators that help you understand car accident and insurance claims. No signup. Nothing you type is stored.', 'claimfairly' ),
		'cf_contact_email'  => 'hello@claimfairly.com',
		'cf_about_url'      => '/about/',
		'cf_hero_title'     => __( 'Free, honest car accident claim calculators', 'claimfairly' ),
		'cf_hero_text'      => __( 'See how insurers value your claim, with the formula shown, sources cited and nothing stored. Built for everyday drivers, not lawyers.', 'claimfairly' ),
		'cf_same_as'        => '',
	);
}

/**
 * Read a theme option with its default.
 *
 * @param string $key Option key.
 * @return string
 */
function claimfairly_opt( $key ) {
	$defaults = claimfairly_option_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return (string) get_theme_mod( $key, $default );
}

/**
 * Register Customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function claimfairly_customize_register( $wp_customize ) {
	$defaults = claimfairly_option_defaults();

	$wp_customize->add_section(
		'claimfairly_settings',
		array(
			'title'    => __( 'ClaimFairly settings', 'claimfairly' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'cf_hero_title'    => array( __( 'Homepage headline', 'claimfairly' ), 'text', 'sanitize_text_field' ),
		'cf_hero_text'     => array( __( 'Homepage intro text', 'claimfairly' ), 'textarea', 'sanitize_textarea_field' ),
		'cf_disclaimer'    => array( __( 'Disclaimer (shown on every tool and in the footer)', 'claimfairly' ), 'textarea', 'sanitize_textarea_field' ),
		'cf_footer_about'  => array( __( 'Footer description', 'claimfairly' ), 'textarea', 'sanitize_textarea_field' ),
		'cf_contact_email' => array( __( 'Contact email', 'claimfairly' ), 'email', 'sanitize_email' ),
		'cf_about_url'     => array( __( 'About page URL (used in the author box)', 'claimfairly' ), 'text', 'esc_url_raw' ),
		'cf_same_as'       => array( __( 'Official profile URLs, one per line (LinkedIn, X, Crunchbase…). Used in Organization schema.', 'claimfairly' ), 'textarea', 'claimfairly_sanitize_url_lines' ),
	);

	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $field[2],
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field[0],
				'section' => 'claimfairly_settings',
				'type'    => $field[1],
			)
		);
	}
}
add_action( 'customize_register', 'claimfairly_customize_register' );

/**
 * Keep only valid URLs, one per line.
 *
 * @param string $value Raw textarea value.
 * @return string
 */
function claimfairly_sanitize_url_lines( $value ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $value );
	$clean = array();
	foreach ( $lines as $line ) {
		$url = esc_url_raw( trim( $line ) );
		if ( $url ) {
			$clean[] = $url;
		}
	}
	return implode( "\n", $clean );
}
