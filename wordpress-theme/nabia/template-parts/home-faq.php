<?php
/**
 * FAQ accordion (native <details> so it works without JavaScript).
 *
 * @package Nabia
 */

?>
<section class="section faq" id="faq">
	<div class="container faq-grid">
		<?php nabia_section_head( '06', __( 'FAQ', 'nabia' ), nabia_mod( 'faq_title' ) ); ?>

		<div class="faq-list">
			<?php foreach ( nabia_faq() as $nabia_index => $nabia_item ) : ?>
				<details class="faq-item" data-reveal <?php echo 0 === $nabia_index ? 'open' : ''; ?>>
					<summary><span><?php echo esc_html( $nabia_item['q'] ); ?></span><span class="faq-icon" aria-hidden="true"><?php nabia_icon( 'plus' ); ?></span></summary>
					<div class="faq-answer"><p><?php echo esc_html( $nabia_item['a'] ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
