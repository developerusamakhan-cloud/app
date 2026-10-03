<?php
/**
 * Bill calculator widget, styled as a printed bill slip.
 *
 * Args: country (lock to pk|in|bd, empty = follow the site switcher), utility, units, embed.
 *
 * @package PowerBachat
 */

$powerbachat_args    = wp_parse_args(
	$args,
	array(
		'country' => '',
		'utility' => '',
		'units'   => '',
		'embed'   => false,
	)
);
$powerbachat_country = in_array( $powerbachat_args['country'], array( 'pk', 'in', 'bd' ), true ) ? $powerbachat_args['country'] : '';
$powerbachat_units   = '' !== $powerbachat_args['units'] ? absint( $powerbachat_args['units'] ) : 250;
$powerbachat_uid     = wp_unique_id( 'calc-' );
?>
<div class="calc<?php echo $powerbachat_args['embed'] ? ' calc--embed' : ''; ?>"
	data-calc
	data-country="<?php echo esc_attr( $powerbachat_country ); ?>"
	data-utility="<?php echo esc_attr( sanitize_key( $powerbachat_args['utility'] ) ); ?>"
	data-units="<?php echo esc_attr( $powerbachat_units ); ?>">

	<div class="calc__panel">
		<div class="calc__head">
			<p class="calc__eyebrow"><?php esc_html_e( 'Bill estimator', 'powerbachat' ); ?></p>
			<p class="calc__live"><span class="pulse" aria-hidden="true"></span><?php esc_html_e( 'Live', 'powerbachat' ); ?></p>
		</div>

		<div class="field">
			<label class="field__label" for="<?php echo esc_attr( $powerbachat_uid ); ?>-utility"><?php esc_html_e( 'Your electricity company', 'powerbachat' ); ?></label>
			<div class="select">
				<select id="<?php echo esc_attr( $powerbachat_uid ); ?>-utility" data-calc-utility></select>
			</div>
		</div>

		<div class="field">
			<div class="field__row">
				<label class="field__label" for="<?php echo esc_attr( $powerbachat_uid ); ?>-units">
					<?php esc_html_e( 'Units used', 'powerbachat' ); ?>
					<span class="field__hint" data-calc-period><?php esc_html_e( 'this month', 'powerbachat' ); ?></span>
				</label>
				<div class="units-input">
					<input id="<?php echo esc_attr( $powerbachat_uid ); ?>-units" type="number" inputmode="numeric" min="0" max="5000" step="1" value="<?php echo esc_attr( $powerbachat_units ); ?>" data-calc-units>
					<span>kWh</span>
				</div>
			</div>
			<input class="range" type="range" min="0" max="1000" step="1" value="<?php echo esc_attr( min( 1000, $powerbachat_units ) ); ?>" data-calc-range aria-label="<?php esc_attr_e( 'Units used', 'powerbachat' ); ?>">
			<div class="presets" role="group" aria-label="<?php esc_attr_e( 'Common amounts', 'powerbachat' ); ?>">
				<?php foreach ( array( 100, 200, 300, 500, 700 ) as $powerbachat_preset ) : ?>
					<button type="button" class="preset" data-calc-preset="<?php echo esc_attr( $powerbachat_preset ); ?>"><?php echo esc_html( $powerbachat_preset ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<label class="toggle" data-calc-protected-wrap>
			<input type="checkbox" data-calc-protected>
			<span class="toggle__track" aria-hidden="true"></span>
			<span class="toggle__text">
				<strong><?php esc_html_e( 'Protected consumer', 'powerbachat' ); ?></strong>
				<?php esc_html_e( 'Under 200 units every month for the last 6 months', 'powerbachat' ); ?>
			</span>
		</label>
	</div>

	<div class="slip" aria-live="polite">
		<div class="slip__top">
			<span class="slip__title" data-calc-title><?php esc_html_e( 'Estimated bill', 'powerbachat' ); ?></span>
			<span class="slip__plan" data-calc-plan></span>
		</div>
		<ol class="slip__lines" data-calc-lines></ol>
		<div class="slip__total">
			<span><?php esc_html_e( 'Payable (approx.)', 'powerbachat' ); ?></span>
			<strong data-calc-total>—</strong>
		</div>
		<div class="slip__meta">
			<span><?php esc_html_e( 'Average per unit', 'powerbachat' ); ?> <b data-calc-avg>—</b></span>
			<span data-calc-warn class="slip__warn" hidden></span>
		</div>
		<p class="slip__foot">
			<span data-calc-source></span>
			<?php esc_html_e( 'Excludes monthly fuel adjustment and arrears.', 'powerbachat' ); ?>
		</p>
		<a class="slip__link" href="#" data-calc-link><span data-calc-link-text><?php esc_html_e( 'Open full calculator', 'powerbachat' ); ?></span> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>
	<noscript><p class="calc__noscript"><?php esc_html_e( 'The calculator needs JavaScript. The slab tables on each company page work without it.', 'powerbachat' ); ?></p></noscript>
</div>
