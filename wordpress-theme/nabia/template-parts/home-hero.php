<?php
/**
 * Hero section.
 *
 * @package Nabia
 */

$nabia_words = nabia_mod_list( 'hero_rotating' );
$nabia_image = nabia_mod( 'hero_image' );
$nabia_name  = nabia_mod( 'brand_name' );
$nabia_first = trim( strtok( $nabia_name, ' ' ) );
?>
<section class="hero" aria-label="<?php esc_attr_e( 'Introduction', 'nabia' ); ?>">
	<div class="hero-bg" aria-hidden="true">
		<span class="blob blob-1" data-parallax="-0.2"></span>
		<span class="blob blob-2" data-parallax="0.25"></span>
		<span class="grid-lines"></span>
	</div>

	<div class="container hero-inner">
		<div class="hero-copy">
			<?php if ( nabia_mod( 'hero_badge' ) ) : ?>
				<p class="badge" data-reveal><span class="pulse" aria-hidden="true"></span><?php echo esc_html( nabia_mod( 'hero_badge' ) ); ?></p>
			<?php endif; ?>

			<h1 class="hero-title">
				<span class="line"><span data-hero-line><?php echo nabia_tight( nabia_mod( 'hero_line_1' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
				<span class="line"><span data-hero-line><?php echo nabia_tight( nabia_mod( 'hero_line_2' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
				<?php if ( $nabia_words ) : ?>
					<span class="line">
						<span class="rotator" data-hero-line data-words="<?php echo esc_attr( wp_json_encode( $nabia_words ) ); ?>">
							<span class="rotator-word"><?php echo nabia_tight( $nabia_words[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</span>
					</span>
				<?php endif; ?>
			</h1>

			<p class="hero-text" data-reveal><?php echo esc_html( nabia_mod( 'hero_text' ) ); ?></p>

			<div class="hero-actions" data-reveal>
				<?php if ( nabia_mod( 'hero_cta_label' ) ) : ?>
					<a class="btn btn-accent btn-lg" href="<?php echo esc_url( nabia_mod( 'hero_cta_url' ) ); ?>" data-magnetic>
						<span><?php echo esc_html( nabia_mod( 'hero_cta_label' ) ); ?></span><?php nabia_icon( 'arrow' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( nabia_mod( 'hero_cta2_label' ) ) : ?>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( nabia_mod( 'hero_cta2_url' ) ); ?>" data-magnetic>
						<span><?php echo esc_html( nabia_mod( 'hero_cta2_label' ) ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( nabia_mod( 'intro_video' ) ) : ?>
					<a class="intro-link" href="#about">
						<span class="intro-link-play" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>
						<?php esc_html_e( 'Watch my intro', 'nabia' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="hero-visual" data-reveal>
			<div class="stage" data-tilt>
				<?php if ( $nabia_image ) : ?>
					<div class="stage-photo">
						<img src="<?php echo esc_url( $nabia_image ); ?>" alt="<?php echo esc_attr( $nabia_name ); ?>" width="560" height="700" fetchpriority="high">
					</div>
				<?php else : ?>
					<div class="mockup" aria-hidden="true">
						<div class="mockup-bar">
							<span class="mockup-dots"><i></i><i></i><i></i></span>
							<span class="mockup-url"><?php nabia_icon( 'shield' ); ?><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
						</div>
						<div class="mockup-body">
							<div class="mk-nav"><span class="mk-logo"></span><span class="mk-links"><i></i><i></i><i></i></span><span class="mk-pill"></span></div>
							<div class="mk-hero">
								<div class="mk-copy">
									<span class="mk-kicker"><?php esc_html_e( 'New collection', 'nabia' ); ?></span>
									<span class="mk-heading"><?php esc_html_e( 'Grow your brand online.', 'nabia' ); ?></span>
									<i class="mk-line"></i><i class="mk-line short"></i>
									<span class="mk-btn"><?php esc_html_e( 'Get started', 'nabia' ); ?></span>
								</div>
								<div class="mk-art"><span class="mk-sun"></span><span class="mk-hill"></span></div>
							</div>
							<div class="mk-cards"><span></span><span></span><span></span></div>
						</div>
					</div>
				<?php endif; ?>

				<div class="chip chip-speed" aria-hidden="true">
					<svg class="gauge" viewBox="0 0 44 44"><circle class="gauge-track" cx="22" cy="22" r="18"/><circle class="gauge-fill" cx="22" cy="22" r="18" pathLength="100"/></svg>
					<span class="chip-text"><strong>98</strong><?php esc_html_e( 'PageSpeed', 'nabia' ); ?></span>
				</div>

				<div class="chip chip-review" aria-hidden="true">
					<span class="chip-stars"><?php nabia_icon( 'star' ); ?><?php nabia_icon( 'star' ); ?><?php nabia_icon( 'star' ); ?><?php nabia_icon( 'star' ); ?><?php nabia_icon( 'star' ); ?></span>
					<span class="chip-quote"><?php esc_html_e( '“Absolutely love my new website!”', 'nabia' ); ?></span>
				</div>

				<div class="chip chip-growth" aria-hidden="true">
					<span class="chip-text"><strong>+42%</strong><?php esc_html_e( 'more leads', 'nabia' ); ?></span>
					<span class="bars"><i></i><i></i><i></i><i></i><i></i></span>
				</div>

				<div class="collab-cursor" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="22" height="22"><path d="M4 3l16 7.5-7 1.8L9.5 20z"/></svg>
					<span><?php echo esc_html( $nabia_first ); ?></span>
				</div>
			</div>

			<a class="spin-badge" href="#work" aria-label="<?php esc_attr_e( 'Scroll to work', 'nabia' ); ?>">
				<svg viewBox="0 0 120 120" aria-hidden="true">
					<defs><path id="spin-circle" d="M60,60 m-46,0 a46,46 0 1,1 92,0 a46,46 0 1,1 -92,0"/></defs>
					<text><textPath href="#spin-circle" textLength="286" lengthAdjust="spacing"><?php esc_html_e( 'WordPress developer • Graphic designer •', 'nabia' ); ?></textPath></text>
				</svg>
				<span class="spin-badge-arrow"><?php nabia_icon( 'arrow' ); ?></span>
			</a>
		</div>
	</div>

	<div class="container">
		<ul class="stats" aria-label="<?php esc_attr_e( 'Key figures', 'nabia' ); ?>">
			<?php for ( $nabia_i = 1; $nabia_i <= 4; $nabia_i++ ) : ?>
				<?php
				$nabia_num = nabia_mod( 'stat_' . $nabia_i . '_number' );
				if ( '' === $nabia_num ) {
					continue;
				}
				?>
				<li class="stat" data-reveal style="--i:<?php echo (int) $nabia_i; ?>">
					<span class="stat-number"><span data-count="<?php echo esc_attr( $nabia_num ); ?>"><?php echo esc_html( $nabia_num ); ?></span><span class="stat-suffix"><?php echo esc_html( nabia_mod( 'stat_' . $nabia_i . '_suffix' ) ); ?></span></span>
					<span class="stat-label"><?php echo esc_html( nabia_mod( 'stat_' . $nabia_i . '_label' ) ); ?></span>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>
