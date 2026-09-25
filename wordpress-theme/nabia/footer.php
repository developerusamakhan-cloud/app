<?php
/**
 * Site footer.
 *
 * @package Nabia
 */

$nabia_email = nabia_mod( 'contact_email' );
$nabia_wa    = nabia_whatsapp_url();
?>
</main>

<footer class="site-footer">
	<div class="container">
		<div class="footer-top">
			<div class="footer-intro">
				<?php nabia_logo(); ?>
				<p><?php echo esc_html( nabia_mod( 'footer_text' ) ); ?></p>
			</div>

			<div class="footer-col">
				<h3 class="footer-heading"><?php esc_html_e( 'Say hello', 'nabia' ); ?></h3>
				<?php if ( $nabia_email ) : ?>
					<a class="footer-link" href="mailto:<?php echo esc_attr( antispambot( $nabia_email ) ); ?>"><?php echo esc_html( antispambot( $nabia_email ) ); ?></a>
				<?php endif; ?>
				<?php if ( $nabia_wa ) : ?>
					<a class="footer-link" href="<?php echo esc_url( $nabia_wa ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp', 'nabia' ); ?></a>
				<?php endif; ?>
				<?php if ( nabia_mod( 'portal_url' ) ) : ?>
					<a class="footer-link" href="<?php echo esc_url( nabia_mod( 'portal_url' ) ); ?>"><?php esc_html_e( 'Client portal', 'nabia' ); ?></a>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<h3 class="footer-heading"><?php esc_html_e( 'Explore', 'nabia' ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'depth'          => 1,
						'fallback_cb'    => 'nabia_menu_fallback',
					)
				);
				?>
			</div>

			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<div class="footer-col footer-widgets"><?php dynamic_sidebar( 'footer-1' ); ?></div>
			<?php endif; ?>
		</div>

		<div class="footer-giant" aria-hidden="true">
			<span data-parallax="0.15"><?php echo esc_html( nabia_mod( 'brand_name' ) ); ?></span>
		</div>

		<div class="footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( nabia_mod( 'brand_name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'nabia' ); ?></p>
			<?php nabia_social_links( 'socials-small' ); ?>
			<a class="back-to-top" href="#top" data-magnetic>
				<?php esc_html_e( 'Back to top', 'nabia' ); ?>
				<?php nabia_icon( 'arrow-up' ); ?>
			</a>
		</div>
	</div>
</footer>

<?php if ( $nabia_wa ) : ?>
	<a class="float-chat" href="<?php echo esc_url( $nabia_wa ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'nabia' ); ?>" data-magnetic>
		<?php nabia_icon( 'whatsapp' ); ?>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
