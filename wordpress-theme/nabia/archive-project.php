<?php
/**
 * Portfolio archive (Websites) with category filter.
 *
 * @package Nabia
 */

get_header();

$nabia_tax     = nabia_portfolio_taxonomy();
$nabia_terms   = $nabia_tax ? get_terms(
	array(
		'taxonomy'   => $nabia_tax,
		'hide_empty' => true,
	)
) : array();
$nabia_terms   = is_wp_error( $nabia_terms ) ? array() : $nabia_terms;
$nabia_current = ( $nabia_tax && is_tax( $nabia_tax ) ) ? get_queried_object_id() : 0;
$nabia_total   = (int) wp_count_posts( nabia_portfolio_type() )->publish;

nabia_page_header(
	array(
		'eyebrow' => __( 'Portfolio', 'nabia' ),
		'icon'    => 'grid',
		'title'   => $nabia_current ? single_term_title( '', false ) : nabia_mod( 'work_title' ),
		'intro'   => __( 'A selection of websites and online stores I designed and built for clients around the world. Click any project to see the details and visit the live site.', 'nabia' ),
		'crumbs'  => $nabia_current ? array( array( __( 'Portfolio', 'nabia' ), nabia_portfolio_url() ) ) : array(),
		'aside'   => nabia_aside_card(
			__( 'At a glance', 'nabia' ),
			array(
				array( __( 'Projects', 'nabia' ), (string) $nabia_total ),
				array( __( 'Categories', 'nabia' ), (string) count( $nabia_terms ) ),
				array( __( 'Platforms', 'nabia' ), __( 'WordPress, Shopify & more', 'nabia' ) ),
				array( __( 'Experience', 'nabia' ), nabia_mod( 'stat_1_number' ) . nabia_mod( 'stat_1_suffix' ) . ' ' . __( 'years', 'nabia' ) ),
			),
			nabia_button( __( 'Start your project', 'nabia' ), nabia_hire_url() )
		),
		'class'   => 'is-archive',
	)
);
?>
<section class="section section-tight work">
	<div class="container">
		<?php if ( $nabia_terms ) : ?>
			<ul class="filter-pills">
				<li><a class="<?php echo $nabia_current ? '' : 'is-active'; ?>" href="<?php echo esc_url( nabia_portfolio_url() ); ?>"><?php esc_html_e( 'All', 'nabia' ); ?></a></li>
				<?php foreach ( $nabia_terms as $nabia_term ) : ?>
					<li><a class="<?php echo $nabia_current === $nabia_term->term_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $nabia_term ) ); ?>"><?php echo esc_html( $nabia_term->name ); ?> <em><?php echo (int) $nabia_term->count; ?></em></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

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
