<?php
/**
 * Site footer.
 *
 * @package ClaimFairly
 */

$claimfairly_email = claimfairly_opt( 'cf_contact_email' );
?>
</main>

<footer class="site-footer">
	<div class="container">
		<div class="site-footer__grid">
			<div class="site-footer__about">
				<p class="site-footer__brand"><?php bloginfo( 'name' ); ?></p>
				<p><?php echo esc_html( claimfairly_opt( 'cf_footer_about' ) ); ?></p>
				<?php if ( $claimfairly_email ) : ?>
					<p><a href="mailto:<?php echo esc_attr( antispambot( $claimfairly_email ) ); ?>"><?php echo esc_html( antispambot( $claimfairly_email ) ); ?></a></p>
				<?php endif; ?>
			</div>

			<?php if ( has_nav_menu( 'footer-tools' ) ) : ?>
				<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Tools', 'claimfairly' ); ?>">
					<p class="site-footer__heading"><?php esc_html_e( 'Free tools', 'claimfairly' ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-tools',
							'container'      => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'footer-site' ) ) : ?>
				<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Site', 'claimfairly' ); ?>">
					<p class="site-footer__heading"><?php esc_html_e( 'ClaimFairly', 'claimfairly' ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-site',
							'container'      => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>

		<div class="site-footer__legal">
			<p><?php echo esc_html( claimfairly_opt( 'cf_disclaimer' ) ); ?> <?php esc_html_e( 'ClaimFairly is not a law firm and is not affiliated with any insurance company.', 'claimfairly' ); ?></p>
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
