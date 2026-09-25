<?php
/**
 * Portfolio archive (Websites).
 *
 * @package Nabia
 */

get_header();

$nabia_tax     = nabia_portfolio_taxonomy();
$nabia_current = ( $nabia_tax && is_tax( $nabia_tax ) ) ? get_queried_object_id() : 0;
$nabia_total   = (int) wp_count_posts( nabia_portfolio_type() )->publish;

nabia_page_header(
	array(
		'eyebrow' => __( 'Portfolio', 'nabia' ),
		'icon'    => 'grid',
		'title'   => $nabia_current ? single_term_title( '', false ) : nabia_mod( 'work_title' ),
		'intro'   => __( 'A selection of websites and online stores I designed and built for clients around the world. Click any project to visit the live website.', 'nabia' ),
		'crumbs'  => $nabia_current ? array( array( __( 'Portfolio', 'nabia' ), nabia_portfolio_url() ) ) : array(),
		'aside'   => nabia_aside_card(
			__( 'At a glance', 'nabia' ),
			array(
				array( __( 'Projects', 'nabia' ), (string) $nabia_total ),
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
<section class="section section-tight work portfolio-page">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="work-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/project', 'card' );
				endwhile;
				?>
			</div>
			<?php nabia_pagination(); ?>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'New projects are on their way.', 'nabia' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/why' );
get_template_part( 'template-parts/home', 'testimonials' );
get_footer();
