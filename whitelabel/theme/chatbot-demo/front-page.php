<?php
/**
 * Front page: what the chatbot can do.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

get_header();

$chatbot_demo_svg   = chatbot_demo_svg_kses();
$chatbot_demo_title = chatbot_demo_mod( 'chatbot_demo_hero_title', __( 'An AI chatbot that knows your website', 'chatbot-demo' ) );
$chatbot_demo_intro = chatbot_demo_mod( 'chatbot_demo_hero_text', __( 'It reads your pages, posts and products, answers visitors in their own language, shows where each answer comes from, and turns interested visitors into leads. This website is a live demo: ask the chat anything about it.', 'chatbot-demo' ) );

$chatbot_demo_features = array(
	array( 'scan', __( 'Learns your website in one click', 'chatbot-demo' ), __( 'It scans your published pages, posts, products and page builder content, and updates itself when you edit them.', 'chatbot-demo' ) ),
	array( 'globe', __( 'Speaks your visitor\'s language', 'chatbot-demo' ), __( 'A visitor can ask in English, Urdu, Arabic, Spanish or many other languages and get the answer in the same language.', 'chatbot-demo' ) ),
	array( 'link', __( 'Shows its sources', 'chatbot-demo' ), __( 'Each answer can list the pages it came from, so visitors can read more and trust what they see.', 'chatbot-demo' ) ),
	array( 'user', __( 'Captures leads', 'chatbot-demo' ), __( 'When a visitor is interested, the chat asks for their name, email and phone. Leads are saved on your own website and emailed to you.', 'chatbot-demo' ) ),
	array( 'clock', __( 'Answers day and night', 'chatbot-demo' ), __( 'Visitors get an answer at any hour, even when nobody is at the desk.', 'chatbot-demo' ) ),
	array( 'palette', __( 'Matches your brand', 'chatbot-demo' ), __( 'Set the colour, position, icon, avatar, welcome message and suggested questions, and see the result in a live preview.', 'chatbot-demo' ) ),
	array( 'list', __( 'Keeps a conversation log', 'chatbot-demo' ), __( 'Analytics, the most asked questions and a list of questions it could not answer, so you can add the answer once.', 'chatbot-demo' ) ),
	array( 'shield', __( 'Respects privacy', 'chatbot-demo' ), __( 'Data stays in your WordPress database. Export and erase tools and automatic deletion of old conversations are built in.', 'chatbot-demo' ) ),
	array( 'chat', __( 'Works on any WordPress site', 'chatbot-demo' ), __( 'Works with Elementor. In WooCommerce shops it shows product cards with Add to cart and looks up order status.', 'chatbot-demo' ) ),
);

$chatbot_demo_questions = array(
	__( 'What can this chatbot do?', 'chatbot-demo' ),
	__( 'How do I add it to my website?', 'chatbot-demo' ),
	__( 'Which languages does it speak?', 'chatbot-demo' ),
	__( 'How does lead capture work?', 'chatbot-demo' ),
	__( 'Is my data safe?', 'chatbot-demo' ),
);

$chatbot_demo_faq = array(
	array( __( 'Do I need to write the answers myself?', 'chatbot-demo' ), __( 'No. The chatbot reads the content already on your website and answers from it. You can add instructions, such as the tone to use or topics to avoid.', 'chatbot-demo' ) ),
	array( __( 'What if it does not know the answer?', 'chatbot-demo' ), __( 'It says so instead of making something up, and can offer to take the visitor\'s details so you can follow up.', 'chatbot-demo' ) ),
	array( __( 'Will it slow down my website?', 'chatbot-demo' ), __( 'The chat loads after the page and only talks to the AI service when a visitor sends a message.', 'chatbot-demo' ) ),
	array( __( 'Can it use my logo and colours?', 'chatbot-demo' ), __( 'Yes. The colour, icon, avatar, name and messages can all be changed to match your brand.', 'chatbot-demo' ) ),
);
?>

<section class="hero">
	<div class="wrap">
		<div>
			<span class="eyebrow"><?php esc_html_e( 'Live demo', 'chatbot-demo' ); ?></span>
			<h1><?php echo esc_html( $chatbot_demo_title ); ?></h1>
			<p class="hero__lead"><?php echo esc_html( $chatbot_demo_intro ); ?></p>
			<div class="hero__actions">
				<a class="btn" href="#try"><?php esc_html_e( 'Ask the chat now', 'chatbot-demo' ); ?></a>
				<a class="btn btn--ghost" href="#features"><?php esc_html_e( 'See what it can do', 'chatbot-demo' ); ?></a>
			</div>
		</div>
		<div class="chat-mock" aria-hidden="true">
			<div class="chat-mock__head"><span class="chat-mock__dot"></span><?php esc_html_e( 'Website assistant', 'chatbot-demo' ); ?></div>
			<div class="bubble bubble--bot"><?php esc_html_e( 'Hi! Ask me anything about this website.', 'chatbot-demo' ); ?></div>
			<div class="bubble bubble--user"><?php esc_html_e( 'Can it answer in Urdu?', 'chatbot-demo' ); ?></div>
			<div class="bubble bubble--bot"><?php esc_html_e( 'Yes. Visitors can ask in their own language and I reply in the same language, using the content of this website.', 'chatbot-demo' ); ?><small><?php esc_html_e( 'Source: Features', 'chatbot-demo' ); ?></small></div>
		</div>
	</div>
</section>

<section id="features" class="section section--alt">
	<div class="wrap">
		<div class="section__head">
			<h2><?php esc_html_e( 'What the chatbot can do', 'chatbot-demo' ); ?></h2>
			<p><?php esc_html_e( 'Everything below is switched on for this demo. Try any of it in the chat.', 'chatbot-demo' ); ?></p>
		</div>
		<div class="grid">
			<?php foreach ( $chatbot_demo_features as $chatbot_demo_f ) : ?>
				<div class="card">
					<span class="card__icon"><?php echo wp_kses( chatbot_demo_icon( $chatbot_demo_f[0] ), $chatbot_demo_svg ); ?></span>
					<h3><?php echo esc_html( $chatbot_demo_f[1] ); ?></h3>
					<p><?php echo esc_html( $chatbot_demo_f[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="section__head">
			<h2><?php esc_html_e( 'Live on your website in three steps', 'chatbot-demo' ); ?></h2>
		</div>
		<ol class="steps">
			<li><h3><?php esc_html_e( 'Install the plugin', 'chatbot-demo' ); ?></h3><p><?php esc_html_e( 'Upload it in WordPress under Plugins, Add New, Upload Plugin, then activate it.', 'chatbot-demo' ); ?></p></li>
			<li><h3><?php esc_html_e( 'Connect an AI service', 'chatbot-demo' ); ?></h3><p><?php esc_html_e( 'The setup wizard guides you through it step by step.', 'chatbot-demo' ); ?></p></li>
			<li><h3><?php esc_html_e( 'Scan and go live', 'chatbot-demo' ); ?></h3><p><?php esc_html_e( 'One click reads your website. Turn the chat on and it starts answering.', 'chatbot-demo' ); ?></p></li>
		</ol>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="section__head">
			<h2><?php esc_html_e( 'What it does not do', 'chatbot-demo' ); ?></h2>
			<p><?php esc_html_e( 'So you know exactly what to expect.', 'chatbot-demo' ); ?></p>
		</div>
		<ul class="limits">
			<?php
			foreach ( array(
				__( 'It does not search the internet. It answers only from your website and the files you add.', 'chatbot-demo' ),
				__( 'It does not take payments or complete orders.', 'chatbot-demo' ),
				__( 'It does not book appointments into a calendar. It collects details so you can confirm.', 'chatbot-demo' ),
				__( 'It is not a live chat. A "Talk to a person" button can send visitors to WhatsApp or your contact page.', 'chatbot-demo' ),
				__( 'It does not read scanned PDFs or text inside images.', 'chatbot-demo' ),
				__( 'It works on your WordPress website, not inside WhatsApp or Messenger.', 'chatbot-demo' ),
			) as $chatbot_demo_limit ) :
				?>
				<li><?php echo esc_html( $chatbot_demo_limit ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php $chatbot_demo_limits_page = get_page_by_path( 'limits' ); ?>
		<?php if ( $chatbot_demo_limits_page ) : ?>
			<p class="limits__more"><a href="<?php echo esc_url( get_permalink( $chatbot_demo_limits_page ) ); ?>"><?php esc_html_e( 'See the full list', 'chatbot-demo' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>

<section id="try" class="section section--alt">
	<div class="wrap try">
		<div>
			<h2><?php esc_html_e( 'Try it now', 'chatbot-demo' ); ?></h2>
			<p><?php esc_html_e( 'The chat has read every page of this website. Copy one of these questions, or ask your own:', 'chatbot-demo' ); ?></p>
			<ul>
				<?php foreach ( $chatbot_demo_questions as $chatbot_demo_q ) : ?>
					<li><?php echo esc_html( $chatbot_demo_q ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="try__chat">
			<?php if ( chatbot_demo_has_chat() ) : ?>
				<?php echo do_shortcode( '[aisa_chat mode="inline" height="460"]' ); ?>
			<?php else : ?>
				<p class="try__note"><?php esc_html_e( 'Activate the chatbot plugin to see the chat here.', 'chatbot-demo' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="section__head">
			<h2><?php esc_html_e( 'Common questions', 'chatbot-demo' ); ?></h2>
		</div>
		<div class="faq">
			<?php foreach ( $chatbot_demo_faq as $chatbot_demo_item ) : ?>
				<details>
					<summary><?php echo esc_html( $chatbot_demo_item[0] ); ?></summary>
					<p><?php echo esc_html( $chatbot_demo_item[1] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="cta">
			<h2><?php esc_html_e( 'Want this chatbot on your website?', 'chatbot-demo' ); ?></h2>
			<p><?php esc_html_e( 'Tell us about your website and we will set it up for you.', 'chatbot-demo' ); ?></p>
			<?php
			$chatbot_demo_contact = get_page_by_path( 'contact' );
			$chatbot_demo_href    = $chatbot_demo_contact ? get_permalink( $chatbot_demo_contact ) : '#try';
			?>
			<a class="btn" href="<?php echo esc_url( $chatbot_demo_href ); ?>"><?php esc_html_e( 'Contact us', 'chatbot-demo' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
