<?php
/**
 * Infinite skills marquee.
 *
 * @package Nabia
 */

$nabia_items = nabia_mod_list( 'marquee_items' );
if ( ! $nabia_items ) {
	return;
}
?>
<section class="marquee-section" aria-label="<?php esc_attr_e( 'Skills', 'nabia' ); ?>">
	<div class="marquee-wrap">
		<div class="marquee">
			<?php for ( $nabia_copy = 0; $nabia_copy < 2; $nabia_copy++ ) : ?>
				<ul class="marquee-track" <?php echo $nabia_copy ? 'aria-hidden="true"' : ''; ?>>
					<?php foreach ( $nabia_items as $nabia_item ) : ?>
						<li><?php echo esc_html( $nabia_item ); ?><span class="marquee-star" aria-hidden="true">✦</span></li>
					<?php endforeach; ?>
				</ul>
			<?php endfor; ?>
		</div>
	</div>
</section>
