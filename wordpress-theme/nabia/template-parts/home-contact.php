<?php
/**
 * Big contact call-to-action.
 *
 * @package Nabia
 */

$nabia_email     = nabia_mod( 'contact_email' );
$nabia_wa        = nabia_whatsapp_url();
$nabia_shortcode = nabia_mod( 'contact_shortcode' );
?>
<section class="section contact" id="contact">
	<div class="container">
		<div class="contact-card">
			<span class="contact-glow" aria-hidden="true"></span>
			<p class="eyebrow" data-reveal><span class="eyebrow-num">07</span><?php esc_html_e( 'Contact', 'nabia' ); ?></p>
			<h2 class="contact-title" data-split><?php echo esc_html( nabia_mod( 'cta_title' ) ); ?></h2>
			<p class="contact-text" data-reveal><?php echo esc_html( nabia_mod( 'cta_text' ) ); ?></p>

			<?php if ( $nabia_shortcode ) : ?>
				<div class="contact-form" data-reveal><?php echo do_shortcode( $nabia_shortcode ); ?></div>
			<?php endif; ?>

			<div class="contact-actions" data-reveal>
				<?php if ( $nabia_email ) : ?>
					<a class="contact-email" href="mailto:<?php echo esc_attr( antispambot( $nabia_email ) ); ?>" data-magnetic data-cursor="<?php esc_attr_e( 'Write', 'nabia' ); ?>">
						<?php nabia_icon( 'mail' ); ?><span><?php echo esc_html( antispambot( $nabia_email ) ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $nabia_wa ) : ?>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( $nabia_wa ); ?>" target="_blank" rel="noopener noreferrer" data-magnetic>
						<?php nabia_icon( 'whatsapp' ); ?><span><?php esc_html_e( 'WhatsApp me', 'nabia' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
			<?php nabia_social_links( 'socials-contact' ); ?>
		</div>
	</div>
</section>
