<?php
/**
 * Process steps.
 *
 * @package Nabia
 */

?>
<section class="section process section-dark" id="process">
	<div class="container">
		<?php nabia_section_head( __( 'Process', 'nabia' ), nabia_mod( 'process_title' ) ); ?>

		<ol class="process-list">
			<?php foreach ( nabia_process() as $nabia_index => $nabia_step ) : ?>
				<li class="process-step" data-reveal style="--i:<?php echo (int) $nabia_index; ?>">
					<span class="process-num"><?php echo esc_html( sprintf( '%02d', $nabia_index + 1 ) ); ?></span>
					<h3 class="process-title"><?php echo esc_html( $nabia_step['title'] ); ?></h3>
					<p><?php echo nabia_link_text( $nabia_step['text'], 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
