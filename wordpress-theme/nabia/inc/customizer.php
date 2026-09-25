<?php
/**
 * Customizer options (Appearance → Customize → Nabia Theme).
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a checkbox.
 *
 * @param mixed $value Value.
 * @return bool
 */
function nabia_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function nabia_customize_register( $wp_customize ) {
	$defaults = nabia_defaults();

	$wp_customize->add_panel(
		'nabia_panel',
		array(
			'title'    => __( 'Nabia Theme', 'nabia' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'nabia_general' => __( 'General', 'nabia' ),
		'nabia_hero'    => __( 'Hero', 'nabia' ),
		'nabia_stats'   => __( 'Marquee & Stats', 'nabia' ),
		'nabia_about'   => __( 'About & Section Titles', 'nabia' ),
		'nabia_portfolio' => __( 'Portfolio', 'nabia' ),
		'nabia_videos'  => __( 'Video Reviews', 'nabia' ),
		'nabia_google'  => __( 'Google Reviews', 'nabia' ),
		'nabia_pricing' => __( 'Pricing', 'nabia' ),
		'nabia_audit'   => __( 'Free Audit', 'nabia' ),
		'nabia_contact' => __( 'Contact', 'nabia' ),
		'nabia_social'  => __( 'Links: Linktree, Fiverr & Upwork', 'nabia' ),
	);
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section(
			$id,
			array(
				'title' => $title,
				'panel' => 'nabia_panel',
			)
		);
	}

	/*
	 * Each field: key => array( section, type, label ).
	 * Types: text, textarea, url, email, checkbox, color, image.
	 */
	$fields = array(
		'brand_name'         => array( 'nabia_general', 'text', __( 'Brand name (used when no logo is set)', 'nabia' ) ),
		'use_custom_logo'    => array( 'nabia_general', 'checkbox', __( 'Use my uploaded logo image (Site Identity) instead of the animated name logo', 'nabia' ) ),
		'color_scheme'       => array( 'nabia_general', 'select', __( 'Colour scheme', 'nabia' ) ),
		'accent_color'       => array( 'nabia_general', 'color', __( 'Custom accent colour (optional, overrides the scheme)', 'nabia' ) ),
		'enable_preloader'   => array( 'nabia_general', 'checkbox', __( 'Show intro preloader', 'nabia' ) ),
		'enable_cursor'      => array( 'nabia_general', 'checkbox', __( 'Custom animated cursor (desktop)', 'nabia' ) ),
		'enable_smooth'      => array( 'nabia_general', 'checkbox', __( 'Smooth scrolling', 'nabia' ) ),
		'footer_text'        => array( 'nabia_general', 'textarea', __( 'Footer text', 'nabia' ) ),
		'footer_marquee'     => array( 'nabia_general', 'text', __( 'Footer scrolling slogan', 'nabia' ) ),
		'footer_cta_title'   => array( 'nabia_general', 'text', __( 'Footer call-to-action title', 'nabia' ) ),
		'footer_cta_text'    => array( 'nabia_general', 'textarea', __( 'Footer call-to-action text', 'nabia' ) ),

		'hero_badge'         => array( 'nabia_hero', 'text', __( 'Badge text', 'nabia' ) ),
		'hero_line_1'        => array( 'nabia_hero', 'text', __( 'Headline line 1', 'nabia' ) ),
		'hero_line_2'        => array( 'nabia_hero', 'text', __( 'Headline line 2', 'nabia' ) ),
		'hero_rotating'      => array( 'nabia_hero', 'text', __( 'Rotating endings (comma separated)', 'nabia' ) ),
		'hero_text'          => array( 'nabia_hero', 'textarea', __( 'Intro text', 'nabia' ) ),
		'hero_cta_label'     => array( 'nabia_hero', 'text', __( 'Primary button label', 'nabia' ) ),
		'hero_cta_url'       => array( 'nabia_hero', 'url', __( 'Primary button link', 'nabia' ) ),
		'hero_cta2_label'    => array( 'nabia_hero', 'text', __( 'Secondary button label', 'nabia' ) ),
		'hero_cta2_url'      => array( 'nabia_hero', 'url', __( 'Secondary button link', 'nabia' ) ),
		'hero_image'         => array( 'nabia_hero', 'image', __( 'Portrait / hero image', 'nabia' ) ),

		'marquee_items'      => array( 'nabia_stats', 'textarea', __( 'Marquee words (comma separated)', 'nabia' ) ),
		'stat_1_number'      => array( 'nabia_stats', 'text', __( 'Stat 1 number', 'nabia' ) ),
		'stat_1_suffix'      => array( 'nabia_stats', 'text', __( 'Stat 1 suffix', 'nabia' ) ),
		'stat_1_label'       => array( 'nabia_stats', 'text', __( 'Stat 1 label', 'nabia' ) ),
		'stat_2_number'      => array( 'nabia_stats', 'text', __( 'Stat 2 number', 'nabia' ) ),
		'stat_2_suffix'      => array( 'nabia_stats', 'text', __( 'Stat 2 suffix', 'nabia' ) ),
		'stat_2_label'       => array( 'nabia_stats', 'text', __( 'Stat 2 label', 'nabia' ) ),
		'stat_3_number'      => array( 'nabia_stats', 'text', __( 'Stat 3 number', 'nabia' ) ),
		'stat_3_suffix'      => array( 'nabia_stats', 'text', __( 'Stat 3 suffix', 'nabia' ) ),
		'stat_3_label'       => array( 'nabia_stats', 'text', __( 'Stat 3 label', 'nabia' ) ),
		'stat_4_number'      => array( 'nabia_stats', 'text', __( 'Stat 4 number', 'nabia' ) ),
		'stat_4_suffix'      => array( 'nabia_stats', 'text', __( 'Stat 4 suffix', 'nabia' ) ),
		'stat_4_label'       => array( 'nabia_stats', 'text', __( 'Stat 4 label', 'nabia' ) ),

		'about_title'        => array( 'nabia_about', 'text', __( 'About title', 'nabia' ) ),
		'about_text'         => array( 'nabia_about', 'textarea', __( 'About text', 'nabia' ) ),
		'author_image'       => array( 'nabia_about', 'image', __( 'Author photo (used for every author avatar: blog posts, author box and comments)', 'nabia' ) ),
		'about_image'        => array( 'nabia_about', 'image', __( 'About image (shown when no intro video is set, falls back to the author photo)', 'nabia' ) ),
		'intro_video'        => array( 'nabia_about', 'url', __( 'Intro video (MP4 link from your Media Library)', 'nabia' ) ),
		'intro_video_poster' => array( 'nabia_about', 'image', __( 'Intro video cover image (optional)', 'nabia' ) ),
		'intro_video_caption' => array( 'nabia_about', 'text', __( 'Intro video caption', 'nabia' ) ),
		'services_title'     => array( 'nabia_about', 'text', __( 'Services title', 'nabia' ) ),
		'platforms_title'    => array( 'nabia_about', 'text', __( 'Platforms title', 'nabia' ) ),
		'platforms_text'     => array( 'nabia_about', 'textarea', __( 'Platforms intro', 'nabia' ) ),
		'work_title'         => array( 'nabia_about', 'text', __( 'Work title', 'nabia' ) ),
		'process_title'      => array( 'nabia_about', 'text', __( 'Process title', 'nabia' ) ),
		'testimonials_title' => array( 'nabia_about', 'text', __( 'Testimonials title', 'nabia' ) ),
		'faq_title'          => array( 'nabia_about', 'text', __( 'FAQ title', 'nabia' ) ),

		'portfolio_post_type' => array( 'nabia_portfolio', 'select', __( 'Which posts are your portfolio?', 'nabia' ) ),
		'portfolio_count'    => array( 'nabia_portfolio', 'text', __( 'How many projects on the homepage', 'nabia' ) ),

		'videos_title'       => array( 'nabia_videos', 'text', __( 'Section title', 'nabia' ) ),
		'videos_text'        => array( 'nabia_videos', 'textarea', __( 'Section intro', 'nabia' ) ),
		'video_reviews'      => array( 'nabia_videos', 'textarea', __( 'Videos: one per line: MP4 or YouTube link | Client name | Short caption', 'nabia' ) ),
		'video_layout'       => array( 'nabia_videos', 'select', __( 'Layout', 'nabia' ) ),

		'google_place_id'    => array( 'nabia_google', 'text', __( 'Google Place ID', 'nabia' ) ),
		'google_api_key'     => array( 'nabia_google', 'text', __( 'Google Places API key', 'nabia' ) ),
		'google_min_rating'  => array( 'nabia_google', 'select', __( 'Only show reviews with at least', 'nabia' ) ),

		'pricing_title'      => array( 'nabia_pricing', 'text', __( 'Pricing title', 'nabia' ) ),
		'pricing_text'       => array( 'nabia_pricing', 'textarea', __( 'Pricing intro', 'nabia' ) ),
		'plan_web_1'         => array( 'nabia_pricing', 'textarea', __( 'Website plan 1', 'nabia' ) ),
		'plan_web_2'         => array( 'nabia_pricing', 'textarea', __( 'Website plan 2', 'nabia' ) ),
		'plan_web_3'         => array( 'nabia_pricing', 'textarea', __( 'Website plan 3', 'nabia' ) ),
		'plan_popular'       => array( 'nabia_pricing', 'select', __( 'Highlight as “Most popular”', 'nabia' ) ),
		'plan_care_1'        => array( 'nabia_pricing', 'textarea', __( 'Maintenance plan 1', 'nabia' ) ),
		'plan_care_2'        => array( 'nabia_pricing', 'textarea', __( 'Maintenance plan 2', 'nabia' ) ),
		'plan_care_3'        => array( 'nabia_pricing', 'textarea', __( 'Maintenance plan 3', 'nabia' ) ),
		'care_popular'       => array( 'nabia_pricing', 'select', __( 'Maintenance: highlight as “Most popular”', 'nabia' ) ),
		'care_period'        => array( 'nabia_pricing', 'text', __( 'Maintenance price period (e.g. /month)', 'nabia' ) ),
		'hire_url'           => array( 'nabia_pricing', 'url', __( '“Hire me” button link (empty = your /hire-me/ page or the contact section)', 'nabia' ) ),

		'audit_title'        => array( 'nabia_audit', 'text', __( 'Title', 'nabia' ) ),
		'audit_text'         => array( 'nabia_audit', 'textarea', __( 'Text', 'nabia' ) ),
		'audit_points'       => array( 'nabia_audit', 'textarea', __( 'What you check (comma separated)', 'nabia' ) ),
		'audit_shortcode'    => array( 'nabia_audit', 'text', __( 'Use my own form instead (shortcode, optional)', 'nabia' ) ),

		'cta_title'          => array( 'nabia_contact', 'text', __( 'Contact title', 'nabia' ) ),
		'cta_text'           => array( 'nabia_contact', 'textarea', __( 'Contact text', 'nabia' ) ),
		'contact_email'      => array( 'nabia_contact', 'email', __( 'Email', 'nabia' ) ),
		'contact_whatsapp'   => array( 'nabia_contact', 'text', __( 'WhatsApp number (with country code)', 'nabia' ) ),
		'whatsapp_message'   => array( 'nabia_contact', 'text', __( 'WhatsApp: first message typed for the visitor', 'nabia' ) ),
		'gchat_email'        => array( 'nabia_contact', 'email', __( 'Google Chat email', 'nabia' ) ),
		'gchat_url'          => array( 'nabia_contact', 'url', __( 'Google Chat link (the button opens this and copies your Google Chat email)', 'nabia' ) ),
		'contact_shortcode'  => array( 'nabia_contact', 'text', __( 'Contact form shortcode (e.g. Contact Form 7 / WPForms)', 'nabia' ) ),

		'social_upwork'      => array( 'nabia_social', 'url', __( 'Upwork profile link', 'nabia' ) ),
		'hire_title'         => array( 'nabia_social', 'text', __( 'Fiverr / Upwork section: title', 'nabia' ) ),
		'hire_text'          => array( 'nabia_social', 'textarea', __( 'Fiverr / Upwork section: text', 'nabia' ) ),
		'fiverr_note'        => array( 'nabia_social', 'text', __( 'Fiverr button text', 'nabia' ) ),
		'upwork_note'        => array( 'nabia_social', 'text', __( 'Upwork button text', 'nabia' ) ),
		'social_fiverr'      => array( 'nabia_social', 'url', __( 'Fiverr profile link', 'nabia' ) ),
		'social_linktree'    => array( 'nabia_social', 'url', __( 'Linktree link (the only social button shown on the site)', 'nabia' ) ),
	);

	$sanitizers = array(
		'text'     => 'sanitize_text_field',
		'textarea' => 'sanitize_textarea_field',
		'url'      => 'esc_url_raw',
		'email'    => 'sanitize_email',
		'checkbox' => 'nabia_sanitize_checkbox',
		'color'    => 'sanitize_hex_color',
		'image'    => 'esc_url_raw',
		'select'   => 'sanitize_key',
	);

	$schemes = array();
	foreach ( nabia_color_schemes() as $scheme_key => $scheme ) {
		$schemes[ $scheme_key ] = $scheme['label'];
	}
	$post_types = array();
	foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {
		if ( ! in_array( $post_type->name, array( 'attachment', 'page', 'post', 'testimonial', 'video_review' ), true ) ) {
			$post_types[ $post_type->name ] = $post_type->labels->name . ' (' . $post_type->name . ')';
		}
	}
	if ( ! isset( $post_types['websites'] ) ) {
		$post_types['websites'] = __( 'Websites (websites)', 'nabia' );
	}
	$popular_choices = array(
		'0' => __( 'None', 'nabia' ),
		'1' => __( 'Plan 1', 'nabia' ),
		'2' => __( 'Plan 2', 'nabia' ),
		'3' => __( 'Plan 3', 'nabia' ),
	);
	$choices = array(
		'care_popular'        => $popular_choices,
		'plan_popular'        => array(
			'0' => __( 'None', 'nabia' ),
			'1' => __( 'Plan 1', 'nabia' ),
			'2' => __( 'Plan 2', 'nabia' ),
			'3' => __( 'Plan 3', 'nabia' ),
		),
		'google_min_rating'   => array(
			'1' => __( '1 star (show all)', 'nabia' ),
			'3' => __( '3 stars', 'nabia' ),
			'4' => __( '4 stars', 'nabia' ),
			'5' => __( '5 stars only', 'nabia' ),
		),
		'portfolio_post_type' => $post_types,
		'color_scheme' => $schemes,
		'video_layout' => array(
			'auto'     => __( 'Automatic (Shorts links = vertical)', 'nabia' ),
			'wide'     => __( 'Wide: big player + playlist', 'nabia' ),
			'vertical' => __( 'Vertical: row of big Shorts-style videos', 'nabia' ),
		),
	);

	$descriptions = array(
		'video_reviews'   => __( 'Paste MP4 links from your Media Library (or YouTube links), one per line. The client name is read from the file name, or add it after a | sign: https://…/review.mp4 | Sarah Malik | New store in 2 weeks. You can also use Dashboard → Video Reviews.', 'nabia' ),
		'google_place_id' => __( 'Find it at developers.google.com/maps/documentation/places/web-service/place-id, search your business name and copy the ID (starts with “ChIJ…”).', 'nabia' ),
		'plan_web_1'      => __( 'Line 1: plan name. Line 2: price (leave the line empty for “Custom quote”). Then one feature per line. Start a line with - to show it as not included.', 'nabia' ),
		'plan_care_1'     => __( 'Same format. Optional extra lines: “Was: $69.99” shows a crossed-out old price, “Subtitle: 1 day in a month” and “Note: 3 days support”.', 'nabia' ),
		'google_api_key'  => __( 'Google Cloud Console → enable “Places API (New)” → Credentials → Create API key (restrict it to Places API). Stored on your server only; visitors never see it. Reviews refresh every 12 hours.', 'nabia' ),
	);

	foreach ( $fields as $key => $field ) {
		list( $section, $type, $label ) = $field;

		$wp_customize->add_setting(
			$key,
			array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => $sanitizers[ $type ],
				'transport'         => 'refresh',
			)
		);

		if ( 'color' === $type ) {
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					$key,
					array(
						'label'   => $label,
						'section' => $section,
					)
				)
			);
		} elseif ( 'image' === $type ) {
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					$key,
					array(
						'label'   => $label,
						'section' => $section,
					)
				)
			);
		} elseif ( 'select' === $type ) {
			$wp_customize->add_control(
				$key,
				array(
					'label'   => $label,
					'section' => $section,
					'type'    => 'select',
					'choices' => $choices[ $key ],
				)
			);
		} else {
			$wp_customize->add_control(
				$key,
				array(
					'label'       => $label,
					'section'     => $section,
					'type'        => $type,
					'description' => isset( $descriptions[ $key ] ) ? $descriptions[ $key ] : '',
				)
			);
		}
	}
}
add_action( 'customize_register', 'nabia_customize_register' );
