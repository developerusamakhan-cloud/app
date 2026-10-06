<?php
/**
 * Service pages: detailed content per service, lookups and links.
 *
 * Pages are created from Appearance → Nabia Setup (Services + one page per service).
 * Each service page uses the "Service page" template and shows the content below.
 * Change any of it with the `nabia_service_details` filter in a child theme.
 *
 * The copy is written around the search terms people use most for each service
 * (US search data, spring and summer 2026), with direct answers in the FAQ so
 * Google and AI assistants can quote them.
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
			'headline' => __( 'Small business website design that turns visitors into clients', 'nabia' ),
			'intro'    => __( 'People decide in a few seconds whether your business looks trustworthy. I design custom websites for small businesses that look professional, load fast on phones and lead visitors to one clear next step: calling, booking or buying.', 'nabia' ),
			'features' => array(
				array( __( 'Custom website design', 'nabia' ), __( 'No recycled templates. Every layout is designed around your brand, your customers and the questions they ask before they buy.', 'nabia' ) ),
				array( __( 'Clickable prototype first', 'nabia' ), __( 'You click through your new website in Figma and we refine it together before any development starts.', 'nabia' ) ),
				array( __( 'Mobile-first layouts', 'nabia' ), __( 'Most small business visitors arrive on a phone, so every page is designed for small screens first and then scaled up.', 'nabia' ) ),
				array( __( 'Pages that convert', 'nabia' ), __( 'Clear headlines, honest proof like reviews and results, and calls to action placed where people are ready to act.', 'nabia' ) ),
				array( __( 'Accessible and readable', 'nabia' ), __( 'Good contrast, comfortable font sizes and keyboard-friendly menus, so nobody is left out.', 'nabia' ) ),
				array( __( 'Built on your platform', 'nabia' ), __( 'I build the design on WordPress, Shopify, Webflow, Wix, Squarespace or in custom code, whichever fits your budget.', 'nabia' ) ),
			),
			'get'      => array( __( 'Homepage and inner page designs', 'nabia' ), __( 'Mobile and desktop versions', 'nabia' ), __( 'Clickable Figma prototype', 'nabia' ), __( 'Style guide with colours and fonts', 'nabia' ), __( 'Two rounds of revisions', 'nabia' ), __( 'Design files you own', 'nabia' ) ),
			'for'      => array( __( 'Small businesses that need a professional first impression', 'nabia' ), __( 'Companies with an outdated website that needs a redesign', 'nabia' ), __( 'Brands that want a unique look, not a template', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'How much does small business website design cost?', 'nabia' ), sprintf(
					/* translators: 1-3: prices */
					__( 'My website packages start at %1$s for a starter site, %2$s for a business website and %3$s for an online store. Every price is fixed and agreed before work starts, so there are no surprise invoices.', 'nabia' ),
					nabia_price( 'plan_web_1' ),
					nabia_price( 'plan_web_2' ),
					nabia_price( 'plan_web_3' )
				) ),
				array( __( 'How long does it take to design a website?', 'nabia' ), __( 'A simple website takes about 1 to 2 weeks and a larger business website 2 to 3 weeks. The biggest factor is usually content, so I help you with text and images if you need it.', 'nabia' ) ),
				array( __( 'Do I get to see the design before it is built?', 'nabia' ), __( 'Yes. You get a clickable prototype and we refine it together before development starts.', 'nabia' ) ),
				array( __( 'Can you redesign my existing website?', 'nabia' ), __( 'Yes. I keep what already works, such as pages that rank on Google, and fix what holds you back. Old addresses are redirected so you do not lose traffic.', 'nabia' ) ),
			),
		),
		'wordpress-development'   => array(
			'headline' => __( 'WordPress developer for hire: fast, secure sites you can edit yourself', 'nabia' ),
			'intro'    => __( 'WordPress website design and development is my speciality. I build custom themes, clean Elementor sites and pixel-perfect rebuilds of designs you love, then hand them over so you can update text, images and posts without calling a developer.', 'nabia' ),
			'features' => array(
				array( __( 'Custom WordPress themes', 'nabia' ), __( 'Lightweight themes made for your site only, without the bloat of big multipurpose themes.', 'nabia' ) ),
				array( __( 'Elementor builds', 'nabia' ), __( 'Prefer drag and drop editing? I build tidy Elementor sites that stay fast.', 'nabia' ) ),
				array( __( 'Plugin setup and customisation', 'nabia' ), __( 'Bookings, memberships, forms and integrations configured and adjusted to how your business works.', 'nabia' ) ),
				array( __( 'Pixel-perfect rebuilds', 'nabia' ), __( 'Send me a Figma file or a site you love and I rebuild it accurately in WordPress.', 'nabia' ) ),
				array( __( 'Fixes, migrations and rescues', 'nabia' ), __( 'Moving hosts, fixing errors, cleaning hacked sites and finishing projects another developer left behind.', 'nabia' ) ),
				array( __( 'Payments and integrations', 'nabia' ), __( 'Stripe, PayPal, email marketing and CRM tools connected securely.', 'nabia' ) ),
			),
			'get'      => array( __( 'Fully responsive WordPress website', 'nabia' ), __( 'Speed and SEO basics set up', 'nabia' ), __( 'Security and backup plugins configured', 'nabia' ), __( 'Contact forms connected to your email', 'nabia' ), __( 'Video walkthrough of your dashboard', 'nabia' ), __( 'One month of free support', 'nabia' ) ),
			'for'      => array( __( 'Businesses that want to manage their own content', 'nabia' ), __( 'Blogs, portfolios and service websites', 'nabia' ), __( 'Anyone with a broken or slow WordPress site', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Why hire a freelance WordPress developer instead of an agency?', 'nabia' ), __( 'You talk directly to the person building your site, decisions are quicker and you do not pay for account managers. With 5+ years of experience and 300+ projects delivered, I handle design, development and support myself.', 'nabia' ) ),
				array( __( 'What does a WordPress developer do?', 'nabia' ), __( 'A WordPress developer builds and customises themes, sets up plugins, connects payments and forms, fixes errors and keeps the site fast and secure. A good one also makes the dashboard simple enough for you to use.', 'nabia' ) ),
				array( __( 'Will I be able to update the website myself?', 'nabia' ), __( 'Yes. Everything is set up so you can edit text, images and posts, and you get a video walkthrough.', 'nabia' ) ),
				array( __( 'Can you fix my existing WordPress site?', 'nabia' ), __( 'Yes. Send me the link and a short description and I will tell you what is wrong and what it costs to fix before I touch anything.', 'nabia' ) ),
			),
		),
		'shopify-woocommerce'     => array(
			'headline' => __( 'Shopify and WooCommerce developer for online stores that sell', 'nabia' ),
			'intro'    => __( 'Whether you sell ten products or a thousand, your store has to feel quick, trustworthy and easy to check out. I design and build Shopify stores and WooCommerce stores with clear product pages, secure payments and a checkout that does not scare buyers away.', 'nabia' ),
			'features' => array(
				array( __( 'Shopify store design', 'nabia' ), __( 'Theme setup and customisation, apps, collections and a checkout your customers trust.', 'nabia' ) ),
				array( __( 'WooCommerce development', 'nabia' ), __( 'Full control on WordPress with products, variations, shipping and taxes configured properly.', 'nabia' ) ),
				array( __( 'Product pages that sell', 'nabia' ), __( 'Sharp photos, clear pricing, reviews and trust badges in the places buyers look.', 'nabia' ) ),
				array( __( 'Payments and shipping', 'nabia' ), __( 'Stripe, PayPal, Apple Pay and local gateways, plus shipping zones and rates.', 'nabia' ) ),
				array( __( 'Product upload', 'nabia' ), __( 'I can upload and organise your products, categories and descriptions for you.', 'nabia' ) ),
				array( __( 'Email and marketing', 'nabia' ), __( 'Order emails, abandoned cart reminders and newsletter sign ups that bring buyers back.', 'nabia' ) ),
			),
			'get'      => array( __( 'Complete, ready-to-sell online store', 'nabia' ), __( 'Payment gateway connected and tested', 'nabia' ), __( 'Shipping and tax settings', 'nabia' ), __( 'Product upload (up to 30 included)', 'nabia' ), __( 'Order and customer email setup', 'nabia' ), __( 'Training video for managing orders', 'nabia' ) ),
			'for'      => array( __( 'Brands starting to sell online', 'nabia' ), __( 'Shops moving from Etsy or Instagram to their own store', 'nabia' ), __( 'Stores that need a redesign to convert better', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Shopify or WooCommerce: which is better?', 'nabia' ), __( 'Shopify is simpler to run and handles hosting and security for you, but has a monthly fee and app costs. WooCommerce runs on WordPress, has no platform fee and gives you full control, but needs good hosting and regular updates. I recommend one after a short chat about your products and budget.', 'nabia' ) ),
				array( __( 'How much does an online store cost?', 'nabia' ), sprintf(
					/* translators: %s: price */
					__( 'My online store package is %s and includes the store pages, payment setup and your first products uploaded. Larger catalogues or custom features are quoted separately, always as a fixed price.', 'nabia' ),
					nabia_price( 'plan_web_3' )
				) ),
				array( __( 'Can you move my store from another platform?', 'nabia' ), __( 'Yes. I migrate products, customers and orders to Shopify or WooCommerce and redirect old product links so you keep your Google rankings.', 'nabia' ) ),
			),
		),
		'wix-webflow-squarespace' => array(
			'headline' => __( 'Wix, Squarespace and Webflow website design, done properly', 'nabia' ),
			'intro'    => __( 'Wix, Squarespace and Webflow are great when you want to run your own site without code. The problem is that most sites built on them look like templates. I design and build custom sites on all three and hand over something you can edit yourself with confidence.', 'nabia' ),
			'features' => array(
				array( __( 'Wix website design', 'nabia' ), __( 'Custom designs, bookings, stores and member areas built in Wix and Wix Studio.', 'nabia' ) ),
				array( __( 'Squarespace website design', 'nabia' ), __( 'Elegant, simple sites for creatives, restaurants, coaches and service businesses.', 'nabia' ) ),
				array( __( 'Webflow development', 'nabia' ), __( 'Pixel-perfect, animated websites with a CMS your team can update.', 'nabia' ) ),
				array( __( 'Template customisation', 'nabia' ), __( 'Already have a template? I make it look and work like a custom site.', 'nabia' ) ),
				array( __( 'SEO and speed setup', 'nabia' ), __( 'Titles, descriptions, image optimisation and Google Search Console connected from day one.', 'nabia' ) ),
				array( __( 'Platform moves', 'nabia' ), __( 'Move from Wix, Squarespace or Webflow to WordPress, or the other way around, without losing content.', 'nabia' ) ),
			),
			'get'      => array( __( 'Custom-designed no-code website', 'nabia' ), __( 'Mobile layouts checked on every page', 'nabia' ), __( 'Forms, bookings or store set up', 'nabia' ), __( 'SEO basics and analytics', 'nabia' ), __( 'Handover call and video guide', 'nabia' ), __( 'One month of free support', 'nabia' ) ),
			'for'      => array( __( 'Owners who want to edit everything themselves', 'nabia' ), __( 'Creatives, coaches, cafés and small businesses', 'nabia' ), __( 'Anyone already using one of these platforms', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'Wix vs WordPress: which should I choose?', 'nabia' ), __( 'Wix is easier and includes hosting, which suits small sites you want to run yourself. WordPress is more flexible, better for blogs, larger sites and SEO heavy growth, and you own it outright. If you plan to grow content or sell a lot online, WordPress usually wins.', 'nabia' ) ),
				array( __( 'Which platform should I choose: Wix, Squarespace or Webflow?', 'nabia' ), __( 'Wix is the easiest, Squarespace looks the most polished out of the box, and Webflow is the most flexible for custom design and animation. Tell me your goals and I will recommend one honestly.', 'nabia' ) ),
				array( __( 'Do I pay the platform subscription?', 'nabia' ), __( 'Yes. The plan is in your name so you always own your website. I help you pick the right plan so you do not overpay.', 'nabia' ) ),
			),
		),
		'custom-websites'         => array(
			'headline' => __( 'Custom website development, hand-coded for speed and freedom', 'nabia' ),
			'intro'    => __( 'Sometimes a website builder is not enough. I hand-code websites in HTML, CSS, JavaScript and PHP when you need unique features, the best possible speed, or a design that no template can deliver.', 'nabia' ),
			'features' => array(
				array( __( 'Clean, modern code', 'nabia' ), __( 'Semantic HTML, modern CSS and lightweight JavaScript that any developer can maintain later.', 'nabia' ) ),
				array( __( 'Very fast pages', 'nabia' ), __( 'No page builder overhead, so pages load quickly and score well on Core Web Vitals.', 'nabia' ) ),
				array( __( 'Custom features', 'nabia' ), __( 'Calculators, dashboards, quote forms, APIs and whatever your business process needs.', 'nabia' ) ),
				array( __( 'Landing pages', 'nabia' ), __( 'Focused campaign pages built quickly for ads and launches.', 'nabia' ) ),
				array( __( 'Design to code', 'nabia' ), __( 'Figma, XD or PSD designs turned into responsive, pixel-perfect pages.', 'nabia' ) ),
				array( __( 'Simple hosting', 'nabia' ), __( 'Deployed to reliable hosting with SSL, backups and an easy update process.', 'nabia' ) ),
			),
			'get'      => array( __( 'Responsive hand-coded website', 'nabia' ), __( 'Source code you fully own', 'nabia' ), __( 'Performance and SEO optimisation', 'nabia' ), __( 'Cross-browser testing', 'nabia' ), __( 'Deployment and SSL setup', 'nabia' ), __( 'One month of free support', 'nabia' ) ),
			'for'      => array( __( 'Startups with unique product ideas', 'nabia' ), __( 'Marketing teams that need fast landing pages', 'nabia' ), __( 'Designers who need their Figma files built', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'When is custom website development worth it?', 'nabia' ), __( 'When you need features no plugin or builder offers, when speed is critical for ads or SEO, or when a template keeps getting in the way of your design. For a simple brochure site, WordPress or a builder is usually the better value.', 'nabia' ) ),
				array( __( 'Can I still edit a custom-coded site?', 'nabia' ), __( 'Yes. I can add a simple content editor or a headless CMS so you can change text and images yourself.', 'nabia' ) ),
				array( __( 'Is custom code more expensive?', 'nabia' ), __( 'Not always. Simple sites cost about the same as a builder site and are often faster and cheaper to host.', 'nabia' ) ),
			),
		),
		'ai-website-solutions'    => array(
			'headline' => __( 'AI chatbots and AI tools for your website, set up safely', 'nabia' ),
			'intro'    => __( 'An AI chatbot for your website can answer customers at 3am, qualify leads and take repetitive questions off your plate. I add practical, affordable AI features to your existing site, trained only on your own business information and always with a human one click away.', 'nabia' ),
			'features' => array(
				array( __( 'AI chatbot for your website', 'nabia' ), __( 'A friendly assistant that answers common questions from your own content and collects leads around the clock.', 'nabia' ) ),
				array( __( 'AI-assisted content', 'nabia' ), __( 'Blog posts, product descriptions and page copy drafted with AI, then edited by a human so it sounds like you.', 'nabia' ) ),
				array( __( 'Smart forms', 'nabia' ), __( 'Forms that qualify leads, route enquiries and send instant, personal replies.', 'nabia' ) ),
				array( __( 'Automations', 'nabia' ), __( 'Connect your site to email, CRM, bookings and spreadsheets so busywork happens by itself.', 'nabia' ) ),
				array( __( 'AI search', 'nabia' ), __( 'Help visitors find the right product or answer with natural-language search.', 'nabia' ) ),
				array( __( 'Privacy-minded setup', 'nabia' ), __( 'Clear limits, no sensitive data shared, and the bot always tells people they are talking to AI.', 'nabia' ) ),
			),
			'get'      => array( __( 'AI feature set up on your website', 'nabia' ), __( 'Trained on your pages, FAQs and documents', 'nabia' ), __( 'Branded design that matches your site', 'nabia' ), __( 'Lead capture sent to your inbox or CRM', 'nabia' ), __( 'Monthly usage overview', 'nabia' ), __( 'Tweaks during the first month', 'nabia' ) ),
			'for'      => array( __( 'Businesses answering the same questions every day', 'nabia' ), __( 'Stores with many products', 'nabia' ), __( 'Busy owners who want to save hours every week', 'nabia' ) ),
			'plans'    => '',
			'faq'      => array(
				array( __( 'Is an AI website builder enough, or do I need a web designer?', 'nabia' ), __( 'AI website builders are fine for a quick placeholder site. For a business that depends on its website for leads, you still need someone to plan the pages, write for real customers, set up SEO and make it convert. I use AI to work faster, but the thinking is human.', 'nabia' ) ),
				array( __( 'Will an AI chatbot say wrong things about my business?', 'nabia' ), __( 'It only answers from the information you approve, and hands over to you when it is not sure.', 'nabia' ) ),
				array( __( 'Does it work with my current website?', 'nabia' ), __( 'Yes. AI features can be added to WordPress, Shopify, Wix, Webflow, Squarespace and custom sites.', 'nabia' ) ),
				array( __( 'Which AI tools do you work with?', 'nabia' ), __( 'I work with leading models like ChatGPT, Claude and Gemini, plus tools such as Zapier and Make for automations. I pick what fits your budget and your needs, not whatever is trending this week.', 'nabia' ) ),
				array( __( 'How much does it cost to run AI on my website?', 'nabia' ), __( 'For most small businesses the monthly AI usage costs only a few dollars. I set sensible limits so there are never surprise bills, and I show you exactly where the costs come from.', 'nabia' ) ),
				array( __( 'Will AI replace the human touch with my customers?', 'nabia' ), __( 'Not at all. AI handles the quick, repetitive questions so you have more time for real conversations. Customers can always reach a real person.', 'nabia' ) ),
			),
		),
		'branding-graphic-design' => array(
			'headline' => __( 'Logo design services and brand identity people remember', 'nabia' ),
			'intro'    => __( 'Your logo, colours and visuals are often the first thing people notice. As a graphic designer I create logos and brand identity designs that look consistent everywhere: on your website, on social media and in print.', 'nabia' ),
			'features' => array(
				array( __( 'Custom logo design', 'nabia' ), __( 'Original logo concepts with versions for web, social media and print. No stock icons, no AI clip art.', 'nabia' ) ),
				array( __( 'Brand identity design', 'nabia' ), __( 'Colour palette, typography and visual style that tell your story and feel like you.', 'nabia' ) ),
				array( __( 'Social media kits', 'nabia' ), __( 'Post and story templates in Canva or Figma so your feed always looks on-brand.', 'nabia' ) ),
				array( __( 'Print design', 'nabia' ), __( 'Business cards, flyers, brochures, menus and packaging.', 'nabia' ) ),
				array( __( 'Website graphics', 'nabia' ), __( 'Banners, icons and illustrations that make your website stand out.', 'nabia' ) ),
				array( __( 'Brand guidelines', 'nabia' ), __( 'A short, simple guide so everyone uses your brand the right way.', 'nabia' ) ),
			),
			'get'      => array( __( 'Logo in all formats (SVG, PNG, PDF)', 'nabia' ), __( 'Colour and font guide', 'nabia' ), __( 'Social media templates', 'nabia' ), __( 'Business card design', 'nabia' ), __( 'Revisions until you love it', 'nabia' ), __( 'Full ownership of all files', 'nabia' ) ),
			'for'      => array( __( 'New businesses and startups', 'nabia' ), __( 'Brands that feel outdated or inconsistent', 'nabia' ), __( 'Anyone launching a new website', 'nabia' ) ),
			'plans'    => '',
			'faq'      => array(
				array( __( 'How much does a logo cost?', 'nabia' ), __( 'It depends on how much research and how many brand pieces you need. A logo on its own costs far less than a full brand identity with guidelines and templates. Tell me what you need and you get a fixed quote within 24 hours.', 'nabia' ) ),
				array( __( 'How many logo concepts do I get?', 'nabia' ), __( 'Usually three different directions, then we refine your favourite together.', 'nabia' ) ),
				array( __( 'Do I own the logo files?', 'nabia' ), __( 'Yes. You receive every file (SVG, PNG and PDF) and full ownership once the project is paid.', 'nabia' ) ),
				array( __( 'Can you design my website and branding together?', 'nabia' ), __( 'Yes, and it is the best way to get a consistent look. Ask for a combined quote.', 'nabia' ) ),
			),
		),
		'speed-seo'               => array(
			'headline' => __( 'Website speed optimization and SEO for small businesses', 'nabia' ),
			'intro'    => __( 'A slow website loses visitors, sales and Google rankings. I speed up your site, fix technical SEO issues and set up the basics so search engines and AI assistants understand your pages and customers can find you.', 'nabia' ),
			'features' => array(
				array( __( 'Core Web Vitals', 'nabia' ), __( 'Faster loading, stable layouts and quick responses to taps, measured before and after.', 'nabia' ) ),
				array( __( 'Image optimisation', 'nabia' ), __( 'Modern formats, correct sizes and lazy loading without losing quality.', 'nabia' ) ),
				array( __( 'Caching and CDN', 'nabia' ), __( 'Smart caching and content delivery so pages load fast for visitors everywhere.', 'nabia' ) ),
				array( __( 'On-page SEO', 'nabia' ), __( 'Titles, descriptions, headings and internal links written for the terms your customers search.', 'nabia' ) ),
				array( __( 'Technical SEO', 'nabia' ), __( 'Sitemaps, schema markup, redirects and indexing issues fixed.', 'nabia' ) ),
				array( __( 'Google tools', 'nabia' ), __( 'Search Console, Analytics and your Google Business Profile connected so you can see results.', 'nabia' ) ),
			),
			'get'      => array( __( 'Before and after speed report', 'nabia' ), __( 'Technical SEO fixes', 'nabia' ), __( 'Optimised images and caching', 'nabia' ), __( 'Search Console and Analytics setup', 'nabia' ), __( 'Keyword-ready page titles', 'nabia' ), __( 'Plain-English summary of changes', 'nabia' ) ),
			'for'      => array( __( 'Websites that feel slow on mobile', 'nabia' ), __( 'Small businesses invisible on Google', 'nabia' ), __( 'Stores losing sales to slow pages', 'nabia' ) ),
			'plans'    => '',
			'faq'      => array(
				array( __( 'Why is my website slow?', 'nabia' ), __( 'The usual causes are oversized images, too many plugins or apps, cheap shared hosting, no caching and heavy page builders. A free audit shows which of these applies to your site.', 'nabia' ) ),
				array( __( 'How much faster will my site be?', 'nabia' ), __( 'It depends on the starting point, but most sites load noticeably faster. You get a before and after report so you can see the difference.', 'nabia' ) ),
				array( __( 'Do you guarantee first place on Google?', 'nabia' ), __( 'Nobody honest can. I fix what holds your site back so it has the best chance to rank.', 'nabia' ) ),
			),
		),
		'website-maintenance'     => array(
			'headline' => __( 'Website maintenance services, so you never have to worry', 'nabia' ),
			'intro'    => sprintf(
				/* translators: %s: price */
				__( 'Every website needs regular care to stay secure, fast and working. My WordPress maintenance plans cover updates, backups, security, uptime checks and small edits every month, from %s, so you can focus on running your business.', 'nabia' ),
				nabia_price( 'plan_care_1' )
			),
			'features' => array(
				array( __( 'Safe updates', 'nabia' ), __( 'WordPress, theme and plugin updates tested first, so nothing breaks on your live site.', 'nabia' ) ),
				array( __( 'Off-site backups', 'nabia' ), __( 'Regular backups stored away from your server, so your site can be restored in minutes.', 'nabia' ) ),
				array( __( 'Security monitoring', 'nabia' ), __( 'Malware scans, firewall and login protection against hackers and spam.', 'nabia' ) ),
				array( __( 'Uptime monitoring', 'nabia' ), __( 'I know when your site is down, often before you do.', 'nabia' ) ),
				array( __( 'Content edits', 'nabia' ), __( 'Text, image and page changes included every month.', 'nabia' ) ),
				array( __( 'Monthly report', 'nabia' ), __( 'A short, clear summary of everything that was done.', 'nabia' ) ),
			),
			'get'      => array( __( 'Peace of mind every month', 'nabia' ), __( 'Fast, personal support', 'nabia' ), __( 'Secure, updated website', 'nabia' ), __( 'Backups you can rely on', 'nabia' ), __( 'Edit hours included', 'nabia' ), __( 'Cancel any time', 'nabia' ) ),
			'for'      => array( __( 'Busy owners without time for updates', 'nabia' ), __( 'Online stores that cannot afford downtime', 'nabia' ), __( 'Anyone who was hacked before', 'nabia' ) ),
			'plans'    => 'care',
			'faq'      => array(
				array( __( 'What do website maintenance services include?', 'nabia' ), __( 'Core, theme and plugin updates, off-site backups, security scans, uptime monitoring, speed checks, bug fixes and small content edits, plus a monthly report of what was done.', 'nabia' ) ),
				array( __( 'How much does website maintenance cost?', 'nabia' ), sprintf(
					/* translators: 1-3: prices */
					__( 'My care plans cost %1$s, %2$s or %3$s per month depending on how many support days you need. There is no long contract.', 'nabia' ),
					nabia_price( 'plan_care_1' ),
					nabia_price( 'plan_care_2' ),
					nabia_price( 'plan_care_3' )
				) ),
				array( __( 'Is there a contract?', 'nabia' ), __( 'No long contract. Plans are monthly and you can cancel any time.', 'nabia' ) ),
				array( __( 'Do you maintain sites you did not build?', 'nabia' ), __( 'Yes. I start with a quick check-up, then take over the care of your site.', 'nabia' ) ),
			),
		),
		'church-websites'         => array(
			'headline' => __( 'Church website design and nonprofit websites that welcome people in', 'nabia' ),
			'intro'    => __( 'Most people visit your website before they ever visit your church or charity. I design church and nonprofit websites that answer first-time visitor questions in seconds, make giving and volunteering simple, and are easy for staff and volunteers to keep up to date.', 'nabia' ),
			'features' => array(
				array( __( 'Plan your visit', 'nabia' ), __( 'Service times, location, parking, kids and what to expect, right where first-time visitors look.', 'nabia' ) ),
				array( __( 'Sermons and media', 'nabia' ), __( 'A sermon library by series, speaker and topic, with video or audio and the newest message first.', 'nabia' ) ),
				array( __( 'Online giving and donations', 'nabia' ), __( 'A trusted giving platform with one clear button on every page, suggested amounts and monthly giving.', 'nabia' ) ),
				array( __( 'Events, groups and volunteers', 'nabia' ), __( 'A simple calendar, small groups and serving opportunities with sign-up forms that reach the right person.', 'nabia' ) ),
				array( __( 'Easy for volunteers', 'nabia' ), __( 'Add a sermon, event or news post in minutes without breaking the design, with a video guide for your team.', 'nabia' ) ),
				array( __( 'Found on Google', 'nabia' ), __( 'Clear page titles, Google Business Profile tips and fast mobile pages, so people searching nearby can find you.', 'nabia' ) ),
			),
			'get'      => array( __( 'Church or nonprofit website, mobile-first', 'nabia' ), __( 'Plan your visit and about pages', 'nabia' ), __( 'Sermons, events or programmes set up', 'nabia' ), __( 'Online giving or donation page connected', 'nabia' ), __( 'Video guide for staff and volunteers', 'nabia' ), __( 'One month of free support', 'nabia' ) ),
			'for'      => array( __( 'Churches and ministries of every size', 'nabia' ), __( 'Charities and community nonprofits', 'nabia' ), __( 'Church plants that need a first website', 'nabia' ) ),
			'plans'    => 'web',
			'faq'      => array(
				array( __( 'What should a church website include?', 'nabia' ), __( 'Service times and location, a Plan your visit page, kids and youth information, beliefs, staff, sermons, events, online giving and an easy way to contact you, all working well on phones.', 'nabia' ) ),
				array(
					__( 'How much does a church or nonprofit website cost?', 'nabia' ),
					sprintf(
						/* translators: 1-2: prices */
						__( 'My website packages start at %1$s, and most church and nonprofit sites fit the %2$s business package. You get a fixed price before work starts.', 'nabia' ),
						nabia_price( 'plan_web_1' ),
						nabia_price( 'plan_web_2' )
					),
				),
				array( __( 'Can our volunteers update the website?', 'nabia' ), __( 'Yes. The site is built so volunteers can add sermons, events and news in minutes, and everyone gets a short video guide.', 'nabia' ) ),
				array( __( 'Can you set up online giving?', 'nabia' ), __( 'Yes. I connect a trusted giving or donation platform and add a clear Give button on every page, with suggested amounts and monthly giving.', 'nabia' ) ),
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
	$slug = get_query_var( 'nabia_service' );
	if ( ! $slug ) {
		$slug = get_post_meta( get_the_ID(), '_nabia_service', true );
	}
	if ( ! $slug ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
	}
	return nabia_get_service( $slug );
}

/**
 * Other addresses a service page may have on your site (for pages you made yourself).
 *
 * @return array slug => alternative slugs.
 */
function nabia_service_aliases() {
	return apply_filters(
		'nabia_service_aliases',
		array(
			'website-maintenance'   => array( 'monthly-website-maintenance', 'care-maintenance', 'care-and-maintenance', 'website-care', 'maintenance' ),
			'speed-seo'             => array( 'speed-and-seo', 'speed-seo-optimization', 'seo' ),
			'shopify-woocommerce'   => array( 'shopify-and-woocommerce', 'shopify-woocommerce-stores', 'ecommerce' ),
			'wix-webflow-squarespace' => array( 'wix-webflow-and-squarespace' ),
			'custom-websites'       => array( 'custom-coded-websites', 'custom-website-development' ),
			'church-websites'       => array( 'church-website-design', 'church-and-nonprofit-websites', 'church-nonprofit-websites' ),
			'branding-graphic-design' => array( 'branding-and-graphic-design', 'branding' ),
			'ai-website-solutions'  => array( 'ai-solutions' ),
			'wordpress-development' => array( 'wordpress-developer' ),
			'web-design'            => array( 'website-design' ),
		)
	);
}

/**
 * Your own published page for a service, found by its address (services/slug, slug or an alias).
 *
 * @param string $slug Service slug.
 * @return WP_Post|null
 */
function nabia_service_page_by_path( $slug ) {
	$aliases = nabia_service_aliases();
	$names   = array_merge( array( $slug ), isset( $aliases[ $slug ] ) ? $aliases[ $slug ] : array() );
	foreach ( $names as $name ) {
		foreach ( array( 'services/' . $name, $name ) as $path ) {
			$page = get_page_by_path( $path );
			if ( $page && 'publish' === $page->post_status ) {
				return $page;
			}
		}
	}
	return null;
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
		// Pages you created yourself with a "Service: …" template.
		$templated = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'page-templates/service-', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_compare'   => 'LIKE',
				'no_found_rows'  => true,
			)
		);
		foreach ( $templated as $page ) {
			$template = get_post_meta( $page->ID, '_wp_page_template', true );
			if ( preg_match( '#page-templates/service-([a-z0-9-]+)\.php$#', $template, $m ) && ! isset( $map[ $m[1] ] ) ) {
				$map[ $m[1] ] = get_permalink( $page );
			}
		}
	}
	if ( ! isset( $map[ $slug ] ) ) {
		// A page you made yourself at the usual address or a common alternative.
		$page         = nabia_service_page_by_path( $slug );
		$map[ $slug ] = $page ? get_permalink( $page ) : '';
	}
	return $map[ $slug ];
}

