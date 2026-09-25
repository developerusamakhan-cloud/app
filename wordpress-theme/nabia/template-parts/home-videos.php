<?php
/**
 * Client video reviews from YouTube. Videos load and autoplay (muted) when scrolled into view.
 *
 * Add videos in Dashboard → Video Reviews (or the Customizer list).
 *
 * @package Nabia
 */

$nabia_videos = nabia_video_reviews();

if ( ! $nabia_videos && ! current_user_can( 'edit_posts' ) ) {
	return;
}

$nabia_count  = count( $nabia_videos );
$nabia_layout = nabia_mod( 'video_layout' );
$nabia_has_files = (bool) wp_list_filter( $nabia_videos, array( 'type' => 'file' ) );
if ( $nabia_has_files ) {
	// Self-hosted videos always use the carousel.
	$nabia_layout = 'vertical';
} elseif ( 'auto' === $nabia_layout || ! in_array( $nabia_layout, array( 'wide', 'vertical' ), true ) ) {
	$nabia_vertical = $nabia_videos && count( wp_list_filter( $nabia_videos, array( 'vertical' => true ) ) ) === $nabia_count;
	$nabia_layout   = $nabia_vertical ? 'vertical' : 'wide';
}
$nabia_channel = nabia_mod( 'social_youtube' );
?>
<section class="section videos section-dark" id="reviews">
	<div class="container">
		<div class="videos-head">
			<?php nabia_section_head( __( 'Video reviews', 'nabia' ), nabia_mod( 'videos_title' ) ); ?>
			<div class="videos-meta" data-reveal>
				<?php if ( nabia_mod( 'videos_text' ) ) : ?>
					<p class="videos-intro"><?php echo esc_html( nabia_mod( 'videos_text' ) ); ?></p>
				<?php endif; ?>
				<div class="videos-actions">
					<?php if ( $nabia_count ) : ?>
						<span class="videos-count"><strong><?php echo (int) $nabia_count; ?></strong> <?php echo esc_html( _n( 'video review', 'video reviews', $nabia_count, 'nabia' ) ); ?></span>
					<?php endif; ?>
					<?php if ( $nabia_channel ) : ?>
						<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( $nabia_channel ); ?>" target="_blank" rel="noopener noreferrer"><?php nabia_icon( 'youtube' ); ?><span><?php esc_html_e( 'More on YouTube', 'nabia' ); ?></span></a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ( ! $nabia_videos ) : ?>
			<div class="videos-empty">
				<span class="videos-empty-icon"><?php nabia_icon( 'youtube' ); ?></span>
				<h3><?php esc_html_e( 'Add your YouTube video testimonials', 'nabia' ); ?></h3>
				<p><?php esc_html_e( 'Only logged-in editors see this box. Go to Dashboard → Video Reviews → Add video review, type the client name and paste the YouTube link. Every video appears here in a big player with a playlist, playing automatically (muted) when visitors scroll to it.', 'nabia' ); ?></p>
				<a class="btn btn-accent" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=video_review' ) ); ?>"><span><?php esc_html_e( 'Add your first video review', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
			</div>

		<?php elseif ( 'vertical' === $nabia_layout ) : ?>
			<div class="short-carousel" data-carousel data-reveal>
				<div class="short-reel" data-reel data-autoplay-all>
					<?php foreach ( $nabia_videos as $nabia_video ) : ?>
						<?php $nabia_meta = trim( $nabia_video['role'] . ( $nabia_video['role'] && $nabia_video['caption'] ? ' · ' : '' ) . $nabia_video['caption'] ); ?>
						<figure class="short-card">
							<?php if ( 'file' === $nabia_video['type'] ) : ?>
								<div class="video-frame is-vertical is-file" data-video-src="<?php echo esc_url( $nabia_video['src'] ); ?>">
									<video muted loop playsinline preload="none" controls aria-label="<?php echo esc_attr( sprintf( /* translators: %s: client name */ __( 'Video review by %s', 'nabia' ), $nabia_video['name'] ) ); ?>">
										<source data-src="<?php echo esc_url( $nabia_video['src'] ); ?>#t=0.1">
									</video>
									<button class="video-sound" type="button"><?php nabia_icon( 'volume' ); ?><span><?php esc_html_e( 'Tap for sound', 'nabia' ); ?></span></button>
								</div>
							<?php else : ?>
								<div class="video-frame is-vertical" data-video-id="<?php echo esc_attr( $nabia_video['id'] ); ?>">
									<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nabia_video['id'] . '/hqdefault.jpg' ); ?>" alt="" loading="lazy" width="480" height="360">
									<button class="video-sound" type="button"><?php nabia_icon( 'volume' ); ?><span><?php esc_html_e( 'Tap for sound', 'nabia' ); ?></span></button>
								</div>
							<?php endif; ?>
							<?php if ( $nabia_video['name'] || $nabia_meta ) : ?>
								<figcaption>
									<span class="short-stars" aria-hidden="true">★★★★★</span>
									<?php if ( $nabia_video['name'] ) : ?>
										<strong><?php echo esc_html( $nabia_video['name'] ); ?></strong>
									<?php endif; ?>
									<?php if ( $nabia_meta ) : ?>
										<span><?php echo esc_html( $nabia_meta ); ?></span>
									<?php endif; ?>
								</figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
				<?php if ( $nabia_count > 1 ) : ?>
					<div class="carousel-controls">
						<button class="slider-btn" type="button" data-carousel-step="-1" aria-label="<?php esc_attr_e( 'Previous videos', 'nabia' ); ?>"><?php nabia_icon( 'arrow' ); ?></button>
						<span class="carousel-dots" data-carousel-dots></span>
						<button class="slider-btn" type="button" data-carousel-step="1" aria-label="<?php esc_attr_e( 'Next videos', 'nabia' ); ?>"><?php nabia_icon( 'arrow' ); ?></button>
					</div>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<?php $nabia_main = $nabia_videos[0]; ?>
			<div class="video-stage<?php echo $nabia_count > 1 ? ' has-list' : ''; ?>" data-video-stage data-reveal>
				<div class="video-main">
					<div class="video-frame" data-video-id="<?php echo esc_attr( $nabia_main['id'] ); ?>">
						<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nabia_main['id'] . '/hqdefault.jpg' ); ?>" alt="" loading="lazy" width="480" height="360">
						<button class="video-sound" type="button"><?php nabia_icon( 'volume' ); ?><span><?php esc_html_e( 'Tap for sound', 'nabia' ); ?></span></button>
					</div>
					<div class="video-caption" data-video-caption>
						<span class="video-caption-avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $nabia_main['name'] ? $nabia_main['name'] : '★', 0, 1 ) ); ?></span>
						<span class="video-caption-text">
							<strong data-caption-name><?php echo esc_html( $nabia_main['name'] ); ?></strong>
							<span data-caption-meta><?php echo esc_html( trim( $nabia_main['role'] . ( $nabia_main['role'] && $nabia_main['caption'] ? ' · ' : '' ) . $nabia_main['caption'] ) ); ?></span>
						</span>
						<?php if ( $nabia_count > 1 ) : ?>
							<span class="video-nav">
								<button class="slider-btn" type="button" data-video-step="-1" aria-label="<?php esc_attr_e( 'Previous video', 'nabia' ); ?>"><?php nabia_icon( 'arrow' ); ?></button>
								<button class="slider-btn" type="button" data-video-step="1" aria-label="<?php esc_attr_e( 'Next video', 'nabia' ); ?>"><?php nabia_icon( 'arrow' ); ?></button>
							</span>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $nabia_count > 1 ) : ?>
					<div class="video-playlist">
						<p class="video-playlist-title"><?php esc_html_e( 'All reviews', 'nabia' ); ?> <span><?php echo (int) $nabia_count; ?></span></p>
						<ol class="video-list">
							<?php foreach ( $nabia_videos as $nabia_index => $nabia_video ) : ?>
								<?php $nabia_meta = trim( $nabia_video['role'] . ( $nabia_video['role'] && $nabia_video['caption'] ? ' · ' : '' ) . $nabia_video['caption'] ); ?>
								<li>
									<button class="video-thumb<?php echo 0 === $nabia_index ? ' is-active' : ''; ?>" type="button" data-id="<?php echo esc_attr( $nabia_video['id'] ); ?>" data-name="<?php echo esc_attr( $nabia_video['name'] ); ?>" data-meta="<?php echo esc_attr( $nabia_meta ); ?>">
										<span class="video-thumb-img">
											<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nabia_video['id'] . '/mqdefault.jpg' ); ?>" alt="" loading="lazy" width="320" height="180">
											<span class="video-thumb-play"><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>
										</span>
										<span class="video-thumb-text">
											<strong><?php echo esc_html( $nabia_video['name'] ? $nabia_video['name'] : __( 'Client review', 'nabia' ) ); ?></strong>
											<?php echo esc_html( $nabia_meta ); ?>
										</span>
									</button>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
