<?php
/**
 * Project card (inside the loop).
 *
 * @package Nabia
 */

$nabia_live = nabia_project_live_url( get_the_ID() );
$nabia_url  = $nabia_live ? $nabia_live : nabia_portfolio_url();
?>
<article <?php post_class( 'project-card' ); ?> data-reveal data-cursor="<?php esc_attr_e( 'Visit', 'nabia' ); ?>">
	<a class="project-link" href="<?php echo esc_url( $nabia_url ); ?>"<?php echo $nabia_live ? ' target="_blank" rel="noopener"' : ''; ?>>
		<?php if ( 'image' !== nabia_mod( 'portfolio_thumbs' ) ) : ?>
			<?php $nabia_show = nabia_project_showcase( get_the_ID() ); ?>
			<div class="project-media is-showcase" data-view="<?php esc_attr_e( 'Visit site ↗', 'nabia' ); ?>" style="--hue:<?php echo esc_attr( $nabia_show['hue'] ); ?>">
				<div class="sc-browser">
					<span class="sc-bar" aria-hidden="true"><i></i><i></i><i></i><?php if ( $nabia_show['domain'] ) : ?><span class="sc-url"><?php echo esc_html( $nabia_show['domain'] ); ?></span><?php endif; ?></span>
					<div class="sc-screen<?php echo $nabia_show['tall'] ? ' is-tall' : ''; ?>">
						<?php if ( $nabia_show['desktop'] ) : ?>
							<img src="<?php echo esc_url( $nabia_show['desktop'] ); ?>"<?php if ( $nabia_show['srcset'] ) : ?> srcset="<?php echo esc_attr( $nabia_show['srcset'] ); ?>" sizes="(max-width: 700px) 72vw, (max-width: 1024px) 38vw, 440px"<?php endif; ?> alt="<?php echo esc_attr( sprintf( /* translators: %s: project name */ __( '%s website on desktop', 'nabia' ), get_the_title() ) ); ?>" loading="lazy" decoding="async">
						<?php else : ?>
							<span class="sc-empty" aria-hidden="true"><span class="mock-title"><?php the_title(); ?></span><span class="mock-line"></span><span class="mock-line short"></span></span>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( $nabia_show['mobile'] ) : ?>
					<div class="sc-phone"><div class="sc-phone-screen"><img src="<?php echo esc_url( $nabia_show['mobile'] ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: project name */ __( '%s website on mobile', 'nabia' ), get_the_title() ) ); ?>" loading="lazy" decoding="async"></div></div>
				<?php endif; ?>
			</div>
		<?php else : ?>
		<div class="project-media" data-view="<?php esc_attr_e( 'Visit site ↗', 'nabia' ); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'nabia-card', array( 'loading' => 'lazy' ) ); ?>
			<?php else : ?>
				<div class="mock" aria-hidden="true">
					<span class="mock-bar"><i></i><i></i><i></i></span>
					<span class="mock-title"><?php the_title(); ?></span>
					<span class="mock-line"></span><span class="mock-line short"></span>
				</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="project-meta">
			<h3 class="project-title"><?php the_title(); ?></h3>
		</div>
	</a>
</article>
