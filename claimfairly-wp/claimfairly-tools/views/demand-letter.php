<?php
/**
 * Demand Letter Generator: injury, property damage and diminished value versions.
 *
 * @package ClaimFairlyTools
 * @var string $uid    Unique id prefix.
 * @var array  $preset Preset values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cft_type = in_array( $preset['type'], array( 'injury', 'property', 'dv' ), true ) ? $preset['type'] : 'injury';
$cft_id   = static function ( $key ) use ( $uid ) {
	return esc_attr( $uid . '-' . $key );
};
?>
<p class="cf-banner"><strong>This is a template.</strong> It gives you a clear, organized starting point. Consider having an attorney review it before you send it, especially if you were hurt.</p>

<form class="cf-form cf-form--2col">
	<fieldset class="cf-choices cf-choices--row cf-form__full">
		<legend>What is the letter for?</legend>
		<label class="cf-choice"><input type="radio" name="type" value="injury" <?php checked( $cft_type, 'injury' ); ?>> <span><strong>Injury claim</strong><small>Medical bills, lost wages, pain</small></span></label>
		<label class="cf-choice"><input type="radio" name="type" value="property" <?php checked( $cft_type, 'property' ); ?>> <span><strong>Property damage</strong><small>Repairs, rental, towing</small></span></label>
		<label class="cf-choice"><input type="radio" name="type" value="dv" <?php checked( $cft_type, 'dv' ); ?>> <span><strong>Diminished value</strong><small>Lost resale value after repair</small></span></label>
	</fieldset>

	<p class="cf-form__section cf-form__full">About you</p>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'name' ); ?>">Your full name</label>
		<input id="<?php echo $cft_id( 'name' ); ?>" name="name" autocomplete="name" data-required data-error="Enter your name.">
		<span class="cf-error" id="<?php echo $cft_id( 'name' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'contact' ); ?>">Phone or email <span class="cf-optional">optional</span></label>
		<input id="<?php echo $cft_id( 'contact' ); ?>" name="contact" autocomplete="email">
	</div>
	<div class="cf-field cf-form__full">
		<label for="<?php echo $cft_id( 'address' ); ?>">Your mailing address</label>
		<textarea id="<?php echo $cft_id( 'address' ); ?>" name="address" rows="2" autocomplete="street-address" data-required data-error="Enter your mailing address."></textarea>
		<span class="cf-error" id="<?php echo $cft_id( 'address' ); ?>-error" hidden></span>
	</div>

	<p class="cf-form__section cf-form__full">The insurance company</p>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'insurer' ); ?>">Insurance company</label>
		<input id="<?php echo $cft_id( 'insurer' ); ?>" name="insurer" data-required data-error="Enter the insurance company's name.">
		<span class="cf-error" id="<?php echo $cft_id( 'insurer' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'adjuster' ); ?>">Adjuster's name <span class="cf-optional">optional</span></label>
		<input id="<?php echo $cft_id( 'adjuster' ); ?>" name="adjuster">
	</div>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'claim' ); ?>">Claim number <span class="cf-optional">optional</span></label>
		<input id="<?php echo $cft_id( 'claim' ); ?>" name="claim">
	</div>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'insured' ); ?>">Their insured (the other driver) <span class="cf-optional">optional</span></label>
		<input id="<?php echo $cft_id( 'insured' ); ?>" name="insured">
	</div>
	<div class="cf-field cf-form__full">
		<label for="<?php echo $cft_id( 'insurerAddress' ); ?>">Insurer's mailing address <span class="cf-optional">optional</span></label>
		<textarea id="<?php echo $cft_id( 'insurerAddress' ); ?>" name="insurerAddress" rows="2"></textarea>
	</div>

	<p class="cf-form__section cf-form__full">The accident</p>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'date' ); ?>">Date of the accident</label>
		<input id="<?php echo $cft_id( 'date' ); ?>" name="date" type="date" data-required data-error="Pick the date of the accident.">
		<span class="cf-error" id="<?php echo $cft_id( 'date' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'location' ); ?>">Where it happened</label>
		<input id="<?php echo $cft_id( 'location' ); ?>" name="location" placeholder="Main St and 5th Ave, Austin, TX" data-required data-error="Enter where the accident happened.">
		<span class="cf-error" id="<?php echo $cft_id( 'location' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field cf-form__full">
		<label for="<?php echo $cft_id( 'facts' ); ?>">What happened, in plain words</label>
		<textarea id="<?php echo $cft_id( 'facts' ); ?>" name="facts" rows="4" placeholder="I was stopped at a red light when your insured rear-ended my car. The police report lists your insured as at fault." data-required data-error="Describe what happened in a sentence or two."></textarea>
		<span class="cf-help">Stick to facts: where you were, what the other driver did, the police report. No guesses about injuries you haven't been diagnosed with.</span>
		<span class="cf-error" id="<?php echo $cft_id( 'facts' ); ?>-error" hidden></span>
	</div>

	<div class="cf-field cf-form__full" data-show="property dv">
		<label for="<?php echo $cft_id( 'vehicle' ); ?>">Your vehicle</label>
		<input id="<?php echo $cft_id( 'vehicle' ); ?>" name="vehicle" placeholder="2022 Toyota RAV4 XLE" data-required data-error="Enter your car's year, make and model.">
		<span class="cf-error" id="<?php echo $cft_id( 'vehicle' ); ?>-error" hidden></span>
	</div>

	<div class="cf-field cf-form__full" data-show="injury">
		<label for="<?php echo $cft_id( 'injuries' ); ?>">Your injuries and treatment</label>
		<textarea id="<?php echo $cft_id( 'injuries' ); ?>" name="injuries" rows="3" placeholder="Neck sprain (whiplash) and a lower back strain. ER visit on the day of the crash, 12 weeks of physical therapy." data-required data-error="Describe your injuries and treatment."></textarea>
		<span class="cf-error" id="<?php echo $cft_id( 'injuries' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field" data-show="injury">
		<label for="<?php echo $cft_id( 'medical' ); ?>">Total medical bills</label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span><input id="<?php echo $cft_id( 'medical' ); ?>" name="medical" inputmode="decimal" data-type="number" data-required data-min="0" data-error="Enter your medical bills (0 if none)."></div>
		<span class="cf-error" id="<?php echo $cft_id( 'medical' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field" data-show="injury">
		<label for="<?php echo $cft_id( 'wages' ); ?>">Lost wages <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span><input id="<?php echo $cft_id( 'wages' ); ?>" name="wages" inputmode="decimal" data-type="number"></div>
	</div>
	<div class="cf-field cf-form__full" data-show="injury">
		<label for="<?php echo $cft_id( 'items' ); ?>">Itemized bills <span class="cf-optional">optional, one per line</span></label>
		<textarea id="<?php echo $cft_id( 'items' ); ?>" name="items" rows="3" placeholder="City Hospital ER: $3,200&#10;Spine Physical Therapy: $2,450"></textarea>
	</div>

	<div class="cf-field" data-show="property">
		<label for="<?php echo $cft_id( 'repair' ); ?>">Repair cost</label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span><input id="<?php echo $cft_id( 'repair' ); ?>" name="repair" inputmode="decimal" data-type="number" data-required data-min="0" data-error="Enter the repair cost."></div>
		<span class="cf-error" id="<?php echo $cft_id( 'repair' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field" data-show="property">
		<label for="<?php echo $cft_id( 'rental' ); ?>">Rental car <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span><input id="<?php echo $cft_id( 'rental' ); ?>" name="rental" inputmode="decimal" data-type="number"></div>
	</div>
	<div class="cf-field" data-show="property">
		<label for="<?php echo $cft_id( 'towing' ); ?>">Towing and storage <span class="cf-optional">optional</span></label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span><input id="<?php echo $cft_id( 'towing' ); ?>" name="towing" inputmode="decimal" data-type="number"></div>
	</div>

	<div class="cf-field" data-show="dv">
		<label for="<?php echo $cft_id( 'preValue' ); ?>">Value before the accident</label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span><input id="<?php echo $cft_id( 'preValue' ); ?>" name="preValue" inputmode="decimal" data-type="number" data-required data-min="1" data-error="Enter the car's value before the accident."></div>
		<span class="cf-error" id="<?php echo $cft_id( 'preValue' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field" data-show="dv">
		<label for="<?php echo $cft_id( 'basis' ); ?>">How you got the diminished value number</label>
		<select id="<?php echo $cft_id( 'basis' ); ?>" name="basis">
			<option value="appraisal">An independent diminished value appraisal</option>
			<option value="market">My own research on comparable sales</option>
			<option value="17c">The 17c formula</option>
		</select>
	</div>

	<p class="cf-form__section cf-form__full">Your demand</p>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'amount' ); ?>">Amount you are asking for</label>
		<div class="cf-input-group"><span class="cf-input-group__affix">$</span><input id="<?php echo $cft_id( 'amount' ); ?>" name="amount" inputmode="decimal" data-type="number" data-required data-min="1" data-error="Enter the amount you are demanding."></div>
		<span class="cf-help">Your opening number. It is normal to ask for more than you expect to settle for.</span>
		<span class="cf-error" id="<?php echo $cft_id( 'amount' ); ?>-error" hidden></span>
	</div>
	<div class="cf-field">
		<label for="<?php echo $cft_id( 'days' ); ?>">Days they have to respond</label>
		<div class="cf-input-group"><input id="<?php echo $cft_id( 'days' ); ?>" name="days" value="30" inputmode="numeric" data-type="number" data-required data-min="7" data-max="90" data-error="Pick between 7 and 90 days."><span class="cf-input-group__affix">days</span></div>
		<span class="cf-error" id="<?php echo $cft_id( 'days' ); ?>-error" hidden></span>
	</div>

	<div class="cf-actions cf-form__full">
		<button class="cf-btn" type="submit">Create my letter</button>
	</div>
</form>

<div class="cf-result cf-result--letter" data-result hidden>
	<p class="cf-result__label">Your letter</p>
	<p class="cf-muted">Read it through and edit anything right here before you copy or download it.</p>
	<label class="screen-reader-text" for="<?php echo $cft_id( 'letter' ); ?>">Letter text</label>
	<textarea class="cf-letter" id="<?php echo $cft_id( 'letter' ); ?>" data-out="letter" rows="24" spellcheck="true"></textarea>
	<div class="cf-actions">
		<button type="button" class="cf-btn" data-act="pdf">Download PDF</button>
		<button type="button" class="cf-btn cf-btn--ghost" data-act="copy">Copy text</button>
		<button type="button" class="cf-btn cf-btn--ghost" data-act="print">Print</button>
	</div>
	<div class="cf-changes">
		<h3>Before you send it</h3>
		<ul>
			<li>Attach copies (never originals) of bills, records, photos, the police report and proof of lost pay.</li>
			<li>Send it by certified mail or through the insurer's claim portal so you have proof of delivery.</li>
			<li>Keep a copy and write down the date you sent it.</li>
			<li>If the insurer calls, you can ask them to put any offer in writing.</li>
		</ul>
	</div>
	<?php echo cft_disclaimer_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
</div>
