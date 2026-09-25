<?php
/**
 * Service pages: detailed content per service, lookups and links.
 *
 * Pages are created from Appearance → Nabia Setup (Services + one page per service).
 * Each service page uses the "Service page" template and shows the content below.
 * Change any of it with the `nabia_service_details` filter in a child theme.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detailed content for every service, keyed by slug.
 *
 * @return array
 */
function nabia_service_details() {
	$details = array(
		'web-design'              => array(
			'headline' => __( 'Websites that look sharp and turn visitors into clients', 'nabia' ),
			'intro'    => __( 'Good design is not decoration. It is how visitors decide in a few seconds whether to trust you. I design clean, modern websites around your brand and your customers, so every page guides people to the next step.', 'nabia' ),
			'features' => array(
				array( __( 'Conversion-first layouts', 'nabia' ), __( 'Clear headlines, strong calls to action and pages built around what your customers need to know.', 'nabia' ) ),
				array( __( 'Clickable prototypes', 'nabia' ), __( 'See and click through your new website in Figma before a single line of code is written.', 'nabia' ) ),
				array( __( 'Mobile-first design', 'nabia' ), __( 'Most visitors arrive on a phone, so every layout is designed for small screens first.', 'nabia' ) ),
				array( __( 'On-brand visuals', 'nabia' ), __( 'Colours, fonts, icons and imagery that match your brand and feel consistent everywhere.', 'nabia' ) ),
				array( __( 'Accessible by default', 'nabia' ), __( 'Readable contrast, sensible font sizes and keyboard-friendly navigation for everyone.', 'nabia' ) ),
				array( __( 'Ready for any platform', 'nabia' ), __( 'Designs that I can build on WordPress, Shopify, Webflow, Wix, Squarespace or in custom code.', 'nabia' ) ),
			),
			'get'      => array( __( 'Homepage and inner page designs', 'nabia' ), __( 'Mobile and desktop versions', 'nabia' ), __( 'Clickable Figma prototype', 'nabia' ), __( 'Style guide with colours and fonts', 'nabia' ), __( 'Two rounds of revisions', 'nabia' ), __( 'Design files you own', 'nabia' ) ),
			'for'      => array( __( 'New businesses that need a professional first impression', 'nabia' ), __( 'Companies with an outdated website', 'nabia' ), __( 'Brands that want a unique look, not a template', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Do I get to see the design before it is built?', 'nabia' ), __( 'Yes. You get a clickable prototype and we refine it together before development starts.', 'nabia' ) ),
				array( __( 'Can you work with my existing logo and brand?', 'nabia' ), __( 'Of course. If you need a new logo or brand refresh, I can design that too.', 'nabia' ) ),
			),
		),
		'wordpress-development'   => array(
			'headline' => __( 'WordPress websites that are fast, secure and easy to edit', 'nabia' ),
			'intro'    => __( 'WordPress is my speciality. From custom themes and Elementor builds to plugin tweaks and pixel-perfect clones of a design you love, I build WordPress sites that load fast and that you can update without calling a developer.', 'nabia' ),
			'features' => array(
				array( __( 'Custom themes', 'nabia' ), __( 'Lightweight themes built for your site only, without the bloat of big multipurpose themes.', 'nabia' ) ),
				array( __( 'Elementor builds', 'nabia' ), __( 'Prefer drag and drop? I build clean Elementor sites that stay fast and tidy.', 'nabia' ) ),
				array( __( 'Plugin customisation', 'nabia' ), __( 'Booking systems, memberships, forms and integrations set up and adjusted to your needs.', 'nabia' ) ),
				array( __( 'Pixel-perfect clones', 'nabia' ), __( 'Send me a design or a site you love and I will rebuild it exactly in WordPress.', 'nabia' ) ),
				array( __( 'Migrations & fixes', 'nabia' ), __( 'Moving hosts, fixing errors, cleaning up hacked sites or rescuing half-finished projects.', 'nabia' ) ),
				array( __( 'Payment integrations', 'nabia' ), __( 'Stripe, PayPal and other gateways connected securely for payments and bookings.', 'nabia' ) ),
			),
			'get'      => array( __( 'Fully responsive WordPress website', 'nabia' ), __( 'Speed and SEO basics set up', 'nabia' ), __( 'Security and backup plugins configured', 'nabia' ), __( 'Contact forms connected to your email', 'nabia' ), __( 'Video walkthrough of your dashboard', 'nabia' ), __( 'One month of free support', 'nabia' ) ),
			'for'      => array( __( 'Businesses that want to manage their own content', 'nabia' ), __( 'Blogs, portfolios and service websites', 'nabia' ), __( 'Anyone with a broken or slow WordPress site', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Will I be able to update the website myself?', 'nabia' ), __( 'Yes. Everything is set up so you can edit text, images and posts, and you get a video walkthrough.', 'nabia' ) ),
				array( __( 'Can you fix my existing WordPress site?', 'nabia' ), __( 'Yes. Send me the link and a short description and I will tell you what is wrong and what it costs to fix.', 'nabia' ) ),
			),
		),
		'shopify-woocommerce'     => array(
			'headline' => __( 'Online stores that are a pleasure to shop', 'nabia' ),
			'intro'    => __( 'Whether you sell ten products or a thousand, your store needs to be quick, trustworthy and simple to check out. I build Shopify and WooCommerce stores with smooth checkouts, secure payments and product pages that actually sell.', 'nabia' ),
			'features' => array(
				array( __( 'Shopify stores', 'nabia' ), __( 'Theme setup and customisation, apps, collections and a checkout your customers trust.', 'nabia' ) ),
				array( __( 'WooCommerce stores', 'nabia' ), __( 'Full control on WordPress with products, variations, shipping and taxes configured.', 'nabia' ) ),
				array( __( 'Product pages that sell', 'nabia' ), __( 'Great photos, clear pricing, reviews and trust badges in the right places.', 'nabia' ) ),
				array( __( 'Payments & shipping', 'nabia' ), __( 'Stripe, PayPal, Apple Pay and local gateways, plus shipping zones and rates.', 'nabia' ) ),
				array( __( 'Product upload', 'nabia' ), __( 'I can upload and organise your products, categories and descriptions for you.', 'nabia' ) ),
				array( __( 'Email & marketing', 'nabia' ), __( 'Order emails, abandoned cart reminders and newsletter integrations.', 'nabia' ) ),
			),
			'get'      => array( __( 'Complete, ready-to-sell online store', 'nabia' ), __( 'Payment gateway connected and tested', 'nabia' ), __( 'Shipping and tax settings', 'nabia' ), __( 'Product upload (up to 30 included)', 'nabia' ), __( 'Order and customer email setup', 'nabia' ), __( 'Training video for managing orders', 'nabia' ) ),
			'for'      => array( __( 'Brands starting to sell online', 'nabia' ), __( 'Shops moving from Etsy or Instagram to their own store', 'nabia' ), __( 'Stores that need a redesign to convert better', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Shopify or WooCommerce, which is better?', 'nabia' ), __( 'Shopify is simpler to run, WooCommerce gives more control and no monthly platform fee. I will recommend the best fit after a quick chat.', 'nabia' ) ),
				array( __( 'Can you move my store from another platform?', 'nabia' ), __( 'Yes. I can migrate products, customers and orders to Shopify or WooCommerce.', 'nabia' ) ),
			),
		),
		'wix-webflow-squarespace' => array(
			'headline' => __( 'Beautiful no-code websites you can run yourself', 'nabia' ),
			'intro'    => __( 'Love the simplicity of Wix, Webflow or Squarespace? I design and build on all three, set everything up properly, and hand over a site you can change yourself without touching code.', 'nabia' ),
			'features' => array(
				array( __( 'Wix & Wix Studio', 'nabia' ), __( 'Custom designs, bookings, stores and member areas built on Wix.', 'nabia' ) ),
				array( __( 'Webflow', 'nabia' ), __( 'Pixel-perfect, animated websites with a CMS that is easy to update.', 'nabia' ) ),
				array( __( 'Squarespace', 'nabia' ), __( 'Elegant, simple sites for creatives, restaurants and service businesses.', 'nabia' ) ),
				array( __( 'Template customisation', 'nabia' ), __( 'Already have a template? I will make it look and feel custom.', 'nabia' ) ),
				array( __( 'SEO & speed setup', 'nabia' ), __( 'Titles, descriptions, image optimisation and Google Search Console connected.', 'nabia' ) ),
				array( __( 'Platform moves', 'nabia' ), __( 'Move from Wix, Squarespace or Webflow to WordPress, or the other way around.', 'nabia' ) ),
			),
			'get'      => array( __( 'Custom-designed no-code website', 'nabia' ), __( 'Mobile layouts checked on every page', 'nabia' ), __( 'Forms, bookings or store set up', 'nabia' ), __( 'SEO basics and analytics', 'nabia' ), __( 'Handover call and video guide', 'nabia' ), __( 'One month of free support', 'nabia' ) ),
			'for'      => array( __( 'Owners who want to edit everything themselves', 'nabia' ), __( 'Creatives, coaches, cafés and small businesses', 'nabia' ), __( 'Anyone already using one of these platforms', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Which platform should I choose?', 'nabia' ), __( 'Wix is the easiest, Squarespace the most elegant out of the box, and Webflow the most flexible. Tell me your goals and I will recommend one.', 'nabia' ) ),
				array( __( 'Do I pay the platform subscription?', 'nabia' ), __( 'Yes, the platform plan is in your name so you always own your website. I help you pick the right plan.', 'nabia' ) ),
			),
		),
		'custom-websites'         => array(
			'headline' => __( 'Hand-coded websites with total freedom and top speed', 'nabia' ),
			'intro'    => __( 'Sometimes a website builder is not enough. I hand-code websites in HTML, CSS, JavaScript and PHP when you need unique features, the best possible speed, or a design that no template can deliver.', 'nabia' ),
			'features' => array(
				array( __( 'Clean, modern code', 'nabia' ), __( 'Semantic HTML, modern CSS and lightweight JavaScript that is easy to maintain.', 'nabia' ) ),
				array( __( 'Lightning fast', 'nabia' ), __( 'No page builder overhead, so pages load in a blink and score high on Core Web Vitals.', 'nabia' ) ),
				array( __( 'Custom features', 'nabia' ), __( 'Calculators, dashboards, forms, APIs and anything your business needs.', 'nabia' ) ),
				array( __( 'Landing pages', 'nabia' ), __( 'High-converting campaign pages built quickly for ads and launches.', 'nabia' ) ),
				array( __( 'Design to code', 'nabia' ), __( 'Figma, XD or PSD designs turned into responsive, pixel-perfect pages.', 'nabia' ) ),
				array( __( 'Easy hosting', 'nabia' ), __( 'Deployed to reliable hosting with SSL, backups and a simple update process.', 'nabia' ) ),
			),
			'get'      => array( __( 'Responsive hand-coded website', 'nabia' ), __( 'Source code you fully own', 'nabia' ), __( 'Performance and SEO optimisation', 'nabia' ), __( 'Cross-browser testing', 'nabia' ), __( 'Deployment and SSL setup', 'nabia' ), __( 'One month of free support', 'nabia' ) ),
			'for'      => array( __( 'Startups with unique product ideas', 'nabia' ), __( 'Marketing teams that need fast landing pages', 'nabia' ), __( 'Designers who need their Figma files built', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Can I still edit a custom-coded site?', 'nabia' ), __( 'Yes. I can add a simple content editor or a headless CMS so you can change text and images yourself.', 'nabia' ) ),
				array( __( 'Is custom code more expensive?', 'nabia' ), __( 'Not always. Simple sites cost about the same as a builder site and are faster and cheaper to host.', 'nabia' ) ),
			),
		),
		'ai-website-solutions'    => array(
			'headline' => __( 'Put AI to work on your website', 'nabia' ),
			'intro'    => __( 'AI can answer your customers at 3am, help you write content faster and take repetitive tasks off your plate. I add practical, affordable AI features to your website, set up safely and trained on your own business information.', 'nabia' ),
			'features' => array(
				array( __( 'AI chatbot', 'nabia' ), __( 'A friendly assistant that answers common questions from your own content and collects leads 24/7.', 'nabia' ) ),
				array( __( 'AI-assisted content', 'nabia' ), __( 'Blog posts, product descriptions and page copy drafted with AI, then edited by a human.', 'nabia' ) ),
				array( __( 'Smart forms', 'nabia' ), __( 'Forms that qualify leads, route enquiries and send instant, personalised replies.', 'nabia' ) ),
				array( __( 'Automations', 'nabia' ), __( 'Connect your site to email, CRM, booking and spreadsheets so busywork happens by itself.', 'nabia' ) ),
				array( __( 'AI search', 'nabia' ), __( 'Help visitors find the right product or answer with smart, natural-language search.', 'nabia' ) ),
				array( __( 'Privacy-minded setup', 'nabia' ), __( 'Clear limits, no sensitive data shared, and a human always one click away.', 'nabia' ) ),
			),
			'get'      => array( __( 'AI feature set up on your website', 'nabia' ), __( 'Trained on your pages, FAQs and documents', 'nabia' ), __( 'Branded design that matches your site', 'nabia' ), __( 'Lead capture sent to your inbox or CRM', 'nabia' ), __( 'Monthly usage overview', 'nabia' ), __( 'Tweaks during the first month', 'nabia' ) ),
			'for'      => array( __( 'Businesses answering the same questions every day', 'nabia' ), __( 'Stores with many products', 'nabia' ), __( 'Busy owners who want to save hours every week', 'nabia' ) ),
			'plans'    => '',
			'faq'      => array(
				array( __( 'Will an AI chatbot say wrong things about my business?', 'nabia' ), __( 'It only answers from the information you approve, and hands over to you when it is not sure.', 'nabia' ) ),
				array( __( 'Does it work with my current website?', 'nabia' ), __( 'Yes. AI features can be added to WordPress, Shopify, Wix, Webflow, Squarespace and custom sites.', 'nabia' ) ),
			),
		),
		'branding-graphic-design' => array(
			'headline' => __( 'A brand people remember', 'nabia' ),
			'intro'    => __( 'Your logo, colours and visuals are often the first thing people notice. As a graphic designer I create identities that look professional everywhere, from your website to social media and print.', 'nabia' ),
			'features' => array(
				array( __( 'Logo design', 'nabia' ), __( 'Original logo concepts with variations for web, social and print.', 'nabia' ) ),
				array( __( 'Brand identity', 'nabia' ), __( 'Colour palette, typography and visual style that tell your story.', 'nabia' ) ),
				array( __( 'Social media kits', 'nabia' ), __( 'Post and story templates in Canva or Figma so your feed always looks on-brand.', 'nabia' ) ),
				array( __( 'Print design', 'nabia' ), __( 'Business cards, flyers, brochures, menus and packaging.', 'nabia' ) ),
				array( __( 'Website graphics', 'nabia' ), __( 'Banners, icons and illustrations that make your website stand out.', 'nabia' ) ),
				array( __( 'Brand guidelines', 'nabia' ), __( 'A simple guide so everyone uses your brand the right way.', 'nabia' ) ),
			),
			'get'      => array( __( 'Logo in all formats (SVG, PNG, PDF)', 'nabia' ), __( 'Colour and font guide', 'nabia' ), __( 'Social media templates', 'nabia' ), __( 'Business card design', 'nabia' ), __( 'Revisions until you love it', 'nabia' ), __( 'Full ownership of all files', 'nabia' ) ),
			'for'      => array( __( 'New businesses and startups', 'nabia' ), __( 'Brands that feel outdated or inconsistent', 'nabia' ), __( 'Anyone launching a new website', 'nabia' ) ),
			'plans'    => '',
			'faq'      => array(
				array( __( 'How many logo concepts do I get?', 'nabia' ), __( 'Usually three different directions, then we refine your favourite together.', 'nabia' ) ),
				array( __( 'Can you design my website and branding together?', 'nabia' ), __( 'Yes, and it is the best way to get a consistent look. Ask for a combined quote.', 'nabia' ) ),
			),
		),
		'speed-seo'               => array(
			'headline' => __( 'A faster website that Google loves', 'nabia' ),
			'intro'    => __( 'A slow website loses visitors and rankings. I speed up your site, fix technical SEO issues and set up the basics so Google understands your pages and customers can find you.', 'nabia' ),
			'features' => array(
				array( __( 'Core Web Vitals', 'nabia' ), __( 'Faster loading, stable layouts and quick interactions for better scores.', 'nabia' ) ),
				array( __( 'Image optimisation', 'nabia' ), __( 'Modern formats, correct sizes and lazy loading without losing quality.', 'nabia' ) ),
				array( __( 'Caching & CDN', 'nabia' ), __( 'Smart caching and content delivery so pages load fast worldwide.', 'nabia' ) ),
				array( __( 'On-page SEO', 'nabia' ), __( 'Titles, descriptions, headings and internal links done right.', 'nabia' ) ),
				array( __( 'Technical SEO', 'nabia' ), __( 'Sitemaps, schema markup, redirects and indexing issues fixed.', 'nabia' ) ),
				array( __( 'Google tools', 'nabia' ), __( 'Search Console and Analytics connected so you can see results.', 'nabia' ) ),
			),
			'get'      => array( __( 'Before and after speed report', 'nabia' ), __( 'Technical SEO fixes', 'nabia' ), __( 'Optimised images and caching', 'nabia' ), __( 'Search Console and Analytics setup', 'nabia' ), __( 'Keyword-ready page titles', 'nabia' ), __( 'Plain-English summary of changes', 'nabia' ) ),
			'for'      => array( __( 'Websites that feel slow on mobile', 'nabia' ), __( 'Businesses invisible on Google', 'nabia' ), __( 'Stores losing sales to slow pages', 'nabia' ) ),
			'plans'    => '',
			'faq'      => array(
				array( __( 'How much faster will my site be?', 'nabia' ), __( 'It depends on the starting point, but most sites load noticeably faster. You get a before and after report.', 'nabia' ) ),
				array( __( 'Do you guarantee first place on Google?', 'nabia' ), __( 'Nobody honest can. I fix what holds your site back so it has the best chance to rank.', 'nabia' ) ),
			),
		),
		'website-maintenance'     => array(
			'headline' => __( 'Monthly website maintenance, so you never have to worry', 'nabia' ),
			'intro'    => __( 'Your website needs regular care to stay secure, fast and up to date. With a monthly plan I handle updates, backups, security and small changes, so you can focus on running your business.', 'nabia' ),
			'features' => array(
				array( __( 'Updates done safely', 'nabia' ), __( 'WordPress, theme and plugin updates tested so nothing breaks.', 'nabia' ) ),
				array( __( 'Backups', 'nabia' ), __( 'Regular off-site backups, so your site can be restored in minutes.', 'nabia' ) ),
				array( __( 'Security monitoring', 'nabia' ), __( 'Malware scans, firewall and login protection against hackers.', 'nabia' ) ),
				array( __( 'Uptime monitoring', 'nabia' ), __( 'I know when your site is down, often before you do.', 'nabia' ) ),
				array( __( 'Content edits', 'nabia' ), __( 'Text, image and page changes included every month.', 'nabia' ) ),
				array( __( 'Monthly report', 'nabia' ), __( 'A short, clear summary of everything that was done.', 'nabia' ) ),
			),
			'get'      => array( __( 'Peace of mind every month', 'nabia' ), __( 'Fast, personal support', 'nabia' ), __( 'Secure, updated website', 'nabia' ), __( 'Backups you can rely on', 'nabia' ), __( 'Edit hours included', 'nabia' ), __( 'Cancel any time', 'nabia' ) ),
			'for'      => array( __( 'Busy owners without time for updates', 'nabia' ), __( 'Online stores that cannot afford downtime', 'nabia' ), __( 'Anyone who was hacked before', 'nabia' ) ),
			'plans'    => 'care',
			'faq'      => array(
				array( __( 'Is there a contract?', 'nabia' ), __( 'No long contract. Plans are monthly and you can cancel any time.', 'nabia' ) ),
				array( __( 'Do you maintain sites you did not build?', 'nabia' ), __( 'Yes. I start with a quick check-up, then take over the care of your site.', 'nabia' ) ),
			),
		),
	);
	return apply_filters( 'nabia_service_details', $details );
}

/**
 * Full data for one service (card fields + details).
 *
 * @param string $slug Service slug.
 * @return array|null
 */
function nabia_get_service( $slug ) {
	foreach ( nabia_services() as $service ) {
		if ( isset( $service['slug'] ) && $service['slug'] === $slug ) {
			$details = nabia_service_details();
			return array_merge( $service, isset( $details[ $slug ] ) ? $details[ $slug ] : array() );
		}
	}
	return null;
}

/**
 * The service shown on the current page (from page meta, or the page slug).
 *
 * @return array|null
 */
function nabia_current_service() {
	$slug = get_post_meta( get_the_ID(), '_nabia_service', true );
	if ( ! $slug ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
	}
	return nabia_get_service( $slug );
}

/**
 * Permalink of the page that shows a service, if it has been created.
 *
 * @param string $slug Service slug.
 * @return string
 */
function nabia_service_url( $slug ) {
	static $map = null;
	if ( null === $map ) {
		$map   = array();
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'meta_key'       => '_nabia_service', // phpcs:ignore WordPress.DB.SlowDBQuery
				'no_found_rows'  => true,
			)
		);
		foreach ( $pages as $page ) {
			$map[ get_post_meta( $page->ID, '_nabia_service', true ) ] = get_permalink( $page );
		}
	}
	return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
}

/**
 * Permalink of a page created by the setup screen (services, pricing, audit).
 *
 * @param string $role Page role.
 * @return string
 */
function nabia_page_url( $role ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_nabia_page', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $role, // phpcs:ignore WordPress.DB.SlowDBQuery
			'no_found_rows'  => true,
		)
	);
	return $pages ? get_permalink( $pages[0] ) : '';
}
