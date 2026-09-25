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
		'enable_smooth'      => true,
		'brand_name'         => 'Nabia Khan',
		'use_custom_logo'    => false,

		// Hero.
		'hero_badge'         => 'Available for new projects',
		'hero_line_1'        => 'I design & build',
		'hero_line_2'        => 'websites that',
		'hero_rotating'      => 'stand out., sell more., load fast., wow people.',
		'hero_text'          => 'WordPress developer & graphic designer with 5+ years of experience. I build fast, conversion-focused websites on WordPress, Shopify, Wix, Webflow and custom code, with smart AI features built in.',
		'hero_cta_label'     => 'Start a project',
		'hero_cta_url'       => '#contact',
		'hero_cta2_label'    => 'See my work',
		'hero_cta2_url'      => '#work',
		'hero_image'         => '',

		// Marquee.
		'marquee_items'      => 'WordPress, Shopify, Wix, Webflow, Squarespace, WooCommerce, Elementor, AI Chatbots, Custom Code, UI / UX Design, Branding, SEO',

		// Stats.
		'stat_1_number'      => '5',
		'stat_1_suffix'      => '+',
		'stat_1_label'       => 'Years of experience',
		'stat_2_number'      => '300',
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

		// Platforms.
		'platforms_title'    => 'One developer, every platform',
		'platforms_text'     => 'Already on a platform, or not sure which one to pick? I design and build on all the big ones, and add AI where it helps.',

		// Sections.
		'services_title'     => 'Everything your website needs',
		'work_title'         => 'Selected work',
		'process_title'      => 'How we will work together',
		'testimonials_title' => 'Kind words from clients',
		'faq_title'          => 'Questions? Answers.',

		// Video reviews.
		'videos_title'       => 'Hear it straight from my clients',
		'videos_text'        => 'Real people, real projects. Press play and hear what working together feels like.',
		'video_reviews'      => "https://nabiakhan.com/wp-content/uploads/2026/09/vidssave.com-Annika-Dose-_-Happy-Client-_-Nabia-Khan-720P.mp4
