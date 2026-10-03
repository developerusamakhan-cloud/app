<?php
/**
 * Battery backup calculator: appliances in, backup hours out.
 *
 * @package PowerBachat
 */

$powerbachat_uid  = wp_unique_id( 'batt-' );
$powerbachat_load = array(
	array( 'fan', __( 'Ceiling fan', 'powerbachat' ), 80, 2 ),
	array( 'led', __( 'LED bulb', 'powerbachat' ), 12, 4 ),
	array( 'tube', __( 'LED tube light', 'powerbachat' ), 20, 1 ),
	array( 'tv', __( 'LED TV (40 inch)', 'powerbachat' ), 80, 0 ),
	array( 'router', __( 'Wi-Fi router', 'powerbachat' ), 12, 1 ),
	array( 'laptop', __( 'Laptop charging', 'powerbachat' ), 60, 0 ),
	array( 'fridge', __( 'Inverter fridge (average)', 'powerbachat' ), 120, 0 ),
	array( 'phone', __( 'Phone charging', 'powerbachat' ), 10, 2 ),
);
$powerbachat_batteries = array(
	array( 'tubular200', __( 'Tubular lead-acid, 12 V 200 Ah', 'powerbachat' ), 12, 200, 0.5 ),
	array( 'tubular150', __( 'Tubular lead-acid, 12 V 150 Ah', 'powerbachat' ), 12, 150, 0.5 ),
	array( 'tubular130', __( 'Tubular lead-acid, 12 V 130 Ah', 'powerbachat' ), 12, 130, 0.5 ),
	array( 'lfp12', __( 'Lithium (LiFePO4), 12.8 V 100 Ah', 'powerbachat' ), 12.8, 100, 0.9 ),
	array( 'lfp24', __( 'Lithium (LiFePO4), 25.6 V 100 Ah', 'powerbachat' ), 25.6, 100, 0.9 ),
	array( 'lfp48', __( 'Lithium (LiFePO4), 51.2 V 100 Ah', 'powerbachat' ), 51.2, 100, 0.9 ),
);
?>
<div class="battery" data-battery>
	<div class="battery__inputs">
		<p class="battery__title"><?php esc_html_e( 'What runs during a power cut?', 'powerbachat' ); ?></p>
		<ul class="battery__loads">
			<?php foreach ( $powerbachat_load as $powerbachat_item ) : ?>
				<li class="battery__load">
					<label for="<?php echo esc_attr( $powerbachat_uid . '-' . $powerbachat_item[0] ); ?>">
						<?php echo esc_html( $powerbachat_item[1] ); ?>
						<small><?php echo esc_html( $powerbachat_item[2] . ' W' ); ?></small>
					</label>
					<div class="stepper">
						<button type="button" data-step="-1" aria-label="<?php esc_attr_e( 'One less', 'powerbachat' ); ?>">&minus;</button>
						<input id="<?php echo esc_attr( $powerbachat_uid . '-' . $powerbachat_item[0] ); ?>" type="number" min="0" max="20" value="<?php echo esc_attr( $powerbachat_item[3] ); ?>" data-watts="<?php echo esc_attr( $powerbachat_item[2] ); ?>" inputmode="numeric">
						<button type="button" data-step="1" aria-label="<?php esc_attr_e( 'One more', 'powerbachat' ); ?>">+</button>
					</div>
				</li>
			<?php endforeach; ?>
			<li class="battery__load">
				<label for="<?php echo esc_attr( $powerbachat_uid ); ?>-extra"><?php esc_html_e( 'Anything else', 'powerbachat' ); ?> <small><?php esc_html_e( 'watts', 'powerbachat' ); ?></small></label>
				<input class="battery__extra" id="<?php echo esc_attr( $powerbachat_uid ); ?>-extra" type="number" min="0" max="5000" step="10" value="0" data-extra inputmode="numeric">
			</li>
		</ul>

		<div class="battery__pack">
			<label class="field__label" for="<?php echo esc_attr( $powerbachat_uid ); ?>-type"><?php esc_html_e( 'Battery', 'powerbachat' ); ?></label>
			<div class="select select--light">
				<select id="<?php echo esc_attr( $powerbachat_uid ); ?>-type" data-battery-type>
					<?php foreach ( $powerbachat_batteries as $powerbachat_b ) : ?>
						<option value="<?php echo esc_attr( $powerbachat_b[0] ); ?>" data-volts="<?php echo esc_attr( $powerbachat_b[2] ); ?>" data-ah="<?php echo esc_attr( $powerbachat_b[3] ); ?>" data-dod="<?php echo esc_attr( $powerbachat_b[4] ); ?>"><?php echo esc_html( $powerbachat_b[1] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<label class="field__label" for="<?php echo esc_attr( $powerbachat_uid ); ?>-count"><?php esc_html_e( 'How many batteries', 'powerbachat' ); ?></label>
			<div class="stepper stepper--wide">
				<button type="button" data-step="-1" aria-label="<?php esc_attr_e( 'One less', 'powerbachat' ); ?>">&minus;</button>
				<input id="<?php echo esc_attr( $powerbachat_uid ); ?>-count" type="number" min="1" max="16" value="1" data-battery-count inputmode="numeric">
				<button type="button" data-step="1" aria-label="<?php esc_attr_e( 'One more', 'powerbachat' ); ?>">+</button>
			</div>
		</div>
	</div>

	<div class="battery__result" aria-live="polite">
		<p class="battery__label"><?php esc_html_e( 'Backup time', 'powerbachat' ); ?></p>
		<p class="battery__hours" data-battery-hours>...</p>
		<div class="battery__gauge" aria-hidden="true"><span data-battery-gauge></span></div>
		<dl class="battery__stats">
			<div><dt><?php esc_html_e( 'Running load', 'powerbachat' ); ?></dt><dd data-battery-load>...</dd></div>
			<div><dt><?php esc_html_e( 'Usable energy', 'powerbachat' ); ?></dt><dd data-battery-usable>...</dd></div>
			<div><dt><?php esc_html_e( 'Inverter size', 'powerbachat' ); ?></dt><dd data-battery-inverter>...</dd></div>
		</dl>
		<p class="battery__verdict" data-battery-verdict></p>
		<p class="battery__fine"><?php esc_html_e( 'Assumes 85% inverter efficiency, 50% safe discharge for lead-acid and 90% for lithium. Real backup drops as batteries age and in very hot rooms.', 'powerbachat' ); ?></p>
	</div>
</div>
