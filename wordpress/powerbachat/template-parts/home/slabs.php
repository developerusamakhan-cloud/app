<?php
/**
 * "The slab cliff" — interactive bar chart of the total bill at each consumption level.
 *
 * Args: country (lock), embed.
 *
 * @package PowerBachat
 */

$powerbachat_args    = wp_parse_args(
	$args,
	array(
		'country' => '',
		'embed'   => false,
	)
);
$powerbachat_country = in_array( $powerbachat_args['country'], array( 'pk', 'in', 'bd' ), true ) ? $powerbachat_args['country'] : '';
$powerbachat_tag     = $powerbachat_args['embed'] ? 'div' : 'section';
?>
<<?php echo esc_html( $powerbachat_tag ); ?> class="section slabs<?php echo $powerbachat_args['embed'] ? ' slabs--embed' : ''; ?>"<?php echo $powerbachat_args['embed'] ? '' : ' id="slabs"'; ?> data-chart data-country="<?php echo esc_attr( $powerbachat_country ); ?>">
	<div class="wrap slabs__inner">
		<div class="slabs__copy">
			<?php if ( ! $powerbachat_args['embed'] ) : ?>
				<p class="kicker"><span class="kicker__num">02</span><?php esc_html_e( 'Unit rates', 'powerbachat' ); ?></p>
				<h2 class="section__title"><?php esc_html_e( 'The slab cliff nobody explains on the bill.', 'powerbachat' ); ?></h2>
				<p class="section__lede"><?php esc_html_e( 'A bill does not climb in a straight line. Rates step up at fixed points, and at some of them the whole bill jumps. Hover any bar to see what that month would cost.', 'powerbachat' ); ?></p>
			<?php endif; ?>

			<div class="cliff reveal">
				<p class="cliff__label" data-chart-cliff-label><?php esc_html_e( 'One extra unit costs', 'powerbachat' ); ?></p>
				<p class="cliff__value" data-chart-cliff>—</p>
				<p class="cliff__detail" data-chart-cliff-detail></p>
			</div>

			<p class="slabs__note" data-chart-note></p>
		</div>

		<figure class="chart reveal" data-chart-figure>
			<figcaption class="chart__caption">
				<span class="chart__title" data-chart-title><?php esc_html_e( 'Monthly bill by units used', 'powerbachat' ); ?></span>
				<span class="chart__sub" data-chart-sub><?php esc_html_e( 'incl. taxes, excl. fuel adjustment', 'powerbachat' ); ?></span>
			</figcaption>
			<div class="chart__plot" data-chart-plot role="img" aria-label="<?php esc_attr_e( 'Bar chart of the estimated bill at each consumption level', 'powerbachat' ); ?>"></div>
			<div class="chart__tip" data-chart-tip hidden></div>
			<div class="chart__foot">
				<span class="chart__legend"><i class="chart__key chart__key--you" aria-hidden="true"></i><?php esc_html_e( 'Your units (from the calculator)', 'powerbachat' ); ?></span>
				<button type="button" class="link-btn" data-chart-table-toggle aria-expanded="false">
					<?php echo powerbachat_icon( 'table' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php esc_html_e( 'View as table', 'powerbachat' ); ?></span>
				</button>
			</div>
			<div class="chart__table" data-chart-table hidden></div>
		</figure>
	</div>
</<?php echo esc_html( $powerbachat_tag ); ?>>
