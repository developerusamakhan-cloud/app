<?php
/**
 * Client video reviews from YouTube. Videos load and autoplay (muted) when scrolled into view.
 *
 * @package Nabia
 */

$nabia_videos = nabia_video_reviews();

if ( ! $nabia_videos && ! current_user_can( 'edit_theme_options' ) ) {
	return;
}

$nabia_layout = nabia_mod( 'video_layout' );
if ( 'auto' === $nabia_layout || ! in_array( $nabia_layout, array( 'wide', 'vertical' ), true ) ) {
	$nabia_vertical = $nabia_videos && count( wp_list_filter( $nabia_videos, array( 'vertical' => true ) ) ) === count( $nabia_videos );
	$nabia_layout   = $nabia_vertical ? 'vertical' : 'wide';
}
?>
<section class="section videos section-dark" id="reviews">
	<div class="container">
		<div class="section-head-row">
			<?php nabia_section_head( __( 'Video reviews', 'nabia' ), nabia_mod( 'videos_title' ) ); ?>
			<?php if ( nabia_mod( 'videos_text' ) ) : ?>
				<p class="videos-intro" data-reveal><?php echo esc_html( nabia_mod( 'videos_text' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( ! $nabia_videos ) : ?>
			<div class="videos-empty">
				<span class="videos-empty-icon"><?php nabia_icon( 'youtube' ); ?></span>
				<p><?php esc_html_e( 'Only you can see this. Paste your YouTube review links in Appearance → Customize → Nabia Theme → Video Reviews and they will appear here, playing automatically (muted) when visitors scroll to them.', 'nabia' ); ?></p>
				<?php if ( is_customize_preview() === false ) : ?>
					<a class="btn btn-accent" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=nabia_videos' ) ); ?>"><span><?php esc_html_e( 'Add videos', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
				<?php endif; ?>
			</div>

		<?php elseif ( 'vertical' === $nabia_layout ) : ?>
			<div class="video-reel" data-reveal>
				<?php foreach ( $nabia_videos as $nabia_video ) : ?>
					<figure class="video-card">
						<div class="video-frame is-vertical" data-video-id="<?php echo esc_attr( $nabia_video['id'] ); ?>">
							<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nabia_video['id'] . '/hqdefault.jpg' ); ?>" alt="" loading="lazy" width="480" height="360">
							<button class="video-sound" type="button"><?php nabia_icon( 'volume' ); ?><span><?php esc_html_e( 'Tap for sound', 'nabia' ); ?></span></button>
						</div>
						<?php if ( $nabia_video['name'] || $nabia_video['caption'] ) : ?>
							<figcaption>
								<strong><?php echo esc_html( $nabia_video['name'] ); ?></strong>
								<?php echo esc_html( $nabia_video['caption'] ); ?>
							</figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>

		<?php else : ?>
			<?php $nabia_main = $nabia_videos[0]; ?>
			<div class="video-stage<?php echo count( $nabia_videos ) > 1 ? ' has-list' : ''; ?>" data-video-stage data-reveal>
				<div class="video-main">
					<div class="video-frame" data-video-id="<?php echo esc_attr( $nabia_main['id'] ); ?>">
						<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nabia_main['id'] . '/hqdefault.jpg' ); ?>" alt="" loading="lazy" width="480" height="360">
						<button class="video-sound" type="button"><?php nabia_icon( 'volume' ); ?><span><?php esc_html_e( 'Tap for sound', 'nabia' ); ?></span></button>
					</div>
					<p class="video-caption" data-video-caption>
						<strong><?php echo esc_html( $nabia_main['name'] ); ?></strong>
						<span><?php echo esc_html( $nabia_main['caption'] ); ?></span>
					</p>
				</div>

				<?php if ( count( $nabia_videos ) > 1 ) : ?>
					<ul class="video-list">
						<?php foreach ( $nabia_videos as $nabia_index => $nabia_video ) : ?>
							<li>
								<button class="video-thumb<?php echo 0 === $nabia_index ? ' is-active' : ''; ?>" type="button" data-id="<?php echo esc_attr( $nabia_video['id'] ); ?>" data-name="<?php echo esc_attr( $nabia_video['name'] ); ?>" data-caption="<?php echo esc_attr( $nabia_video['caption'] ); ?>">
									<span class="video-thumb-img">
										<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nabia_video['id'] . '/mqdefault.jpg' ); ?>" alt="" loading="lazy" width="320" height="180">
										<span class="video-thumb-play"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>
									</span>
									<span class="video-thumb-text">
										<strong><?php echo esc_html( $nabia_video['name'] ? $nabia_video['name'] : __( 'Client review', 'nabia' ) ); ?></strong>
										<?php echo esc_html( $nabia_video['caption'] ); ?>
									</span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