https://nabiakhan.com/wp-content/uploads/2026/09/vidssave.com-Dr-Craig-Duncan-_-Happy-Client-_-Nabia-Khan-480P.mp4
https://nabiakhan.com/wp-content/uploads/2026/09/vidssave.com-Phillip-Duff-_-Happy-Client-_-Nabia-Khan-480P.mp4
https://nabiakhan.com/wp-content/uploads/2026/09/vidssave.com-Reginald-Hilliard-_-Happy-Client-_-Nabia-Khan-480P.mp4
https://nabiakhan.com/wp-content/uploads/2026/09/vidssave.com-Tara-Lori-_-Happy-Client-_-Nabia-Khan-720P.mp4",
		'video_layout'       => 'auto',

		// Pricing. Each plan: line 1 = name, line 2 = price, then one feature per line.
		// Start a feature with "-" to show it as not included.
		'pricing_title'      => 'Simple, honest pricing',
		'pricing_text'       => 'Fixed prices, no surprises. Every website includes a custom design, speed optimisation and one month of free support.',
		'hire_url'           => '',
		'plan_web_1'         => "New Startup\n$499\nWas: $600\nCustom theme\nOptimised images\n3 pages\nHosting setup\nContent upload\nContact form\nSocial icons\nFree support (1 month)\n- Domain & hosting not included",
		'plan_web_2'         => "Business Website\n$799\nWas: $999\nCustom theme\nOptimised images\n5 pages\nHosting setup\nContent + product upload (20)\nContact form + login forms\nSocial icons\nFree support (1 month)\n- Domain & hosting not included",
		'plan_web_3'         => "E-Commerce Store\n$1199\nWas: $1499\nCustom theme\nOptimised images\n8 pages\nHosting setup\nContent + product upload (30)\nContact form + booking forms\nSocial icons + email integration\nFree support (1 month)\n- Domain & hosting not included",
		'plan_popular'       => '2',
		'plan_care_1'        => "Basic\n$45.99\nWas: $69.99\nSubtitle: 1 day in a month\nPlugin & theme updates\nWordPress backup\nSpeed optimisation\nBug fixing\nSpam comment removal\nResponsive issues\nContent management\nSEO health check\nDatabase optimisation\nNote: 3 days support",
		'plan_care_2'        => "Standard\n$79.99\nWas: $100\nSubtitle: 15 days in a month\nPlugin & theme updates\nWordPress backup\nSpeed optimisation\nBug fixing\nSpam comment removal\nResponsive issues\nContent management\nSEO health check\nDatabase optimisation\nNote: 15 days support",
		'plan_care_3'        => "Premium\n$159.99\nWas: $200\nSubtitle: 30 days in a month\nPlugin & theme updates\nWordPress backup\nSpeed optimisation\nBug fixing\nSpam comment removal\nResponsive issues\nContent management\nSEO health check\nDatabase optimisation\nNote: 30 days support",
		'care_popular'       => '3',
		'care_period'        => '/month',

		// Free audit.
		'audit_title'        => 'Get a free website audit',
		'audit_text'         => 'Not sure what is holding your website back? Send me your link and I will review it personally, then send you a short report with clear, practical fixes. No cost, no obligation.',
		'audit_points'       => 'Speed & Core Web Vitals, SEO basics & Google visibility, Mobile experience, Security & updates, Design & trust signals, Conversion: calls to action & forms',
		'audit_shortcode'    => '',

		// Google reviews.
		'google_api_key'     => '',
		'google_place_id'    => '',
		'google_min_rating'  => '4',

		// Fiverr / Upwork call-to-action.
		'hire_title'         => 'Prefer to hire through Fiverr or Upwork?',
		'hire_text'          => 'Work with me on the platform you already trust: secure payments, clear milestones and reviews you can check.',
		'fiverr_note'        => 'Level 2 seller · Order a gig',
		'upwork_note'        => 'Send me an invite',

		// Contact.
		'cta_title'          => "Let's build something bold.",
		'cta_text'           => 'Tell me about your project and I will get back to you within 24 hours with ideas, a timeline and a clear quote.',
		'contact_email'      => 'info@nabiakhan.com',
		'contact_whatsapp'   => '',
		'contact_shortcode'  => '',

		// Social.
		'social_linkedin'    => '',
		'social_instagram'   => '',
		'social_behance'     => '',
		'social_dribbble'    => '',
		'social_github'      => '',
		'social_youtube'     => '',
		'social_upwork'      => 'https://www.upwork.com/freelancers/~017b55e580768aed42',
		'social_fiverr'      => 'https://www.fiverr.com/nabia_khan',
		'social_tiktok'      => '',
		'social_linktree'    => 'https://linktr.ee/nabia_khan',

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
				'slug'  => 'web-design',
				'title' => __( 'Web Design', 'nabia' ),
				'text'  => __( 'Interfaces that look sharp, feel effortless and guide visitors straight to the contact button.', 'nabia' ),
				'tags'  => array( 'UI / UX', 'Wireframes', 'Figma' ),
			),
			array(
				'icon'  => 'code',
				'slug'  => 'wordpress-development',
				'title' => __( 'WordPress Development', 'nabia' ),
				'text'  => __( 'Custom themes, Elementor builds, plugin customisation and pixel-perfect clones. Fast, secure and easy to edit.', 'nabia' ),
				'tags'  => array( 'Custom themes', 'Elementor', 'PHP' ),
			),
			array(
				'icon'  => 'cart',
				'slug'  => 'shopify-woocommerce',
				'title' => __( 'Shopify & WooCommerce Stores', 'nabia' ),
				'text'  => __( 'Online stores with smooth checkouts, payment integrations and product pages that actually sell.', 'nabia' ),
				'tags'  => array( 'Shopify', 'WooCommerce', 'Stripe' ),
			),
			array(
				'icon'  => 'grid',
				'slug'  => 'wix-webflow-squarespace',
				'title' => __( 'Wix, Webflow & Squarespace', 'nabia' ),
				'text'  => __( 'Beautiful sites on the no-code platform you love, set up so you can change everything yourself.', 'nabia' ),
				'tags'  => array( 'Wix', 'Webflow', 'Squarespace' ),
			),
			array(
				'icon'  => 'terminal',
				'slug'  => 'custom-websites',
				'title' => __( 'Custom-Coded Websites', 'nabia' ),
				'text'  => __( 'Hand-built HTML, CSS, JavaScript and PHP sites for when you need total freedom and top speed.', 'nabia' ),
				'tags'  => array( 'HTML & CSS', 'JavaScript', 'PHP' ),
			),
			array(
				'icon'  => 'sparkles',
				'slug'  => 'ai-website-solutions',
				'title' => __( 'AI Website Solutions', 'nabia' ),
				'text'  => __( 'AI chatbots, smart forms, AI-assisted content and automations that answer customers and save you hours every week.', 'nabia' ),
				'tags'  => array( 'AI chatbots', 'AI content', 'Automation' ),
			),
			array(
				'icon'  => 'pen',
				'slug'  => 'branding-graphic-design',
				'title' => __( 'Branding & Graphic Design', 'nabia' ),
				'text'  => __( 'Logos, visual identities, social media kits and print, for a consistent look that people remember.', 'nabia' ),
				'tags'  => array( 'Logo', 'Identity', 'Social kits' ),
			),
			array(
				'icon'  => 'bolt',
				'slug'  => 'speed-seo',
				'title' => __( 'Speed & SEO', 'nabia' ),
				'text'  => __( 'Core Web Vitals tuning, on-page SEO and clean markup, so Google and your visitors love your site.', 'nabia' ),
				'tags'  => array( 'Core Web Vitals', 'On-page SEO' ),
			),
			array(
				'icon'  => 'shield',
				'slug'  => 'website-maintenance',
				'title' => __( 'Care & Maintenance', 'nabia' ),
				'text'  => __( 'Updates, backups, security hardening and small edits every month, so you can focus on your business.', 'nabia' ),
				'tags'  => array( 'Updates', 'Backups', 'Security' ),
			),
		)
	);
}

