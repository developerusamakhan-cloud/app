<?php
/**
 * 404 page: friendly message, search and helpful links.
 *
 * @package Nabia
 */

get_header();

$nabia_aside = '<div class="lost-visual" aria-hidden="true"><span class="lost-num">4<span class="lost-zero"></span>4</span>'
	. '<span class="chip lost-chip lost-chip-1">' . nabia_get_icon( 'search' ) . esc_html__( 'Page not found', 'nabia' ) . '</span>'
	. '<span class="chip lost-chip lost-chip-2">' . nabia_get_icon( 'sparkles' ) . esc_html__( 'Let’s get you back', 'nabia' ) . '</span></div>';

nabia_page_header(
	array(
		'eyebrow' => __( 'Error 404', 'nabia' ),
		'icon'    => 'search',
		'title'   => __( 'This page went on holiday.', 'nabia' ),
		'intro'   => __( 'The link may be broken or the page may have moved. Search below, or jump to one of the pages people visit most.', 'nabia' ),
		'actions' => nabia_button( __( 'Back home', 'nabia' ), home_url( '/' ) ) . nabia_button( __( 'See services', 'nabia' ), nabia_page_url( 'services' ) ? nabia_page_url( 'services' ) : home_url( '/#services' ), 'ghost' ),
		'aside'   => $nabia_aside,
		'class'   => 'is-404',
	)
);

$nabia_blog  = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : '';
$nabia_links = array_filter(
	array(
		array( 'layout', __( 'Services', 'nabia' ), __( 'Everything I can build for you', 'nabia' ), nabia_page_url( 'services' ) ? nabia_page_url( 'services' ) : home_url( '/#services' ) ),
		array( 'grid', __( 'Portfolio', 'nabia' ), __( 'Websites I have designed and built', 'nabia' ), nabia_portfolio_url() ),
		array( 'star', __( 'Pricing', 'nabia' ), __( 'Website packages and maintenance', 'nabia' ), nabia_page_url( 'pricing' ) ? nabia_page_url( 'pricing' ) : home_url( '/#pricing' ) ),
		$nabia_blog ? array( 'pen', __( 'Blog', 'nabia' ), __( 'Tips for a better website', 'nabia' ), $nabia_blog ) : null,
		array( 'mail', __( 'Contact', 'nabia' ), __( 'Tell me about your project', 'nabia' ), nabia_hire_url() ),
	)
);
?>
<section class="section section-tight lost">
	<div class="container">
		<div class="lost-search" data-reveal>
			<p><?php esc_html_e( 'Looking for something specific?', 'nabia' ); ?></p>
			<?php get_search_form(); ?>
		</div>
		<ul class="quick-links">
			<?php foreach ( $nabia_links as $nabia_index => $nabia_link ) : ?>
				<li data-reveal style="--i:<?php echo (int) $nabia_index; ?>">
					<a href="<?php echo esc_url( $nabia_link[3] ); ?>">
						<span class="quick-icon"><?php nabia_icon( $nabia_link[0] ); ?></span>
						<strong><?php echo esc_html( $nabia_link[1] ); ?></strong>
						<span><?php echo esc_html( $nabia_link[2] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
nabia_related_posts_section( __( 'Latest articles', 'nabia' ) );
get_footer();
