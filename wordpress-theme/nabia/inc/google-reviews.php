<?php
/**
 * Google reviews: fetched from the official Google Places API (New), cached, and merged
 * with the Testimonials post type.
 *
 * Setup: Customize → Nabia Theme → Google Reviews → API key + Place ID.
 * Google returns up to 5 reviews per request; every review ever returned is kept, so the
 * collection grows over time as Google rotates which reviews it shows.
 *
 * Shortcode anywhere: [nabia_google_reviews]
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const NABIA_REVIEWS_OPTION = 'nabia_google_reviews_store';

/**
 * Is the Google integration configured?
 *
 * @return bool
 */
function nabia_google_configured() {
	return nabia_mod( 'google_api_key' ) && nabia_mod( 'google_place_id' );
}

/**
 * Get Google place data (rating, total, reviews), refreshing from the API at most every 12 hours.
 *
 * @param bool $force Skip the cache.
 * @return array|null { name, rating, total, maps_url, write_url, reviews[], error, updated }
 */
function nabia_google_reviews_data( $force = false ) {
	if ( ! nabia_google_configured() ) {
		return null;
	}

	$place_id = trim( nabia_mod( 'google_place_id' ) );
	$store    = get_option( NABIA_REVIEWS_OPTION, array() );
	$store    = is_array( $store ) ? $store : array();
	$fresh    = get_transient( 'nabia_google_reviews_fresh' );

	if ( ! $force && $fresh && isset( $store['place_id'] ) && $store['place_id'] === $place_id ) {
		return $store;
	}

	$response = wp_remote_get(
		'https://places.googleapis.com/v1/places/' . rawurlencode( $place_id ) . '?languageCode=' . rawurlencode( substr( get_locale(), 0, 2 ) ),
		array(
			'timeout' => 8,
			'headers' => array(
				'X-Goog-Api-Key'   => trim( nabia_mod( 'google_api_key' ) ),
				'X-Goog-FieldMask' => 'displayName,rating,userRatingCount,reviews,googleMapsUri',
			),
		)
	);

	$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
	$body = is_wp_error( $response ) ? array() : json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || ! is_array( $body ) ) {
		$message = is_wp_error( $response ) ? $response->get_error_message() : ( isset( $body['error']['message'] ) ? $body['error']['message'] : 'HTTP ' . $code );
		// Keep showing the last good reviews; retry in an hour.
		$store['error'] = sanitize_text_field( $message );
		update_option( NABIA_REVIEWS_OPTION, $store, false );
		set_transient( 'nabia_google_reviews_fresh', 1, HOUR_IN_SECONDS );
		return ( isset( $store['place_id'] ) && $store['place_id'] === $place_id ) ? $store : array( 'error' => $store['error'] );
	}

	// Start a new collection if the Place ID changed.
	$reviews = ( isset( $store['place_id'], $store['reviews'] ) && $store['place_id'] === $place_id ) ? $store['reviews'] : array();

	foreach ( isset( $body['reviews'] ) ? (array) $body['reviews'] : array() as $review ) {
		$key  = isset( $review['name'] ) ? md5( $review['name'] ) : md5( wp_json_encode( $review ) );
		$text = '';
		if ( isset( $review['originalText']['text'] ) ) {
			$text = $review['originalText']['text'];
		} elseif ( isset( $review['text']['text'] ) ) {
			$text = $review['text']['text'];
		}
		$reviews[ $key ] = array(
			'author'  => isset( $review['authorAttribution']['displayName'] ) ? sanitize_text_field( $review['authorAttribution']['displayName'] ) : '',
			'author_url' => isset( $review['authorAttribution']['uri'] ) ? esc_url_raw( $review['authorAttribution']['uri'] ) : '',
			'photo'   => isset( $review['authorAttribution']['photoUri'] ) ? esc_url_raw( $review['authorAttribution']['photoUri'] ) : '',
			'rating'  => isset( $review['rating'] ) ? (int) $review['rating'] : 5,
			'text'    => sanitize_textarea_field( $text ),
			'time'    => isset( $review['publishTime'] ) ? strtotime( $review['publishTime'] ) : time(),
			'url'     => isset( $review['googleMapsUri'] ) ? esc_url_raw( $review['googleMapsUri'] ) : '',
		);
	}

	// Newest first, keep at most 60.
	uasort(
		$reviews,
		function ( $a, $b ) {
			return $b['time'] - $a['time'];
		}
	);
	$reviews = array_slice( $reviews, 0, 60, true );

	$store = array(
		'place_id'  => $place_id,
		'name'      => isset( $body['displayName']['text'] ) ? sanitize_text_field( $body['displayName']['text'] ) : '',
		'rating'    => isset( $body['rating'] ) ? round( (float) $body['rating'], 1 ) : 0,
		'total'     => isset( $body['userRatingCount'] ) ? (int) $body['userRatingCount'] : count( $reviews ),
		'maps_url'  => isset( $body['googleMapsUri'] ) ? esc_url_raw( $body['googleMapsUri'] ) : '',
		'write_url' => 'https://search.google.com/local/writereview?placeid=' . rawurlencode( $place_id ),
		'reviews'   => $reviews,
		'error'     => '',
		'updated'   => time(),
	);

	update_option( NABIA_REVIEWS_OPTION, $store, false );
	set_transient( 'nabia_google_reviews_fresh', 1, 12 * HOUR_IN_SECONDS );

	return $store;
}

