<?php
/**
 * Platforms I build on (WordPress, Shopify, Wix, Webflow, Squarespace, custom code, AI).
 *
 * @package Nabia
 */

?>
<section class="section section-tight platforms" id="platforms">
	<div class="container">
		<div class="platforms-head">
			<?php nabia_section_head( __( 'Platforms', 'nabia' ), nabia_mod( 'platforms_title' ) ); ?>
			<p class="platforms-text" data-reveal><?php echo esc_html( nabia_mod( 'platforms_text' ) ); ?></p>
		</div>

		<ul class="platform-grid">
			<?php foreach ( nabia_platforms() as $nabia_index => $nabia_platform ) : ?>
				<li class="platform-tile" data-reveal style="--i:<?php echo (int) $nabia_index % 4; ?>;--brand:<?php echo esc_attr( $nabia_platform['color'] ); ?>">
					<span class="platform-mark" aria-hidden="true"><?php echo esc_html( $nabia_platform['mark'] ); ?></span>
					<span class="platform-info">
						<strong><?php echo esc_html( $nabia_platform['name'] ); ?></strong>
						<span><?php echo esc_html( $nabia_platform['text'] ); ?></span>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
