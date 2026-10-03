<?php
/**
 * How the numbers are kept honest.
 *
 * @package PowerBachat
 */

$powerbachat_steps = array(
	array(
		'title' => __( 'We read the notification itself', 'powerbachat' ),
		'text'  => __( 'Every slab comes from the regulator’s own published order — not from another website that copied it last year.', 'powerbachat' ),
	),
	array(
		'title' => __( 'We test against real bills', 'powerbachat' ),
		'text'  => __( 'Before a calculator goes live we run actual household bills through it. If our total is off by more than the fuel adjustment, it does not ship.', 'powerbachat' ),
	),
	array(
		'title' => __( 'We date-stamp everything', 'powerbachat' ),
		'text'  => __( 'Each tool shows when its rates were last checked. If a tariff changes, the page changes the same week — and the old rates stay on record.', 'powerbachat' ),
	),
);
?>
<section class="section trust">
	<div class="wrap trust__inner">
		<div class="trust__stamp reveal" aria-hidden="true">
			<svg viewBox="0 0 200 200">
				<defs><path id="stamp-circle" d="M100 100 m-74 0 a74 74 0 1 1 148 0 a74 74 0 1 1 -148 0" /></defs>
				<circle cx="100" cy="100" r="92" />
				<circle cx="100" cy="100" r="58" />
				<text><textPath href="#stamp-circle"><?php esc_html_e( 'RATES CHECKED BY HAND • NO GUESSWORK •', 'powerbachat' ); ?></textPath></text>
			</svg>
			<span class="trust__stamp-date"><?php echo esc_html( powerbachat_mod( 'pb_tariff_checked' ) ); ?></span>
		</div>

		<div class="trust__copy">
			<p class="kicker"><span class="kicker__num" aria-hidden="true"></span><?php esc_html_e( 'Our method', 'powerbachat' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'A calculator is only as good as its rate table.', 'powerbachat' ); ?></h2>
			<ol class="steps">
				<?php foreach ( $powerbachat_steps as $powerbachat_i => $powerbachat_step ) : ?>
					<li class="step reveal" style="--i:<?php echo (int) $powerbachat_i; ?>">
						<span class="step__num"><?php echo esc_html( str_pad( (string) ( $powerbachat_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<h3 class="step__title"><?php echo esc_html( $powerbachat_step['title'] ); ?></h3>
						<p><?php echo esc_html( $powerbachat_step['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
