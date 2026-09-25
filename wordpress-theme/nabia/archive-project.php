<?php
/**
 * Portfolio archive with type filter.
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
$nabia_current = ( $nabia_tax && is_tax( $nabia_tax ) ) ? get_queried_object_id() : 0;
?>
<section class="page-hero">
	<div class="container">
		<p class="eyebrow" data-reveal><?php esc_html_e( 'Portfolio', 'nabia' ); ?></p>
		<h1 class="page-title" data-split><?php echo esc_html( is_tax() ? single_term_title( '', false ) : nabia_mod( 'work_title' ) ); ?></h1>

		<?php if ( $nabia_terms && ! is_wp_error( $nabia_terms ) ) : ?>
			<ul class="filter-pills" data-reveal>
				<li><a class="<?php echo $nabia_current ? '' : 'is-active'; ?>" href="<?php echo esc_url( nabia_portfolio_url() ); ?>"><?php esc_html_e( 'All', 'nabia' ); ?></a></li>
				<?php foreach ( $nabia_terms as $nabia_term ) : ?>
					<li><a class="<?php echo $nabia_current === $nabia_term->term_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $nabia_term ) ); ?>"><?php echo esc_html( $nabia_term->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>

<section class="section section-tight work">
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
get_footer();
