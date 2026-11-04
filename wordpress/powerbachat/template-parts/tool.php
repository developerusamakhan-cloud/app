<?php
/**
 * Converter widget for a tool page. Both boxes can be typed in; the settings below
 * them (volts, phase, power factor...) change the factor between the two.
 *
 * Args: tool (from powerbachat_tool_for()).
 *
 * @package PowerBachat
 */

$powerbachat_tool = isset( $args['tool'] ) ? $args['tool'] : null;
if ( ! $powerbachat_tool ) {
	return;
}
$powerbachat_uid    = wp_unique_id( 'tool-' );
$powerbachat_factor = powerbachat_tool_factor(
	$powerbachat_tool['calc'],
	array_map(
		function ( $param ) {
			return $param['default'];
		},
		$powerbachat_tool['params']
	)
);
$powerbachat_from   = $powerbachat_tool['from']['default'];
?>
<div class="calc tool" data-tool="<?php echo esc_attr( wp_json_encode( powerbachat_tool_client_config( $powerbachat_tool ) ) ); ?>">
	<div class="calc__panel tool__panel">
		<div class="calc__head">
			<p class="calc__eyebrow"><?php echo esc_html( $powerbachat_tool['name'] ); ?></p>
			<p class="calc__live"><span class="pulse" aria-hidden="true"></span><?php esc_html_e( 'Live', 'powerbachat' ); ?></p>
		</div>

		<div class="tool__io">
			<label class="tool__box" for="<?php echo esc_attr( $powerbachat_uid ); ?>-from">
				<span class="tool__label"><?php echo esc_html( $powerbachat_tool['from']['label'] ); ?></span>
				<span class="tool__value">
					<input id="<?php echo esc_attr( $powerbachat_uid ); ?>-from" type="number" inputmode="decimal" step="any" min="0" value="<?php echo esc_attr( $powerbachat_from ); ?>" data-tool-from>
					<span class="tool__unit"><?php echo esc_html( $powerbachat_tool['from']['unit'] ); ?></span>
				</span>
			</label>
			<span class="tool__eq" aria-hidden="true">=</span>
			<label class="tool__box" for="<?php echo esc_attr( $powerbachat_uid ); ?>-to">
				<span class="tool__label"><?php echo esc_html( $powerbachat_tool['to']['label'] ); ?></span>
				<span class="tool__value">
					<input id="<?php echo esc_attr( $powerbachat_uid ); ?>-to" type="number" inputmode="decimal" step="any" min="0" value="<?php echo esc_attr( str_replace( ',', '', powerbachat_tool_num( $powerbachat_from * $powerbachat_factor ) ) ); ?>" data-tool-to>
					<span class="tool__unit"><?php echo esc_html( $powerbachat_tool['to']['unit'] ); ?></span>
				</span>
			</label>
		</div>

		<?php if ( $powerbachat_tool['params'] ) : ?>
			<div class="tool__params">
				<?php foreach ( $powerbachat_tool['params'] as $powerbachat_key => $powerbachat_param ) : ?>
					<?php $powerbachat_id = $powerbachat_uid . '-' . $powerbachat_key; ?>
					<div class="field tool__param tool__param--<?php echo esc_attr( $powerbachat_param['type'] ); ?>" data-tool-param-wrap="<?php echo esc_attr( $powerbachat_key ); ?>">
						<label class="field__label" for="<?php echo esc_attr( $powerbachat_id ); ?>">
							<?php echo esc_html( $powerbachat_param['label'] ); ?>
							<?php if ( ! empty( $powerbachat_param['hint'] ) ) : ?>
								<span class="field__hint"><?php echo esc_html( $powerbachat_param['hint'] ); ?></span>
							<?php endif; ?>
						</label>
						<?php if ( 'select' === $powerbachat_param['type'] ) : ?>
							<div class="select">
								<select id="<?php echo esc_attr( $powerbachat_id ); ?>" data-tool-param="<?php echo esc_attr( $powerbachat_key ); ?>">
									<?php foreach ( $powerbachat_param['options'] as $powerbachat_value => $powerbachat_label ) : ?>
										<option value="<?php echo esc_attr( $powerbachat_value ); ?>"<?php selected( $powerbachat_param['default'], $powerbachat_value ); ?>><?php echo esc_html( $powerbachat_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php else : ?>
							<div class="tool__number">
								<input id="<?php echo esc_attr( $powerbachat_id ); ?>" type="number" inputmode="decimal" value="<?php echo esc_attr( $powerbachat_param['default'] ); ?>" step="<?php echo esc_attr( $powerbachat_param['step'] ); ?>"<?php echo isset( $powerbachat_param['min'] ) ? ' min="' . esc_attr( $powerbachat_param['min'] ) . '"' : ''; ?><?php echo isset( $powerbachat_param['max'] ) ? ' max="' . esc_attr( $powerbachat_param['max'] ) . '"' : ''; ?> data-tool-param="<?php echo esc_attr( $powerbachat_key ); ?>">
								<?php if ( ! empty( $powerbachat_param['unit'] ) ) : ?>
									<span class="tool__unit"><?php echo esc_html( $powerbachat_param['unit'] ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $powerbachat_param['presets'] ) ) : ?>
								<div class="presets" role="group" aria-label="<?php echo esc_attr( $powerbachat_param['label'] ); ?>">
									<?php foreach ( $powerbachat_param['presets'] as $powerbachat_preset ) : ?>
										<button type="button" class="preset<?php echo (float) $powerbachat_preset === (float) $powerbachat_param['default'] ? ' is-active' : ''; ?>" data-tool-preset="<?php echo esc_attr( $powerbachat_key ); ?>" data-value="<?php echo esc_attr( $powerbachat_preset ); ?>"><?php echo esc_html( $powerbachat_preset . ( empty( $powerbachat_param['unit'] ) ? '' : ' ' . $powerbachat_param['unit'] ) ); ?></button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="slip tool__slip" aria-live="polite">
		<div class="slip__top">
			<span class="slip__title"><?php esc_html_e( 'Result', 'powerbachat' ); ?></span>
			<span class="slip__plan"><?php esc_html_e( 'Rounded', 'powerbachat' ); ?></span>
		</div>
		<div class="slip__total">
			<span data-tool-sum><?php echo esc_html( powerbachat_tool_num( $powerbachat_from ) . ' ' . $powerbachat_tool['from']['unit'] ); ?></span>
			<strong data-tool-result><?php echo esc_html( powerbachat_tool_num( $powerbachat_from * $powerbachat_factor ) . ' ' . $powerbachat_tool['to']['unit'] ); ?></strong>
		</div>
		<p class="tool__formula" data-tool-formula></p>
		<?php if ( ! empty( $powerbachat_tool['pair'] ) && powerbachat_page_exists( '/tools/' . $powerbachat_tool['pair'] . '/' ) ) : ?>
			<div class="slip__foot">
				<a class="slip__link" href="<?php echo esc_url( powerbachat_tool_url( $powerbachat_tool['pair'] ) ); ?>">
					<?php
					$powerbachat_pair = powerbachat_tools()[ $powerbachat_tool['pair'] ];
					/* translators: %s: name of the reverse converter, e.g. "Amps to Watts" */
					printf( esc_html__( 'Need it the other way? %s', 'powerbachat' ), esc_html( $powerbachat_pair['name'] ) );
					?>
					<?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<noscript><p class="calc__noscript"><?php esc_html_e( 'The live converter needs JavaScript. The table on this page works without it.', 'powerbachat' ); ?></p></noscript>
</div>
