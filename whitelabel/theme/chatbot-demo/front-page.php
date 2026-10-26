<?php
/**
 * The one page: what the chatbot can and cannot do, and a live demo.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

get_header();

$chatbot_demo_c   = chatbot_demo_content();
$chatbot_demo_svg = chatbot_demo_svg_kses();
?>

<section class="hero">
	<div class="wrap">
		<div>
			<span class="eyebrow"><?php esc_html_e( 'Live demo', 'chatbot-demo' ); ?></span>
			<h1><?php echo esc_html( chatbot_demo_mod( 'chatbot_demo_hero_title', $chatbot_demo_c['title'] ) ); ?></h1>
			<p class="hero__lead"><?php echo esc_html( chatbot_demo_mod( 'chatbot_demo_hero_text', $chatbot_demo_c['intro'] ) ); ?></p>
			<div class="hero__actions">
				<a class="btn" href="#try"><?php esc_html_e( 'Try the chat', 'chatbot-demo' ); ?></a>
				<a class="btn btn--ghost" href="#features"><?php esc_html_e( 'See what it can do', 'chatbot-demo' ); ?></a>
			</div>
		</div>
		<div class="chat-mock" aria-hidden="true">
			<div class="chat-mock__head"><span class="chat-mock__dot"></span><?php esc_html_e( 'Website assistant', 'chatbot-demo' ); ?></div>
			<div class="bubble bubble--bot"><?php esc_html_e( 'Hi! Ask me anything about this website.', 'chatbot-demo' ); ?></div>
			<div class="bubble bubble--user"><?php esc_html_e( 'Can it answer in Spanish?', 'chatbot-demo' ); ?></div>
			<div class="bubble bubble--bot"><?php esc_html_e( 'Yes. Visitors can ask in their own language and I reply in the same language, using the content of this website.', 'chatbot-demo' ); ?><small><?php esc_html_e( 'Source: Home', 'chatbot-demo' ); ?></small></div>
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
			<?php foreach ( $chatbot_demo_c['features'] as $chatbot_demo_f ) : ?>
				<div class="card">
					<span class="card__icon"><?php echo wp_kses( chatbot_demo_icon( $chatbot_demo_f[0] ), $chatbot_demo_svg ); ?></span>
					<h3><?php echo esc_html( $chatbot_demo_f[1] ); ?></h3>
					<p><?php echo esc_html( $chatbot_demo_f[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section id="how" class="section">
	<div class="wrap">
		<div class="section__head">
			<h2><?php esc_html_e( 'Live on your website in three steps', 'chatbot-demo' ); ?></h2>
		</div>
		<ol class="steps">
			<?php foreach ( $chatbot_demo_c['steps'] as $chatbot_demo_step ) : ?>
				<li><h3><?php echo esc_html( $chatbot_demo_step[0] ); ?></h3><p><?php echo esc_html( $chatbot_demo_step[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<section id="limits" class="section section--alt">
	<div class="wrap">
		<div class="section__head">
			<h2><?php esc_html_e( 'What it can and cannot do', 'chatbot-demo' ); ?></h2>
			<p><?php esc_html_e( 'An honest list, so you know exactly what to expect.', 'chatbot-demo' ); ?></p>
		</div>
		<div class="compare">
			<div class="compare__col compare__col--yes">
				<h3><?php esc_html_e( 'It can', 'chatbot-demo' ); ?></h3>
				<ul>
					<?php foreach ( $chatbot_demo_c['can'] as $chatbot_demo_item ) : ?>
						<li><?php echo esc_html( $chatbot_demo_item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="compare__col compare__col--no">
				<h3><?php esc_html_e( 'It cannot', 'chatbot-demo' ); ?></h3>
				<ul>
					<?php foreach ( $chatbot_demo_c['cannot'] as $chatbot_demo_item ) : ?>
						<li><?php echo esc_html( $chatbot_demo_item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<section id="try" class="section">
	<div class="wrap try">
		<div>
			<h2><?php esc_html_e( 'Try it now', 'chatbot-demo' ); ?></h2>
			<p><?php esc_html_e( 'The chat has read this page. Ask one of these questions, or your own, in any language:', 'chatbot-demo' ); ?></p>
			<ul class="try__list">
				<?php foreach ( $chatbot_demo_c['questions'] as $chatbot_demo_q ) : ?>
					<li><?php echo esc_html( $chatbot_demo_q ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="try__chat">
			<?php if ( chatbot_demo_has_chat() ) : ?>
				<?php echo do_shortcode( '[aisa_chat mode="inline" height="480"]' ); ?>
			<?php else : ?>
				<p class="try__note"><?php esc_html_e( 'Activate the chatbot plugin to see the chat here.', 'chatbot-demo' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>

<section id="faq" class="section section--alt">
	<div class="wrap">
		<div class="section__head">
			<h2><?php esc_html_e( 'Common questions', 'chatbot-demo' ); ?></h2>
		</div>
		<div class="faq">
			<?php foreach ( $chatbot_demo_c['faq'] as $chatbot_demo_item ) : ?>
				<details>
					<summary><?php echo esc_html( $chatbot_demo_item[0] ); ?></summary>
					<p><?php echo esc_html( $chatbot_demo_item[1] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_footer();
