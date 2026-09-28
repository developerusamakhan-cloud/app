<?php
/**
 * Shortcodes.
 *
 *   [cf_tool name="diminished-value"]
 *   [cf_tool name="settlement-take-home" amount="50000"]
 *   [cf_tool name="settlement-estimator" state="CA" severity="moderate"]
 *   [cf_tool name="pain-and-suffering" severity="serious"]
 *   [cf_tool name="demand-letter" type="injury|property|dv"]
 *   [cf_state_facts state="CA"]      Facts box for a state page.
 *   [cf_state_table]                 All states: fault system, negligence rule, deadline.
 *
 * @package ClaimFairlyTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [cf_tool]
 *
 * @param array $atts Attributes.
 * @return string
 */
function cft_tool_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'name'     => '',
			'state'    => '',
			'amount'   => '',
			'severity' => '',
			'type'     => '',
		),
		$atts,
		'cf_tool'
	);
	$name = sanitize_key( $atts['name'] );
	$view = CFT_DIR . 'views/' . $name . '.php';

	if ( ! isset( cft_tools()[ $name ] ) || ! file_exists( $view ) ) {
		return current_user_can( 'edit_posts' )
			? '<p class="cf-admin-note">' . esc_html( sprintf( 'ClaimFairly Tools: unknown tool "%s". (Only editors see this.)', $name ) ) . '</p>'
			: '';
	}

	wp_enqueue_style( 'cft-tools' );
	wp_enqueue_script( 'cft-' . $name );

	$preset = array(
		'state'    => strtoupper( sanitize_key( $atts['state'] ) ),
		'amount'   => is_numeric( $atts['amount'] ) ? (float) $atts['amount'] : '',
		'severity' => sanitize_key( $atts['severity'] ),
		'type'     => sanitize_key( $atts['type'] ),
	);
	$uid = 'cft-' . $name . '-' . wp_unique_id();

	ob_start();
	echo '<section class="cf-tool" id="' . esc_attr( $uid ) . '" data-cf-tool="' . esc_attr( $name ) . '" data-cf-preset="' . esc_attr( wp_json_encode( $preset ) ) . '">';
	include $view;
	echo '<noscript><p class="cf-noscript">' . esc_html__( 'This calculator needs JavaScript. Everything runs in your browser and nothing is sent anywhere.', 'claimfairly-tools' ) . '</p></noscript>';
	echo '</section>';
	return ob_get_clean();
}
add_shortcode( 'cf_tool', 'cft_tool_shortcode' );

/**
 * Standard disclaimer inside results. Uses the theme's text when available.
 *
 * @return string
 */
function cft_disclaimer_html() {
	$text = function_exists( 'claimfairly_opt' )
		? claimfairly_opt( 'cf_disclaimer' )
		: __( 'Educational estimate, not legal advice. Results vary. Consult a licensed attorney in your state.', 'claimfairly-tools' );
	return '<p class="cf-result__disclaimer"><strong>' . esc_html__( 'Not legal advice.', 'claimfairly-tools' ) . '</strong> ' . esc_html( $text ) . '</p>';
}

/**
 * <option> list of states.
 *
 * @param string $selected Selected code.
 * @return string
 */
function cft_state_options( $selected = '' ) {
	$html = '<option value="">' . esc_html__( 'Choose your state', 'claimfairly-tools' ) . '</option>';
	foreach ( cft_states() as $code => $state ) {
		$html .= '<option value="' . esc_attr( $code ) . '"' . selected( $selected, $code, false ) . '>' . esc_html( $state['name'] ) . '</option>';
	}
	return $html;
}

