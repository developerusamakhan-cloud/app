<?php
/**
 * Site footer: scrolling slogan, call-to-action, links and a giant name.
 *
 * @package Nabia
 */

$nabia_email    = nabia_mod( 'contact_email' );
$nabia_wa       = nabia_whatsapp_url();
$nabia_contact  = is_front_page() ? '#contact' : home_url( '/#contact' );
$nabia_slogan   = nabia_mod( 'footer_marquee' );
// The front page and case studies already end with the big contact section.
$nabia_show_cta = ! is_front_page() && ! is_singular( 'project' );
?>
</main>

<footer class="site-footer">
	<?php if ( $nabia_slogan ) : ?>
		<a class="footer-marquee" href="<?php echo esc_url( $nabia_contact ); ?>" data-cursor="<?php esc_attr_e( 'Say hi', 'nabia' ); ?>">
			<span class="screen-reader-text"><?php echo esc_html( $nabia_slogan ); ?></span>
			<?php for ( $nabia_copy = 0; $nabia_copy < 2; $nabia_copy++ ) : ?>
				<span class="footer-marquee-track" aria-hidden="true">
					<?php for ( $nabia_rep = 0; $nabia_rep < 3; $nabia_rep++ ) : ?>
						<span class="fm-text"><?php echo esc_html( $nabia_slogan ); ?></span>
						<span class="fm-star">✦</span>
						<span class="fm-text fm-outline"><?php echo esc_html( $nabia_slogan ); ?></span>
						<span class="fm-star">✦</span>
					<?php endfor; ?>
				</span>
			<?php endfor; ?>
		</a>
	<?php endif; ?>

	<div class="container">
		<?php if ( $nabia_show_cta ) : ?>
			<div class="footer-cta">
				<div class="footer-cta-copy">
					<?php if ( nabia_mod( 'hero_badge' ) ) : ?>
						<p class="badge badge-dark"><span class="pulse" aria-hidden="true"></span><?php echo esc_html( nabia_mod( 'hero_badge' ) ); ?></p>
					<?php endif; ?>
					<h2 class="footer-cta-title" data-split><?php echo esc_html( nabia_mod( 'footer_cta_title' ) ); ?></h2>
					<p class="footer-cta-text"><?php echo esc_html( nabia_mod( 'footer_cta_text' ) ); ?></p>
					<div class="footer-cta-actions">
						<?php if ( $nabia_email ) : ?>
							<a class="btn btn-accent btn-lg" href="mailto:<?php echo esc_attr( antispambot( $nabia_email ) ); ?>" data-magnetic><?php nabia_icon( 'mail' ); ?><span><?php echo esc_html( antispambot( $nabia_email ) ); ?></span></a>
						<?php endif; ?>
						<?php if ( $nabia_wa ) : ?>
							<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( $nabia_wa ); ?>" target="_blank" rel="noopener noreferrer" data-magnetic><?php nabia_icon( 'whatsapp' ); ?><span><?php esc_html_e( 'WhatsApp', 'nabia' ); ?></span></a>
						<?php endif; ?>
					</div>
				</div>

				<a class="footer-orb" href="<?php echo esc_url( $nabia_contact ); ?>" data-magnetic>
					<svg viewBox="0 0 200 200" aria-hidden="true">
						<defs><path id="orb-circle" d="M100,100 m-78,0 a78,78 0 1,1 156,0 a78,78 0 1,1 -156,0"/></defs>
						<text><textPath href="#orb-circle" textLength="488" lengthAdjust="spacing"><?php esc_html_e( 'Start a project • Start a project •', 'nabia' ); ?></textPath></text>
					</svg>
					<span class="footer-orb-core"><?php nabia_icon( 'arrow-up' ); ?></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Start a project', 'nabia' ); ?></span>
				</a>
			</div>
		<?php endif; ?>

		<div class="footer-grid">
			<div class="footer-brand">
				<?php nabia_logo( 'logo-light' ); ?>
				<p><?php echo esc_html( nabia_mod( 'footer_text' ) ); ?></p>
				<?php nabia_social_links(); ?>
			</div>

			<div class="footer-col">
				<h3 class="footer-heading"><?php esc_html_e( 'Services', 'nabia' ); ?></h3>
				<ul class="footer-menu">
					<?php foreach ( array_slice( nabia_services(), 0, 5 ) as $nabia_service ) : ?>
						<li><a href="<?php echo esc_url( is_front_page() ? '#services' : home_url( '/#services' ) ); ?>"><?php echo esc_html( $nabia_service['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
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

			<div class="footer-col">
				<h3 class="footer-heading"><?php esc_html_e( 'Say hello', 'nabia' ); ?></h3>
				<ul class="footer-menu">
					<?php if ( $nabia_email ) : ?>
						<li><a href="mailto:<?php echo esc_attr( antispambot( $nabia_email ) ); ?>"><?php echo esc_html( antispambot( $nabia_email ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( $nabia_wa ) : ?>
						<li><a href="<?php echo esc_url( $nabia_wa ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp', 'nabia' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<div class="footer-col footer-widgets"><?php dynamic_sidebar( 'footer-1' ); ?></div>
			<?php endif; ?>
		</div>

		<div class="footer-giant" aria-hidden="true">
			<span><?php echo esc_html( nabia_mod( 'brand_name' ) ); ?></span>
		</div>

		<div class="footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( nabia_mod( 'brand_name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'nabia' ); ?></p>
			<p class="footer-made"><?php esc_html_e( 'Designed & built in-house', 'nabia' ); ?> <span class="footer-heart" aria-hidden="true">✦</span></p>
			<a class="back-to-top" href="#top" data-magnetic>
				<?php esc_html_e( 'Back to top', 'nabia' ); ?>
				<span class="back-to-top-icon"><?php nabia_icon( 'arrow-up' ); ?></span>
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
