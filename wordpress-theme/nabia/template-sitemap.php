<?php
/**
 * Template Name: Sitemap
 * Template Post Type: page
 *
 * A readable sitemap for visitors (and a strong internal-linking page for Google):
 * every page, service, project and article, plus a link to the XML sitemap.
 *
 * @package Nabia
 */

get_header();

nabia_page_header(
	array(
		'eyebrow' => __( 'Sitemap', 'nabia' ),
		'icon'    => 'grid',
		'title'   => get_the_title() ? get_the_title() : __( 'Sitemap', 'nabia' ),
		'intro'   => __( 'Every page on this website in one place.', 'nabia' ),
		'actions' => nabia_button( __( 'XML sitemap', 'nabia' ), nabia_sitemap_url( true ), 'ghost', true ),
	)
);

$nabia_posts = get_posts(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 200,
		'no_found_rows'  => true,
	)
);
$nabia_sites = get_posts(
	array(
		'post_type'      => nabia_portfolio_type(),
		'posts_per_page' => 200,
		'no_found_rows'  => true,
	)
);
?>
<section class="section section-tight sitemap">
	<div class="container sitemap-grid">
		<div class="sitemap-col">
			<h2><?php esc_html_e( 'Pages', 'nabia' ); ?></h2>
			<ul>
				<?php
				wp_list_pages(
					array(
						'title_li' => '',
						'exclude'  => get_the_ID(),
					)
				);
				?>
			</ul>
		</div>

		<div class="sitemap-col">
			<h2><?php esc_html_e( 'Services', 'nabia' ); ?></h2>
			<ul>
				<?php foreach ( nabia_services() as $nabia_service ) : ?>
					<?php $nabia_url = nabia_service_url( $nabia_service['slug'] ); ?>
					<?php if ( $nabia_url ) : ?>
						<li><a href="<?php echo esc_url( $nabia_url ); ?>"><?php echo esc_html( $nabia_service['title'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php if ( $nabia_sites ) : ?>
				<h2><a href="<?php echo esc_url( nabia_portfolio_url() ); ?>"><?php esc_html_e( 'Portfolio', 'nabia' ); ?></a></h2>
				<ul>
					<?php foreach ( $nabia_sites as $nabia_site ) : ?>
						<li><a href="<?php echo esc_url( nabia_project_url( $nabia_site->ID ) ); ?>"<?php echo nabia_project_live_url( $nabia_site->ID ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( get_the_title( $nabia_site ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ( $nabia_posts ) : ?>
			<div class="sitemap-col">
				<h2><a href="<?php echo esc_url( nabia_blog_url() ); ?>"><?php esc_html_e( 'Blog', 'nabia' ); ?></a></h2>
				<ul>
					<?php foreach ( $nabia_posts as $nabia_post ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $nabia_post ) ); ?>"><?php echo esc_html( get_the_title( $nabia_post ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
