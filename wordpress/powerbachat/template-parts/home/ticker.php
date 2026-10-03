<?php
/**
 * Rate ticker: slab rates and panel prices scrolling like a market board.
 *
 * @package PowerBachat
 */

$powerbachat_data  = powerbachat_data();
$powerbachat_items = array();

$powerbachat_feed = array(
	'pk' => array( 'pk-nepra', 'standard' ),
	'in' => array( 'in-kseb', 'standard' ),
	'bd' => array( 'bd-berc', 'standard' ),
);

foreach ( $powerbachat_feed as $powerbachat_code => $powerbachat_ref ) {
	$powerbachat_tariff = $powerbachat_data['tariffs'][ $powerbachat_ref[0] ] ?? null;
	if ( ! $powerbachat_tariff ) {
		continue;
	}
	$powerbachat_currency = $powerbachat_data['countries'][ $powerbachat_code ]['currency'];
	$powerbachat_sched    = $powerbachat_tariff['plans'][ $powerbachat_ref[1] ]['schedules'][0];
	$powerbachat_prev     = 0;
	foreach ( array_slice( $powerbachat_sched['slabs'], 0, 4 ) as $powerbachat_slab ) {
		$powerbachat_items[] = array(
			'tag'   => strtoupper( $powerbachat_code ),
			'label' => null === $powerbachat_slab[0] ? sprintf( '%d+ units', $powerbachat_prev + 1 ) : sprintf( '%d–%d units', $powerbachat_prev + 1, $powerbachat_slab[0] ),
			'value' => $powerbachat_currency . ' ' . number_format( $powerbachat_slab[1], 2 ),
			'move'  => '',
		);
		$powerbachat_prev = (int) $powerbachat_slab[0];
	}
}

foreach ( array_slice( powerbachat_price_rows(), 0, 4 ) as $powerbachat_row ) {
	$powerbachat_items[] = array(
		'tag'   => 'SOLAR',
		'label' => $powerbachat_row['brand'] . ' ' . preg_replace( '/^.*?(\d{3,4}\s?W)$/i', '$1', $powerbachat_row['model'] ),
		'value' => powerbachat_mod( 'pb_price_currency' ) . ' ' . number_format( $powerbachat_row['price'], 1 ) . '/W',
		'move'  => $powerbachat_row['change'] < 0 ? 'down' : ( $powerbachat_row['change'] > 0 ? 'up' : 'flat' ),
	);
}

if ( ! $powerbachat_items ) {
	return;
}
?>
<div class="ticker" aria-label="<?php esc_attr_e( 'Current rates', 'powerbachat' ); ?>">
	<div class="ticker__label"><?php esc_html_e( 'Rate board', 'powerbachat' ); ?></div>
	<div class="ticker__viewport">
		<?php for ( $powerbachat_pass = 0; $powerbachat_pass < 2; $powerbachat_pass++ ) : ?>
			<ul class="ticker__track"<?php echo $powerbachat_pass ? ' aria-hidden="true"' : ''; ?>>
				<?php foreach ( $powerbachat_items as $powerbachat_item ) : ?>
					<li class="ticker__item">
						<span class="ticker__tag"><?php echo esc_html( $powerbachat_item['tag'] ); ?></span>
						<span class="ticker__name"><?php echo esc_html( $powerbachat_item['label'] ); ?></span>
						<span class="ticker__value"><?php echo esc_html( $powerbachat_item['value'] ); ?></span>
						<?php if ( $powerbachat_item['move'] ) : ?>
							<span class="ticker__move ticker__move--<?php echo esc_attr( $powerbachat_item['move'] ); ?>">
								<?php echo powerbachat_icon( $powerbachat_item['move'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="screen-reader-text"><?php echo esc_html( $powerbachat_item['move'] ); ?></span>
							</span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endfor; ?>
	</div>
</div>
