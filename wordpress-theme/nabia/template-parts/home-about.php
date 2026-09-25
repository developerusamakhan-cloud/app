<?php
/**
 * About section.
 *
 * @package Nabia
 */

$nabia_about_image = nabia_mod( 'about_image' ) ? nabia_mod( 'about_image' ) : nabia_author_photo_url();
$nabia_intro_video = nabia_mod( 'intro_video' );
$nabia_intro_thumb = nabia_mod( 'intro_video_poster' );
$nabia_skills      = array(
	'WordPress & Elementor' => 95,
	'Graphic Design'        => 90,
	'HTML, CSS & jQuery'    => 92,
	'WooCommerce & PHP'     => 85,
);
?>
<section class="section about" id="about">
	<div class="container about-grid">
		<div class="about-media" data-reveal>
			<?php if ( $nabia_intro_video ) : ?>
				<figure class="intro-video">
					<div class="intro-video-stack">
					<div class="intro-video-frame">
						<video src="<?php echo esc_url( $nabia_intro_video ); ?>" <?php echo $nabia_intro_thumb ? 'poster="' . esc_url( $nabia_intro_thumb ) . '"' : ''; ?> muted loop playsinline controls preload="metadata" data-intro-video></video>
						<button class="video-sound intro-sound" type="button" data-intro-sound>
							<?php nabia_icon( 'volume' ); ?><span><?php esc_html_e( 'Tap for sound', 'nabia' ); ?></span>
						</button>
						<span class="intro-badge"><span class="pulse" aria-hidden="true"></span><?php esc_html_e( 'My intro', 'nabia' ); ?></span>
					</div>
					<span class="intro-hello" aria-hidden="true"><span class="intro-hello-wave">👋</span><?php esc_html_e( 'Hi, I’m', 'nabia' ); ?> <?php echo esc_html( trim( strtok( nabia_mod( 'brand_name' ), ' ' ) ) ); ?>!</span>
					</div>
					<figcaption class="intro-caption"><?php echo esc_html( nabia_mod( 'intro_video_caption' ) ); ?></figcaption>
				</figure>
			<?php elseif ( $nabia_about_image ) : ?>
				<img src="<?php echo esc_url( $nabia_about_image ); ?>" alt="<?php echo esc_attr( nabia_mod( 'brand_name' ) ); ?>" loading="lazy" width="600" height="720">
			<?php else : ?>
				<div class="about-art" aria-hidden="true">
					<span class="shape shape-circle" data-parallax="-0.08"></span>
					<span class="shape shape-square" data-parallax="0.1"></span>
					<span class="shape shape-pill" data-parallax="-0.14"></span>
					<span class="about-art-text">Hello!</span>
				</div>
			<?php endif; ?>
		</div>

		<div class="about-copy">
			<?php nabia_section_head( __( 'About me', 'nabia' ), nabia_mod( 'about_title' ) ); ?>
			<div class="about-text" data-reveal>
				<?php echo wp_kses_post( wpautop( nabia_mod( 'about_text' ) ) ); ?>
			</div>

			<ul class="skills" data-reveal>
				<?php foreach ( apply_filters( 'nabia_skills', $nabia_skills ) as $nabia_skill => $nabia_level ) : ?>
					<li class="skill">
						<span class="skill-head"><span><?php echo esc_html( $nabia_skill ); ?></span><span><?php echo (int) $nabia_level; ?>%</span></span>
						<span class="skill-bar"><span style="--level:<?php echo (int) $nabia_level; ?>%"></span></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
