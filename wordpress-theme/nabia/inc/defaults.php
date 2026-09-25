<?php
/**
 * Default content for every Customizer option, so the theme looks complete out of the box.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All default theme mods.
 *
 * @return array
 */
function nabia_defaults() {
	return array(
		// General.
		'color_scheme'       => 'violet',
		'accent_color'       => '',
		'enable_preloader'   => true,
		'enable_cursor'      => true,
		'brand_name'         => 'Nabia Khan',
		'brand_tagline'      => 'WordPress & Design',

		// Hero.
		'hero_badge'         => 'Available for new projects',
		'hero_line_1'        => 'I design & build',
		'hero_line_2'        => 'websites that',
		'hero_rotating'      => 'stand out., sell more., load fast., wow people.',
		'hero_text'          => 'WordPress developer & graphic designer with 5+ years of experience crafting bold, fast and conversion-focused websites for brands around the world.',
		'hero_cta_label'     => 'Start a project',
		'hero_cta_url'       => '#contact',
		'hero_cta2_label'    => 'See my work',
		'hero_cta2_url'      => '#work',
		'hero_image'         => '',

		// Marquee.
		'marquee_items'      => 'WordPress, Elementor, WooCommerce, UI / UX Design, Branding, Graphic Design, PHP, HTML & CSS, jQuery, Bootstrap, Speed Optimisation, SEO',

		// Stats.
		'stat_1_number'      => '5',
		'stat_1_suffix'      => '+',
		'stat_1_label'       => 'Years of experience',
		'stat_2_number'      => '250',
		'stat_2_suffix'      => '+',
		'stat_2_label'       => 'Projects delivered',
		'stat_3_number'      => '100',
		'stat_3_suffix'      => '%',
		'stat_3_label'       => 'Job success score',
		'stat_4_number'      => '24',
		'stat_4_suffix'      => 'h',
		'stat_4_label'       => 'Average reply time',

		// About.
		'about_title'        => 'Design that looks sharp. Code that works hard.',
		'about_text'         => "I'm Nabia, a full-time WordPress developer, front-end developer and graphic designer. I believe great design and smart development go hand in hand, so I handle both: from the first sketch to the final line of code, under one roof.\n\nWhether you need a brand-new website, a WooCommerce store, a redesign or a pixel-perfect clone of a design you love, I'll build it fast, responsive and easy for you to manage.",
		'about_image'        => '',
		'intro_video'        => 'https://nabiakhan.com/wp-content/uploads/2026/09/WhatsApp-Video-2026-04-16-at-2.38.06-PM.mp4',
		'intro_video_poster' => '',
		'intro_video_caption' => 'Hi, I’m Nabia 👋 Meet the person behind your next website.',

		// Portfolio.
		'portfolio_post_type' => 'websites',
		'portfolio_count'    => '6',

		// Sections.
		'services_title'     => 'What I can do for you',
		'work_title'         => 'Selected work',
		'process_title'      => 'How we will work together',
		'testimonials_title' => 'Kind words from clients',
		'faq_title'          => 'Questions? Answers.',

		// Video reviews.
		'videos_title'       => 'Hear it straight from my clients',
		'videos_text'        => 'Real people, real projects. Press play and hear what working together feels like.',
		'video_reviews'      => "https://www.youtube.com/shorts/MwMlc611rIw\nhttps://www.youtube.com/shorts/4vpWeFGHn9I\nhttps://www.youtube.com/shorts/jRGgGIXQLXc\nhttps://www.youtube.com/shorts/eCPKyawWOcM\nhttps://www.youtube.com/shorts/zkNOevDxl5I",
		'video_layout'       => 'auto',

		// Google reviews.
		'google_api_key'     => '',
		'google_place_id'    => '',
		'google_min_rating'  => '4',

		// Fiverr / Upwork call-to-action.
		'hire_title'         => 'Prefer to hire through Fiverr or Upwork?',
		'hire_text'          => 'Work with me on the platform you already trust: secure payments, clear milestones and reviews you can check.',
		'fiverr_note'        => 'Order a gig',
		'upwork_note'        => 'Send me an invite',

		// Contact.
		'cta_title'          => "Let's build something bold.",
		'cta_text'           => 'Tell me about your project and I will get back to you within 24 hours with ideas, a timeline and a clear quote.',
		'contact_email'      => 'hello@nabiakhan.com',
		'contact_whatsapp'   => '',
		'contact_shortcode'  => '',
		'portal_url'         => 'https://portal.nabiakhan.com/',

		// Social.
		'social_linkedin'    => '',
		'social_instagram'   => '',
		'social_behance'     => '',
		'social_dribbble'    => '',
		'social_github'      => '',
		'social_youtube'     => 'https://www.youtube.com/channel/UCDi-EYBfzHqvHDzAGK8uEiw',
		'social_upwork'      => '',
		'social_fiverr'      => '',

		// Footer.
		'footer_text'        => 'Creative WordPress developer & graphic designer. Building websites that refuse to be boring.',
		'footer_marquee'     => "Let's work together",
		'footer_cta_title'   => 'Got a project in mind?',
		'footer_cta_text'    => "Websites, stores, branding or a quick fix: tell me what you need and you'll get a plan and a quote within 24 hours.",
	);
}

