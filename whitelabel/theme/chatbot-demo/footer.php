<?php
/**
 * Footer.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<footer class="site-footer">
	<div class="wrap site-footer__top">
		<div class="site-footer__brand">
			<span class="brand">
				<span class="brand__mark"><?php echo wp_kses( chatbot_demo_icon( 'chat' ), chatbot_demo_svg_kses() ); ?></span>
				<span><?php bloginfo( 'name' ); ?></span>
			</span>
			<p><?php esc_html_e( 'An AI chatbot for WordPress websites that answers visitors from your own content, in their language.', 'chatbot-demo' ); ?></p>
		</div>
		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'chatbot-demo' ); ?>">
			<?php chatbot_demo_nav(); ?>
		</nav>
	</div>
	<div class="wrap site-footer__bottom">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<button type="button" class="site-footer__chat" data-open-chat><?php esc_html_e( 'Ask the chat', 'chatbot-demo' ); ?></button>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
