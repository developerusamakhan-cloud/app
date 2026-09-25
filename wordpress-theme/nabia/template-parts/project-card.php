<?php
/**
 * Project card (inside the loop).
 *
 * @package Nabia
 */

$nabia_tax   = nabia_portfolio_taxonomy();
$nabia_types = $nabia_tax ? get_the_terms( get_the_ID(), $nabia_tax ) : array();
$nabia_type  = ( $nabia_types && ! is_wp_error( $nabia_types ) ) ? $nabia_types[0]->name : get_post_meta( get_the_ID(), '_nabia_role', true );
$nabia_year  = get_post_meta( get_the_ID(), '_nabia_year', true );
$nabia_year  = $nabia_year ? $nabia_year : get_the_date( 'Y' );
$nabia_live  = nabia_project_live_url( get_the_ID() );
?>
<article <?php post_class( 'project-card' ); ?> data-reveal data-cursor="<?php esc_attr_e( 'View', 'nabia' ); ?>">
	<a class="project-link" href="<?php the_permalink(); ?>">
		<div class="project-media" data-view="<?php esc_attr_e( 'View project →', 'nabia' ); ?>">
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
		<div class="project-meta">
			<h3 class="project-title"><?php the_title(); ?></h3>
			<p class="project-type">
				<?php echo esc_html( $nabia_type ); ?>
				<?php if ( $nabia_year ) : ?>
					· <?php echo esc_html( $nabia_year ); ?>
				<?php endif; ?>
			</p>
		</div>
	</a>
	<?php if ( $nabia_live ) : ?>
		<a class="project-live" href="<?php echo esc_url( $nabia_live ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visit site', 'nabia' ); ?> ↗</a>
	<?php endif; ?>
</article>
