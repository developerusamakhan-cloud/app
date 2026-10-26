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
	<div class="wrap">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