/**
 * Get a theme mod with its default.
 *
 * @param string $key Option key.
 * @return mixed
 */
function nabia_mod( $key ) {
	$defaults = nabia_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( $key, $default );
}

/**
 * Split a comma separated option into a clean list.
 *
 * @param string $key Option key.
 * @return array
 */
function nabia_mod_list( $key ) {
	return array_values( array_filter( array_map( 'trim', explode( ',', (string) nabia_mod( $key ) ) ) ) );
}

/**
 * Default services (used on the front page).
 *
 * @return array
 */
function nabia_services() {
	return apply_filters(
		'nabia_services',
		array(
			array(
				'icon'  => 'layout',
				'title' => __( 'Web Design', 'nabia' ),
				'text'  => __( 'Interfaces that look sharp, feel effortless and guide visitors straight to the “contact me” button.', 'nabia' ),
				'tags'  => array( 'UI / UX', 'Wireframes', 'Figma' ),
			),
			array(
				'icon'  => 'code',
				'title' => __( 'WordPress Development', 'nabia' ),
				'text'  => __( 'Custom themes, Elementor builds, plugin customisation and pixel-perfect clones — fast, secure and easy to edit.', 'nabia' ),
				'tags'  => array( 'Custom themes', 'Elementor', 'PHP' ),
			),
			array(
				'icon'  => 'cart',
				'title' => __( 'E-commerce', 'nabia' ),
				'text'  => __( 'WooCommerce stores with smooth checkouts, payment integrations and product pages that actually sell.', 'nabia' ),
				'tags'  => array( 'WooCommerce', 'Stripe', 'PayPal' ),
			),
			array(
				'icon'  => 'pen',
				'title' => __( 'Branding & Graphic Design', 'nabia' ),
				'text'  => __( 'Logos, visual identities, social media kits and print — a consistent look that people remember.', 'nabia' ),
				'tags'  => array( 'Logo', 'Identity', 'Social kits' ),
			),
			array(
				'icon'  => 'bolt',
				'title' => __( 'Speed & SEO', 'nabia' ),
				'text'  => __( 'Core Web Vitals tuning, on-page SEO and clean markup so Google — and your visitors — love your site.', 'nabia' ),
				'tags'  => array( 'Core Web Vitals', 'On-page SEO' ),
			),
			array(
				'icon'  => 'shield',
				'title' => __( 'Care & Maintenance', 'nabia' ),
				'text'  => __( 'Updates, backups, security hardening and small edits every month, so you can focus on your business.', 'nabia' ),
				'tags'  => array( 'Updates', 'Backups', 'Security' ),
			),
		)
	);
}

/**
 * Default process steps.
 *
 * @return array
 */
function nabia_process() {
	return apply_filters(
		'nabia_process',
		array(
			array(
				'title' => __( 'Discover', 'nabia' ),
				'text'  => __( 'A quick call to understand your business, your audience and your goals. You get a clear plan and a fixed quote.', 'nabia' ),
			),
			array(
				'title' => __( 'Design', 'nabia' ),
				'text'  => __( 'Moodboard, wireframes and a polished design you can click through — we refine it together until it feels right.', 'nabia' ),
			),
			array(
				'title' => __( 'Develop', 'nabia' ),
				'text'  => __( 'I build it in WordPress: responsive, fast, SEO-ready and simple for you to update without touching code.', 'nabia' ),
			),
			array(
				'title' => __( 'Launch & grow', 'nabia' ),
				'text'  => __( 'Testing, go-live and a walkthrough video. After launch I stay around for support, tweaks and new ideas.', 'nabia' ),
			),
		)
	);
}

/**
 * Default FAQ.
 *
 * @return array
 */
function nabia_faq() {
	return apply_filters(
		'nabia_faq',
		array(
			array(
				'q' => __( 'How long does a website take?', 'nabia' ),
				'a' => __( 'A landing page usually takes 3–5 days, a full business website 1–3 weeks and an online store 2–4 weeks, depending on content and features.', 'nabia' ),
			),
			array(
				'q' => __( 'Will I be able to edit the website myself?', 'nabia' ),
				'a' => __( 'Yes. Every site is built so you can change text, images, products and blog posts yourself. You also get a personal video walkthrough.', 'nabia' ),
			),
			array(
				'q' => __( 'Can you redesign or fix my existing WordPress site?', 'nabia' ),
				'a' => __( 'Absolutely. I can refresh the design, fix bugs, speed it up, move it to a new host or rebuild it completely — whatever gives you the best result.', 'nabia' ),
			),
			array(
				'q' => __( 'Do you also design logos and branding?', 'nabia' ),
				'a' => __( 'Yes — as a graphic designer I create logos, brand identities, social media templates and print materials, so your website and brand match perfectly.', 'nabia' ),
			),
			array(
				'q' => __( 'How do payments work?', 'nabia' ),
				'a' => __( 'Typically 50% upfront and 50% on launch. For larger projects we can split it into milestones. I work through direct payment or freelance platforms.', 'nabia' ),
			),
		)
	);
}
