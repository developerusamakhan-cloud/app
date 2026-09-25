<?php
/**
 * Hero section.
 *
 * @package Nabia
 */

$nabia_words = nabia_mod_list( 'hero_rotating' );
$nabia_image = nabia_mod( 'hero_image' );
$nabia_name  = nabia_mod( 'brand_name' );
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
				<span class="line"><span data-hero-line><?php echo esc_html( nabia_mod( 'hero_line_1' ) ); ?></span></span>
				<span class="line"><span data-hero-line><?php echo esc_html( nabia_mod( 'hero_line_2' ) ); ?></span></span>
				<?php if ( $nabia_words ) : ?>
					<span class="line">
						<span class="rotator" data-hero-line data-words="<?php echo esc_attr( wp_json_encode( $nabia_words ) ); ?>">
							<span class="rotator-word"><?php echo esc_html( $nabia_words[0] ); ?></span>
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
			</div>
		</div>

		<div class="hero-visual" data-reveal>
			<div class="portrait" data-tilt>
				<?php if ( $nabia_image ) : ?>
					<img src="<?php echo esc_url( $nabia_image ); ?>" alt="<?php echo esc_attr( $nabia_name ); ?>" width="560" height="700" fetchpriority="high">
				<?php else : ?>
					<div class="portrait-placeholder" aria-hidden="true">
						<span class="ph-window"><i></i><i></i><i></i></span>
						<span class="ph-block ph-block-1"></span>
						<span class="ph-block ph-block-2"></span>
						<span class="ph-block ph-block-3"></span>
						<span class="ph-initials"><?php echo esc_html( mb_substr( $nabia_name, 0, 1 ) ); ?></span>
					</div>
				<?php endif; ?>

				<span class="float-tag float-tag-1"><?php nabia_icon( 'code' ); ?> WordPress</span>
				<span class="float-tag float-tag-2"><?php nabia_icon( 'pen' ); ?> <?php esc_html_e( 'Design', 'nabia' ); ?></span>
				<span class="float-tag float-tag-3"><?php nabia_icon( 'bolt' ); ?> <?php esc_html_e( 'Fast', 'nabia' ); ?></span>
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
				<li class="stat" data-reveal>
					<span class="stat-number"><span data-count="<?php echo esc_attr( $nabia_num ); ?>"><?php echo esc_html( $nabia_num ); ?></span><?php echo esc_html( nabia_mod( 'stat_' . $nabia_i . '_suffix' ) ); ?></span>
					<span class="stat-label"><?php echo esc_html( nabia_mod( 'stat_' . $nabia_i . '_label' ) ); ?></span>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>
