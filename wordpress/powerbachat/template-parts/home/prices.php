<?php
/**
 * Solar panel price board (edited in Customizer → PowerBachat → Rates & prices).
 *
 * @package PowerBachat
 */

$powerbachat_rows = powerbachat_price_rows();
if ( ! $powerbachat_rows ) {
	return;
}
$powerbachat_cur = powerbachat_mod( 'pb_price_currency' );
?>
<section class="section prices" id="prices" data-only="pk">
	<div class="wrap prices__inner">
		<div class="prices__copy">
			<p class="kicker kicker--light"><span class="kicker__num" aria-hidden="true"></span><?php esc_html_e( 'Price board', 'powerbachat' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Solar panel rates, without the WhatsApp haggling.', 'powerbachat' ); ?></h2>
			<p class="section__lede">
				<?php esc_html_e( 'Market rates per watt for A-grade panels in Pakistan. Use them as a yardstick when an installer’s quote lands on your phone.', 'powerbachat' ); ?>
			</p>
			<ul class="prices__links">
				<li><a href="<?php echo esc_url( home_url( '/pk/solar-panel-price-today/' ) ); ?>"><?php esc_html_e( 'Solar panel price today', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/pk/5kw-solar-system-price/' ) ); ?>"><?php esc_html_e( '5 kW system price', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/pk/cost-of-solar-panels/' ) ); ?>"><?php esc_html_e( 'Full cost of a solar setup', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
			</ul>
		</div>

		<div class="board reveal">
			<div class="board__head">
				<span><?php esc_html_e( 'Panel', 'powerbachat' ); ?></span>
				<span><?php echo esc_html( $powerbachat_cur ); ?> / W</span>
				<span><?php esc_html_e( 'Change', 'powerbachat' ); ?></span>
			</div>
			<ol class="board__rows">
				<?php foreach ( $powerbachat_rows as $powerbachat_i => $powerbachat_row ) : ?>
					<?php
					$powerbachat_move = $powerbachat_row['change'] < 0 ? 'down' : ( $powerbachat_row['change'] > 0 ? 'up' : 'flat' );
					preg_match( '/(\d{3,4})\s?W/i', $powerbachat_row['model'], $powerbachat_watt );
					?>
					<li class="board__row" style="--i:<?php echo (int) $powerbachat_i; ?>">
						<span class="board__panel">
							<strong><?php echo esc_html( $powerbachat_row['brand'] ); ?></strong>
							<small><?php echo esc_html( $powerbachat_row['model'] ); ?></small>
						</span>
						<span class="board__price">
							<span class="flip" data-flip="<?php echo esc_attr( number_format( $powerbachat_row['price'], 1 ) ); ?>"><?php echo esc_html( number_format( $powerbachat_row['price'], 1 ) ); ?></span>
							<?php if ( ! empty( $powerbachat_watt[1] ) ) : ?>
								<small>
									<?php
									/* translators: 1: currency, 2: panel price */
									printf( esc_html__( '%1$s %2$s per panel', 'powerbachat' ), esc_html( $powerbachat_cur ), esc_html( number_format_i18n( round( $powerbachat_row['price'] * (int) $powerbachat_watt[1], -2 ) ) ) );
									?>
								</small>
							<?php endif; ?>
						</span>
						<span class="board__move board__move--<?php echo esc_attr( $powerbachat_move ); ?>">
							<?php echo powerbachat_icon( $powerbachat_move ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( 'flat' === $powerbachat_move ? '0.0' : number_format( abs( $powerbachat_row['change'] ), 1 ) ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="board__foot">
				<?php
				/* translators: %s: date */
				printf( esc_html__( 'Updated %s · ex-warehouse, before delivery and installation', 'powerbachat' ), esc_html( powerbachat_mod( 'pb_price_updated' ) ) );
				?>
			</p>
			<?php if ( powerbachat_prices_are_default() && current_user_can( 'edit_theme_options' ) ) : ?>
				<p class="admin-hint">
					<?php esc_html_e( 'Only admins see this: these are the theme’s sample prices. Replace them in Appearance → Customize → PowerBachat → Rates & prices.', 'powerbachat' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</section>
