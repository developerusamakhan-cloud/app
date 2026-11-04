<?php
/**
 * Home page band for visitors outside Pakistan, India and Bangladesh: the
 * electrical converters first, with a pointer to the South Asia bill calculators
 * further down. Hidden for home visitors by data-region-only.
 *
 * @package PowerBachat
 */

if ( ! function_exists( 'powerbachat_tools' ) || ! powerbachat_page_exists( '/tools/' ) ) {
	return;
}
$powerbachat_popular = array( 'watts-to-amps', 'amps-to-watts', 'kva-to-kw', 'kwh-to-watts', 'btu-to-tons', 'amp-hours-to-watt-hours' );
$powerbachat_links   = wp_list_pluck( powerbachat_tool_links(), 1 );
$powerbachat_popular = array_values(
	array_filter(
		$powerbachat_popular,
		function ( $slug ) use ( $powerbachat_links ) {
			return in_array( '/tools/' . $slug . '/', $powerbachat_links, true );
		}
	)
);
?>
<section class="global-band" data-region-only="world" aria-labelledby="global-title">
	<div class="wrap">
		<p class="kicker kicker--light"><span class="kicker__rule" aria-hidden="true"></span><?php esc_html_e( 'Electrical calculators', 'powerbachat' ); ?></p>
		<h2 class="global-band__title" id="global-title"><?php esc_html_e( 'Watts, amps, kVA and BTU, worked out in seconds', 'powerbachat' ); ?></h2>
		<p class="global-band__lede"><?php esc_html_e( 'Free converters that work both ways as you type, show the formula they used and cover 120 V and 230 V supplies, single phase, three phase and DC.', 'powerbachat' ); ?></p>
		<?php echo powerbachat_tool_cards( $powerbachat_popular, __( 'Popular converters', 'powerbachat' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<p class="global-band__more">
			<a class="btn btn--volt" href="<?php echo esc_url( home_url( '/tools/' ) ); ?>"><?php esc_html_e( 'See all converters', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<a class="global-band__south" href="#calculator"><?php esc_html_e( 'Paying a bill in Pakistan, India or Bangladesh? Our bill calculators are below.', 'powerbachat' ); ?></a>
		</p>
	</div>
</section>