/**
 * Refresh right after the key or Place ID is saved in the Customizer.
 */
function nabia_google_reviews_refresh() {
	delete_transient( 'nabia_google_reviews_fresh' );
	if ( nabia_google_configured() ) {
		nabia_google_reviews_data( true );
	}
}
add_action( 'customize_save_after', 'nabia_google_reviews_refresh' );

/**
 * Human "3 months ago" for a timestamp.
 *
 * @param int $time Unix time.
 * @return string
 */
function nabia_time_ago( $time ) {
	/* translators: %s: human time difference, e.g. "3 months" */
	return sprintf( __( '%s ago', 'nabia' ), human_time_diff( (int) $time, time() ) );
}

/**
 * Colourful Google "G" logo.
 *
 * @return string
 */
function nabia_google_logo() {
	return '<svg class="google-g" viewBox="0 0 48 48" width="22" height="22" aria-hidden="true" focusable="false"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>';
}

/**
 * Everything the reviews section shows: Google summary + Google reviews + Testimonials posts.
 *
 * @return array { google: array|null, items: array[] }
 */
function nabia_reviews_items() {
	$google = nabia_google_reviews_data();
	$min    = max( 1, min( 5, (int) nabia_mod( 'google_min_rating' ) ) );
	$items  = array();

	if ( $google && ! empty( $google['reviews'] ) ) {
		foreach ( $google['reviews'] as $review ) {
			if ( $review['rating'] < $min || '' === trim( $review['text'] ) ) {
				continue;
			}
			$items[] = array(
				'name'   => $review['author'],
				'role'   => nabia_time_ago( $review['time'] ),
				'quote'  => $review['text'],
				'rating' => $review['rating'],
				'avatar' => $review['photo'],
				'url'    => $review['url'] ? $review['url'] : $review['author_url'],
				'google' => true,
			);
		}
	}

	$posts = get_posts(
		array(
			'post_type'      => 'testimonial',
			'posts_per_page' => 24,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		)
	);
	foreach ( $posts as $post ) {
		$items[] = array(
			'name'   => get_the_title( $post ),
			'role'   => (string) get_post_meta( $post->ID, '_nabia_author_role', true ),
			'quote'  => wp_strip_all_tags( $post->post_content ),
			'rating' => (int) get_post_meta( $post->ID, '_nabia_rating', true ),
			'avatar' => get_the_post_thumbnail_url( $post, 'thumbnail' ),
			'url'    => '',
			'google' => false,
		);
	}

	if ( ! $items ) {
		foreach ( nabia_demo_testimonials() as $demo ) {
			$items[] = $demo + array(
				'rating' => 5,
				'avatar' => '',
				'url'    => '',
				'google' => false,
			);
		}
	}

	return array(
		'google' => $google,
		'items'  => $items,
	);
}

/**
 * Shortcode: [nabia_google_reviews]
 *
 * @return string
 */
function nabia_reviews_shortcode() {
	ob_start();
	echo '<div class="nabia-reviews-embed">';
	get_template_part( 'template-parts/home', 'testimonials', array( 'embedded' => true ) );
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'nabia_google_reviews', 'nabia_reviews_shortcode' );
