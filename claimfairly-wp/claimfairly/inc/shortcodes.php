<?php
/**
 * Content shortcodes. Calculators live in the ClaimFairly Tools plugin ([cf_tool]).
 *
 *   [cf_faq] [cf_q q="Question?"]Answer[/cf_q] [/cf_faq]   FAQ accordion + FAQPage schema.
 *   [cf_note type="info|warning|tip"]Text[/cf_note]        Highlighted note.
 *   [cf_disclaimer]                            Standard disclaimer box.
 *   [cf_sources] [cf_author_box] [cf_related]  Place a trust block manually.
 *   [cf_last_reviewed]                         Inline "Last reviewed" date.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ items collected during rendering, printed as FAQPage JSON-LD in the footer.
 *
 * @param array|null $add Item to add.
 * @return array
 */
function claimfairly_faq_store( $add = null ) {
	static $items = array();
	if ( null !== $add ) {
		$items[] = $add;
	}
	return $items;
}

/**
 * [cf_faq] wrapper.
 *
 * @param array  $atts    Attributes.
 * @param string $content Inner content.
 * @return string
 */
function claimfairly_faq_shortcode( $atts, $content = '' ) {
	$atts  = shortcode_atts(
		array( 'title' => __( 'Frequently asked questions', 'claimfairly' ) ),
		$atts,
		'cf_faq'
	);
	$html  = '<section class="cf-faq faq">';
	if ( '' !== $atts['title'] ) {
		$html .= '<h2>' . esc_html( $atts['title'] ) . '</h2>';
	}
	// wpautop runs before shortcodes, leaving <br>/<p> between items; drop them.
	$items = do_shortcode( shortcode_unautop( trim( (string) $content ) ) );
	$items = preg_replace( '#^(?:<br\s*/?>|</?p>|\s)+#', '', $items );
	$items = preg_replace( '#(?:<br\s*/?>|</?p>|\s)+(?=<details|$)#', '', $items );
	$html .= $items;
	$html .= '</section>';
	return $html;
}
add_shortcode( 'cf_faq', 'claimfairly_faq_shortcode' );

/**
 * [cf_q q="..."]Answer[/cf_q]
 *
 * @param array  $atts    Attributes.
 * @param string $content Answer.
 * @return string
 */
function claimfairly_faq_item_shortcode( $atts, $content = '' ) {
	$atts     = shortcode_atts( array( 'q' => '' ), $atts, 'cf_q' );
	$question = trim( $atts['q'] );
	if ( '' === $question ) {
		return '';
	}
	$answer = wpautop( do_shortcode( trim( (string) $content ) ) );
	$answer = wp_kses_post( $answer );

	claimfairly_faq_store(
		array(
			'q' => wp_strip_all_tags( $question ),
			'a' => trim( wp_strip_all_tags( $answer ) ),
		)
	);

	return '<details class="faq__item"><summary><h3 class="faq__q">' . esc_html( $question ) . '</h3><span class="faq__icon" aria-hidden="true"></span></summary><div class="faq__a">' . $answer . '</div></details>';
}
add_shortcode( 'cf_q', 'claimfairly_faq_item_shortcode' );

/**
 * [cf_note type="info"]...[/cf_note]
 *
 * @param array  $atts    Attributes.
 * @param string $content Content.
 * @return string
 */
function claimfairly_note_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'type' => 'info' ), $atts, 'cf_note' );
	$type = in_array( $atts['type'], array( 'info', 'warning', 'tip' ), true ) ? $atts['type'] : 'info';
	return '<div class="cf-note cf-note--' . esc_attr( $type ) . '">' . wp_kses_post( wpautop( do_shortcode( trim( (string) $content ) ) ) ) . '</div>';
}
add_shortcode( 'cf_note', 'claimfairly_note_shortcode' );

add_shortcode(
	'cf_disclaimer',
	static function () {
		return claimfairly_get_disclaimer();
	}
);

add_shortcode(
	'cf_sources',
	static function () {
		return claimfairly_get_sources_box();
	}
);

add_shortcode(
	'cf_author_box',
	static function () {
		return claimfairly_get_author_box();
	}
);

add_shortcode(
	'cf_related',
	static function () {
		return claimfairly_get_related_box();
	}
);

