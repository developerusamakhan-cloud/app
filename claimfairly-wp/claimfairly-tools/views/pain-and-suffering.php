<?php
/**
 * Pain and Suffering Calculator: multiplier and per diem methods.
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
	'minor'    => array( 'Minor', '1.5x to 2x' ),
	'moderate' => array( 'Moderate', '2x to 3x' ),
	'serious'  => array( 'Serious', '3x to 4x' ),
	'severe'   => array( 'Severe', '4x to 5x' ),
);
?>
<form class="cf-form cf-form--2col">
	<div class="cf-field cf-form__full">
		<label for="<?php echo esc_attr( $uid ); ?>-medical">Total medical bills</label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-medical" name="medical" inputmode="decimal" autocomplete="off" data-type="number" data-required data-min="0" data-error="Enter your medical bills (0 if none)." placeholder="10,000">
		</div>
		<span class="cf-help">Everything so far plus treatment your doctor says you still need.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-medical-error" hidden></span>
	</div>

	<fieldset class="cf-choices cf-choices--row cf-form__full">
		<legend>Injury severity</legend>
		<?php foreach ( $cft_sev_opts as $cft_key => $cft_opt ) : ?>
			<label class="cf-choice"><input type="radio" name="severity" value="<?php echo esc_attr( $cft_key ); ?>" <?php checked( $cft_sev, $cft_key ); ?>> <span><strong><?php echo esc_html( $cft_opt[0] ); ?></strong><small><?php echo esc_html( $cft_opt[1] ); ?></small></span></label>
		<?php endforeach; ?>
	</fieldset>

	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-income">Yearly income <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-income" name="income" inputmode="decimal" autocomplete="off" data-type="number" placeholder="52,000">
		</div>
		<span class="cf-help">We divide it by 260 working days to suggest a daily rate.</span>
	</div>
	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-rate">Daily rate</label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-rate" name="rate" inputmode="decimal" autocomplete="off" data-type="number" data-required data-min="1" data-error="Enter a daily rate, or fill in yearly income." placeholder="200">
			<span class="cf-input-group__affix">/ day</span>
		</div>
		<span class="cf-help">What one day of living with the pain is worth. Most people use their daily pay.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-rate-error" hidden></span>
	</div>
	<div class="cf-field cf-form__full">
		<label for="<?php echo esc_attr( $uid ); ?>-days">Days until you recovered (or expect to)</label>
		<div class="cf-input-group">
			<input id="<?php echo esc_attr( $uid ); ?>-days" name="days" inputmode="numeric" autocomplete="off" data-type="number" data-required data-min="1" data-max="3650" data-error="Enter the number of recovery days (1 to 3,650)." placeholder="90">
			<span class="cf-input-group__affix">days</span>
		</div>
		<span class="cf-help">From the accident to the day your doctor released you, or the expected date.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-days-error" hidden></span>
	</div>

	<div class="cf-actions cf-form__full">
		<button class="cf-btn" type="submit">Compare both methods</button>
	</div>
</form>

<div class="cf-result" data-result hidden aria-live="polite">
	<p class="cf-result__label">Pain and suffering range across both methods</p>
	<p class="cf-range" data-out="range"></p>
	<p class="cf-result__sub">Use this as a talking point, not a price tag. Adjusters use their own software and rarely say how it works.</p>
	<div class="cf-compare">
		<div class="cf-compare__col">
			<p class="cf-compare__title">Multiplier method</p>
			<p class="cf-compare__value" data-out="mult"></p>
			<p class="cf-muted" data-out="multMath"></p>
			<p>Most common with insurers. It ties pain to how much treatment you needed.</p>
		</div>
		<div class="cf-compare__col">
			<p class="cf-compare__title">Per diem method</p>
			<p class="cf-compare__value" data-out="diem"></p>
			<p class="cf-muted" data-out="diemMath"></p>
			<p>Works best when recovery took a long time but the bills were small.</p>
		</div>
	</div>
	<div class="cf-callout" data-out="note"></div>
	<div class="cf-actions">
		<a class="cf-btn" data-out="estimator" href="#">Add this to a full claim estimate</a>
		<button type="button" class="cf-btn cf-btn--ghost" data-cf-print>Print</button>
	</div>
	<?php echo cft_disclaimer_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
</div>
