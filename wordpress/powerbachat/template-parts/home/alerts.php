<?php
/**
 * Tariff-change alerts: email form + optional WhatsApp channel.
 *
 * @package PowerBachat
 */

$powerbachat_action   = powerbachat_mod( 'pb_newsletter_url' );
$powerbachat_whatsapp = powerbachat_mod( 'pb_whatsapp_url' );
?>
<section class="section alerts">
	<div class="wrap">
		<div class="alerts__card reveal">
			<div class="alerts__copy">
				<h2 class="alerts__title"><?php esc_html_e( 'Tariff changed? Hear it from us before your bill tells you.', 'powerbachat' ); ?></h2>
				<p><?php esc_html_e( 'One short message when rates, fuel adjustments or solar prices move. Nothing else, and you can leave anytime.', 'powerbachat' ); ?></p>
			</div>
			<div class="alerts__actions">
				<?php if ( $powerbachat_action ) : ?>
					<form class="alerts__form" method="post" action="<?php echo esc_url( $powerbachat_action ); ?>" target="_blank">
						<label class="screen-reader-text" for="alerts-email"><?php esc_html_e( 'Email address', 'powerbachat' ); ?></label>
						<?php echo powerbachat_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<input id="alerts-email" type="email" name="EMAIL" autocomplete="email" required placeholder="<?php esc_attr_e( 'you@example.com', 'powerbachat' ); ?>">
						<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Notify me', 'powerbachat' ); ?></button>
					</form>
				<?php else : ?>
					<form class="alerts__form" data-pb-form="alerts" novalidate>
						<label class="screen-reader-text" for="alerts-email"><?php esc_html_e( 'Email address', 'powerbachat' ); ?></label>
						<?php echo powerbachat_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<input id="alerts-email" type="email" name="email" autocomplete="email" required placeholder="<?php esc_attr_e( 'you@example.com', 'powerbachat' ); ?>">
						<span class="contact-form__trap" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></span>
						<button class="btn btn--ink" type="submit"><?php esc_html_e( 'Notify me', 'powerbachat' ); ?></button>
					</form>
					<p class="form-status alerts__status" data-form-status role="status" aria-live="polite" hidden></p>
					<p class="alerts__thanks" data-form-thanks hidden><?php esc_html_e( 'You are on the list. We will email you when the next tariff lands.', 'powerbachat' ); ?></p>
					<p class="alerts__legal">
						<?php
						printf(
							/* translators: %s: privacy policy link */
							esc_html__( 'One click to unsubscribe, any time. %s.', 'powerbachat' ),
							'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '">' . esc_html__( 'Privacy policy', 'powerbachat' ) . '</a>'
						);
						?>
					</p>
				<?php endif; ?>
				<?php if ( $powerbachat_whatsapp ) : ?>
					<a class="alerts__wa" href="<?php echo esc_url( $powerbachat_whatsapp ); ?>" target="_blank" rel="noopener">
						<?php echo powerbachat_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php esc_html_e( 'Or follow the WhatsApp channel', 'powerbachat' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
