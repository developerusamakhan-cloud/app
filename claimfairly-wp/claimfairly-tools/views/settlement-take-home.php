<?php
/**
 * Settlement Take-Home Calculator.
 *
 * @package ClaimFairlyTools
 * @var string $uid    Unique id prefix.
 * @var array  $preset Preset values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cft_amount = '' !== $preset['amount'] ? number_format( (float) $preset['amount'] ) : '';
?>
<form class="cf-form cf-form--2col">
	<div class="cf-field cf-form__full">
		<label for="<?php echo esc_attr( $uid ); ?>-amount">Settlement amount</label>
		<div class="cf-input-group">
			<span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-amount" name="amount" value="<?php echo esc_attr( $cft_amount ); ?>" inputmode="decimal" autocomplete="off" data-type="number" data-required data-min="1" data-error="Enter the settlement amount." placeholder="50,000">
		</div>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-amount-error" hidden></span>
	</div>

	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-fee">Attorney fee</label>
		<div class="cf-input-group">
			<input id="<?php echo esc_attr( $uid ); ?>-fee" name="fee" value="33.33" inputmode="decimal" autocomplete="off" data-type="number" data-required data-min="0" data-max="60" data-error="Enter a fee between 0 and 60%.">
			<span class="cf-input-group__affix">%</span>
		</div>
		<span class="cf-help">One third (33.33%) is the most common. Many contracts go to 40% if the case goes to trial. Use 0 if you have no lawyer.</span>
		<span class="cf-error" id="<?php echo esc_attr( $uid ); ?>-fee-error" hidden></span>
	</div>

	<fieldset class="cf-choices">
		<legend>When is the fee taken out?</legend>
		<label class="cf-choice"><input type="radio" name="timing" value="before" checked> <span><strong>From the full settlement</strong><small>Most common. Check your fee agreement.</small></span></label>
		<label class="cf-choice"><input type="radio" name="timing" value="after"> <span><strong>After case costs are paid</strong><small>Better for you. You pay a fee on a smaller number.</small></span></label>
	</fieldset>

	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-costs">Case costs <span class="cf-optional">optional</span></label>
		<div class="cf-input-group">
			<span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-costs" name="costs" inputmode="decimal" autocomplete="off" data-type="number" placeholder="0">
		</div>
		<span class="cf-help">Filing fees, medical records, expert reports, depositions. Your lawyer can tell you the running total.</span>
	</div>

	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-liens">Medical bills and liens to repay <span class="cf-optional">optional</span></label>
		<div class="cf-input-group">
			<span class="cf-input-group__affix">$</span>
			<input id="<?php echo esc_attr( $uid ); ?>-liens" name="liens" inputmode="decimal" autocomplete="off" data-type="number" placeholder="0">
		</div>
		<span class="cf-help">Unpaid hospital bills, health insurance or Medicare/Medicaid liens that get paid from the settlement.</span>
	</div>

	<div class="cf-field">
		<label for="<?php echo esc_attr( $uid ); ?>-reduction">Lien reduction <span class="cf-optional">optional</span></label>
		<div class="cf-input-group">
			<input id="<?php echo esc_attr( $uid ); ?>-reduction" name="reduction" inputmode="decimal" autocomplete="off" data-type="number" data-min="0" data-max="100" placeholder="0">
			<span class="cf-input-group__affix">%</span>
		</div>
		<span class="cf-help">Lawyers often negotiate liens down. Leave at 0 if you don't know yet.</span>
	</div>

	<div class="cf-actions cf-form__full">
		<button class="cf-btn" type="submit">See what I keep</button>
	</div>
</form>

<div class="cf-result" data-result hidden aria-live="polite">
	<p class="cf-result__label">Your estimated take-home</p>
	<p class="cf-range" data-out="range"></p>
	<p class="cf-result__sub" data-out="sub"></p>
	<div data-out="chart"></div>
	<table class="cf-breakdown"><tbody data-out="rows"></tbody></table>
	<div class="cf-formula"><span class="cf-formula__title">The math</span><span data-out="formula"></span></div>
	<div class="cf-callout" data-out="compare"></div>
	<div class="cf-examples">
		<h3>Same fee, other settlement sizes</h3>
		<p class="cf-muted">Fee only. Costs and liens are left out so you can compare.</p>
		<table class="cf-breakdown"><thead><tr><th>Settlement</th><th>Fee</th><th>Before costs and liens</th></tr></thead><tbody data-out="examples"></tbody></table>
	</div>
	<div class="cf-actions">
		<button type="button" class="cf-btn cf-btn--ghost" data-cf-print>Print</button>
	</div>
	<?php echo cft_disclaimer_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
</div>
