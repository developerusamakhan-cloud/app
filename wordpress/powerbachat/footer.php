<?php
/**
 * Site footer.
 *
 * @package PowerBachat
 */

$powerbachat_data = powerbachat_data();
?>
</main>

<footer class="site-footer">
	<div class="wrap">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<?php powerbachat_logo(); ?>
				<p><?php esc_html_e( 'Bill calculators, unit rates and solar prices for your home. Built by people who also open their bill and wince.', 'powerbachat' ); ?></p>
				<p class="site-footer__checked">
					<?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php
					printf(
						/* translators: %s: month and year */
						esc_html__( 'Every rate re-checked %s', 'powerbachat' ),
						esc_html( powerbachat_mod( 'pb_tariff_checked' ) )
					);
					?>
				</p>
			</div>

			<?php foreach ( $powerbachat_data['countries'] as $powerbachat_code => $powerbachat_country ) : ?>
				<nav class="site-footer__col" data-only="<?php echo esc_attr( $powerbachat_code ); ?>" aria-label="<?php echo esc_attr( $powerbachat_country['name'] ); ?>">
					<h2 class="site-footer__heading"><a href="<?php echo esc_url( home_url( $powerbachat_country['hub'] ) ); ?>"><?php echo esc_html( $powerbachat_country['name'] ); ?></a></h2>
					<ul>
						<?php foreach ( $powerbachat_country['utilities'] as $powerbachat_utility ) : ?>
							<li><a href="<?php echo esc_url( home_url( $powerbachat_utility['url'] ) ); ?>">
								<?php
								/* translators: %s: utility abbreviation */
								printf( esc_html__( '%s bill calculator', 'powerbachat' ), esc_html( $powerbachat_utility['abbr'] ) );
								?>
							</a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endforeach; ?>
		</div>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="site-footer__menu" aria-label="<?php esc_attr_e( 'Footer', 'powerbachat' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<p class="site-footer__wordmark" aria-hidden="true">Power<em>Bachat</em></p>

		<div class="site-footer__bottom">
			<p class="site-footer__note"><?php echo esc_html( powerbachat_mod( 'pb_footer_note' ) ); ?></p>
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
