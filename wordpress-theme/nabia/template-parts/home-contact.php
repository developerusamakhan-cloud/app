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
			<?php nabia_eyebrow( __( 'Contact', 'nabia' ) ); ?>
			<h2 class="contact-title" data-split><?php echo esc_html( nabia_mod( 'cta_title' ) ); ?></h2>
			<p class="contact-text" data-reveal><?php echo esc_html( nabia_mod( 'cta_text' ) ); ?></p>

			<?php if ( $nabia_shortcode ) : ?>
				<div class="contact-form" data-reveal><?php echo do_shortcode( $nabia_shortcode ); ?></div>
			<?php endif; ?>

			<div class="contact-tiles" data-reveal>
				<?php if ( $nabia_email ) : ?>
					<a class="contact-tile is-primary" href="mailto:<?php echo esc_attr( antispambot( $nabia_email ) ); ?>">
						<span class="ct-icon"><?php nabia_icon( 'mail' ); ?></span>
						<span class="ct-text"><strong><?php esc_html_e( 'Email me', 'nabia' ); ?></strong><small><?php echo esc_html( antispambot( $nabia_email ) ); ?></small></span>
						<span class="ct-arrow"><?php nabia_icon( 'arrow-up' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $nabia_wa ) : ?>
					<a class="contact-tile" href="<?php echo esc_url( $nabia_wa ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="ct-icon"><?php nabia_icon( 'whatsapp' ); ?></span>
						<span class="ct-text"><strong><?php esc_html_e( 'WhatsApp', 'nabia' ); ?></strong><small><?php esc_html_e( 'Fastest reply', 'nabia' ); ?></small></span>
						<span class="ct-arrow"><?php nabia_icon( 'arrow-up' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( nabia_gchat_url() ) : ?>
					<a class="contact-tile" href="<?php echo esc_url( nabia_gchat_url() ); ?>" target="_blank" rel="noopener noreferrer" data-copy="<?php echo esc_attr( nabia_mod( 'gchat_email' ) ); ?>">
						<span class="ct-icon"><?php nabia_icon( 'gchat' ); ?></span>
						<span class="ct-text"><strong><?php esc_html_e( 'Google Chat', 'nabia' ); ?></strong><small><?php esc_html_e( 'Chat from Gmail', 'nabia' ); ?></small></span>
						<span class="ct-arrow"><?php nabia_icon( 'arrow-up' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( nabia_mod( 'enable_livechat' ) ) : ?>
					<button type="button" class="contact-tile" data-livechat>
						<span class="ct-icon"><?php nabia_icon( 'chat-live' ); ?></span>
						<span class="ct-text"><strong><?php echo esc_html( nabia_mod( 'livechat_label' ) ? nabia_mod( 'livechat_label' ) : __( 'Live chat', 'nabia' ) ); ?></strong><small><?php esc_html_e( 'Talk to me now', 'nabia' ); ?></small></span>
						<span class="ct-arrow"><?php nabia_icon( 'arrow-up' ); ?></span>
					</button>
				<?php endif; ?>
			</div>
			<?php nabia_social_links( 'socials-contact' ); ?>
		</div>
	</div>
</section>
