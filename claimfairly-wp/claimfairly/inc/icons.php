<?php
/**
 * Inline SVG icons and the tool color map.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual identity of each tool: color token, tint and icon.
 *
 * @return array<string,array{color:string,tint:string,icon:string,label:string}>
 */
function claimfairly_tool_styles() {
	return array(
		'settlement-estimator' => array( 'color' => 'blue', 'icon' => 'calculator', 'label' => __( 'Most used', 'claimfairly' ), 'desc' => __( 'What your injury claim may be worth', 'claimfairly' ) ),
		'diminished-value'     => array( 'color' => 'violet', 'icon' => 'car-down', 'label' => __( '17c formula', 'claimfairly' ), 'desc' => __( 'Value your car lost after the repair', 'claimfairly' ) ),
		'pain-and-suffering'   => array( 'color' => 'rose', 'icon' => 'pulse', 'label' => __( '2 methods', 'claimfairly' ), 'desc' => __( 'Multiplier and per diem, side by side', 'claimfairly' ) ),
		'settlement-take-home' => array( 'color' => 'orange', 'icon' => 'pie', 'label' => __( 'Fees and liens', 'claimfairly' ), 'desc' => __( 'What you keep after fees and liens', 'claimfairly' ) ),
		'demand-letter'        => array( 'color' => 'navy', 'icon' => 'letter', 'label' => __( 'PDF download', 'claimfairly' ), 'desc' => __( 'A ready-to-send letter as a PDF', 'claimfairly' ) ),
	);
}

/**
 * Color and icon for a page, based on its tool key or page type.
 *
 * @param int|null $post_id Post ID.
 * @return array{color:string,icon:string}
 */
function claimfairly_page_style( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$tool    = (string) get_post_meta( $post_id, '_cf_tool_key', true );
	$styles  = claimfairly_tool_styles();
	if ( $tool && isset( $styles[ $tool ] ) ) {
		return $styles[ $tool ];
	}
	$by_type = array(
		'guide'   => array( 'color' => 'green', 'icon' => 'book' ),
		'state'   => array( 'color' => 'blue', 'icon' => 'pin' ),
		'injury'  => array( 'color' => 'rose', 'icon' => 'bandage' ),
		'insurer' => array( 'color' => 'navy', 'icon' => 'shield' ),
		'tool'    => array( 'color' => 'green', 'icon' => 'calculator' ),
	);
	$type = function_exists( 'claimfairly_get_page_type' ) ? claimfairly_get_page_type( $post_id ) : 'standard';
	return isset( $by_type[ $type ] ) ? $by_type[ $type ] : array( 'color' => 'green', 'icon' => 'spark' );
}

/**
 * Inline SVG icon (24px grid, stroke based, inherits currentColor).
 *
 * @param string $name Icon name.
 * @param int    $size Pixel size.
 * @return string
 */
function claimfairly_icon( $name, $size = 24 ) {
	$paths = array(
		'calculator' => '<rect x="4.5" y="2.5" width="15" height="19" rx="3"/><rect x="7.5" y="5.5" width="9" height="4" rx="1"/><path d="M8 13h.01M12 13h.01M16 13h.01M8 17h.01M12 17h.01M16 17h.01"/>',
		'car-down'   => '<path d="M5 13.5 6.6 9a2 2 0 0 1 1.9-1.4h7a2 2 0 0 1 1.9 1.4l1.6 4.5"/><rect x="3.5" y="13.5" width="17" height="4.5" rx="1.6"/><path d="M6.5 18v1.5M17.5 18v1.5M7 15.8h.01M17 15.8h.01"/><path d="M17 2.5v4m0 0-1.8-1.8M17 6.5l1.8-1.8"/>',
		'pulse'      => '<path d="M20.4 12.7A8.6 8.6 0 0 1 12 20.5a8.4 8.4 0 0 1-3-.6"/><path d="M3.3 13.9A5 5 0 0 1 3 12c0-3.3 2.4-5.5 5-5.5 1.7 0 3 .8 4 2 1-1.2 2.3-2 4-2 2.6 0 5 2.2 5 5.5"/><path d="M2.5 13.5h4l2-3.5 3 6 2-3.5h8"/>',
		'pie'        => '<path d="M12 3a9 9 0 1 0 9 9h-9V3Z"/><path d="M15 2.6A9 9 0 0 1 21.4 9H15V2.6Z"/>',
		'letter'     => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.8 6.5 8.2 6 8.2-6"/>',
		'book'       => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z"/><path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20v3H6.5A2.5 2.5 0 0 1 4 20.5ZM8.5 7.5h7M8.5 11h5"/>',
		'pin'        => '<path d="M12 21s-6.5-5.6-6.5-11A6.5 6.5 0 0 1 18.5 10c0 5.4-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.4"/>',
		'bandage'    => '<rect x="1.8" y="8" width="20.4" height="8" rx="4" transform="rotate(-45 12 12)"/><path d="M10 10h.01M14 10h.01M10 14h.01M14 14h.01"/>',
		'shield'     => '<path d="M12 2.8 4.5 5.6v5.8c0 4.7 3.2 8.6 7.5 9.8 4.3-1.2 7.5-5.1 7.5-9.8V5.6L12 2.8Z"/><path d="m8.8 12 2.2 2.2 4.4-4.5"/>',
		'spark'      => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8"/>',
		'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'check'      => '<path d="m5 12.5 4.2 4.2L19 7"/>',
		'lock'       => '<rect x="4.5" y="10.5" width="15" height="10" rx="2.5"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/>',
		'formula'    => '<path d="M4 7h7M7.5 3.5v7M4 17.5h7M14 5l6 6M20 5l-6 6M14 15h6M14 19h6"/>',
		'doc'        => '<path d="M6 2.5h8l4.5 4.5v14.5H6z"/><path d="M14 2.5V7h4.5M9 12h6M9 16h6"/>',
		'range'      => '<path d="M3 17h18M6 17V9M12 17V5M18 17v-6"/>',
		'search'     => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.4-4.4"/>',
		'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.8 6.5 8.2 6 8.2-6"/>',
		'chevron'    => '<path d="m6 9 6 6 6-6"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'spark';
	}
	return '<svg class="cf-icon" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Logo mark: a rounded square with an even scale inside (fair split).
 *
 * @return string
 */
function claimfairly_logo_mark( $variant = 'dark' ) {
	// Dark variant: navy square (light backgrounds). Light variant: amber square (dark backgrounds).
	$bg    = 'light' === $variant ? '#ffc53d' : '#14213d';
	$lines = 'light' === $variant ? '#14213d' : '#ffffff';
	$pans  = 'light' === $variant ? '#ffffff' : '#ffc53d';
	return '<svg class="brand__mark" width="34" height="34" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><rect width="100" height="100" rx="29" fill="' . $bg . '"/><path d="M50 25v50M32 75h36M28 37h44" fill="none" stroke="' . $lines . '" stroke-width="6.5" stroke-linecap="round"/><path d="M28 37 20 56a8.8 8.8 0 0 0 16 0L28 37ZM72 37l-8 19a8.8 8.8 0 0 0 16 0L72 37Z" fill="' . $pans . '"/></svg>';
}

/**
 * URL of the page that hosts a tool (by its tool key), or the tool plugin's default path.
 *
 * @param string $tool Tool key.
 * @return string
 */
function claimfairly_tool_page_url( $tool ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_cf_tool_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $tool, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
		)
	);
	if ( $pages ) {
		return get_permalink( $pages[0] );
	}
	return function_exists( 'cft_tool_url' ) ? cft_tool_url( $tool ) : home_url( '/' );
}
