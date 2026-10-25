<?php
/**
 * Not found.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-head">
	<div class="wrap">
		<h1><?php esc_html_e( 'Page not found', 'chatbot-demo' ); ?></h1>
		<p><?php esc_html_e( 'This page does not exist. Ask the chat in the corner, or go back to the front page.', 'chatbot-demo' ); ?></p>
		<p><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Front page', 'chatbot-demo' ); ?></a></p>
	</div>
</section>
<?php
get_footer();