add_shortcode(
	'cf_last_reviewed',
	static function () {
		$reviewed = claimfairly_last_reviewed_raw();
		if ( ! $reviewed ) {
			return '';
		}
		return '<time datetime="' . esc_attr( $reviewed ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $reviewed ) ) . '</time>';
	}
);

/**
 * [cf_editor_note]Reminder for yourself[/cf_editor_note]: shown only to logged-in editors.
 *
 * @param array  $atts    Attributes.
 * @param string $content Content.
 * @return string
 */
function claimfairly_editor_note_shortcode( $atts, $content = '' ) {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return '';
	}
	return '<div class="cf-note cf-note--warning"><p><strong>' . esc_html__( 'Note for editors:', 'claimfairly' ) . '</strong> ' . esc_html( trim( (string) $content ) ) . '</p></div>';
}
add_shortcode( 'cf_editor_note', 'claimfairly_editor_note_shortcode' );

/**
 * [cf_founder] The site owner's name (Customizer setting, default "James").
 *
 * @return string
 */
function claimfairly_founder_shortcode() {
	return esc_html( claimfairly_founder_name() );
}
add_shortcode( 'cf_founder', 'claimfairly_founder_shortcode' );

/**
 * URL of the XML sitemap: the SEO plugin's index when one is active,
 * otherwise the sitemap built into WordPress.
 *
 * @return string
 */
function claimfairly_xml_sitemap_url() {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' ) ) {
		return home_url( '/sitemap_index.xml' );
	}
	return home_url( '/wp-sitemap.xml' );
}

/**
 * [cf_html_sitemap] Every published page, grouped for people (not robots).
 *
 * @return string
 */
function claimfairly_html_sitemap_shortcode() {
	$pages = get_pages(
		array(
			'post_status' => 'publish',
			'sort_column' => 'menu_order,post_title',
		)
	);
	$groups = array(
		'tool'     => array( __( 'Free calculators', 'claimfairly' ), 'calculator', array() ),
		'guide'    => array( __( 'Settlement guides', 'claimfairly' ), 'pie', array() ),
		'state'    => array( __( 'State rules', 'claimfairly' ), 'pin', array() ),
		'injury'   => array( __( 'Injury guides', 'claimfairly' ), 'bandage', array() ),
		'insurer'  => array( __( 'Insurer guides', 'claimfairly' ), 'shield', array() ),
		'standard' => array( __( 'About ClaimFairly', 'claimfairly' ), 'book', array() ),
	);
	$front = (int) get_option( 'page_on_front' );
	foreach ( $pages as $page ) {
		if ( (int) $page->ID === $front || 'sitemap' === $page->post_name ) {
			continue;
		}
		$type = claimfairly_get_page_type( $page->ID );
		$type = isset( $groups[ $type ] ) ? $type : 'standard';
		$groups[ $type ][2][] = '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
	}

	$html = '<div class="html-sitemap">';

	// Guides (posts), grouped by category.
	$cats = get_categories( array( 'hide_empty' => true ) );
	if ( $cats ) {
		$html .= '<section class="html-sitemap__group html-sitemap__group--wide"><h2>' . claimfairly_icon( 'book', 20 ) . esc_html__( 'Guides', 'claimfairly' ) . '</h2><div class="html-sitemap__cols">';
		foreach ( $cats as $cat ) {
			$posts = get_posts(
				array(
					'category'       => $cat->term_id,
					'posts_per_page' => -1,
				)
			);
			$html .= '<div><h3><a href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a></h3><ul>';
			foreach ( $posts as $post ) {
				$html .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>';
			}
			$html .= '</ul></div>';
		}
		$html .= '</div></section>';
	}

	foreach ( $groups as $group ) {
		if ( ! $group[2] ) {
			continue;
		}
		$html .= '<section class="html-sitemap__group"><h2>' . claimfairly_icon( $group[1], 20 ) . esc_html( $group[0] ) . '</h2><ul>' . implode( '', $group[2] ) . '</ul></section>';
	}
	$html .= '</div>';
	$html .= '<p class="html-sitemap__xml">' . esc_html__( 'Looking for the file search engines use?', 'claimfairly' ) . ' <a href="' . esc_url( claimfairly_xml_sitemap_url() ) . '">' . esc_html__( 'Open the XML sitemap', 'claimfairly' ) . '</a>.</p>';
	return $html;
}
add_shortcode( 'cf_html_sitemap', 'claimfairly_html_sitemap_shortcode' );
