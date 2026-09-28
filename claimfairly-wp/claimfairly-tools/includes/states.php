<?php
/**
 * State rules data (data/states.json) and helpers.
 *
 * @package ClaimFairlyTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All states keyed by two-letter code.
 *
 * @return array<string,array>
 */
function cft_states() {
	static $states = null;
	if ( null !== $states ) {
		return $states;
	}
	$states = array();
	$raw    = file_get_contents( CFT_DIR . 'data/states.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$data   = json_decode( (string) $raw, true );
	if ( isset( $data['states'] ) && is_array( $data['states'] ) ) {
		foreach ( $data['states'] as $row ) {
			$states[ $row['code'] ] = $row;
		}
	}
	return apply_filters( 'cft_states', $states );
}

/**
 * One state by code (case-insensitive) or slug.
 *
 * @param string $key Code like "CA" or slug like "california".
 * @return array|null
 */
function cft_state( $key ) {
	$states = cft_states();
	$upper  = strtoupper( (string) $key );
	if ( isset( $states[ $upper ] ) ) {
		return $states[ $upper ];
	}
	foreach ( $states as $state ) {
		if ( $state['slug'] === $key ) {
			return $state;
		}
	}
	return null;
}

/**
 * Plain-English label for a negligence rule.
 *
 * @param string $rule Rule key.
 * @return string
 */
function cft_rule_label( $rule ) {
	$labels = array(
		'pure'         => __( 'Pure comparative negligence', 'claimfairly-tools' ),
		'mod50'        => __( 'Modified comparative negligence (50% bar)', 'claimfairly-tools' ),
		'mod51'        => __( 'Modified comparative negligence (51% bar)', 'claimfairly-tools' ),
		'mod51_noneco' => __( 'Modified comparative negligence (51% bar for pain and suffering)', 'claimfairly-tools' ),
		'contributory' => __( 'Contributory negligence', 'claimfairly-tools' ),
		'slight_gross' => __( 'Slight / gross negligence comparison', 'claimfairly-tools' ),
	);
	return isset( $labels[ $rule ] ) ? $labels[ $rule ] : $rule;
}

/**
 * What the rule means for someone who was partly at fault.
 *
 * @param string $rule Rule key.
 * @return string
 */
function cft_rule_explainer( $rule ) {
	$text = array(
		'pure'         => __( 'You can recover something even if you were mostly at fault. Your amount is reduced by your share of the blame. At 30% fault, you keep 70%.', 'claimfairly-tools' ),
		'mod50'        => __( 'Your amount is reduced by your share of fault, but if you are 50% or more at fault you recover nothing.', 'claimfairly-tools' ),
		'mod51'        => __( 'Your amount is reduced by your share of fault, and you recover nothing if you are 51% or more at fault. At exactly 50% you can still recover half.', 'claimfairly-tools' ),
		'mod51_noneco' => __( 'Economic losses are reduced by your share of fault. Pain and suffering is barred completely if you are more than 50% at fault.', 'claimfairly-tools' ),
		'contributory' => __( 'This is the strictest rule in the country. If you are found even 1% at fault, you can be barred from recovering anything. A few narrow exceptions exist, so talk to a local attorney before giving up.', 'claimfairly-tools' ),
		'slight_gross' => __( 'You can recover only if your negligence was "slight" compared with the other driver\'s. There is no fixed percentage, so a judge or jury decides.', 'claimfairly-tools' ),
	);
	return isset( $text[ $rule ] ) ? $text[ $rule ] : '';
}

/**
 * Plain-English label for the fault system.
 *
 * @param string $system at-fault | no-fault | choice.
 * @return string
 */
function cft_fault_label( $system ) {
	$labels = array(
		'at-fault' => __( 'At-fault (tort) state', 'claimfairly-tools' ),
		'no-fault' => __( 'No-fault state', 'claimfairly-tools' ),
		'choice'   => __( 'Choice no-fault state', 'claimfairly-tools' ),
	);
	return isset( $labels[ $system ] ) ? $labels[ $system ] : $system;
}

/**
 * Minimum liability limits in words, e.g. 25/50/25.
 *
 * @param string $limits Limits string.
 * @return string
 */
function cft_limits_words( $limits ) {
	$parts = array_map( 'intval', explode( '/', (string) $limits ) );
	if ( 3 !== count( $parts ) ) {
		return $limits;
	}
	return sprintf(
		/* translators: 1: per person, 2: per accident, 3: property damage, all in thousands of dollars. */
		__( '$%1$s,000 per person and $%2$s,000 per accident for injuries, plus $%3$s,000 for property damage', 'claimfairly-tools' ),
		$parts[0],
		$parts[1],
		$parts[2]
	);
}

/**
 * Data passed to JavaScript (only what the tools need).
 *
 * @return array
 */
function cft_states_for_js() {
	$out = array();
	foreach ( cft_states() as $code => $state ) {
		$out[] = array(
			'code'  => $code,
			'name'  => $state['name'],
			'rule'  => $state['rule'],
			'fault' => $state['fault_system'],
			'note'  => $state['note'],
		);
	}
	return $out;
}

/**
 * Published state pages (children of /states/), keyed by slug. One query per request.
 *
 * @return array<string,WP_Post>
 */
function cft_published_state_pages() {
	static $pages = null;
	if ( null !== $pages ) {
		return $pages;
	}
	$pages = array();
	$hub   = get_page_by_path( 'states' );
	if ( ! $hub ) {
		return $pages;
	}
	foreach ( get_pages(
		array(
			'parent'      => $hub->ID,
			'post_status' => 'publish',
		)
	) as $page ) {
		$pages[ $page->post_name ] = $page;
	}
	return $pages;
}
