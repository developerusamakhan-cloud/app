<?php
/**
 * Single portfolio item (Websites / Projects): case study layout.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	$nabia_id    = get_the_ID();
	$nabia_tax   = nabia_portfolio_taxonomy();
	$nabia_terms = $nabia_tax ? get_the_terms( $nabia_id, $nabia_tax ) : array();
	$nabia_term  = ( $nabia_terms && ! is_wp_error( $nabia_terms ) ) ? $nabia_terms[0] : null;
	$nabia_live  = nabia_project_live_url( $nabia_id );
	$nabia_year  = get_post_meta( $nabia_id, '_nabia_year', true );
	$nabia_year  = $nabia_year ? $nabia_year : get_the_date( 'Y' );
	$nabia_host  = $nabia_live ? preg_replace( '#^www\.#', '', (string) wp_parse_url( $nabia_live, PHP_URL_HOST ) ) : '';

	$nabia_rows = array_filter(
		array(
			array( __( 'Client', 'nabia' ), get_post_meta( $nabia_id, '_nabia_client', true ) ),
			array( __( 'Category', 'nabia' ), $nabia_term ? $nabia_term->name : '' ),
			array( __( 'Services', 'nabia' ), get_post_meta( $nabia_id, '_nabia_role', true ) ),
			array( __( 'Year', 'nabia' ), $nabia_year ),
			array( __( 'Website', 'nabia' ), $nabia_host ),
		),
		function ( $row ) {
			return '' !== (string) $row[1];
		}
	);

	$nabia_crumbs = array( array( __( 'Portfolio', 'nabia' ), nabia_portfolio_url() ) );
	if ( $nabia_term ) {
		$nabia_crumbs[] = array( $nabia_term->name, get_term_link( $nabia_term ) );
	}

	$nabia_actions = ( $nabia_live ? nabia_button( __( 'Visit live site', 'nabia' ), $nabia_live, 'accent', true ) : '' )
		. nabia_button( __( 'Start a similar project', 'nabia' ), nabia_hire_url(), $nabia_live ? 'ghost' : 'accent' );

	nabia_page_header(
		array(
			'eyebrow' => $nabia_term ? $nabia_term->name : __( 'Case study', 'nabia' ),
			'icon'    => 'layout',
			'title'   => get_the_title(),
			'intro'   => has_excerpt() ? get_the_excerpt() : '',
			'crumbs'  => $nabia_crumbs,
			'actions' => $nabia_actions,
			'aside'   => nabia_aside_card( __( 'Project details', 'nabia' ), $nabia_rows ),
			'class'   => 'is-project',
		)
	);
	?>
	<article <?php post_class( 'section section-tight single-project' ); ?>>
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="browser-frame" data-reveal>
					<div class="browser-bar" aria-hidden="true">
						<span class="mockup-dots"><i></i><i></i><i></i></span>
						<span class="mockup-url"><?php nabia_icon( 'shield' ); ?><?php echo esc_html( $nabia_host ? $nabia_host : get_the_title() ); ?></span>
					</div>
					<?php the_post_thumbnail( 'full' ); ?>
				</figure>
			<?php endif; ?>

			<?php if ( '' !== trim( get_the_content() ) ) : ?>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php if ( $nabia_live ) : ?>
				<p class="project-cta" data-reveal>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( $nabia_live ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'See the live website', 'nabia' ); ?></span><?php nabia_icon( 'arrow-up' ); ?></a>
				</p>
			<?php endif; ?>
		</div>
	</article>

	<?php
	nabia_related_websites_section( __( 'More projects like this', 'nabia' ), $nabia_id );
	get_template_part( 'template-parts/home', 'audit' );

	$nabia_next = get_adjacent_post( false, '', false );
	if ( ! $nabia_next ) {
		$nabia_first = get_posts(
			array(
				'post_type'      => get_post_type(),
				'posts_per_page' => 1,
				'order'          => 'ASC',
				'post__not_in'   => array( $nabia_id ),
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
		<?php
	endif;
endwhile;

get_footer();
