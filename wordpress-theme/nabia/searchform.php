<?php
/**
 * Search form.
 *
 * @package Nabia
 */

$nabia_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $nabia_id ); ?>"><?php esc_html_e( 'Search for:', 'nabia' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $nabia_id ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Search…', 'nabia' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button type="submit" class="search-submit" aria-label="<?php esc_attr_e( 'Search', 'nabia' ); ?>"><?php nabia_icon( 'search' ); ?></button>
</form>
