<?php
/**
 * Car Accident Settlement Estimator.
 *
 * @package ClaimFairlyTools
 * @var string $uid    Unique id prefix.
 * @var array  $preset Preset values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cft_sev = in_array( $preset['severity'], array( 'minor', 'moderate', 'serious', 'severe' ), true ) ? $preset['severity'] : 'moderate';
$cft_sev_opts = array(
	'minor'    => array( 'Minor', 'Soft tissue, sprains, a few weeks of treatment', '1.5 to 2' ),
	'moderate' => array( 'Moderate', 'Months of treatment, physical therapy, some missed work', '2 to 3' ),
	'serious'  => array( 'Serious', 'Fractures, injections or surgery, long recovery', '3 to 4' ),
	'severe'   => array( 'Severe', 'Permanent injury, major surgery, lasting limits on daily life', '4 to 5' ),
);
?>
<form class="cf-form cf-form--2col">
	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-med">Medical bills so far</label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-med" name="med" inputmode="decimal" autocomplete="off" data-type="number" data-required data-min="0" data-error="Enter your medical bills so far (0 if none)." placeholder="8,000">
		</div>
		<span class="cf-help">The billed amount from your ER, doctor, imaging and therapy bills.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-med-error" hidden></span>
	</div>
	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-future">Future medical costs <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-future" name="future" inputmode="decimal" autocomplete="off" data-type="number" placeholder="0">
		</div>
		<span class="cf-help">Only if a doctor has said you need more treatment.</span>
	</div>
	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-wages">Lost wages <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-wages" name="wages" inputmode="decimal" autocomplete="off" data-type="number" placeholder="0">
		</div>
		<span class="cf-help">Pay you missed because of the injury, including used sick days.</span>
	</div>
	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-pd">Car repair or property damage <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-pd" name="pd" inputmode="decimal" autocomplete="off" data-type="number" placeholder="0">
		</div>
		<span class="cf-help">Shown separately. It is usually settled on its own, often sooner.</span>
	</div>

	<fieldset class="cf-choices cf-choices--grid cf-form__full">
		<legend>How serious is the injury?</legend>
		<?php foreach ( $cft_sev_opts as $cft_key => $cft_opt ) : ?>
			<label class="cf-choice"><input type="radio" name="severity" value="<?php echo esc_attr( $cft_key ); ?>" <?php checked( $cft_sev, $cft_key ); ?>> <span><strong><?php echo esc_html( $cft_opt[0] ); ?></strong><small><?php echo esc_html( $cft_opt[1] ); ?>. Multiplier <?php echo esc_html( $cft_opt[2] ); ?>.</small></span></label>
		<?php endforeach; ?>
	</fieldset>

	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-state">State where the accident happened</label>
		<select id="<?php echo esc_attr( $uid ); ?>-state" name="state" data-required data-error="Pick the state where the crash happened.">
			<?php echo cft_state_options( $preset['state'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
		</select>
		<span class="cf-help">Each state treats shared fault differently. This can change everything.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-state-error" hidden></span>
	</div>
	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-fault">Your share of the fault</label>
		<div class="cf-input-group">
			<input id="<?php echo esc_attr( $uid ); ?>-fault" name="fault" value="0" inputmode="numeric" autocomplete="off" data-type="number" data-required data-min="0" data-max="100" data-error="Enter a number from 0 to 100.">
			<span class="cf-input-group__affix">%</span>
		</div>
		<span class="cf-help">Use 0 if the other driver was fully to blame. Be honest here; the insurer will be.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-fault-error" hidden></span>
	</div>
	<div class="cf-field cf-form__full">
		<label for="<?php echo esc_attr( $uid ); ?>-limit">The other driver's bodily injury limit <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-limit" name="limit" inputmode="decimal" autocomplete="off" data-type="number" placeholder="25,000">
		</div>
		<span class="cf-help">The per-person limit, if you know it. Insurers rarely pay more than the policy limit, no matter what the claim is worth.</span>
	</div>

	<div class="cf-actions cf-form__full">
		<button class="cf-btn" type="submit">Estimate my claim</button>
	</div>
</form>

<div class="cf-result" data-result hidden aria-live="polite">
	<p class="cf-result__label">Estimated injury claim range</p>
	<p class="cf-range" data-out="range"></p>
	<p class="cf-result__sub" data-out="sub"></p>
	<table class="cf-breakdown"><thead><tr><th></th><th>Low</th><th>High</th></tr></thead><tbody data-out="rows"></tbody></table>
	<div class="cf-formula"><span class="cf-formula__title">The math</span><span data-out="formula"></span></div>
	<div class="cf-callout" data-out="state"></div>
	<div class="cf-changes">
		<h3>What can change this number</h3>
		<ul>
			<li><strong>Evidence.</strong> Photos, a police report, witnesses and steady treatment records all push the number up.</li>
			<li><strong>Gaps in treatment.</strong> Waiting weeks to see a doctor, or stopping therapy early, gives the adjuster a reason to pay less.</li>
			<li><strong>Policy limits.</strong> If the other driver carries only the state minimum, that is often the real ceiling.</li>
			<li><strong>Billed vs paid.</strong> Some states only count what your health insurer actually paid, not the full bill.</li>
		</ul>
	</div>
	<div class="cf-next">
		<p class="cf-next__title">Keep going with these numbers</p>
		<div class="cf-actions">
			<a class="cf-btn" data-out="takehome" href="#">See what I'd take home</a>
			<a class="cf-btn cf-btn--ghost" data-out="pain" href="#">Pain and suffering breakdown</a>
			<a class="cf-btn cf-btn--ghost" data-out="letter" href="#">Write a demand letter</a>
		</div>
	</div>
	<?php echo cft_disclaimer_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
</div>
