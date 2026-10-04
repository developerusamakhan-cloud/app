<?php
/**
 * Site footer: every page for the visitor's country, latest guides, company and
 * legal pages. Links appear only once their page exists.
 *
 * @package PowerBachat
 */

$powerbachat_map     = powerbachat_site_map();
$powerbachat_company = powerbachat_live_links( powerbachat_company_pages() );
$powerbachat_legal   = array_values(
	array_filter(
		$powerbachat_company,
		function ( $item ) {
			return in_array( $item[1], array( '/privacy-policy/', '/terms-of-use/', '/disclaimer/', '/cookie-policy/' ), true );
		}
	)
);
?>
<?php
// Tariff alerts sign-up at the bottom of every page (the home page has its own).
if ( ! is_front_page() && apply_filters( 'powerbachat_show_alerts', true ) ) {
	get_template_part( 'template-parts/home/alerts' );
}
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
				<?php if ( powerbachat_mod( 'pb_whatsapp_url' ) ) : ?>
					<p><a class="site-footer__wa" href="<?php echo esc_url( powerbachat_mod( 'pb_whatsapp_url' ) ); ?>" target="_blank" rel="noopener"><?php echo powerbachat_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Rate alerts on WhatsApp', 'powerbachat' ); ?></a></p>
				<?php endif; ?>
			</div>

			<div class="site-footer__cols">
				<?php foreach ( $powerbachat_map as $powerbachat_code => $powerbachat_sections ) : ?>
					<div class="site-footer__country" data-only="<?php echo esc_attr( $powerbachat_code ); ?>">
						<?php foreach ( $powerbachat_sections as $powerbachat_section ) : ?>
							<?php $powerbachat_links = powerbachat_live_links( $powerbachat_section['items'] ); ?>
							<?php if ( $powerbachat_links ) : ?>
								<nav class="site-footer__col" aria-label="<?php echo esc_attr( $powerbachat_section['label'] ); ?>">
									<h2 class="site-footer__heading"><?php echo esc_html( $powerbachat_section['label'] ); ?></h2>
									<ul>
										<?php foreach ( $powerbachat_links as $powerbachat_link ) : ?>
											<li><a href="<?php echo esc_url( home_url( $powerbachat_link[1] ) ); ?>"><?php echo esc_html( $powerbachat_link[0] ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								</nav>
							<?php endif; ?>
						<?php endforeach; ?>

						<?php
						$powerbachat_term   = powerbachat_country_category( $powerbachat_code );
						$powerbachat_guides = $powerbachat_term ? get_posts(
							array(
								'cat'            => $powerbachat_term->term_id,
								'posts_per_page' => 6,
								'no_found_rows'  => true,
							)
						) : array();
						?>
						<?php if ( $powerbachat_guides ) : ?>
							<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Latest guides', 'powerbachat' ); ?>">
								<h2 class="site-footer__heading"><a href="<?php echo esc_url( powerbachat_guides_url( $powerbachat_code ) ); ?>"><?php esc_html_e( 'Latest guides', 'powerbachat' ); ?></a></h2>
								<ul>
									<?php foreach ( $powerbachat_guides as $powerbachat_guide ) : ?>
										<li><a href="<?php echo esc_url( get_permalink( $powerbachat_guide ) ); ?>"><?php echo esc_html( get_the_title( $powerbachat_guide ) ); ?></a></li>
									<?php endforeach; ?>
									<li><a class="site-footer__more" href="<?php echo esc_url( powerbachat_guides_url( $powerbachat_code ) ); ?>"><?php esc_html_e( 'All guides', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
								</ul>
							</nav>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>

				<?php if ( $powerbachat_company ) : ?>
					<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Company', 'powerbachat' ); ?>">
						<h2 class="site-footer__heading"><?php esc_html_e( 'PowerBachat', 'powerbachat' ); ?></h2>
						<ul>
							<?php foreach ( $powerbachat_company as $powerbachat_link ) : ?>
								<li><a href="<?php echo esc_url( home_url( $powerbachat_link[1] ) ); ?>"><?php echo esc_html( $powerbachat_link[0] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
			</div>
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

		<div class="site-footer__bottom">
			<p class="site-footer__note"><?php echo esc_html( powerbachat_mod( 'pb_footer_note' ) ); ?></p>
			<div class="site-footer__legal">
				<?php if ( $powerbachat_legal ) : ?>
					<ul>
						<?php foreach ( $powerbachat_legal as $powerbachat_link ) : ?>
							<li><a href="<?php echo esc_url( home_url( $powerbachat_link[1] ) ); ?>"><?php echo esc_html( $powerbachat_link[0] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
