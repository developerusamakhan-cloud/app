<?php
/**
 * Template Name: Converter tool
 * Template Post Type: page
 *
 * Converter at the top, the page content (formula, table, examples, FAQs) below,
 * then links to other converters. The tool is picked by the page slug, see
 * inc/tools.php. The /tools/ hub uses this template too and simply has no converter.
 *
 * @package PowerBachat
 */

get_header();

while ( have_posts() ) :
	the_post();
	$powerbachat_tool = powerbachat_tool_for();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'calc-page tool-page' . ( $powerbachat_tool ? '' : ' tool-page--hub' ) ); ?>>
		<header class="calc-page__head">
			<div class="wrap<?php echo $powerbachat_tool ? ' calc-page__grid' : ''; ?>">
				<div class="calc-page__intro">
					<?php powerbachat_breadcrumbs(); ?>
					<h1 class="page-head__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="page-head__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<p class="hero__sig">
						<?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><?php esc_html_e( 'Free, instant and works for 120 V and 230 V countries', 'powerbachat' ); ?></span>
					</p>
				</div>
				<?php
				if ( $powerbachat_tool ) {
					get_template_part( 'template-parts/tool', null, array( 'tool' => $powerbachat_tool ) );
				}
				?>
			</div>
		</header>

		<div class="wrap">
			<div class="prose entry-content">
				<?php the_content(); ?>
			</div>
		</div>

		<?php if ( $powerbachat_tool ) : ?>
			<section class="related tool-related" aria-labelledby="tool-related-title">
				<div class="wrap">
					<h2 class="related__title" id="tool-related-title"><?php esc_html_e( 'More electrical converters', 'powerbachat' ); ?></h2>
					<?php echo powerbachat_tool_cards( powerbachat_related_tools( $powerbachat_tool ), __( 'More electrical converters', 'powerbachat' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( powerbachat_page_exists( '/tools/' ) ) : ?>
						<p class="tool-related__all"><a href="<?php echo esc_url( home_url( '/tools/' ) ); ?>"><?php esc_html_e( 'See all converters', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