/**
 * [cf_state_facts state="CA"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function cft_state_facts_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'state' => '' ), $atts, 'cf_state_facts' );
	$state = cft_state( $atts['state'] );
	if ( ! $state ) {
		return '';
	}
	wp_enqueue_style( 'cft-tools' );

	$rows = array(
		__( 'Insurance system', 'claimfairly-tools' )       => cft_fault_label( $state['fault_system'] ),
		__( 'Fault rule', 'claimfairly-tools' )             => cft_rule_label( $state['rule'] ),
		__( 'Deadline to file an injury lawsuit', 'claimfairly-tools' ) => sprintf(
			/* translators: %d: number of years. */
			_n( '%d year from the accident (usually)', '%d years from the accident (usually)', (int) $state['sol_injury_years'], 'claimfairly-tools' ),
			(int) $state['sol_injury_years']
		),
		__( 'Minimum liability insurance', 'claimfairly-tools' ) => $state['min_liability'] . ' (' . cft_limits_words( $state['min_liability'] ) . ')',
	);

	$html  = '<div class="cf-facts">';
	$html .= '<p class="cf-facts__title">' . esc_html( sprintf( /* translators: %s: state name. */ __( '%s at a glance', 'claimfairly-tools' ), $state['name'] ) ) . '</p>';
	$html .= '<dl>';
	foreach ( $rows as $label => $value ) {
		$html .= '<div class="cf-facts__row"><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
	}
	$html .= '</dl>';
	$html .= '<p class="cf-facts__rule">' . esc_html( cft_rule_explainer( $state['rule'] ) ) . '</p>';
	if ( ! empty( $state['note'] ) ) {
		$html .= '<p class="cf-facts__note">' . esc_html( $state['note'] ) . '</p>';
	}
	if ( ! empty( $state['sources'] ) ) {
		$html .= '<p class="cf-facts__sources">' . esc_html__( 'Sources:', 'claimfairly-tools' ) . ' ';
		$links = array();
		foreach ( $state['sources'] as $source ) {
			$links[] = '<a href="' . esc_url( $source['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $source['title'] ) . '</a>';
		}
		$html .= implode( ', ', $links ) . '</p>';
	}
	$html .= '<p class="cf-facts__reviewed">' . esc_html__( 'Last checked:', 'claimfairly-tools' ) . ' ' . esc_html( mysql2date( get_option( 'date_format' ), $state['reviewed'] ) ) . '</p>';
	if ( empty( $state['verified'] ) && current_user_can( 'edit_posts' ) ) {
		$html .= '<p class="cf-admin-note">' . esc_html__( 'Editors only: this state is not marked as verified yet. Check it against the official statute and insurance department, add sources in data/states.json and set "verified" to true.', 'claimfairly-tools' ) . '</p>';
	}
	$html .= '</div>';
	return $html;
}
add_shortcode( 'cf_state_facts', 'cft_state_facts_shortcode' );

/**
 * [cf_state_table] All states in one table, with links to state pages that exist.
 *
 * @return string
 */
function cft_state_table_shortcode() {
	wp_enqueue_style( 'cft-tools' );
	$html  = '<div class="cf-table-wrap"><table class="cf-state-table"><thead><tr>';
	$html .= '<th scope="col">' . esc_html__( 'State', 'claimfairly-tools' ) . '</th>';
	$html .= '<th scope="col">' . esc_html__( 'System', 'claimfairly-tools' ) . '</th>';
	$html .= '<th scope="col">' . esc_html__( 'Fault rule', 'claimfairly-tools' ) . '</th>';
	$html .= '<th scope="col">' . esc_html__( 'Injury deadline', 'claimfairly-tools' ) . '</th>';
	$html .= '</tr></thead><tbody>';
	$short = array(
		'pure'         => __( 'Pure comparative', 'claimfairly-tools' ),
		'mod50'        => __( '50% bar', 'claimfairly-tools' ),
		'mod51'        => __( '51% bar', 'claimfairly-tools' ),
		'mod51_noneco' => __( '51% bar (pain and suffering)', 'claimfairly-tools' ),
		'contributory' => __( 'Contributory', 'claimfairly-tools' ),
		'slight_gross' => __( 'Slight / gross', 'claimfairly-tools' ),
	);
	foreach ( cft_states() as $state ) {
		$page = get_page_by_path( 'states/' . $state['slug'] );
		$name = esc_html( $state['name'] );
		if ( $page && 'publish' === $page->post_status ) {
			$name = '<a href="' . esc_url( get_permalink( $page ) ) . '">' . $name . '</a>';
		}
		$html .= '<tr><th scope="row">' . $name . '</th>';
		$html .= '<td>' . esc_html( ucfirst( str_replace( '-', ' ', $state['fault_system'] ) ) ) . '</td>';
		$html .= '<td>' . esc_html( $short[ $state['rule'] ] ?? $state['rule'] ) . '</td>';
		/* translators: %d: number of years. */
		$html .= '<td>' . esc_html( sprintf( _n( '%d year', '%d years', (int) $state['sol_injury_years'], 'claimfairly-tools' ), (int) $state['sol_injury_years'] ) ) . '</td></tr>';
	}
	$html .= '</tbody></table></div>';
	return $html;
}
add_shortcode( 'cf_state_table', 'cft_state_table_shortcode' );
