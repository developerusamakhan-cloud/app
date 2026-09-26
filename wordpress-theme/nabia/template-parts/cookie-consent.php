<?php
/**
 * Cookie consent card. The choice is saved for 6 months in the "nabia_consent" cookie
 * and passed to Google Consent Mode. "Cookie settings" in the footer opens it again.
 * Settings: Customize > Nabia Theme > General.
 *
 * @package Nabia
 */

$nabia_privacy = get_privacy_policy_url();
?>
<div class="cookie-card" id="cookie-card" role="dialog" aria-modal="false" aria-labelledby="cookie-title" aria-describedby="cookie-text" data-cookie hidden>
	<div class="cookie-top">
		<span class="cookie-art" aria-hidden="true">
			<svg viewBox="0 0 64 64" width="56" height="56">
				<defs>
					<mask id="cookie-bite">
						<rect width="64" height="64" fill="#fff"/>
						<circle cx="54" cy="12" r="10" fill="#000"/>
						<circle cx="60" cy="24" r="6" fill="#000"/>
					</mask>
				</defs>
				<g mask="url(#cookie-bite)">
					<circle class="cookie-dough" cx="32" cy="32" r="26"/>
					<circle class="cookie-chip" cx="22" cy="22" r="3.2"/>
					<circle class="cookie-chip" cx="35" cy="30" r="2.6"/>
					<circle class="cookie-chip" cx="24" cy="41" r="3"/>
					<circle class="cookie-chip" cx="40" cy="45" r="2.4"/>
					<circle class="cookie-chip" cx="44" cy="35" r="1.8"/>
				</g>
				<circle class="cookie-crumb c1" cx="55" cy="36" r="1.6"/>
				<circle class="cookie-crumb c2" cx="58" cy="42" r="1.1"/>
				<circle class="cookie-crumb c3" cx="51" cy="44" r="0.9"/>
			</svg>
		</span>
		<div class="cookie-copy">
			<p class="cookie-title" id="cookie-title"><?php echo esc_html( nabia_mod( 'cookie_title' ) ); ?></p>
			<p class="cookie-text" id="cookie-text">
				<?php echo esc_html( nabia_mod( 'cookie_text' ) ); ?>
				<?php if ( $nabia_privacy ) : ?>
					<a href="<?php echo esc_url( $nabia_privacy ); ?>"><?php esc_html_e( 'Privacy policy', 'nabia' ); ?></a>
				<?php endif; ?>
			</p>
		</div>
	</div>

	<div class="cookie-prefs" data-cookie-prefs hidden>
		<div class="cookie-pref">
			<span class="cookie-pref-text"><strong><?php esc_html_e( 'Essential', 'nabia' ); ?></strong><small><?php esc_html_e( 'Keep the site, forms and your choices working.', 'nabia' ); ?></small></span>
			<span class="cookie-always"><?php esc_html_e( 'Always on', 'nabia' ); ?></span>
		</div>
		<label class="cookie-pref">
			<span class="cookie-pref-text"><strong><?php esc_html_e( 'Analytics', 'nabia' ); ?></strong><small><?php esc_html_e( 'Anonymous stats that show which pages help people most.', 'nabia' ); ?></small></span>
			<input class="cookie-switch" type="checkbox" data-cookie-type="a" role="switch">
		</label>
		<label class="cookie-pref">
			<span class="cookie-pref-text"><strong><?php esc_html_e( 'Marketing', 'nabia' ); ?></strong><small><?php esc_html_e( 'Measure ads and show more relevant offers.', 'nabia' ); ?></small></span>
			<input class="cookie-switch" type="checkbox" data-cookie-type="m" role="switch">
		</label>
	</div>

	<div class="cookie-actions">
		<button class="btn btn-accent btn-sm cookie-accept" type="button" data-cookie-accept><span><?php esc_html_e( 'Accept all', 'nabia' ); ?></span></button>
		<button class="cookie-btn" type="button" data-cookie-essential><?php esc_html_e( 'Essential only', 'nabia' ); ?></button>
		<button class="cookie-btn" type="button" data-cookie-customize aria-expanded="false" data-save="<?php esc_attr_e( 'Save choices', 'nabia' ); ?>"><?php esc_html_e( 'Customize', 'nabia' ); ?></button>
	</div>
</div>

<button class="cookie-fab" type="button" data-cookie-open data-cookie-fab aria-label="<?php esc_attr_e( 'Cookie settings', 'nabia' ); ?>" title="<?php esc_attr_e( 'Cookie settings', 'nabia' ); ?>" hidden>
	<svg viewBox="0 0 64 64" width="26" height="26" aria-hidden="true">
		<path class="cookie-dough" d="M32 6a26 26 0 1 0 25.6 30.5A8 8 0 0 1 47 28a8 8 0 0 1-8-8 8 8 0 0 1-3.5-13.7A26 26 0 0 0 32 6z"/>
		<circle class="cookie-chip" cx="22" cy="23" r="3.2"/>
		<circle class="cookie-chip" cx="34" cy="33" r="2.6"/>
		<circle class="cookie-chip" cx="23" cy="42" r="3"/>
		<circle class="cookie-chip" cx="40" cy="46" r="2.4"/>
	</svg>
</button>
