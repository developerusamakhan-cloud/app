<?php
/**
 * Footer: "Hosting I recommend" card with an affiliate link and a short disclosure.
 *
 * @package Nabia
 */

$nabia_host_url  = nabia_mod( 'hosting_url' );
$nabia_host_name = nabia_mod( 'hosting_name' );
if ( ! $nabia_host_url || ! $nabia_host_name ) {
	return;
}
?>
<div class="host-rec">
	<p class="host-rec-label"><?php esc_html_e( 'Hosting I recommend', 'nabia' ); ?></p>
	<a class="host-rec-link" href="<?php echo esc_url( $nabia_host_url ); ?>" target="_blank" rel="sponsored noopener noreferrer">
		<span class="host-rec-name"><?php echo esc_html( $nabia_host_name ); ?></span>
		<span class="host-rec-cta"><?php esc_html_e( 'Get hosting', 'nabia' ); ?><?php nabia_icon( 'arrow' ); ?></span>
	</a>
	<p class="host-rec-note"><?php esc_html_e( 'Fast, affordable hosting I use for client websites. Affiliate link: I may earn a commission at no extra cost to you.', 'nabia' ); ?></p>
</div>
