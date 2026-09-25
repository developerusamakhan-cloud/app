<?php
/**
 * Services grid.
 *
 * @package Nabia
 */

?>
<section class="section services" id="services">
	<div class="container">
		<?php nabia_section_head( __( 'Services', 'nabia' ), nabia_mod( 'services_title' ) ); ?>

		<div class="services-grid">
			<?php foreach ( nabia_services() as $nabia_index => $nabia_service ) : ?>
				<article class="service-card" data-reveal data-tilt style="--i:<?php echo (int) $nabia_index; ?>">
					<span class="service-num"><?php echo esc_html( sprintf( '%02d', $nabia_index + 1 ) ); ?></span>
					<span class="service-icon"><?php nabia_icon( $nabia_service['icon'] ); ?></span>
					<h3 class="service-title"><?php echo esc_html( $nabia_service['title'] ); ?></h3>
					<p class="service-text"><?php echo esc_html( $nabia_service['text'] ); ?></p>
					<?php if ( ! empty( $nabia_service['tags'] ) ) : ?>
						<ul class="tags">
							<?php foreach ( $nabia_service['tags'] as $nabia_tag ) : ?>
								<li><?php echo esc_html( $nabia_tag ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<span class="service-arrow" aria-hidden="true"><?php nabia_icon( 'arrow-up' ); ?></span>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
