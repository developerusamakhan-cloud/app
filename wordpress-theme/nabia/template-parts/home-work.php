<?php
/**
 * Selected work: real Projects, or placeholders until some are published.
 *
 * @package Nabia
 */

$nabia_projects = new WP_Query(
	array(
		'post_type'           => nabia_portfolio_type(),
		'posts_per_page'      => max( 2, (int) nabia_mod( 'portfolio_count' ) ),
		'orderby'             => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
?>
<section class="section work" id="work">
	<div class="container">
		<div class="section-head-row">
			<?php nabia_section_head( __( 'Portfolio', 'nabia' ), nabia_mod( 'work_title' ) ); ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( nabia_portfolio_url() ); ?>" data-magnetic><span><?php esc_html_e( 'All projects', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
		</div>

		<div class="work-grid">
			<?php if ( $nabia_projects->have_posts() ) : ?>
				<?php
				while ( $nabia_projects->have_posts() ) :
					$nabia_projects->the_post();
					get_template_part( 'template-parts/project', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			<?php else : ?>
				<?php foreach ( nabia_demo_projects() as $nabia_demo ) : ?>
					<article class="project-card" data-reveal data-cursor="<?php esc_attr_e( 'View', 'nabia' ); ?>">
						<div class="project-media" data-view="<?php esc_attr_e( 'View project →', 'nabia' ); ?>" style="--hue:<?php echo esc_attr( $nabia_demo['hue'] ); ?>">
							<div class="mock" aria-hidden="true">
								<span class="mock-bar"><i></i><i></i><i></i></span>
								<span class="mock-title"><?php echo esc_html( $nabia_demo['title'] ); ?></span>
								<span class="mock-line"></span><span class="mock-line short"></span>
								<span class="mock-btn"></span>
							</div>
						</div>
						<div class="project-meta">
							<h3 class="project-title"><?php echo esc_html( $nabia_demo['title'] ); ?></h3>
							<p class="project-type"><?php echo esc_html( $nabia_demo['type'] ); ?> · <?php echo esc_html( $nabia_demo['year'] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					<p class="admin-hint"><?php esc_html_e( 'These are placeholders — add your work under Dashboard → Projects (or choose your portfolio post type in Customize → Nabia Theme → Portfolio).', 'nabia' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
