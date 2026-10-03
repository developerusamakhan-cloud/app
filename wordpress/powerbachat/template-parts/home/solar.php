<?php
/**
 * Solar sizing tool: monthly units in, system size / panels / cost / payback out.
 *
 * Args: country (lock), embed.
 *
 * @package PowerBachat
 */

$powerbachat_args    = wp_parse_args(
	$args,
	array(
		'country' => '',
		'units'   => '',
		'embed'   => false,
	)
);
$powerbachat_country = in_array( $powerbachat_args['country'], array( 'pk', 'in', 'bd' ), true ) ? $powerbachat_args['country'] : '';
$powerbachat_units   = '' !== $powerbachat_args['units'] ? absint( $powerbachat_args['units'] ) : 450;
$powerbachat_uid     = wp_unique_id( 'solar-' );
$powerbachat_tag     = $powerbachat_args['embed'] ? 'div' : 'section';
?>
<<?php echo esc_html( $powerbachat_tag ); ?> class="section solar<?php echo $powerbachat_args['embed'] ? ' solar--embed' : ''; ?>"<?php echo $powerbachat_args['embed'] ? '' : ' id="solar"'; ?> data-solar data-country="<?php echo esc_attr( $powerbachat_country ); ?>">
	<div class="wrap">
		<?php if ( ! $powerbachat_args['embed'] ) : ?>
			<header class="section__head section__head--split">
				<div>
					<p class="kicker"><span class="kicker__num">03</span><?php esc_html_e( 'Solar sizing', 'powerbachat' ); ?></p>
					<h2 class="section__title"><?php esc_html_e( 'How much solar does your house actually need?', 'powerbachat' ); ?></h2>
				</div>
				<p class="section__lede"><?php esc_html_e( 'Installers size systems to sell panels. Start from your own units instead — then compare quotes with a number you trust.', 'powerbachat' ); ?></p>
			</header>
		<?php endif; ?>

		<div class="solar__body">
			<div class="solar__input">
				<label class="field__label" for="<?php echo esc_attr( $powerbachat_uid ); ?>"><?php esc_html_e( 'Your average monthly units', 'powerbachat' ); ?></label>
				<div class="solar__units">
					<output data-solar-out><?php echo esc_html( $powerbachat_units ); ?></output>
					<span><?php esc_html_e( 'units / month', 'powerbachat' ); ?></span>
				</div>
				<input id="<?php echo esc_attr( $powerbachat_uid ); ?>" class="range range--sun" type="range" min="50" max="2000" step="10" value="<?php echo esc_attr( $powerbachat_units ); ?>" data-solar-range>
				<div class="solar__scale" aria-hidden="true"><span>50</span><span>500</span><span>1,000</span><span>1,500</span><span>2,000</span></div>

				<dl class="solar__stats">
					<div class="stat stat--big">
						<dt><?php esc_html_e( 'System size', 'powerbachat' ); ?></dt>
						<dd><span data-solar-kw>—</span><small>kW</small></dd>
					</div>
					<div class="stat">
						<dt><?php esc_html_e( 'Panels', 'powerbachat' ); ?></dt>
						<dd data-solar-panels>—</dd>
					</div>
					<div class="stat">
						<dt><?php esc_html_e( 'Installed cost', 'powerbachat' ); ?></dt>
						<dd data-solar-cost>—</dd>
					</div>
					<div class="stat">
						<dt><?php esc_html_e( 'Saves about', 'powerbachat' ); ?></dt>
						<dd data-solar-save>—</dd>
					</div>
					<div class="stat">
						<dt><?php esc_html_e( 'Pays for itself in', 'powerbachat' ); ?></dt>
						<dd data-solar-payback>—</dd>
					</div>
				</dl>
				<p class="solar__subsidy" data-solar-subsidy hidden></p>
			</div>

			<div class="solar__visual" aria-hidden="true">
				<svg class="sun-arc" viewBox="0 0 400 150">
					<path class="sun-arc__path" d="M20 140 A 180 120 0 0 1 380 140" />
					<circle class="sun-arc__sun" r="13" cx="200" cy="20" data-solar-sun />
				</svg>
				<div class="roof">
					<div class="roof__panels" data-solar-roof></div>
				</div>
				<p class="solar__caption" data-solar-caption></p>
			</div>
		</div>

		<p class="solar__fine" data-solar-fine></p>

		<?php if ( ! $powerbachat_args['embed'] ) : ?>
			<ul class="solar__links">
				<li data-only="pk"><a href="<?php echo esc_url( home_url( '/pk/5kw-solar-system-price/' ) ); ?>"><?php esc_html_e( '5 kW system price in Pakistan', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="pk"><a href="<?php echo esc_url( home_url( '/pk/battery-backup-calculator/' ) ); ?>"><?php esc_html_e( 'Battery backup calculator', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="pk"><a href="<?php echo esc_url( home_url( '/pk/net-metering-vs-net-billing/' ) ); ?>"><?php esc_html_e( 'Net metering vs net billing', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="in"><a href="<?php echo esc_url( home_url( '/in/3kw-solar-system-price/' ) ); ?>"><?php esc_html_e( '3 kW price with subsidy', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="in"><a href="<?php echo esc_url( home_url( '/in/pm-surya-ghar-calculator/' ) ); ?>"><?php esc_html_e( 'PM Surya Ghar calculator', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="in"><a href="<?php echo esc_url( home_url( '/in/solar-installation-cost-calculator/' ) ); ?>"><?php esc_html_e( 'Installation cost calculator', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="bd"><a href="<?php echo esc_url( home_url( '/bd/solar-system-price/' ) ); ?>"><?php esc_html_e( 'Solar system price in Bangladesh', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="bd"><a href="<?php echo esc_url( home_url( '/bd/ips-calculator/' ) ); ?>"><?php esc_html_e( 'IPS backup calculator', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				<li data-only="bd"><a href="<?php echo esc_url( home_url( '/bd/solar-battery-price/' ) ); ?>"><?php esc_html_e( 'Solar battery prices', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
			</ul>
		<?php endif; ?>
	</div>
</<?php echo esc_html( $powerbachat_tag ); ?>>
