<?php
/**
 * "Why work with me" band: four reasons with icons.
 *
 * @package Nabia
 */

$nabia_reasons = apply_filters(
	'nabia_reasons',
	array(
		array( 'bolt', __( 'Fast delivery', 'nabia' ), __( 'Most websites go live in 1 to 3 weeks, with updates at every step.', 'nabia' ) ),
		array( 'shield', __( 'Fixed prices', 'nabia' ), __( 'A clear quote upfront. No hidden costs, no surprises on the invoice.', 'nabia' ) ),
		array(
			'star',
			sprintf(
				/* translators: %s: number of projects, e.g. 300+ */
				__( '%s projects delivered', 'nabia' ),
				nabia_mod( 'stat_2_number' ) . nabia_mod( 'stat_2_suffix' )
			),
			__( 'Level 2 seller on Fiverr with 5+ years of experience and happy clients worldwide.', 'nabia' ),
		),
		array( 'mail', __( 'Real, human support', 'nabia' ), __( 'One month of free support after launch and a reply within 24 hours.', 'nabia' ) ),
	)
);
?>
<section class="why section-tight" aria-label="<?php esc_attr_e( 'Why work with me', 'nabia' ); ?>">
	<div class="container">
		<ul class="why-grid">
			<?php foreach ( $nabia_reasons as $nabia_index => $nabia_reason ) : ?>
				<li class="why-item" data-reveal style="--i:<?php echo (int) $nabia_index; ?>">
					<span class="why-icon"><?php nabia_icon( $nabia_reason[0] ); ?></span>
					<strong><?php echo esc_html( $nabia_reason[1] ); ?></strong>
					<span><?php echo esc_html( $nabia_reason[2] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
