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
					<?php $nabia_url = isset( $nabia_service['slug'] ) ? nabia_service_url( $nabia_service['slug'] ) : ''; ?>
					<h3 class="service-title">
						<?php if ( $nabia_url ) : ?>
							<a class="service-link" href="<?php echo esc_url( $nabia_url ); ?>"><?php echo esc_html( $nabia_service['title'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $nabia_service['title'] ); ?>
						<?php endif; ?>
					</h3>
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
