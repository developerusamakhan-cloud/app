<?php
/**
 * Single post / guide.
 *
 * @package PowerBachat
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'article' ); ?>>
		<header class="page-head page-head--article">
			<div class="wrap">
				<?php powerbachat_breadcrumbs(); ?>
				<h1 class="page-head__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="page-head__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<?php powerbachat_post_meta(); ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="wrap article__hero"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>

		<div class="wrap article__layout">
			<div class="prose entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
			<?php if ( is_active_sidebar( 'sidebar-article' ) ) : ?>
				<aside class="article__aside"><?php dynamic_sidebar( 'sidebar-article' ); ?></aside>
			<?php else : ?>
				<aside class="article__aside">
					<div class="aside-card">
						<p class="aside-card__title"><?php esc_html_e( 'Check your own bill', 'powerbachat' ); ?></p>
						<p><?php esc_html_e( 'Pick your company, enter your units, see the slab-by-slab total.', 'powerbachat' ); ?></p>
						<?php foreach ( POWERBACHAT_COUNTRIES as $powerbachat_cc ) : ?><a data-only="<?php echo esc_attr( $powerbachat_cc ); ?>" class="btn btn--volt btn--sm" href="<?php echo esc_url( powerbachat_bill_url( $powerbachat_cc ) ); ?>"><?php esc_html_e( 'Open calculator', 'powerbachat' ); ?></a><?php endforeach; ?>
					</div>
				</aside>
			<?php endif; ?>
		</div>

		<footer class="wrap article__foot">
			<?php the_tags( '<p class="article__tags">', '', '</p>' ); ?>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span>' . esc_html__( 'Previous', 'powerbachat' ) . '</span>%title',
					'next_text' => '<span>' . esc_html__( 'Next', 'powerbachat' ) . '</span>%title',
				)
			);
			?>
		</footer>
	</article>
	<?php
	get_template_part( 'template-parts/content/related' );
	?>
	<?php
endwhile;

get_footer();
