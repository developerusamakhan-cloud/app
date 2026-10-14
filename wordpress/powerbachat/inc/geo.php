<?php
/**
 * Which country a page shows.
 *
 * The site does not detect where visitors are. The country comes from the page
 * itself:
 *
 *   1. The URL: anything under /pk/, /in/ or /bd/ shows that country.
 *   2. A guide post or guide archive shows the country of its category
 *      (pakistan, india or bangladesh).
 *   3. Every other page (home, legal pages, search) shows the default country set
 *      in Customize > PowerBachat > Home page.
 *
 * No IP lookup, no country headers, no cookie. Pages render the same for everyone,
 * so they cache perfectly.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const POWERBACHAT_COUNTRIES = array( 'pk', 'in', 'bd' );

/**
 * Country implied by the request path (/pk/…, /in/…, /bd/…), or ''.
 *
 * @return string
 */
function powerbachat_path_country() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $base && 0 === strpos( $path, $base ) ) {
		$path = substr( $path, strlen( $base ) );
	}
	$first = strtolower( strtok( trim( $path, '/' ), '/' ) );
	return in_array( $first, POWERBACHAT_COUNTRIES, true ) ? $first : '';
}

/**
 * Country the current page belongs to: its URL (/pk/…), or for a guide post or
 * guide archive its country category. '' when neither.
 *
 * @return string
 */
function powerbachat_forced_country() {
	$country = powerbachat_path_country();
	if ( $country || ! did_action( 'wp' ) ) {
		return $country;
	}
	if ( is_category() && function_exists( 'powerbachat_category_country' ) ) {
		return powerbachat_category_country( get_queried_object() );
	}
	if ( ! is_singular( 'post' ) ) {
		return '';
	}
	$map = array(
		'pakistan'   => 'pk',
		'pk'         => 'pk',
		'india'      => 'in',
		'in'         => 'in',
		'bangladesh' => 'bd',
		'bd'         => 'bd',
	);
	foreach ( get_the_category( get_queried_object_id() ) as $term ) {
		if ( isset( $map[ $term->slug ] ) ) {
			return $map[ $term->slug ];
		}
	}
	return '';
}

/**
 * The country this page is shown for: the page's own country, else the default.
 *
 * @return string pk|in|bd
 */
function powerbachat_current_country() {
	$country = powerbachat_forced_country();
	return $country ? $country : powerbachat_mod( 'pb_default_country' );
}

/**
 * Connection IP, used only to rate-limit form submissions against spam. It is
 * hashed before it is stored and never used to work out a location.
 *
 * @return string
 */
function powerbachat_visitor_ip() {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $keys as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) {
			continue;
		}
		foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) as $ip ) {
			$ip = trim( $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '';
}

/**
 * <html data-country="…"> from the server, which decides which country's content shows.
 *
 * @param string $output Language attributes.
 * @return string
 */
function powerbachat_html_country_attr( $output ) {
	if ( is_admin() ) {
		return $output;
	}
	return $output . ' data-country="' . esc_attr( powerbachat_current_country() ) . '"';
}
add_filter( 'language_attributes', 'powerbachat_html_country_attr' );
