<?php
/**
 * Pricing: website packages and monthly maintenance plans, in two tabs.
 *
 * @package Nabia
 */

$nabia_only = isset( $args['only'] ) ? $args['only'] : '';
?>
<section class="section pricing" id="pricing">
	<div class="container">
		<?php if ( empty( $args['hide_head'] ) ) : ?>
			<div class="pricing-head">
				<?php nabia_section_head( __( 'Pricing', 'nabia' ), nabia_mod( 'pricing_title' ) ); ?>
				<p class="pricing-text" data-reveal><?php echo esc_html( nabia_mod( 'pricing_text' ) ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( ! $nabia_only ) : ?>
			<div class="pricing-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Pricing type', 'nabia' ); ?>" data-reveal>
				<button type="button" role="tab" id="tab-web" aria-controls="panel-web" aria-selected="true" data-tab="web"><?php esc_html_e( 'Websites', 'nabia' ); ?></button>
				<button type="button" role="tab" id="tab-care" aria-controls="panel-care" aria-selected="false" data-tab="care" tabindex="-1"><?php esc_html_e( 'Monthly maintenance', 'nabia' ); ?></button>
			</div>
		<?php endif; ?>

		<?php if ( ! $nabia_only || 'web' === $nabia_only ) : ?>
			<div class="pricing-panel" id="panel-web" role="tabpanel" aria-labelledby="tab-web">
				<?php nabia_render_plans( 'web' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! $nabia_only || 'care' === $nabia_only ) : ?>
			<div class="pricing-panel" id="panel-care" role="tabpanel" aria-labelledby="tab-care" <?php echo $nabia_only ? '' : 'hidden'; ?>>
				<?php nabia_render_plans( 'care' ); ?>
			</div>
		<?php endif; ?>

		<p class="pricing-note" data-reveal>
			<?php esc_html_e( 'Need something different? Every project is unique, so ask for a custom quote.', 'nabia' ); ?>
			<a href="<?php echo esc_url( is_front_page() ? '#audit' : home_url( '/#audit' ) ); ?>"><?php esc_html_e( 'Or start with a free audit', 'nabia' ); ?> &rarr;</a>
		</p>
	</div>
</section>
