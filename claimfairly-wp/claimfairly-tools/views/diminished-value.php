<?php
/**
 * Diminished Value Calculator (17c method).
 *
 * @package ClaimFairlyTools
 * @var string $uid Unique id prefix.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="cf-form cf-form--2col">
	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-value">Car's value before the accident</label>
		<div class="cf-input-group">
			<span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-value" name="value" inputmode="decimal" autocomplete="off" data-type="number" data-required data-min="500" data-max="2000000" data-error="Enter your car's value before the crash (at least $500)." placeholder="25,000">
		</div>
		<span class="cf-help">Check KBB, Edmunds or NADA for a car like yours, in the condition it was in before the crash.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-value-error" hidden></span>
	</div>

	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-miles">Mileage at the time of the accident</label>
		<div class="cf-input-group">
			<input id="<?php echo esc_attr( $uid ); ?>-miles" name="miles" inputmode="numeric" autocomplete="off" data-type="number" data-required data-min="0" data-max="1000000" data-error="Enter the mileage (0 or more)." placeholder="35,000">
			<span class="cf-input-group__affix">miles</span>
		</div>
		<span class="cf-help">The odometer reading on the day of the crash.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-miles-error" hidden></span>
	</div>

	<fieldset class="cf-choices cf-form__full">
		<legend>How bad was the damage?</legend>
		<label class="cf-choice"><input type="radio" name="damage" value="1"> <span><strong>Severe structural damage</strong><small>Frame or unibody bent, major parts replaced</small></span></label>
		<label class="cf-choice"><input type="radio" name="damage" value="0.75"> <span><strong>Major structural and panel damage</strong><small>Structural repairs plus several body panels</small></span></label>
		<label class="cf-choice"><input type="radio" name="damage" value="0.5" checked> <span><strong>Moderate structural and panel damage</strong><small>Some structural work, a few panels</small></span></label>
		<label class="cf-choice"><input type="radio" name="damage" value="0.25"> <span><strong>Minor structural and panel damage</strong><small>Light structural work, mostly panels</small></span></label>
		<label class="cf-choice"><input type="radio" name="damage" value="0"> <span><strong>No structural damage</strong><small>Cosmetic only: bumper, paint, trim</small></span></label>
	</fieldset>

	<div class="cf-field cf-form__full">
		<label for="<?php echo esc_attr( $uid ); ?>-repair">Repair cost <span class="cf-optional">optional</span></label>
		<div class="cf-input-group">
			<span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-repair" name="repair" inputmode="decimal" autocomplete="off" data-type="number" placeholder="6,500">
		</div>
		<span class="cf-help">From your repair estimate or final invoice. Helps you sanity check the damage level you picked.</span>
	</div>

	<div class="cf-actions cf-form__full">
		<button class="cf-btn" type="submit">Calculate diminished value</button>
	</div>
</form>

<div class="cf-result" data-result hidden aria-live="polite">
	<p class="cf-result__label">What the insurer's 17c formula gives</p>
	<p class="cf-range" data-out="range"></p>
	<p class="cf-result__sub" data-out="sub"></p>
	<table class="cf-breakdown"><tbody data-out="rows"></tbody></table>
	<div class="cf-formula"><span class="cf-formula__title">The math</span><span data-out="formula"></span></div>
	<div class="cf-callout" data-out="callout" hidden></div>
	<div class="cf-changes">
		<h3>What can change this number</h3>
		<ul>
			<li><strong>An independent appraisal.</strong> A licensed diminished value appraiser looks at real resale data for your car. Their number is often higher than 17c.</li>
			<li><strong>Your state.</strong> Some states make first-party diminished value claims easier than others. Georgia is the best known example.</li>
			<li><strong>Whose insurer pays.</strong> Claims against the at-fault driver's insurer (third-party) are usually more realistic than claims against your own policy.</li>
			<li><strong>The car itself.</strong> Newer, low-mileage and luxury cars tend to lose more value from an accident history report than 17c admits.</li>
		</ul>
	</div>
	<div class="cf-actions">
		<a class="cf-btn" data-out="letter" href="#">Write a demand letter for this</a>
		<button type="button" class="cf-btn cf-btn--ghost" data-cf-print>Print</button>
	</div>
	<?php echo cft_disclaimer_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
</div>
