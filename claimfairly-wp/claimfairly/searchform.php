<?php
/**
 * Search form with an icon button.
 *
 * @package ClaimFairly
 */

$claimfairly_sid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $claimfairly_sid ); ?>" class="screen-reader-text"><?php esc_html_e( 'Search for:', 'claimfairly' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $claimfairly_sid ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Search articles and calculators', 'claimfairly' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<?php if ( ! empty( $args['post_type'] ) ) : ?>
		<input type="hidden" name="post_type" value="<?php echo esc_attr( $args['post_type'] ); ?>">
	<?php endif; ?>
	<button type="submit" class="search-submit"><span class="search-submit__text"><?php esc_html_e( 'Search', 'claimfairly' ); ?></span></button>
</form>
