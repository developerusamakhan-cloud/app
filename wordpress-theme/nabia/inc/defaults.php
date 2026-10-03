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
		'enable_favicon_anim' => true,
		'brand_name'         => 'Nabia Khan',
		'use_custom_logo'    => false,

		// Hero.
		'hero_badge'         => 'Available for new projects',
		'hero_line_1'        => 'I design & build',
		'hero_line_2'        => 'websites that',
		'hero_rotating'      => 'stand out., sell more., load fast., wow people.',
		'hero_text'          => 'Freelance WordPress developer and website designer with 5+ years of experience and 300+ projects delivered. I design and build fast, easy-to-edit websites and online stores for small businesses on WordPress, Shopify, Wix, Webflow and custom code, with practical AI features when they help.',
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
		'about_text'         => "I'm Nabia, a full-time WordPress developer, front-end developer and graphic designer. For more than five years I have helped small businesses, coaches, clinics and online shops get websites that look professional and actually bring in enquiries. I handle design and development myself, so nothing gets lost between a designer and a developer.\n\nWhether you need a new small business website, a Shopify or WooCommerce store, a redesign, a logo or someone to look after your site every month, you work directly with me from the first call to launch day and after.",
		'about_image'        => '',
		'author_image'       => 'https://nabiakhan.com/wp-content/uploads/2024/07/WhatsApp-Image-2024-07-17-at-6.02.24-AM-min.png',
		'intro_video'        => 'https://nabiakhan.com/wp-content/uploads/2026/09/WhatsApp-Video-2026-04-16-at-2.38.06-PM.mp4',
		'intro_video_poster' => '',
		'intro_video_caption' => 'Hi, I’m Nabia 👋 Meet the person behind your next website.',

		// Portfolio.
		'portfolio_post_type' => 'websites',
		'portfolio_count'    => '6',
		'portfolio_thumbs'   => 'showcase',

		// Platforms.
		'platforms_title'    => 'One developer, every platform',
		'platforms_text'     => 'Already on a platform, or not sure which one to pick? I design and build on all the big ones and give you an honest recommendation, not the one that is easiest for me.',

		// Sections.
		'services_title'     => 'Website design, development and care in one place',
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
		'pricing_text'       => 'How much does a website cost? Here are my fixed prices, with nothing hidden. Every website includes a custom design, speed optimisation, SEO basics and one month of free support.',
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
		'audit_text'         => 'Not sure why your website is not bringing in customers? Enter your link and get an instant score for design, SEO, content and page speed, plus a PDF report with clear, practical fixes. No cost, no obligation.',
		'enable_popup'       => true,
		'enable_cookies'     => true,
		'share_image'        => '',
		'cookie_title'       => 'A few cookies, if that is okay?',
		'cookie_text'        => 'I use cookies to keep this site running smoothly and, with your okay, to see which pages help people most. You choose what is on.',
		'popup_delay'        => '15',
		'popup_title'        => 'Is your website costing you customers?',
		'popup_text'         => 'Get an instant score for design, SEO, content and speed, plus a PDF report with clear fixes. It takes about 60 seconds.',
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
		'cta_text'           => 'Tell me about your project and I will get back to you within 24 hours with honest advice, a timeline and a clear quote. No pressure and no sales scripts.',
		'contact_email'      => 'info@nabiakhan.com',
		'contact_whatsapp'   => '+92 312 1305032',
		'whatsapp_message'   => 'Hi Nabia, I found your website and would like to talk about a project.',
		'gchat_email'        => 'devnabiakhan@gmail.com',
		'enable_livechat'    => true,
		'livechat_label'     => 'Live chat',
		'gchat_url'          => 'https://mail.google.com/chat/u/0/#chat/home',
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
		'footer_text'        => 'Freelance WordPress developer, website designer and graphic designer. I build fast, good looking websites for small businesses that want more customers.',
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
				'text'  => __( 'Custom small business website design that looks sharp, works on every phone and guides visitors straight to the contact button.', 'nabia' ),
				'tags'  => array( 'UI / UX', 'Wireframes', 'Figma' ),
			),
			array(
				'icon'  => 'code',
				'slug'  => 'wordpress-development',
				'title' => __( 'WordPress Development', 'nabia' ),
				'text'  => __( 'Custom WordPress themes, Elementor builds, plugin setup and fixes by a WordPress developer you talk to directly.', 'nabia' ),
				'tags'  => array( 'Custom themes', 'Elementor', 'PHP' ),
			),
			array(
				'icon'  => 'cart',
				'slug'  => 'shopify-woocommerce',
				'title' => __( 'Shopify & WooCommerce Stores', 'nabia' ),
				'text'  => __( 'Shopify and WooCommerce stores with smooth checkouts, secure payments and product pages that actually sell.', 'nabia' ),
				'tags'  => array( 'Shopify', 'WooCommerce', 'Stripe' ),
			),
			array(
				'icon'  => 'grid',
				'slug'  => 'wix-webflow-squarespace',
				'title' => __( 'Wix, Webflow & Squarespace', 'nabia' ),
				'text'  => __( 'Wix, Squarespace and Webflow website design that looks custom, set up so you can change everything yourself.', 'nabia' ),
				'tags'  => array( 'Wix', 'Webflow', 'Squarespace' ),
			),
			array(
				'icon'  => 'terminal',
				'slug'  => 'custom-websites',
				'title' => __( 'Custom-Coded Websites', 'nabia' ),
				'text'  => __( 'Custom website development in HTML, CSS, JavaScript and PHP for when you need total freedom and top speed.', 'nabia' ),
				'tags'  => array( 'HTML & CSS', 'JavaScript', 'PHP' ),
			),
			array(
				'icon'  => 'sparkles',
				'slug'  => 'ai-website-solutions',
				'title' => __( 'AI Website Solutions', 'nabia' ),
				'text'  => __( 'An AI chatbot for your website, smart forms and automations that answer customers and save you hours every week.', 'nabia' ),
				'tags'  => array( 'AI chatbots', 'AI content', 'Automation' ),
			),
			array(
				'icon'  => 'pen',
				'slug'  => 'branding-graphic-design',
				'title' => __( 'Branding & Graphic Design', 'nabia' ),
				'text'  => __( 'Logo design services, brand identity, social media kits and print, for a consistent look that people remember.', 'nabia' ),
				'tags'  => array( 'Logo', 'Identity', 'Social kits' ),
			),
			array(
				'icon'  => 'bolt',
				'slug'  => 'speed-seo',
				'title' => __( 'Speed & SEO', 'nabia' ),
				'text'  => __( 'Website speed optimization, Core Web Vitals and SEO for small businesses, so customers can find you on Google.', 'nabia' ),
				'tags'  => array( 'Core Web Vitals', 'On-page SEO' ),
			),
			array(
				'icon'  => 'shield',
				'slug'  => 'website-maintenance',
				'title' => __( 'Care & Maintenance', 'nabia' ),
				'text'  => __( 'Website maintenance services: updates, backups, security and small edits every month, so you can focus on your business.', 'nabia' ),
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
				'service' => 'wordpress-development',
				'mark'  => 'W',
				'color' => '#21759b',
				'text'  => __( 'Custom themes, Elementor and plugins', 'nabia' ),
			),
			array(
				'name'  => 'Shopify',
				'service' => 'shopify-woocommerce',
				'mark'  => 'S',
				'color' => '#5e8e3e',
				'text'  => __( 'Stores, themes and apps setup', 'nabia' ),
			),
			array(
				'name'  => 'WooCommerce',
				'service' => 'shopify-woocommerce',
				'mark'  => 'Wc',
				'color' => '#7f54b3',
				'text'  => __( 'WordPress shops that convert', 'nabia' ),
			),
			array(
				'name'  => 'Wix',
				'service' => 'wix-webflow-squarespace',
				'mark'  => 'Wx',
				'color' => '#0c6efc',
				'text'  => __( 'Editor X and Wix Studio sites', 'nabia' ),
			),
			array(
				'name'  => 'Webflow',
				'service' => 'wix-webflow-squarespace',
				'mark'  => 'Wf',
				'color' => '#146ef5',
				'text'  => __( 'Pixel-perfect, animated builds', 'nabia' ),
			),
			array(
				'name'  => 'Squarespace',
				'service' => 'wix-webflow-squarespace',
				'mark'  => 'Sq',
				'color' => '#111111',
				'text'  => __( 'Elegant sites, easy to manage', 'nabia' ),
			),
			array(
				'name'  => __( 'Custom code', 'nabia' ),
				'service' => 'custom-websites',
				'mark'  => '</>',
				'color' => '#e34c26',
				'text'  => __( 'HTML, CSS, JavaScript and PHP', 'nabia' ),
			),
			array(
				'name'  => __( 'AI tools', 'nabia' ),
				'service' => 'ai-website-solutions',
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
				'q' => __( 'How much does a website cost?', 'nabia' ),
				'a' => sprintf(
					/* translators: 1-3: prices */
					__( 'With me, a starter website costs %1$s, a business website %2$s and an online store %3$s. The price is fixed before work starts. In general, small business websites in the US range from about $500 with a freelancer to $5,000 or more with an agency, depending on pages, features and content.', 'nabia' ),
					nabia_price( 'plan_web_1' ),
					nabia_price( 'plan_web_2' ),
					nabia_price( 'plan_web_3' )
				),
			),
			array(
				'q' => __( 'How long does a website take?', 'nabia' ),
				'a' => __( 'A landing page usually takes 3 to 5 days, a full business website 1 to 3 weeks and an online store 2 to 4 weeks, depending on content and features.', 'nabia' ),
			),
			array(
				'q' => __( 'Why hire a freelance web designer instead of an agency?', 'nabia' ),
				'a' => __( 'You work directly with the person who designs and builds your site, so answers are quicker, nothing gets lost in hand-offs and you do not pay agency overheads. I have 5+ years of experience, 300+ projects delivered and a 100% job success score.', 'nabia' ),
			),
			array(
				'q' => __( 'Do you only work with WordPress?', 'nabia' ),
				'a' => __( 'No. WordPress website design is my speciality, but I also build on Shopify, WooCommerce, Wix, Webflow and Squarespace, and I hand-code custom websites. I recommend the platform that fits your budget and goals.', 'nabia' ),
			),
			array(
				'q' => __( 'Will I be able to edit the website myself?', 'nabia' ),
				'a' => __( 'Yes. Every site is built so you can change text, images, products and blog posts yourself. You also get a personal video walkthrough.', 'nabia' ),
			),
			array(
				'q' => __( 'Can you redesign or fix my existing website?', 'nabia' ),
				'a' => __( 'Absolutely. I can refresh the design, fix bugs, speed it up, move it to a new host or platform, or rebuild it completely, while keeping the pages that already rank on Google.', 'nabia' ),
			),
			array(
				'q' => __( 'Do you offer website maintenance after launch?', 'nabia' ),
				'a' => sprintf(
					/* translators: %s: price */
					__( 'Yes. My website maintenance plans start at %s per month and cover updates, backups, security, speed checks and small edits. No long contract.', 'nabia' ),
					nabia_price( 'plan_care_1' )
				),
			),
			array(
				'q' => __( 'Do you also design logos and branding?', 'nabia' ),
				'a' => __( 'Yes. As a graphic designer I create logos, brand identities, social media templates and print materials, so your website and brand match perfectly.', 'nabia' ),
			),
			array(
				'q' => __( 'Can you add an AI chatbot to my website?', 'nabia' ),
				'a' => __( 'Yes. I can add an AI chatbot that answers customer questions from your own content, plus smart contact forms, AI-assisted content and automations that connect your site to email, CRM and booking tools.', 'nabia' ),
			),
			array(
				'q' => __( 'How do you use AI in your own work?', 'nabia' ),
				'a' => __( 'AI is my assistant, never my replacement. I use it to research your industry, brainstorm layouts, draft first versions of copy and check code faster. Every design decision, line of code and word on your site is still reviewed and polished by me by hand.', 'nabia' ),
			),
			array(
				'q' => __( 'Will my website content sound robotic or generic?', 'nabia' ),
				'a' => __( 'No. I write in your tone of voice, add your real stories, services and prices, and make sure it reads like a human wrote it for humans. Google rewards helpful, original content, and that is exactly what you get.', 'nabia' ),
			),
			array(
				'q' => __( 'How do payments work?', 'nabia' ),
				'a' => __( 'Usually 50% upfront and 50% on launch. Larger projects can be split into milestones. You can pay me directly or hire me through Fiverr or Upwork.', 'nabia' ),
			),
		)
	);
}
