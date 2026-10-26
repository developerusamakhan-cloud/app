<?php
/**
 * The one-page content. The same texts are shown on the front page and saved into the
 * Home page, so the chatbot learns exactly what visitors read.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

/**
 * All texts of the front page.
 *
 * @return array<string, mixed>
 */
function chatbot_demo_content() {
	return array(
		'title'     => __( 'An AI chatbot that knows your website', 'chatbot-demo' ),
		'intro'     => __( 'It reads your pages, posts, products and files, answers visitors in their own language, shows where each answer comes from, and turns interested visitors into leads. This website is a live demo: ask the chat anything about it.', 'chatbot-demo' ),
		'features'  => array(
			array( 'scan', __( 'Learns your website in one click', 'chatbot-demo' ), __( 'It reads your published pages, posts, WooCommerce products and Elementor content, and updates itself when you edit them.', 'chatbot-demo' ) ),
			array( 'file', __( 'Learns from files and other pages', 'chatbot-demo' ), __( 'Add PDF and DOCX files, pages from other websites, and your own answers to questions you get often.', 'chatbot-demo' ) ),
			array( 'globe', __( 'Speaks your visitor\'s language', 'chatbot-demo' ), __( 'Visitors ask in English, Spanish, French, German, Portuguese, Arabic, Chinese and many other languages and get the answer in the same language.', 'chatbot-demo' ) ),
			array( 'link', __( 'Shows its sources', 'chatbot-demo' ), __( 'Each answer can list the pages it came from, so visitors can read more and trust what they see.', 'chatbot-demo' ) ),
			array( 'search', __( 'Understands meaning', 'chatbot-demo' ), __( 'Smart search finds the right answer even when the visitor uses different words than your website.', 'chatbot-demo' ) ),
			array( 'user', __( 'Captures leads', 'chatbot-demo' ), __( 'When a visitor is interested, the chat asks for their name, email and phone. Leads are saved on your website and sent to your email, Slack or Telegram.', 'chatbot-demo' ) ),
			array( 'cart', __( 'Helps shops sell', 'chatbot-demo' ), __( 'In WooCommerce shops it shows product cards with an Add to cart button and lets customers check their order status.', 'chatbot-demo' ) ),
			array( 'bell', __( 'Greets visitors', 'chatbot-demo' ), __( 'A short message appears near the chat after a few seconds or when the visitor scrolls, so more people start a conversation.', 'chatbot-demo' ) ),
			array( 'clock', __( 'Knows your business hours', 'chatbot-demo' ), __( 'Outside your hours it shows an away status and collects contact details, so no visitor is lost.', 'chatbot-demo' ) ),
			array( 'chart', __( 'Shows what visitors ask', 'chatbot-demo' ), __( 'Analytics, the most asked questions and a list of questions it could not answer, so you can add the answer once.', 'chatbot-demo' ) ),
			array( 'palette', __( 'Matches your brand', 'chatbot-demo' ), __( 'Colour, position, icon, avatar, name, welcome message and suggested questions, with a live preview.', 'chatbot-demo' ) ),
			array( 'shield', __( 'Respects privacy', 'chatbot-demo' ), __( 'Conversations and leads stay in your WordPress database. Export and erase tools and automatic clean-up are built in.', 'chatbot-demo' ) ),
		),
		'steps'     => array(
			array( __( 'Install the plugin', 'chatbot-demo' ), __( 'Upload it in WordPress under Plugins, Add New, Upload Plugin, then activate it.', 'chatbot-demo' ) ),
			array( __( 'Connect an AI service', 'chatbot-demo' ), __( 'The setup wizard guides you step by step. It works with OpenAI, Anthropic Claude, Google Gemini, Groq, Mistral, DeepSeek, OpenRouter and Cloudflare.', 'chatbot-demo' ) ),
			array( __( 'Scan and go live', 'chatbot-demo' ), __( 'One click reads your website. Turn the chat on and it starts answering.', 'chatbot-demo' ) ),
		),
		'can'       => array(
			__( 'Answer questions from your pages, products, files and custom answers.', 'chatbot-demo' ),
			__( 'Reply in the visitor\'s language and show its sources.', 'chatbot-demo' ),
			__( 'Collect leads and send them to email, Slack or Telegram.', 'chatbot-demo' ),
			__( 'Show product cards and look up WooCommerce orders.', 'chatbot-demo' ),
			__( 'Greet visitors and show an away status after hours.', 'chatbot-demo' ),
			__( 'Show a "Talk to a person" button that opens a link you choose, such as WhatsApp.', 'chatbot-demo' ),
		),
		'cannot'    => array(
			__( 'It does not search the internet. It only knows your website and the files and answers you give it.', 'chatbot-demo' ),
			__( 'It does not read password protected, private or draft pages.', 'chatbot-demo' ),
			__( 'It does not read scanned PDFs or text inside images and videos.', 'chatbot-demo' ),
			__( 'It does not take payments or complete orders.', 'chatbot-demo' ),
			__( 'It does not book appointments into a calendar. It collects details so the business can confirm.', 'chatbot-demo' ),
			__( 'It is not a live chat. A person cannot type into the conversation.', 'chatbot-demo' ),
			__( 'It works on your WordPress website, not inside WhatsApp, Messenger or Instagram.', 'chatbot-demo' ),
		),
		'questions' => array(
			__( 'What can this chatbot do?', 'chatbot-demo' ),
			__( 'Which languages does it speak?', 'chatbot-demo' ),
			__( 'Can it read PDF files?', 'chatbot-demo' ),
			__( 'Does it work with WooCommerce?', 'chatbot-demo' ),
			__( 'What can it not do?', 'chatbot-demo' ),
		),
		'faq'       => array(
			array( __( 'Do I need to write the answers myself?', 'chatbot-demo' ), __( 'No. The chatbot answers from the content already on your website and the files you add. You can also give it instructions, such as the tone to use or topics to avoid.', 'chatbot-demo' ) ),
			array( __( 'What happens when it does not know the answer?', 'chatbot-demo' ), __( 'It says it does not know instead of guessing, and can offer to take the visitor\'s details. The question also appears in the list of unanswered questions, where you can add the answer.', 'chatbot-demo' ) ),
			array( __( 'What does my website need?', 'chatbot-demo' ), __( 'WordPress 6.4 or newer, PHP 8.0 or newer, and permission to install plugins. It works with any theme, with the block editor, Elementor and WooCommerce.', 'chatbot-demo' ) ),
			array( __( 'Which files can it learn from?', 'chatbot-demo' ), __( 'PDF, DOCX and TXT files with real text, and pages from other websites by their address. Scanned documents are not read.', 'chatbot-demo' ) ),
			array( __( 'Will it slow down my website?', 'chatbot-demo' ), __( 'No noticeable effect. The chat loads after the page and only contacts the AI service when a visitor sends a message.', 'chatbot-demo' ) ),
			array( __( 'Does it work on phones?', 'chatbot-demo' ), __( 'Yes. On phones the chat opens full screen.', 'chatbot-demo' ) ),
			array( __( 'Where are conversations and leads stored?', 'chatbot-demo' ), __( 'In your own WordPress database. The visitor\'s message and the relevant text from your website are sent to the AI service you connected, so it can write the answer.', 'chatbot-demo' ) ),
			array( __( 'Can I put the chat inside a page?', 'chatbot-demo' ), __( 'Yes. It floats in the corner by default, and can also be placed inside a page with a shortcode or a block. You can hide it on chosen pages.', 'chatbot-demo' ) ),
		),
	);
}

