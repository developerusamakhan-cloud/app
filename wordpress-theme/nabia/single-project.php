<?php
/**
 * Single portfolio project.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	$nabia_details = array(
		__( 'Client', 'nabia' )   => get_post_meta( get_the_ID(), '_nabia_client', true ),
		__( 'Year', 'nabia' )     => get_post_meta( get_the_ID(), '_nabia_year', true ),
		__( 'Services', 'nabia' ) => get_post_meta( get_the_ID(), '_nabia_role', true ),
	);
	$nabia_live = get_post_meta( get_the_ID(), '_nabia_url', true );
	?>
	<article <?php post_class(); ?>>
		<section class="page-hero">
			<div class="container">
				<p class="eyebrow" data-reveal><a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>">← <?php esc_html_e( 'All work', 'nabia' ); ?></a></p>
				<h1 class="page-title" data-split><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="page-intro" data-reveal><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<dl class="project-details" data-reveal>
					<?php foreach ( $nabia_details as $nabia_label => $nabia_value ) : ?>
						<?php if ( $nabia_value ) : ?>
							<div><dt><?php echo esc_html( $nabia_label ); ?></dt><dd><?php echo esc_html( $nabia_value ); ?></dd></div>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ( $nabia_live ) : ?>
						<div><dt><?php esc_html_e( 'Live site', 'nabia' ); ?></dt><dd><a href="<?php echo esc_url( $nabia_live ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visit', 'nabia' ); ?> ↗</a></dd></div>
					<?php endif; ?>
				</dl>
			</div>
		</section>

		<div class="section section-tight">
			<div class="container">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="featured-media featured-wide" data-reveal><?php the_post_thumbnail( 'full' ); ?></figure>
				<?php endif; ?>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		</div>

		<?php
		$nabia_next = get_adjacent_post( false, '', false );
		if ( ! $nabia_next ) {
			$nabia_first = get_posts(
				array(
					'post_type'      => 'project',
					'posts_per_page' => 1,
					'order'          => 'ASC',
					'post__not_in'   => array( get_the_ID() ),
				)
			);
			$nabia_next  = $nabia_first ? $nabia_first[0] : null;
		}
		if ( $nabia_next ) :
			?>
			<a class="next-project" href="<?php echo esc_url( get_permalink( $nabia_next ) ); ?>" data-cursor="<?php esc_attr_e( 'Next', 'nabia' ); ?>">
				<div class="container">
					<span class="eyebrow"><?php esc_html_e( 'Next project', 'nabia' ); ?></span>
					<span class="next-project-title"><?php echo esc_html( get_the_title( $nabia_next ) ); ?> <?php nabia_icon( 'arrow' ); ?></span>
				</div>
			</a>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_template_part( 'template-parts/home', 'contact' );
get_footer();