/**
 * Platforms section.
 *
 * @return array
 */
function nabia_platforms() {
	return apply_filters(
		'nabia_platforms',
		array(
			array(
				'name'  => 'WordPress',
				'mark'  => 'W',
				'color' => '#21759b',
				'text'  => __( 'Custom themes, Elementor and plugins', 'nabia' ),
			),
			array(
				'name'  => 'Shopify',
				'mark'  => 'S',
				'color' => '#5e8e3e',
				'text'  => __( 'Stores, themes and apps setup', 'nabia' ),
			),
			array(
				'name'  => 'WooCommerce',
				'mark'  => 'Wc',
				'color' => '#7f54b3',
				'text'  => __( 'WordPress shops that convert', 'nabia' ),
			),
			array(
				'name'  => 'Wix',
				'mark'  => 'Wx',
				'color' => '#0c6efc',
				'text'  => __( 'Editor X and Wix Studio sites', 'nabia' ),
			),
			array(
				'name'  => 'Webflow',
				'mark'  => 'Wf',
				'color' => '#146ef5',
				'text'  => __( 'Pixel-perfect, animated builds', 'nabia' ),
			),
			array(
				'name'  => 'Squarespace',
				'mark'  => 'Sq',
				'color' => '#111111',
				'text'  => __( 'Elegant sites, easy to manage', 'nabia' ),
			),
			array(
				'name'  => __( 'Custom code', 'nabia' ),
				'mark'  => '</>',
				'color' => '#e34c26',
				'text'  => __( 'HTML, CSS, JavaScript and PHP', 'nabia' ),
			),
			array(
				'name'  => __( 'AI tools', 'nabia' ),
				'mark'  => 'AI',
				'color' => '#7c3aed',
				'text'  => __( 'Chatbots, content and automation', 'nabia' ),
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
				'text'  => __( 'Moodboard, wireframes and a polished design you can click through. We refine it together until it feels right.', 'nabia' ),
			),
			array(
				'title' => __( 'Develop', 'nabia' ),
				'text'  => __( 'I build it on the platform that suits you best: responsive, fast, SEO-ready and simple for you to update.', 'nabia' ),
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
				'a' => __( 'A landing page usually takes 3 to 5 days, a full business website 1 to 3 weeks and an online store 2 to 4 weeks, depending on content and features.', 'nabia' ),
			),
			array(
				'q' => __( 'Do you only work with WordPress?', 'nabia' ),
				'a' => __( 'No. WordPress is my speciality, but I also build on Shopify, Wix, Webflow and Squarespace, and I hand-code custom websites. I will recommend the platform that fits your budget and goals.', 'nabia' ),
			),
			array(
				'q' => __( 'Can you add AI features to my website?', 'nabia' ),
				'a' => __( 'Yes. I can add an AI chatbot that answers customer questions, smart contact forms, AI-assisted blog and product content, and automations that connect your site to email, CRM and booking tools.', 'nabia' ),
			),
			array(
				'q' => __( 'Will I be able to edit the website myself?', 'nabia' ),
				'a' => __( 'Yes. Every site is built so you can change text, images, products and blog posts yourself. You also get a personal video walkthrough.', 'nabia' ),
			),
			array(
				'q' => __( 'Can you redesign or fix my existing website?', 'nabia' ),
				'a' => __( 'Absolutely. I can refresh the design, fix bugs, speed it up, move it to a new host or platform, or rebuild it completely. Whatever gives you the best result.', 'nabia' ),
			),
			array(
				'q' => __( 'Do you also design logos and branding?', 'nabia' ),
				'a' => __( 'Yes. As a graphic designer I create logos, brand identities, social media templates and print materials, so your website and brand match perfectly.', 'nabia' ),
			),
			array(
				'q' => __( 'How do payments work?', 'nabia' ),
				'a' => __( 'Usually 50% upfront and 50% on launch. Larger projects can be split into milestones. You can pay me directly or hire me through Fiverr or Upwork.', 'nabia' ),
			),
		)
	);
}
