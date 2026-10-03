<?php
/**
 * Hero: headline on the left, the live bill estimator on the right.
 *
 * @package PowerBachat
 */

$powerbachat_title = esc_html( powerbachat_mod( 'pb_hero_title' ) );
// Words wrapped in *asterisks* get the hand-drawn underline.
$powerbachat_title = preg_replace( '/\*(.+?)\*/', '<em class="scribble">$1</em>', $powerbachat_title );
if ( false === strpos( $powerbachat_title, '<em' ) ) {
	$powerbachat_title = preg_replace( '/(\S+\s+\S+)([.!?]?)$/u', '<em class="scribble">$1</em>$2', $powerbachat_title );
}

// Bangladesh counts in taka, not rupees.
$powerbachat_title = preg_replace( '/\brupees?\b/i', '<span data-only="pk in">$0</span><span data-only="bd">taka</span>', $powerbachat_title );

$powerbachat_quick = array(
	'pk' => array(
		array( 'LESCO bill', '/pk/lesco-bill-calculator/' ),
		array( 'IESCO bill', '/pk/iesco-bill-calculator/' ),
		array( 'Solar price today', '/pk/solar-panel-price-today/' ),
		array( 'Per unit price', '/pk/electricity-tariff/' ),
	),
	'in' => array(
		array( 'TNEB bill', '/in/tneb-bill-calculator/' ),
		array( 'KSEB bill', '/in/kseb-bill-calculator/' ),
		array( '3 kW with subsidy', '/in/3kw-solar-system-price/' ),
		array( 'PM Surya Ghar', '/in/pm-surya-ghar-calculator/' ),
	),
	'bd' => array(
		array( 'DESCO bill', '/bd/desco-bill-calculator/' ),
		array( 'Solar panel price', '/bd/solar-panel-price/' ),
		array( 'IPS price', '/bd/ips-price/' ),
		array( 'Unit rate 2026', '/bd/electricity-tariff/' ),
	),
);
?>
<section class="hero" id="calculator">
	<div class="hero__grid-bg" aria-hidden="true"></div>
	<div class="wrap hero__inner">
		<div class="hero__copy">
			<p class="kicker kicker--light">
				<span class="urdu" lang="ur" data-only="pk">بجلی بچت</span>
				<span class="kicker__rule" aria-hidden="true" data-only="pk"></span>
				<?php foreach ( powerbachat_data()['countries'] as $powerbachat_code => $powerbachat_c ) : ?>
					<span data-only="<?php echo esc_attr( $powerbachat_code ); ?>"><?php echo esc_html( $powerbachat_c['name'] ); ?></span>
				<?php endforeach; ?>
			</p>
			<h1 class="hero__title"><?php echo wp_kses(
				$powerbachat_title,
				array(
					'em'   => array( 'class' => true ),
					'span' => array( 'data-only' => true ),
				)
			); ?></h1>
			<?php foreach ( array( 'pk', 'in', 'bd' ) as $powerbachat_code ) : ?>
				<p class="hero__lede" data-only="<?php echo esc_attr( $powerbachat_code ); ?>"><?php echo esc_html( powerbachat_mod( 'pb_hero_text_' . $powerbachat_code ) ); ?></p>
			<?php endforeach; ?>

			<div class="hero__quick">
				<p class="hero__quick-label"><?php esc_html_e( 'People are checking', 'powerbachat' ); ?></p>
				<?php foreach ( $powerbachat_quick as $powerbachat_code => $powerbachat_links ) : ?>
					<ul class="chips" data-only="<?php echo esc_attr( $powerbachat_code ); ?>">
						<?php foreach ( $powerbachat_links as $powerbachat_link ) : ?>
							<li><a class="chip" href="<?php echo esc_url( home_url( $powerbachat_link[1] ) ); ?>"><?php echo esc_html( $powerbachat_link[0] ); ?> <?php echo powerbachat_icon( 'arrow-ne' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
			</div>

			<p class="hero__sig">
				<?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span>
					<?php
					printf(
						/* translators: %s: month and year */
						esc_html__( 'Slab rates read off the regulator’s own notification, last checked %s.', 'powerbachat' ),
						esc_html( powerbachat_mod( 'pb_tariff_checked' ) )
					);
					?>
				</span>
			</p>
		</div>

		<div class="hero__tool reveal">
			<?php get_template_part( 'template-parts/calculator' ); ?>
		</div>
	</div>

	<svg class="hero__wires" viewBox="0 0 1440 140" preserveAspectRatio="none" aria-hidden="true">
		<g class="wires">
			<path d="M-20 34 Q 360 104 740 40 T 1460 46" />
			<path d="M-20 50 Q 360 122 740 56 T 1460 62" />
			<path d="M-20 66 Q 360 136 740 72 T 1460 78" />
		</g>
		<g class="pylon" transform="translate(728 18)">
			<path d="M12 0 L4 122 M12 0 L20 122 M0 22 H24 M2 38 H22 M-2 54 H26 M6 70 L18 90 M18 70 L6 90 M5 96 L19 112 M19 96 L5 112" />
		</g>
		<g class="bird" transform="translate(1080 46)">
			<path d="M0 0 c3 -6 9 -7 13 -3 c2 -3 5 -3 7 -1 l-4 2 c0 5 -5 8 -11 7 z" />
		</g>
	</svg>
</section>