/**
 * Permalink of a page created by the setup screen (services, pricing, audit).
 *
 * @param string $role Page role.
 * @return string
 */
function nabia_page_url( $role ) {
	static $cache = array();
	if ( isset( $cache[ $role ] ) ) {
		return $cache[ $role ];
	}
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
	if ( ! $pages ) {
		// Or a page you created yourself with the matching template.
		$templates = array(
			'services' => 'template-services.php',
			'pricing'  => 'template-pricing.php',
			'audit'    => 'template-audit.php',
			'about'     => 'template-about.php',
			'contact'   => 'template-contact.php',
			'portfolio' => 'template-portfolio.php',
			'sitemap'   => 'template-sitemap.php',
		);
		if ( isset( $templates[ $role ] ) ) {
			$pages = get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'     => $templates[ $role ], // phpcs:ignore WordPress.DB.SlowDBQuery
					'no_found_rows'  => true,
				)
			);
		}
	}
	$cache[ $role ] = $pages ? get_permalink( $pages[0] ) : '';
	return $cache[ $role ];
}

/**
 * Blog address: the Posts page, or the homepage when it lists posts.
 *
 * @return string
 */
function nabia_blog_url() {
	$page = (int) get_option( 'page_for_posts' );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * Sitemap address: an HTML "Sitemap" page if you made one, otherwise the XML sitemap
 * of Rank Math / Yoast (sitemap_index.xml) or WordPress itself (wp-sitemap.xml).
 *
 * @param bool $xml Force the XML sitemap.
 * @return string
 */
function nabia_sitemap_url( $xml = false ) {
	if ( ! $xml ) {
		$page = nabia_page_url( 'sitemap' );
		if ( $page ) {
			return $page;
		}
	}
	if ( defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return home_url( '/sitemap_index.xml' );
	}
	return home_url( '/wp-sitemap.xml' );
}
