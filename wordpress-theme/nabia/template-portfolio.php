<?php
/**
 * Template Name: Portfolio
 * Template Post Type: page
 *
 * Portfolio overview: every Website with "Show more".
 * Assign it to your portfolio page (for example /my-works/).
 *
 * @package Nabia
 */

get_header();

$nabia_type  = nabia_portfolio_type();
$nabia_tax   = nabia_portfolio_taxonomy();
$nabia_items = new WP_Query(
	array(
		'post_type'      => $nabia_type,
		'posts_per_page' => 120,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	)
);
$nabia_show  = 12;

nabia_page_header(
	array(
		'eyebrow' => __( 'Portfolio', 'nabia' ),
		'icon'    => 'grid',
		'title'   => get_the_title() ? get_the_title() : nabia_mod( 'work_title' ),
		'intro'   => __( 'Websites and online stores I designed and built for clients around the world. Click any project to visit the live website.', 'nabia' ),
		'aside'   => nabia_aside_card(
			__( 'At a glance', 'nabia' ),
			array(
				array( __( 'Projects', 'nabia' ), (string) $nabia_items->post_count ),
				array( __( 'Clients', 'nabia' ), __( 'Worldwide', 'nabia' ) ),
				array( __( 'Platforms', 'nabia' ), __( 'WordPress, Shopify & more', 'nabia' ) ),
				array( __( 'Experience', 'nabia' ), nabia_mod( 'stat_1_number' ) . nabia_mod( 'stat_1_suffix' ) . ' ' . __( 'years', 'nabia' ) ),
			),
			nabia_button( __( 'Start your project', 'nabia' ), nabia_hire_url() )
		),
		'class'   => 'is-archive',
	)
);
?>
<section class="section section-tight work portfolio-page" data-portfolio>
	<div class="container">
		<?php if ( $nabia_items->have_posts() ) : ?>
			<div class="work-grid" data-portfolio-grid>
				<?php
				$nabia_index = 0;
				while ( $nabia_items->have_posts() ) :
					$nabia_items->the_post();
					$nabia_slugs = $nabia_tax ? wp_get_post_terms( get_the_ID(), $nabia_tax, array( 'fields' => 'slugs' ) ) : array();
					$nabia_slugs = is_wp_error( $nabia_slugs ) ? array() : $nabia_slugs;
					?>
					<div class="pf-item<?php echo $nabia_index >= $nabia_show ? ' is-more' : ''; ?>" data-cats="<?php echo esc_attr( implode( ' ', $nabia_slugs ) ); ?>">
						<?php get_template_part( 'template-parts/project', 'card' ); ?>
					</div>
					<?php
					++$nabia_index;
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<p class="pf-empty" hidden><?php esc_html_e( 'No projects in this category yet.', 'nabia' ); ?></p>
			<?php if ( $nabia_index > $nabia_show ) : ?>
				<div class="review-more">
					<button class="btn btn-ghost" type="button" data-portfolio-more><span><?php esc_html_e( 'Show more projects', 'nabia' ); ?></span></button>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'New projects are on their way.', 'nabia' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
while ( have_posts() ) {
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}
}
get_template_part( 'template-parts/why' );
get_template_part( 'template-parts/home', 'testimonials' );
get_footer();
