<?php
/**
 * Visitor country detection.
 *
 * The site shows one country at a time (Pakistan, India or Bangladesh), chosen
 * automatically. Order of precedence:
 *
 *   1. The URL: anything under /pk/, /in/ or /bd/ is always that country, so
 *      search engines and shared links see the right content whatever their IP.
 *   2. ?pb_country=pk|in|bd is a hidden override for testing (also remembered in a cookie).
 *   3. The pb_cc cookie set after the first detection.
 *   4. A country header from the host or CDN (Cloudflare, CloudFront, server GeoIP).
 *   5. An IP lookup (api.country.is by default), cached per IP for a week.
 *   6. The Customizer fallback country for everyone else.
 *
 * Full-page caches: the HTML carries all three countries' content and the choice is
 * made in the browser (see powerbachat_country_bootstrap() and the REST route below),
 * so a cached page still shows each visitor their own country.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const POWERBACHAT_COUNTRIES = array( 'pk', 'in', 'bd' );

/**
 * Normalise a two-letter ISO code to one of the site's countries, or ''.
 *
 * @param string $code ISO 3166-1 alpha-2 code.
 * @return string
 */
function powerbachat_map_country( $code ) {
	$code = strtolower( trim( (string) $code ) );
	$map  = apply_filters(
		'powerbachat_country_map',
		array(
			'pk' => 'pk',
			'in' => 'in',
			'bd' => 'bd',
		)
	);
	return isset( $map[ $code ] ) ? $map[ $code ] : '';
}

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
 * Country the current page belongs to: its URL (/pk/…), or for a single post its
 * country category (pakistan, india, bangladesh or pk, in, bd). '' when neither.
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
 * Hidden testing override: ?pb_country=in
 *
 * @return string
 */
function powerbachat_query_country() {
	if ( empty( $_GET['pb_country'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '';
	}
	$code = sanitize_key( wp_unslash( $_GET['pb_country'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return in_array( $code, POWERBACHAT_COUNTRIES, true ) ? $code : '';
}

/**
 * Best-guess public IP of the visitor.
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
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return $ip;
			}
		}
	}
	return '';
}

/**
 * Raw ISO code from host/CDN headers, or ''.
 *
 * @return string
 */
function powerbachat_header_country() {
	$keys = array( 'HTTP_CF_IPCOUNTRY', 'HTTP_CLOUDFRONT_VIEWER_COUNTRY', 'HTTP_X_VERCEL_IP_COUNTRY', 'HTTP_X_COUNTRY_CODE', 'HTTP_X_GEO_COUNTRY', 'GEOIP_COUNTRY_CODE', 'HTTP_GEOIP_COUNTRY_CODE' );
	foreach ( $keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
			if ( preg_match( '/^[A-Z]{2}$/', $code ) && 'XX' !== $code ) {
				return $code;
			}
		}
	}
	return '';
}

/**
 * Raw ISO code from an online lookup, cached per IP. '' when unavailable.
 *
 * @param string $ip Visitor IP.
 * @return string
 */
function powerbachat_lookup_country( $ip ) {
	if ( ! $ip || ! powerbachat_mod( 'pb_geo_lookup' ) ) {
		return '';
	}
	$key    = 'pb_geo_' . md5( $ip );
	$cached = get_transient( $key );
	if ( false !== $cached ) {
		return $cached;
	}

	/**
	 * Lookup URL. Must return JSON with a "country" (or "country_code") field.
	 *
	 * @param string $url Lookup URL.
	 * @param string $ip  Visitor IP.
	 */
	$url  = apply_filters( 'powerbachat_geo_lookup_url', 'https://api.country.is/' . rawurlencode( $ip ), $ip );
	$res  = wp_remote_get( $url, array( 'timeout' => 2 ) );
	$code = '';
	if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		$raw  = isset( $body['country'] ) ? $body['country'] : ( isset( $body['country_code'] ) ? $body['country_code'] : '' );
		if ( is_string( $raw ) && preg_match( '/^[A-Za-z]{2}$/', $raw ) ) {
			$code = strtoupper( $raw );
		}
	}
	// Cache misses briefly so a lookup outage does not slow every page.
	set_transient( $key, $code, $code ? WEEK_IN_SECONDS : 10 * MINUTE_IN_SECONDS );
	return $code;
}

/**
 * Country of the visitor from the network (header or lookup), mapped; '' if outside PK/IN/BD.
 *
 * @return array{country:string,source:string,iso:string}
 */
function powerbachat_network_country() {
	$iso = powerbachat_header_country();
	$src = 'header';
	if ( ! $iso ) {
		$iso = powerbachat_lookup_country( powerbachat_visitor_ip() );
		$src = 'lookup';
	}
	$country = powerbachat_map_country( $iso );
	return array(
		'country' => $country ? $country : powerbachat_mod( 'pb_default_country' ),
		'source'  => $country ? $src : 'fallback',
		'iso'     => $iso,
	);
}

/**
 * The country this request should be rendered for.
 *
 * @return string pk|in|bd
 */
function powerbachat_current_country() {
	static $country = null;
	if ( null !== $country ) {
		return $country;
	}
	$country = powerbachat_forced_country();
	if ( ! $country ) {
		$country = powerbachat_query_country();
	}
	if ( ! $country && ! empty( $_COOKIE['pb_cc'] ) ) {
		$cookie  = sanitize_key( wp_unslash( $_COOKIE['pb_cc'] ) );
		$country = in_array( $cookie, POWERBACHAT_COUNTRIES, true ) ? $cookie : '';
	}
	if ( ! $country ) {
		$country = powerbachat_network_country()['country'];
	}
	return $country;
}

/**
 * <html data-country="…"> straight from the server, so it is right even without JavaScript.
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

/**
 * REST: GET /wp-json/powerbachat/v1/country is used by the browser when the page came from a cache.
 */
function powerbachat_register_geo_route() {
	register_rest_route(
		'powerbachat/v1',
		'/country',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function () {
				$result = powerbachat_network_country();
				$res    = rest_ensure_response(
					array(
						'country' => $result['country'],
						'source'  => $result['source'],
					)
				);
				$res->header( 'Cache-Control', 'no-store, private' );
				return $res;
			},
		)
	);
}
add_action( 'rest_api_init', 'powerbachat_register_geo_route' );
