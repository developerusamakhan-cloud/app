<?php
/**
 * SEO titles, meta descriptions and the /sitemap.xml address.
 *
 * Fills the SEO title and description of the main pages and articles once after a theme
 * update, but only where the field is still empty, so your own text in Rank Math or Yoast
 * is never replaced. Also answers /sitemap.xml with the SEO plugin's sitemap index, because
 * some SEO checkers only look at that exact address.
 *
 * @package Nabia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO title and description for pages and articles the setup does not create.
 *
 * Pages are keyed by their path, articles by their slug. A title that leaves room gets
 * " - Site name" added, so every title stays within about 60 characters.
 *
 * @return array { page: array( path => array( title, description ) ), post: array( slug => array( title, description ) ) }
 */
function nabia_extra_seo() {
	$seo = array(
		'page' => array(
			'about-me'   => array( __( 'About Me: WordPress Developer and Web Designer', 'nabia' ), __( 'Meet Nabia Khan, a WordPress developer and website designer who builds fast, easy-to-edit websites that help small businesses win more enquiries.', 'nabia' ) ),
			'blogs'      => array( __( 'Web Design, WordPress and SEO Blog', 'nabia' ), __( 'Practical guides on website design, WordPress, Shopify, SEO, website costs and AI tools for small business owners, in plain English.', 'nabia' ) ),
			'contact-me' => array( __( 'Contact Me: Website Design and WordPress Help', 'nabia' ), __( 'Get in touch about a new website, a redesign, WordPress fixes or monthly maintenance. Send a short message and I will reply with clear next steps.', 'nabia' ) ),
			'hire-me'    => array( __( 'Hire a Freelance WordPress Developer', 'nabia' ), __( 'Hire Nabia Khan as your freelance WordPress developer and web designer for new websites, redesigns, Shopify and WooCommerce stores and ongoing care.', 'nabia' ) ),
			'my-works'   => array( __( 'My Work: Website Design Portfolio', 'nabia' ), __( 'Browse websites I have designed and built for small businesses on WordPress, Shopify, WooCommerce and custom code.', 'nabia' ) ),
		),
		'post' => array(
			'ai-chatbot-for-website'             => array( __( 'AI Chatbot for Your Website: Costs and Setup Guide', 'nabia' ), '' ),
			'ai-website-builder-vs-web-designer' => array( __( 'AI Website Builder vs Web Designer: What AI Can Do', 'nabia' ), '' ),
			'church-website-design'              => array( __( 'Church Website Design for First-Time Visitors', 'nabia' ), '' ),
			'custom-website-development'         => array( __( 'Custom Website Development vs Templates', 'nabia' ), '' ),
			'hiring-a-wordpress-developer'       => array( __( 'Hiring a WordPress Developer: What to Ask and Costs', 'nabia' ), '' ),
			'how-much-does-a-logo-cost'          => array( __( 'How Much Does a Logo Cost? Prices Explained', 'nabia' ), '' ),
			'how-much-does-a-website-cost'       => array( __( 'How Much Does a Website Cost in 2026? Real Prices', 'nabia' ), '' ),
			'seo-for-small-business'             => array( __( 'SEO for Small Business: A Practical Guide', 'nabia' ), '' ),
			'shopify-vs-woocommerce'             => array( __( 'Shopify vs WooCommerce in 2026: Which Is Better?', 'nabia' ), '' ),
			'small-business-website-design'      => array( __( 'Small Business Website Design: 10 Must-Haves', 'nabia' ), '' ),
			'the-importance-of-core-software-the-backbone-of-every-digital-experience' => array( __( 'The Importance of Core Software in Digital Experiences', 'nabia' ), '' ),
			'website-maintenance-services'       => array( __( 'Website Maintenance Services: What They Include and Cost', 'nabia' ), '' ),
			'website-redesign'                   => array( __( 'Website Redesign: 9 Signs It Is Time and What It Costs', 'nabia' ), '' ),
			'website-speed-optimization'         => array( __( 'Website Speed Optimization: Why Your Site Is Slow', 'nabia' ), '' ),
			'why-i-chose-wordpress-for-my-clients-and-why-you-should-too' => array( __( 'Why I Chose WordPress for My Clients', 'nabia' ), '' ),
			'wix-vs-wordpress'                   => array( __( 'Wix vs WordPress: An Honest Comparison', 'nabia' ), '' ),
			'woocommerce-development'            => array( __( 'WooCommerce Development: What Your Store Needs', 'nabia' ), '' ),
			'shopify-development'                => array( __( 'Shopify Development: Themes, Apps and Custom Code', 'nabia' ), '' ),
		),
	);
	return apply_filters( 'nabia_extra_seo', $seo );
}

