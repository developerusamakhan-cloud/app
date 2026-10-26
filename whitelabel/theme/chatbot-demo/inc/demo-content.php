<?php
/**
 * Demo pages: created when the theme is activated, so the chatbot has a website to learn from.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo pages keyed by slug: title, excerpt and block content.
 *
 * @return array<string, array{0:string,1:string,2:string}>
 */
function chatbot_demo_pages() {
	$p  = static function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$h  = static function ( $text ) {
		return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . $text . "</h2>\n<!-- /wp:heading -->\n\n";
	};
	$h3 = static function ( $text ) {
		return "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . $text . "</h3>\n<!-- /wp:heading -->\n\n";
	};
	$ul = static function ( array $items ) {
		$li = '';
		foreach ( $items as $item ) {
			$li .= "<!-- wp:list-item -->\n<li>" . $item . "</li>\n<!-- /wp:list-item -->\n";
		}
		return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . $li . "</ul>\n<!-- /wp:list -->\n\n";
	};
	$ol = static function ( array $items ) {
		$li = '';
		foreach ( $items as $item ) {
			$li .= "<!-- wp:list-item -->\n<li>" . $item . "</li>\n<!-- /wp:list-item -->\n";
		}
		return "<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\">" . $li . "</ol>\n<!-- /wp:list -->\n\n";
	};

	return array(
		'home'             => array(
			'Home',
			'An AI chatbot that knows your website.',
			$p( 'This website is a live demo of an AI chatbot for WordPress websites. The chat reads the pages of a website and answers visitors from them, in their own language, and collects leads from interested visitors.' ),
		),
		'features'         => array(
			'Features',
			'Everything the chatbot does, in plain words.',
			$p( 'The chatbot is a WordPress plugin. Once installed, it adds a chat to your website that answers visitors using the content of your own pages, posts and products.' )
			. $h( 'Learns your website in one click' )
			. $p( 'Press one button and the chatbot reads every published page, post and WooCommerce product, including content built with Elementor and the block editor. When you edit or publish content, it updates by itself. Password protected, private and draft content is never included.' )
			. $h( 'Answers in the visitor\'s language' )
			. $p( 'Visitors can write in English, Urdu, Hindi, Arabic, Spanish, French, German, Turkish and many other languages. The chatbot replies in the same language, even when your website is written in only one language.' )
			. $h( 'Shows the sources behind each answer' )
			. $p( 'Under an answer the chat can list the pages it used, with links, so visitors can read more.' )
			. $h( 'Lead capture' )
			. $p( 'The chatbot answers first. After a few real questions, or sooner when a visitor asks for a call back, it offers to take their details. The form can ask for name, email, phone and a message, with a consent checkbox. Leads are saved in your WordPress database, emailed to you and can be exported as a CSV file. Spam is blocked with a hidden field, a rate limit and an optional Cloudflare Turnstile check.' )
			. $h( 'Conversation log' )
			. $p( 'Every conversation is saved so you can read what visitors ask. It is the quickest way to find the questions your website does not answer yet.' )
			. $h( 'Design that matches your brand' )
			. $ul(
				array(
					'Brand colour, with text colour picked automatically for good contrast.',
					'Chat on the left or right of the screen.',
					'Launcher icon and avatar, including your own image.',
					'Assistant name, welcome message and suggested questions.',
					'A live preview while you change settings.',
				)
			)
			. $h( 'AI services' )
			. $p( 'On WordPress 7.0 and later the chatbot can use the AI connection set up for the whole site under Settings, Connectors. It also works with its own keys for Groq, OpenRouter, Google Gemini and Cloudflare Workers AI, and with OpenAI, Anthropic Claude, Mistral and DeepSeek. You can add more than one; if one service fails or reaches its limit, the next one answers. If none answers, the chat replies with the most relevant parts of your pages.' )
			. $h( 'Extra knowledge and custom answers' )
			. $p( 'Besides your pages, the chatbot can learn from PDF and DOCX files, other web pages and answers you write yourself for questions you get often.' )
			. $h( 'Analytics and unanswered questions' )
			. $p( 'See how many chats and leads you get, which questions come up most, and a list of questions the chat could not answer, so you can add the answer once.' )
			. $h( 'WooCommerce shops' )
			. $p( 'In a shop the chat can show product cards with an Add to cart button, and customers can check their order status with their order number and billing email.' )
			. $h( 'Lead alerts, greetings and business hours' )
			. $p( 'New leads can also be sent to Slack or Telegram. The chat can greet visitors with a short message after a few seconds or when they scroll, and outside your business hours it shows an away status and collects contact details.' )
			. $h( 'Placement' )
			. $p( 'The chat floats in the corner of every page by default. You can also place it inside a page with the [aisa_chat] shortcode or the AI Chat block, and choose the pages where it shows.' ),
		),
		'how-it-works'     => array(
			'How to add it to your website',
			'From install to a working chat in about five minutes.',
			$p( 'You need a WordPress website (version 6.4 or newer) running PHP 8.0 or newer, and permission to install plugins.' )
			. $h( 'Steps' )
			. $ol(
				array(
					'In WordPress, go to Plugins, Add New, Upload Plugin. Choose the plugin zip file, click Install Now, then Activate.',
					'The setup wizard opens. Connect an AI service: on WordPress 7.0 and later use the connection under Settings, Connectors, or paste a key from Groq, OpenRouter, Google Gemini or Cloudflare. Click Test to check it works.',
					'Click Scan to let the chatbot read your website.',
					'Choose your colour, welcome message and suggested questions.',
					'Turn the chat on and open your website to try it.',
				)
			)
			. $h( 'Change the settings later' )
			. $p( 'Everything is in the chatbot menu in the WordPress admin side menu: AI connection, knowledge, appearance, messages, leads, conversations and privacy.' )
			. $h( 'Need help?' )
			. $p( 'We can install and set up the chatbot for you. See the Contact page.' ),
		),
		'use-cases'        => array(
			'Who it is for',
			'Examples of how businesses use the chatbot.',
			$h3( 'Service businesses' )
			. $p( 'Agencies, clinics, consultants, schools and repair services use it to answer questions about services, opening hours and booking, and to collect enquiries outside office hours.' )
			. $h3( 'Online shops' )
			. $p( 'WooCommerce shops use it to answer questions about products, delivery and returns from their own product pages and policies.' )
			. $h3( 'Real estate and travel' )
			. $p( 'It answers questions about listings and packages published on the website, and takes the visitor\'s details for a call back.' )
			. $h3( 'Websites with visitors from many countries' )
			. $p( 'Visitors ask in their own language and get an answer in the same language, without a separate website for each language.' )
			. $h3( 'Web designers and agencies' )
			. $p( 'Agencies add the chatbot to client websites under their own name, logo and colours.' ),
		),
		'limits'           => array(
			'What it can and cannot do',
			'An honest list, so you know what to expect before you add it to your website.',
			$h( 'What it can do' )
			. $ul(
				array(
					'Answer visitor questions from your published pages, posts, products, and the files and custom answers you add.',
					'Reply in the visitor\'s language and show the pages behind each answer.',
					'Collect leads (name, email, phone, message) and send them to your email, Slack or Telegram.',
					'Show WooCommerce product cards with an Add to cart button and look up order status.',
					'Greet visitors with a short message, and show an away status outside business hours.',
					'Show a "Talk to a person" button that opens a link you choose, such as WhatsApp or your contact page.',
				)
			)
			. $h( 'What it cannot do' )
			. $ul(
				array(
					'It does not search the internet. It only knows your website and the files and answers you give it.',
					'It does not read password protected, private or draft pages.',
					'It does not read text inside images, videos or scanned PDF files. PDF and DOCX files need real text.',
					'It does not take payments or complete orders. Visitors finish checkout themselves.',
					'It does not book appointments into a calendar. It collects the visitor\'s details so you can confirm.',
					'It is not a live chat. A person cannot type into the conversation; the "Talk to a person" button sends the visitor to your link instead.',
					'It does not run on WhatsApp, Facebook Messenger or Instagram. It works on your WordPress website.',
					'It needs a connected AI service to write answers. Without one, it replies with the most relevant parts of your pages.',
				)
			)
			. $h( 'Good to know' )
			. $p( 'Like every AI tool, it can sometimes get an answer wrong, especially when your website does not cover the topic. It shows its sources so visitors can check, and the list of unanswered questions shows you what to add.' ),
		),
		'faq'              => array(
			'FAQ',
			'Answers to common questions about the chatbot.',
			$h3( 'Do I have to write the answers?' )
			. $p( 'No. The chatbot answers from the content already on your website. You can add instructions, for example the tone to use, topics to avoid, or a message to show when it does not know.' )
			. $h3( 'What happens when it does not know the answer?' )
			. $p( 'It says it does not know instead of guessing, and can offer to take the visitor\'s details so you can reply.' )
			. $h3( 'Does it work with WooCommerce and Elementor?' )
			. $p( 'Yes. It reads WooCommerce products and pages built with Elementor or the block editor.' )
			. $h3( 'Will it slow down my website?' )
			. $p( 'No noticeable effect. The chat loads after the page, and it only contacts the AI service when a visitor sends a message.' )
			. $h3( 'Does it work on mobile?' )
			. $p( 'Yes. On phones the chat opens full screen.' )
			. $h3( 'Can I change how it looks?' )
			. $p( 'Yes. Colour, position, icon, avatar, name, welcome message and suggested questions can all be changed, with a live preview.' )
			. $h3( 'Can I put it inside a page instead of the corner?' )
			. $p( 'Yes. Use the [aisa_chat] shortcode or the AI Chat block.' ),
		),
		'privacy-and-data' => array(
			'Privacy and data',
			'Where conversations and leads are stored and who sees them.',
			$p( 'The chatbot stores conversations, leads and the text it read from your website in your own WordPress database. Nothing is stored on our servers.' )
			. $p( 'When a visitor sends a message, the message, the recent conversation and the relevant text from the website are sent to the AI service you connected, so it can write the answer. That service\'s own privacy policy applies.' )
			. $h( 'Privacy tools' )
			. $ul(
				array(
					'Works with the WordPress personal data export and erase tools.',
					'Automatic deletion of old conversations after the number of days you choose.',
					'A privacy note under the chat, written by you.',
					'An option to delete all chatbot data when the plugin is deleted.',
				)
			),
		),
		'contact'          => array(
			'Contact',
			'Want this chatbot on your website? Get in touch.',
			$p( 'We install and set up the chatbot on your WordPress website, connect the AI service, scan your content and match the chat to your brand.' )
			. $p( 'The quickest way to reach us is the chat on this page: ask your question, and if you want a call back, leave your details in the chat.' )
			. "<!-- wp:shortcode -->\n[chatbot_demo_contact]\n<!-- /wp:shortcode -->\n",
		),
	);
}

