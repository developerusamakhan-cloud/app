<?php
/**
 * Contact form section. Settings: Dashboard → Submissions → Form settings.
 *
 * @package Nabia
 */

$nabia_s      = nabia_form_settings();
$nabia_status = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nabia_msgs   = array(
	'sent'    => $nabia_s['success_message'],
	'invalid' => __( 'Please add your name, a valid email and a short message.', 'nabia' ),
	'expired' => __( 'The form expired. Please try again.', 'nabia' ),
	'limit'   => __( 'You have sent a few messages already. Please try again later or email me directly.', 'nabia' ),
	'captcha' => __( 'That sum was not quite right. Please solve the little math question and send again.', 'nabia' ),
);
$nabia_math   = nabia_math_captcha();
$nabia_email  = nabia_mod( 'contact_email' );
$nabia_budget = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $nabia_s['budget_options'] ) ) );
$nabia_uid    = wp_unique_id( 'cf-' );
?>
<section class="section contact-form-section" id="contact-form">
	<div class="container contact-form-grid">
		<div class="contact-form-info">
			<h2 class="cf-title"><?php echo nabia_tight( $nabia_s['form_title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<p class="cf-text"><?php echo esc_html( $nabia_s['form_text'] ); ?></p>
			<ul class="cf-facts">
				<?php if ( $nabia_email ) : ?>
					<li>
						<span class="cf-icon"><?php nabia_icon( 'mail' ); ?></span>
						<span><small><?php esc_html_e( 'Email', 'nabia' ); ?></small><a href="mailto:<?php echo esc_attr( antispambot( $nabia_email ) ); ?>"><?php echo esc_html( antispambot( $nabia_email ) ); ?></a></span>
					</li>
				<?php endif; ?>
				<li>
					<span class="cf-icon"><?php nabia_icon( 'bolt' ); ?></span>
					<span><small><?php esc_html_e( 'Reply time', 'nabia' ); ?></small><?php esc_html_e( 'Within 24 hours', 'nabia' ); ?></span>
				</li>
				<?php if ( nabia_mod( 'social_fiverr' ) || nabia_mod( 'social_upwork' ) ) : ?>
					<li>
						<span class="cf-icon"><?php nabia_icon( 'shield' ); ?></span>
						<span>
							<small><?php esc_html_e( 'Prefer a platform?', 'nabia' ); ?></small>
							<?php if ( nabia_mod( 'social_fiverr' ) ) : ?>
								<a href="<?php echo esc_url( nabia_mod( 'social_fiverr' ) ); ?>" target="_blank" rel="noopener noreferrer">Fiverr</a>
							<?php endif; ?>
							<?php if ( nabia_mod( 'social_fiverr' ) && nabia_mod( 'social_upwork' ) ) : ?>
								&middot;
							<?php endif; ?>
							<?php if ( nabia_mod( 'social_upwork' ) ) : ?>
								<a href="<?php echo esc_url( nabia_mod( 'social_upwork' ) ); ?>" target="_blank" rel="noopener noreferrer">Upwork</a>
							<?php endif; ?>
						</span>
					</li>
				<?php endif; ?>
			</ul>
			<?php nabia_quick_contact( __( 'Need a faster reply? Message me directly', 'nabia' ), 'is-info' ); ?>
			<?php nabia_social_links(); ?>
		</div>

		<div class="contact-form-card">
			<?php if ( isset( $nabia_msgs[ $nabia_status ] ) ) : ?>
				<p class="audit-message <?php echo 'sent' === $nabia_status ? 'is-success' : 'is-error'; ?>" role="status"><?php echo esc_html( $nabia_msgs[ $nabia_status ] ); ?></p>
			<?php endif; ?>

			<?php if ( 'sent' === $nabia_status ) : ?>
				<div class="cf-sent">
					<p class="cf-sent-text"><?php esc_html_e( 'Want an answer even faster? I usually reply within minutes on WhatsApp and Google Chat.', 'nabia' ); ?></p>
					<?php nabia_quick_contact( '', 'is-sent' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( 'sent' !== $nabia_status ) : ?>
				<form class="cf-form" method="post" action="<?php echo esc_url( remove_query_arg( array( 'contact', 'audit' ) ) ); ?>" data-nabia-form>
					<input type="hidden" name="action" value="nabia_contact">
					<input type="hidden" name="nabia_t" value="<?php echo esc_attr( nabia_form_token() ); ?>">
					<input type="hidden" name="cf_source" value="<?php echo esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ); ?>">
					<?php wp_nonce_field( 'nabia_contact', 'nabia_contact_nonce' ); ?>
					<p class="audit-hp" aria-hidden="true"><label>Company website <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label></p>

					<div class="field-row">
						<p class="field">
							<label for="<?php echo esc_attr( $nabia_uid ); ?>-name"><?php esc_html_e( 'Your name', 'nabia' ); ?> <span class="req" aria-hidden="true">*</span></label>
							<input id="<?php echo esc_attr( $nabia_uid ); ?>-name" name="cf_name" type="text" autocomplete="name" required>
						</p>
						<p class="field">
							<label for="<?php echo esc_attr( $nabia_uid ); ?>-email"><?php esc_html_e( 'Email', 'nabia' ); ?> <span class="req" aria-hidden="true">*</span></label>
							<input id="<?php echo esc_attr( $nabia_uid ); ?>-email" name="cf_email" type="email" autocomplete="email" required>
						</p>
					</div>

					<?php if ( $nabia_s['show_phone'] || $nabia_s['show_service'] ) : ?>
						<div class="field-row">
							<?php if ( $nabia_s['show_phone'] ) : ?>
								<p class="field">
									<label for="<?php echo esc_attr( $nabia_uid ); ?>-phone"><?php esc_html_e( 'Phone / WhatsApp', 'nabia' ); ?></label>
									<input id="<?php echo esc_attr( $nabia_uid ); ?>-phone" name="cf_phone" type="tel" autocomplete="tel">
								</p>
							<?php endif; ?>
							<?php if ( $nabia_s['show_service'] ) : ?>
								<p class="field">
									<label for="<?php echo esc_attr( $nabia_uid ); ?>-service"><?php esc_html_e( 'Service', 'nabia' ); ?></label>
									<select id="<?php echo esc_attr( $nabia_uid ); ?>-service" name="cf_service">
										<option value=""><?php esc_html_e( 'Choose a service', 'nabia' ); ?></option>
										<?php foreach ( nabia_services() as $nabia_service ) : ?>
											<option><?php echo esc_html( $nabia_service['title'] ); ?></option>
										<?php endforeach; ?>
										<option><?php esc_html_e( 'Something else', 'nabia' ); ?></option>
									</select>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $nabia_s['show_budget'] && $nabia_budget ) : ?>
						<fieldset class="field cf-budget">
							<legend><?php esc_html_e( 'Budget', 'nabia' ); ?></legend>
							<div class="cf-chips">
								<?php foreach ( $nabia_budget as $nabia_index => $nabia_option ) : ?>
									<label class="cf-chip">
										<input type="radio" name="cf_budget" value="<?php echo esc_attr( $nabia_option ); ?>">
										<span><?php echo esc_html( $nabia_option ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</fieldset>
					<?php endif; ?>

					<p class="field">
						<label for="<?php echo esc_attr( $nabia_uid ); ?>-message"><?php esc_html_e( 'Your message', 'nabia' ); ?> <span class="req" aria-hidden="true">*</span></label>
						<textarea id="<?php echo esc_attr( $nabia_uid ); ?>-message" name="cf_message" rows="5" required placeholder="<?php esc_attr_e( 'What would you like to build or improve?', 'nabia' ); ?>"></textarea>
					</p>

					<div class="field cf-math">
						<input type="hidden" name="nabia_cq" value="<?php echo esc_attr( $nabia_math['key'] ); ?>">
						<label for="<?php echo esc_attr( $nabia_uid ); ?>-math">
							<?php esc_html_e( 'Quick check, are you human?', 'nabia' ); ?> <span class="req" aria-hidden="true">*</span>
						</label>
						<div class="cf-math-row">
							<span class="cf-math-q" aria-hidden="true"><?php echo esc_html( $nabia_math['a'] ); ?> <b>+</b> <?php echo esc_html( $nabia_math['b'] ); ?> <b>=</b></span>
							<input id="<?php echo esc_attr( $nabia_uid ); ?>-math" name="cf_math" type="text" inputmode="numeric" pattern="[0-9]{1,2}" maxlength="2" autocomplete="off" required placeholder="?" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: number, 2: number */ __( 'What is %1$d plus %2$d?', 'nabia' ), $nabia_math['a'], $nabia_math['b'] ) ); ?>">
						</div>
					</div>

					<button class="btn btn-accent btn-lg cf-submit" type="submit"><span><?php echo esc_html( $nabia_s['button_label'] ); ?></span><?php nabia_icon( 'arrow' ); ?></button>
					<?php if ( $nabia_s['privacy_note'] ) : ?>
						<p class="audit-small"><?php echo esc_html( $nabia_s['privacy_note'] ); ?></p>
					<?php endif; ?>
				</form>
			<?php endif; ?>
		</div>
	</div>
</section>
