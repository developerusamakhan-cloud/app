<?php
/**
 * Search form.
 *
 * @package PowerBachat
 */

$powerbachat_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $powerbachat_id ); ?>"><?php esc_html_e( 'Search for', 'powerbachat' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $powerbachat_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'LESCO unit price, 5 kW solar…', 'powerbachat' ); ?>">
	<button type="submit" class="btn btn--ink"><?php esc_html_e( 'Search', 'powerbachat' ); ?></button>
</form>
