<?php
/**
 * Site footer.
 *
 * @package ClaimFairly
 */

$claimfairly_email   = claimfairly_opt( 'cf_contact_email' );
$claimfairly_same_as = array_filter( preg_split( '/\r\n|\r|\n/', claimfairly_opt( 'cf_same_as' ) ) );
$claimfairly_menus   = array(
	'footer-tools' => __( 'Calculators', 'claimfairly' ),
	'footer-learn' => __( 'Guides', 'claimfairly' ),
	'footer-site'  => __( 'ClaimFairly', 'claimfairly' ),
);
?>
</main>

<footer class="site-footer">
	<div class="wrap">
		<div class="site-footer__grid">
			<div class="site-footer__about">
				<a class="brand__link brand__link--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo claimfairly_logo_mark(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
					<span class="brand__name"><?php bloginfo( 'name' ); ?></span>
				</a>
				<p><?php echo esc_html( claimfairly_opt( 'cf_footer_about' ) ); ?></p>
				<?php if ( $claimfairly_email ) : ?>
					<p><a class="site-footer__mail" href="mailto:<?php echo esc_attr( antispambot( $claimfairly_email ) ); ?>"><?php echo claimfairly_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( antispambot( $claimfairly_email ) ); ?></a></p>
				<?php endif; ?>
				<?php if ( $claimfairly_same_as ) : ?>
					<ul class="site-footer__social">
						<?php foreach ( $claimfairly_same_as as $claimfairly_url ) : ?>
							<li><a href="<?php echo esc_url( $claimfairly_url ); ?>" rel="me noopener" target="_blank"><?php echo esc_html( ucfirst( preg_replace( '/^www\.|\.(com|org|net|io)$/', '', (string) wp_parse_url( $claimfairly_url, PHP_URL_HOST ) ) ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<?php foreach ( $claimfairly_menus as $claimfairly_loc => $claimfairly_label ) : ?>
				<?php if ( has_nav_menu( $claimfairly_loc ) ) : ?>
					<nav class="site-footer__nav" aria-label="<?php echo esc_attr( $claimfairly_label ); ?>">
						<p class="site-footer__heading"><?php echo esc_html( $claimfairly_label ); ?></p>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => $claimfairly_loc,
								'container'      => false,
								'depth'          => 1,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<div class="site-footer__legal">
			<p><?php echo esc_html( claimfairly_opt( 'cf_disclaimer' ) ); ?> <?php esc_html_e( 'ClaimFairly is not a law firm and is not affiliated with any insurance company.', 'claimfairly' ); ?></p>
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Made for drivers, by a person, with real sources.', 'claimfairly' ); ?></p>
		</div>
	</div>
	<p class="site-footer__word" aria-hidden="true"><?php bloginfo( 'name' ); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
