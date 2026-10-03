<?php
/**
 * Utility directory — one card per electricity company, filtered by the country switch.
 *
 * @package PowerBachat
 */

$powerbachat_data = powerbachat_data();
?>
<section class="section utilities" id="utilities">
	<div class="wrap">
		<header class="section__head">
			<p class="kicker"><span class="kicker__num">01</span><?php esc_html_e( 'Bill calculators', 'powerbachat' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Find your company. Get the real number.', 'powerbachat' ); ?></h2>
			<p class="section__lede"><?php esc_html_e( 'Each calculator uses that company’s own slab table and taxes — not a national average dressed up as one.', 'powerbachat' ); ?></p>
		</header>

		<div class="tabs" role="tablist" aria-label="<?php esc_attr_e( 'Country', 'powerbachat' ); ?>">
			<?php foreach ( $powerbachat_data['countries'] as $powerbachat_code => $powerbachat_country ) : ?>
				<button type="button" class="tabs__btn" role="tab" data-set-country="<?php echo esc_attr( $powerbachat_code ); ?>">
					<?php echo esc_html( $powerbachat_country['name'] ); ?>
					<span class="tabs__count"><?php echo esc_html( count( $powerbachat_country['utilities'] ) ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<?php foreach ( $powerbachat_data['countries'] as $powerbachat_code => $powerbachat_country ) : ?>
			<ul class="util-grid" data-only="<?php echo esc_attr( $powerbachat_code ); ?>">
				<?php foreach ( $powerbachat_country['utilities'] as $powerbachat_i => $powerbachat_utility ) : ?>
					<li class="util-card reveal<?php echo 0 === $powerbachat_i ? ' util-card--lead' : ''; ?>" style="--i:<?php echo (int) $powerbachat_i; ?>">
						<a href="<?php echo esc_url( home_url( $powerbachat_utility['url'] ) ); ?>">
							<span class="util-card__abbr"><?php echo esc_html( $powerbachat_utility['abbr'] ); ?></span>
							<span class="util-card__name"><?php echo esc_html( $powerbachat_utility['name'] ); ?></span>
							<span class="util-card__area"><?php echo esc_html( $powerbachat_utility['area'] ); ?></span>
							<span class="util-card__go"><?php esc_html_e( 'Calculator', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						</a>
						<?php if ( 0 === $powerbachat_i ) : ?>
							<span class="util-card__badge"><?php esc_html_e( 'Most checked', 'powerbachat' ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
				<li class="util-card util-card--hub reveal">
					<a href="<?php echo esc_url( home_url( $powerbachat_country['hub'] ) ); ?>">
						<span class="util-card__name">
							<?php
							/* translators: %s: country */
							printf( esc_html__( 'Everything for %s', 'powerbachat' ), esc_html( $powerbachat_country['name'] ) );
							?>
						</span>
						<span class="util-card__area"><?php esc_html_e( 'Unit rates, tariff history, solar and guides', 'powerbachat' ); ?></span>
						<span class="util-card__go"><?php esc_html_e( 'Open hub', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</a>
				</li>
			</ul>
		<?php endforeach; ?>
	</div>
</section>
