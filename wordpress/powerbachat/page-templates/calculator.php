<?php
/**
 * Template Name: Calculator page
 * Template Post Type: page
 *
 * Calculator at the top, the page content (slab table, explanation, FAQs) below.
 * The utility is detected from the URL (e.g. /pk/lesco-bill-calculator/) or from the
 * custom fields pb_country and pb_utility.
 *
 * @package PowerBachat
 */

get_header();

while ( have_posts() ) :
	the_post();
	$powerbachat_ctx = powerbachat_context_for_page();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'calc-page' ); ?>>
		<header class="calc-page__head">
			<div class="wrap calc-page__grid">
				<div class="calc-page__intro">
					<?php powerbachat_breadcrumbs(); ?>
					<h1 class="page-head__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="page-head__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<p class="hero__sig">
						<?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span>
							<?php
							/* translators: %s: month and year */
							printf( esc_html__( 'Rates checked %s · updated the same week a new tariff is notified', 'powerbachat' ), esc_html( powerbachat_mod( 'pb_tariff_checked' ) ) );
							?>
						</span>
					</p>
				</div>
				<?php
				get_template_part(
					'template-parts/calculator',
					null,
					array(
						'country' => $powerbachat_ctx['country'],
						'utility' => $powerbachat_ctx['utility'],
						'embed'   => true,
					)
				);
				?>
			</div>
		</header>

		<div class="wrap">
			<div class="prose entry-content">
				<?php the_content(); ?>
			</div>
		</div>

		<?php
		get_template_part(
			'template-parts/home/slabs',
			null,
			array(
				'country' => $powerbachat_ctx['country'],
				'embed'   => true,
			)
		);
		?>
	</article>
	<?php
endwhile;

get_footer();
