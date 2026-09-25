<?php
/**
 * Free website audit: what's checked + request form.
 *
 * @package Nabia
 */

$nabia_status    = isset( $_GET['audit'] ) ? sanitize_key( wp_unslash( $_GET['audit'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nabia_shortcode = nabia_mod( 'audit_shortcode' );
$nabia_messages  = array(
	'sent'    => __( 'Thank you! Your audit request is in. I will review your website and email you within 48 hours.', 'nabia' ),
	'invalid' => __( 'Please fill in your name, a valid email and your website address.', 'nabia' ),
	'expired' => __( 'The form expired. Please try again.', 'nabia' ),
	'limit'   => __( 'You have sent a few requests already. Please try again in an hour or email me directly.', 'nabia' ),
);
?>
<section class="section audit" id="audit">
	<div class="container">
		<div class="audit-card">
			<div class="audit-copy">
				<?php nabia_eyebrow( __( 'Free audit', 'nabia' ) ); ?>
				<h2 class="audit-title" data-split><?php echo esc_html( nabia_mod( 'audit_title' ) ); ?></h2>
				<p class="audit-text" data-reveal><?php echo esc_html( nabia_mod( 'audit_text' ) ); ?></p>
				<ul class="audit-points" data-reveal>
					<?php foreach ( nabia_mod_list( 'audit_points' ) as $nabia_point ) : ?>
						<li><span class="audit-tick" aria-hidden="true">&#10003;</span><?php echo esc_html( $nabia_point ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p class="audit-promise" data-reveal>
					<strong>48h</strong>
					<span><?php esc_html_e( 'Personal review, delivered to your inbox within 2 working days.', 'nabia' ); ?></span>
				</p>
			</div>

			<div class="audit-form-wrap" data-reveal>
				<?php if ( isset( $nabia_messages[ $nabia_status ] ) ) : ?>
					<p class="audit-message <?php echo 'sent' === $nabia_status ? 'is-success' : 'is-error'; ?>" role="status"><?php echo esc_html( $nabia_messages[ $nabia_status ] ); ?></p>
				<?php endif; ?>

				<?php if ( $nabia_shortcode ) : ?>
					<?php echo do_shortcode( $nabia_shortcode ); ?>
				<?php elseif ( 'sent' !== $nabia_status ) : ?>
					<form class="audit-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="nabia_audit">
						<?php wp_nonce_field( 'nabia_audit', 'nabia_audit_nonce' ); ?>
						<p class="audit-hp" aria-hidden="true">
							<label>Company website <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
						</p>
						<p class="field">
							<label for="audit-site"><?php esc_html_e( 'Your website', 'nabia' ); ?></label>
							<input id="audit-site" name="audit_site" type="text" inputmode="url" placeholder="yourwebsite.com" required>
						</p>
						<div class="field-row">
							<p class="field">
								<label for="audit-name"><?php esc_html_e( 'Your name', 'nabia' ); ?></label>
								<input id="audit-name" name="audit_name" type="text" autocomplete="name" required>
							</p>
							<p class="field">
								<label for="audit-email"><?php esc_html_e( 'Email', 'nabia' ); ?></label>
								<input id="audit-email" name="audit_email" type="email" autocomplete="email" required>
							</p>
						</div>
						<p class="field">
							<label for="audit-goal"><?php esc_html_e( 'Main goal', 'nabia' ); ?></label>
							<select id="audit-goal" name="audit_goal">
								<?php foreach ( nabia_audit_goals() as $nabia_key => $nabia_label ) : ?>
									<option value="<?php echo esc_attr( $nabia_key ); ?>"><?php echo esc_html( $nabia_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p class="field">
							<label for="audit-message"><?php esc_html_e( 'Anything I should know? (optional)', 'nabia' ); ?></label>
							<textarea id="audit-message" name="audit_message" rows="3"></textarea>
						</p>
						<button class="btn btn-accent btn-lg audit-submit" type="submit"><span><?php esc_html_e( 'Get my free audit', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></button>
						<p class="audit-small"><?php esc_html_e( 'No spam, no sales pressure. Just honest feedback.', 'nabia' ); ?></p>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