/**
 * Whether a saved SEO field is only a default pattern (not text someone wrote).
 *
 * @param int    $post_id Post ID.
 * @param string $value   Saved value.
 * @return bool
 */
function nabia_seo_field_is_default( $post_id, $value ) {
	$value = trim( (string) $value );
	$title = get_post_field( 'post_title', $post_id );
	$name  = get_bloginfo( 'name' );
	$defaults = array(
		'',
		'%title% %sep% %sitename%',
		'%%title%% %%sep%% %%sitename%%',
		'%title% | %sitename%',
		'%title% - %sitename%',
		$title,
		$title . ' %sep% %sitename%',
		$title . ' %%sep%% %%sitename%%',
		$title . ' | ' . $name,
		$title . ' - ' . $name,
	);
	return in_array( $value, $defaults, true );
}

/**
 * Save an SEO title and description on a page or article, only where the field is empty
 * or still holds a default pattern.
 *
 * @param int    $post_id     Post ID.
 * @param string $title       Title without the site name.
 * @param string $description Meta description ('' leaves it alone).
 * @return bool Whether anything was saved.
 */
function nabia_fill_seo_fields( $post_id, $title, $description ) {
	$room = 60 - strlen( ' - ' . get_bloginfo( 'name' ) );
	$fields = array();
	if ( '' !== $title ) {
		$fields['rank_math_title']    = strlen( $title ) <= $room ? $title . ' %sep% %sitename%' : $title;
		$fields['_yoast_wpseo_title'] = strlen( $title ) <= $room ? $title . ' %%sep%% %%sitename%%' : $title;
	}
	if ( '' !== $description ) {
		$fields['rank_math_description'] = $description;
		$fields['_yoast_wpseo_metadesc'] = $description;
	}
	$changed = false;
	foreach ( $fields as $key => $value ) {
		if ( nabia_seo_field_is_default( $post_id, get_post_meta( $post_id, $key, true ) ) ) {
			update_post_meta( $post_id, $key, $value );
			$changed = true;
		}
	}
	return $changed;
}

/**
 * Fill the extra pages and articles.
 *
 * @return int Number of items changed.
 */
function nabia_fill_extra_seo() {
	$seo    = nabia_extra_seo();
	$filled = 0;
	foreach ( $seo['page'] as $path => $meta ) {
		$page = get_page_by_path( $path );
		if ( $page && 'trash' !== $page->post_status ) {
			$filled += nabia_fill_seo_fields( $page->ID, $meta[0], $meta[1] ) ? 1 : 0;
		}
	}
	foreach ( $seo['post'] as $slug => $meta ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $post && 'trash' !== $post->post_status ) {
			$filled += nabia_fill_seo_fields( $post->ID, $meta[0], $meta[1] ) ? 1 : 0;
		}
	}
	return $filled;
}

/**
 * Run the fill once per theme version, the first time an editor opens the dashboard.
 */
function nabia_maybe_fill_seo() {
	if ( get_option( 'nabia_seo_fill_version' ) === NABIA_VERSION || ! current_user_can( 'edit_pages' ) || ! nabia_seo_plugin_active() ) {
		return;
	}
	nabia_setup_fill_seo();
	nabia_fill_extra_seo();
	update_option( 'nabia_seo_fill_version', NABIA_VERSION );
}
add_action( 'admin_init', 'nabia_maybe_fill_seo' );

/**
 * Serve /sitemap.xml as the Rank Math sitemap index (Rank Math only redirects it).
 * The rule is saved by the permalink refresh that runs after each theme update.
 */
function nabia_sitemap_xml_rule() {
	if ( class_exists( 'RankMath\Helper' ) && \RankMath\Helper::is_module_active( 'sitemap' ) ) {
		add_rewrite_rule( '^sitemap\.xml$', 'index.php?sitemap=1', 'top' );
	}
}
add_action( 'init', 'nabia_sitemap_xml_rule', 2 );

/**
 * Keep SEO titles within 60 characters: when " - Site name" pushes a title over, drop it.
 *
 * @param string $title Title from the SEO plugin.
 * @return string
 */
function nabia_trim_long_title( $title ) {
	$suffix = ' - ' . get_bloginfo( 'name' );
	if ( strlen( $title ) > 60 && substr( $title, -strlen( $suffix ) ) === $suffix ) {
		return substr( $title, 0, -strlen( $suffix ) );
	}
	return $title;
}
add_filter( 'rank_math/frontend/title', 'nabia_trim_long_title', 20 );