/**
 * The same content as blocks, saved into the Home page for the chatbot to read.
 *
 * @return string
 */
function chatbot_demo_page_content() {
	$c   = chatbot_demo_content();
	$p   = static function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$h   = static function ( $text ) {
		return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $text ) . "</h2>\n<!-- /wp:heading -->\n\n";
	};
	$ul  = static function ( array $items ) {
		$li = '';
		foreach ( $items as $item ) {
			$li .= "<!-- wp:list-item -->\n<li>" . esc_html( $item ) . "</li>\n<!-- /wp:list-item -->\n";
		}
		return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . $li . "</ul>\n<!-- /wp:list -->\n\n";
	};
	$out = $p( $c['intro'] ) . $h( __( 'What the chatbot can do', 'chatbot-demo' ) );
	foreach ( $c['features'] as $f ) {
		$out .= $p( $f[1] . '. ' . $f[2] );
	}
	$out .= $h( __( 'How to add it to a website', 'chatbot-demo' ) );
	foreach ( $c['steps'] as $i => $step ) {
		$out .= $p( ( $i + 1 ) . '. ' . $step[0] . ': ' . $step[1] );
	}
	$out .= $h( __( 'What it can do', 'chatbot-demo' ) ) . $ul( $c['can'] );
	$out .= $h( __( 'What it cannot do', 'chatbot-demo' ) ) . $ul( $c['cannot'] );
	$out .= $h( __( 'Common questions', 'chatbot-demo' ) );
	foreach ( $c['faq'] as $q ) {
		$out .= $p( $q[0] . ' ' . $q[1] );
	}
	return $out;
}

