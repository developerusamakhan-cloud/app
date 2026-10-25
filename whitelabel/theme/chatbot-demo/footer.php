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
		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
				)
			);
		}
		?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
