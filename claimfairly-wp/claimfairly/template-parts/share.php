<?php
/**
 * Share buttons. Plain links, no third-party scripts or tracking.
 *
 * @package ClaimFairly
 */

$claimfairly_url   = rawurlencode( get_permalink() );
$claimfairly_title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
$claimfairly_links = array(
	'x'        => array( 'X', 'https://twitter.com/intent/tweet?url=' . $claimfairly_url . '&text=' . $claimfairly_title ),
	'facebook' => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $claimfairly_url ),
	'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $claimfairly_url ),
	'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . $claimfairly_title . '%20' . $claimfairly_url ),
	'email'    => array( __( 'Email', 'claimfairly' ), 'mailto:?subject=' . $claimfairly_title . '&body=' . $claimfairly_url ),
);
?>
<section class="share" aria-label="<?php esc_attr_e( 'Share this guide', 'claimfairly' ); ?>">
	<p class="share__title"><?php echo claimfairly_icon( 'share', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Found this useful? Share it with someone dealing with a claim.', 'claimfairly' ); ?></p>
	<ul class="share__list">
		<?php foreach ( $claimfairly_links as $claimfairly_key => $claimfairly_link ) : ?>
			<li><a class="share__btn share__btn--<?php echo esc_attr( $claimfairly_key ); ?>" href="<?php echo esc_url( $claimfairly_link[1] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $claimfairly_link[0] ); ?></a></li>
		<?php endforeach; ?>
		<li><button type="button" class="share__btn share__btn--copy" data-copy-url="<?php echo esc_url( get_permalink() ); ?>"><?php echo claimfairly_icon( 'link', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Copy link', 'claimfairly' ); ?></span></button></li>
	</ul>
</section>