/**
 * Create or refresh the Home page and make it the front page.
 *
 * @return int Home page ID, or 0 on failure.
 */
function chatbot_demo_install() {
	$home = get_page_by_path( 'home' );
	$data = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => 'home',
		'post_title'   => __( 'Home', 'chatbot-demo' ),
		'post_content' => chatbot_demo_page_content(),
	);
	if ( $home ) {
		$data['ID'] = $home->ID;
		$id         = wp_update_post( $data, true );
	} else {
		$id = wp_insert_post( $data, true );
	}
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $id );

	// Demo questions under the chat, only while the plugin still has its own defaults.
	if ( class_exists( 'Aisa_Settings' ) ) {
		$defaults = Aisa_Settings::defaults();
		if ( (string) Aisa_Settings::get( 'suggested_questions' ) === (string) $defaults['suggested_questions'] ) {
			Aisa_Settings::update( array( 'suggested_questions' => implode( "\n", array_slice( chatbot_demo_content()['questions'], 0, 3 ) ) ) );
		}
	}

	update_option( 'chatbot_demo_version', CHATBOT_DEMO_VERSION, false );
	return (int) $id;
}

/**
 * On activation, and once after a theme update, write the Home page.
 *
 * @return void
 */
function chatbot_demo_maybe_install() {
	if ( CHATBOT_DEMO_VERSION !== get_option( 'chatbot_demo_version' ) ) {
		chatbot_demo_install();
	}
}
add_action( 'after_switch_theme', 'chatbot_demo_install' );
add_action( 'admin_init', 'chatbot_demo_maybe_install' );

/**
 * Appearance > Chatbot text screen.
 *
 * @return void
 */
function chatbot_demo_admin_menu() {
	add_theme_page( __( 'Chatbot text', 'chatbot-demo' ), __( 'Chatbot text', 'chatbot-demo' ), 'edit_theme_options', 'chatbot-demo', 'chatbot_demo_admin_page' );
}
add_action( 'admin_menu', 'chatbot_demo_admin_menu' );

/**
 * Chatbot text screen.
 *
 * @return void
 */
function chatbot_demo_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>' . esc_html__( 'Chatbot text', 'chatbot-demo' ) . '</h1>';
	if ( isset( $_GET['done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		echo '<div class="notice notice-success"><p>' . esc_html__( 'The Home page text was refreshed. Run the website scan in the chatbot plugin so it learns it.', 'chatbot-demo' ) . '</p></div>';
	}
	echo '<p>' . esc_html__( 'This theme is a single page. The same text is saved into the Home page, so the chatbot learns what visitors read. The theme refreshes it after each update.', 'chatbot-demo' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'chatbot_demo_install' );
	echo '<input type="hidden" name="action" value="chatbot_demo_install">';
	submit_button( __( 'Refresh the Home page text', 'chatbot-demo' ) );
	echo '</form><p>' . esc_html__( 'Front page heading, intro and accent colour: Appearance, Customize, Chatbot demo.', 'chatbot-demo' ) . '</p></div>';
}

/**
 * Handle the refresh button.
 *
 * @return void
 */
function chatbot_demo_handle_install() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'chatbot-demo' ) );
	}
	check_admin_referer( 'chatbot_demo_install' );
	chatbot_demo_install();
	wp_safe_redirect( admin_url( 'themes.php?page=chatbot-demo&done=1' ) );
	exit;
}
add_action( 'admin_post_chatbot_demo_install', 'chatbot_demo_handle_install' );
