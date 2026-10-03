<?php
/**
 * Not found: the page is in load-shedding.
 *
 * @package PowerBachat
 */

get_header();
?>
<section class="notfound">
	<div class="wrap notfound__inner">
		<svg class="notfound__bulb" viewBox="0 0 120 160" aria-hidden="true">
			<path d="M60 12c-25 0-42 19-42 41 0 16 8 26 16 35 6 7 9 13 9 21h34c0-8 3-14 9-21 8-9 16-19 16-35 0-22-17-41-42-41Z" />
			<path d="M44 121h32M46 133h28M52 145h16" />
			<path class="notfound__filament" d="M50 109V84l10-12 10 12v25" />
		</svg>
		<p class="kicker"><?php esc_html_e( 'Error 404', 'powerbachat' ); ?></p>
		<h1 class="page-head__title"><?php esc_html_e( 'This page is in load-shedding.', 'powerbachat' ); ?></h1>
		<p class="page-head__lede"><?php esc_html_e( 'It may have moved, or it never had power to begin with. The calculators are still running, though.', 'powerbachat' ); ?></p>
		<div class="notfound__actions">
			<a class="btn btn--volt" href="<?php echo esc_url( home_url( '/#calculator' ) ); ?>"><?php esc_html_e( 'Check my bill', 'powerbachat' ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'powerbachat' ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();
