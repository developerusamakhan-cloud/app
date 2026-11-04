<?php
/**
 * Site footer: every page for the visitor's country and the converters, then a
 * bottom bar with the company and legal pages, copyright and
 * credit line. Links appear only once their page exists.
 *
 * @package PowerBachat
 */

$powerbachat_map     = powerbachat_site_map();
$powerbachat_company = powerbachat_live_links( powerbachat_company_pages() );
?>
<?php
// Tariff alerts sign-up at the bottom of every page (the home page has its own).
if ( ! is_front_page() && apply_filters( 'powerbachat_show_alerts', true ) ) {
	get_template_part( 'template-parts/home/alerts', null, array( 'site' => true ) );
}
?>
</main>

<footer class="site-footer">
	<div class="wrap">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<?php powerbachat_logo(); ?>
				<p data-region-only="home"><?php esc_html_e( 'Bill calculators, unit rates and solar prices for your home. Built by people who also open their bill and wince.', 'powerbachat' ); ?></p>
				<p data-region-only="world"><?php esc_html_e( 'Free electrical calculators and converters that show their working, plus electricity bill calculators for Pakistan, India and Bangladesh.', 'powerbachat' ); ?></p>
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

					</div>
				<?php endforeach; ?>

				<?php $powerbachat_tools = powerbachat_tool_links(); ?>
				<?php if ( $powerbachat_tools ) : ?>
					<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Electrical converters', 'powerbachat' ); ?>">
						<h2 class="site-footer__heading"><?php esc_html_e( 'Converters', 'powerbachat' ); ?></h2>
						<ul>
							<?php foreach ( $powerbachat_tools as $powerbachat_link ) : ?>
								<li><a href="<?php echo esc_url( home_url( $powerbachat_link[1] ) ); ?>"><?php echo esc_html( $powerbachat_link[0] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>

				<?php $powerbachat_hubs = powerbachat_live_links( powerbachat_country_hubs() ); ?>
				<?php if ( $powerbachat_hubs ) : ?>
					<nav class="site-footer__col" data-region-only="world" aria-label="<?php esc_attr_e( 'Bill calculators by country', 'powerbachat' ); ?>">
						<h2 class="site-footer__heading"><?php esc_html_e( 'Bill calculators', 'powerbachat' ); ?></h2>
						<ul>
							<?php foreach ( $powerbachat_hubs as $powerbachat_link ) : ?>
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
			<div class="site-footer__bar">
				<?php if ( $powerbachat_company ) : ?>
					<nav class="site-footer__links" aria-label="<?php esc_attr_e( 'Company and legal', 'powerbachat' ); ?>">
						<ul class="site-footer__legal">
							<?php foreach ( $powerbachat_company as $powerbachat_link ) : ?>
								<li><a href="<?php echo esc_url( home_url( $powerbachat_link[1] ) ); ?>"><?php echo esc_html( $powerbachat_link[0] ); ?></a></li>
							<?php endforeach; ?>
							<li><a href="<?php echo esc_url( powerbachat_sitemap_url() ); ?>"><?php esc_html_e( 'Sitemap', 'powerbachat' ); ?></a></li>
							<?php if ( powerbachat_mod( 'pb_cookie_banner' ) ) : ?>
								<li><button type="button" class="site-footer__linkbtn" data-cookie-settings><?php esc_html_e( 'Cookie settings', 'powerbachat' ); ?></button></li>
							<?php endif; ?>
						</ul>
					</nav>
				<?php endif; ?>
				<p class="site-footer__copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'powerbachat' ); ?></p>
				<?php if ( powerbachat_mod( 'pb_credit_name' ) ) : ?>
					<p class="site-footer__credit">
						<?php esc_html_e( 'Made with', 'powerbachat' ); ?>
						<svg class="site-footer__heart" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 20.5s-7.5-4.6-9.3-9.4C1.4 7.6 3.6 4 7.2 4c2 0 3.6 1.1 4.8 2.8C13.2 5.1 14.8 4 16.8 4c3.6 0 5.8 3.6 4.5 7.1-1.8 4.8-9.3 9.4-9.3 9.4Z"/></svg>
						<span class="screen-reader-text"><?php esc_html_e( 'love', 'powerbachat' ); ?></span>
						<?php esc_html_e( 'by', 'powerbachat' ); ?>
						<?php if ( powerbachat_mod( 'pb_credit_url' ) ) : ?>
							<a href="<?php echo esc_url( powerbachat_mod( 'pb_credit_url' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( powerbachat_mod( 'pb_credit_name' ) ); ?></a>
						<?php else : ?>
							<?php echo esc_html( powerbachat_mod( 'pb_credit_name' ) ); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