/**
 * Create the demo pages that do not exist yet, the menu and the static front page.
 *
 * @return int Number of pages created.
 */
function chatbot_demo_install() {
	$created = 0;
	$ids     = array();
	foreach ( chatbot_demo_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = (int) $existing->ID;
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $page[0],
				'post_excerpt' => $page[1],
				'post_content' => $page[2],
				'menu_order'   => count( $ids ),
			),
			true
		);
		if ( ! is_wp_error( $id ) ) {
			$ids[ $slug ] = (int) $id;
			++$created;
		}
	}

	if ( ! empty( $ids['home'] ) && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) ) {
		$menu_name = __( 'Demo menu', 'chatbot-demo' );
		$menu      = wp_get_nav_menu_object( $menu_name );
		$menu_id   = $menu ? (int) $menu->term_id : wp_create_nav_menu( $menu_name );
		if ( ! is_wp_error( $menu_id ) && ! $menu ) {
			foreach ( array( 'features', 'how-it-works', 'limits', 'faq', 'contact' ) as $slug ) {
				if ( empty( $ids[ $slug ] ) ) {
					continue;
				}
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-object-id' => $ids[ $slug ],
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
		}
		if ( ! is_wp_error( $menu_id ) ) {
			$locations['primary'] = $menu_id;
			$locations['footer']  = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	// Demo questions under the chat, only while the plugin still has its own defaults.
	if ( class_exists( 'Aisa_Settings' ) ) {
		$defaults = Aisa_Settings::defaults();
		if ( (string) Aisa_Settings::get( 'suggested_questions' ) === (string) $defaults['suggested_questions'] ) {
			Aisa_Settings::update( array( 'suggested_questions' => "What can this chatbot do?\nHow do I add it to my website?\nWhich languages does it speak?" ) );
		}
	}

	update_option( 'chatbot_demo_installed', 1, false );
	return $created;
}

/**
 * On first activation, add the demo pages.
 *
 * @return void
 */
function chatbot_demo_on_switch() {
	if ( ! get_option( 'chatbot_demo_installed' ) ) {
		chatbot_demo_install();
	}
}
add_action( 'after_switch_theme', 'chatbot_demo_on_switch' );

/**
 * Appearance > Demo content screen.
 *
 * @return void
 */
function chatbot_demo_admin_menu() {
	add_theme_page( __( 'Demo content', 'chatbot-demo' ), __( 'Demo content', 'chatbot-demo' ), 'edit_theme_options', 'chatbot-demo', 'chatbot_demo_admin_page' );
}
add_action( 'admin_menu', 'chatbot_demo_admin_menu' );

/**
 * Demo content screen.
 *
 * @return void
 */
function chatbot_demo_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>' . esc_html__( 'Demo content', 'chatbot-demo' ) . '</h1>';
	if ( isset( $_GET['created'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		/* translators: %d: number of pages */
		echo '<div class="notice notice-success"><p>' . esc_html( sprintf( _n( '%d page created.', '%d pages created.', absint( $_GET['created'] ), 'chatbot-demo' ), absint( $_GET['created'] ) ) ) . '</p></div>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	}
	echo '<p>' . esc_html__( 'The theme adds these pages so the chatbot has a website to learn from. Pages that already exist are left as they are.', 'chatbot-demo' ) . '</p><ul style="list-style:disc;padding-left:20px">';
	foreach ( chatbot_demo_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug );
		echo '<li>' . esc_html( $page[0] ) . ' &middot; ' . ( $existing ? '<a href="' . esc_url( get_permalink( $existing ) ) . '">' . esc_html__( 'exists', 'chatbot-demo' ) . '</a>' : esc_html__( 'missing', 'chatbot-demo' ) ) . '</li>';
	}
	echo '</ul><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'chatbot_demo_install' );
	echo '<input type="hidden" name="action" value="chatbot_demo_install">';
	submit_button( __( 'Create missing pages', 'chatbot-demo' ) );
	echo '</form><p>' . esc_html__( 'After creating pages, open the chatbot plugin and run the website scan again so it learns them.', 'chatbot-demo' ) . '</p>';
	echo '<p>' . esc_html__( 'Front page texts, accent colour and contact details: Appearance, Customize, Chatbot demo.', 'chatbot-demo' ) . '</p></div>';
}

/**
 * Handle "Create missing pages".
 *
 * @return void
 */
function chatbot_demo_handle_install() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'chatbot-demo' ) );
	}
	check_admin_referer( 'chatbot_demo_install' );
	$created = chatbot_demo_install();
	wp_safe_redirect( admin_url( 'themes.php?page=chatbot-demo&created=' . $created ) );
	exit;
}
add_action( 'admin_post_chatbot_demo_install', 'chatbot_demo_handle_install' );

/**
 * [chatbot_demo_contact]: email and phone from the Customizer.
 *
 * @return string
 */
function chatbot_demo_contact_shortcode() {
	$email = sanitize_email( chatbot_demo_mod( 'chatbot_demo_email', (string) get_option( 'admin_email' ) ) );
	$phone = chatbot_demo_mod( 'chatbot_demo_phone' );
	$out   = '<ul class="contact-list">';
	if ( $email ) {
		$out .= '<li>' . esc_html__( 'Email:', 'chatbot-demo' ) . ' <a href="' . esc_url( 'mailto:' . antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a></li>';
	}
	if ( '' !== $phone ) {
		$out .= '<li>' . esc_html__( 'Phone or WhatsApp:', 'chatbot-demo' ) . ' <a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a></li>';
	}
	return $out . '</ul>';
}
add_shortcode( 'chatbot_demo_contact', 'chatbot_demo_contact_shortcode' );
