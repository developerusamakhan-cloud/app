<?php
/**
 * Homepage: hero, trust points, tool grid, optional page content, latest guides.
 *
 * Set Settings → Reading → "A static page" to control the content below the tools.
 *
 * @package ClaimFairly
 */

get_header();
$claimfairly_tools = claimfairly_get_tools();
?>
<section class="hero">
	<div class="container">
		<h1 class="hero__title"><?php echo esc_html( claimfairly_opt( 'cf_hero_title' ) ); ?></h1>
		<p class="hero__text"><?php echo esc_html( claimfairly_opt( 'cf_hero_text' ) ); ?></p>
		<ul class="hero__trust">
			<li><?php esc_html_e( '100% free, no signup', 'claimfairly' ); ?></li>
			<li><?php esc_html_e( 'Nothing you type is stored', 'claimfairly' ); ?></li>
			<li><?php esc_html_e( 'Formula shown on every result', 'claimfairly' ); ?></li>
			<li><?php esc_html_e( 'Official sources cited', 'claimfairly' ); ?></li>
		</ul>
	</div>
</section>

<?php if ( $claimfairly_tools ) : ?>
	<section class="home-section" aria-labelledby="home-tools-title">
		<div class="container">
			<h2 id="home-tools-title" class="home-section__title"><?php esc_html_e( 'Free claim calculators', 'claimfairly' ); ?></h2>
			<ul class="cf-cards cf-cards--tools">
				<?php foreach ( $claimfairly_tools as $claimfairly_tool ) : ?>
					<li class="cf-card">
						<a class="cf-card__link" href="<?php echo esc_url( get_permalink( $claimfairly_tool ) ); ?>">
							<span class="cf-card__title"><?php echo esc_html( get_the_title( $claimfairly_tool ) ); ?></span>
							<span class="cf-card__text"><?php echo esc_html( claimfairly_card_summary( $claimfairly_tool ) ); ?></span>
							<span class="cf-card__cta"><?php esc_html_e( 'Open calculator', 'claimfairly' ); ?> <span aria-hidden="true">→</span></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php elseif ( current_user_can( 'edit_pages' ) ) : ?>
	<div class="container"><div class="cf-note cf-note--info"><p><?php esc_html_e( 'Tool pages appear here automatically. Set "Page type" to "Tool / calculator" in the ClaimFairly: Trust & SEO box of a page. (Only editors see this.)', 'claimfairly' ); ?></p></div></div>
<?php endif; ?>

<?php
if ( 'page' === get_option( 'show_on_front' ) ) :
	while ( have_posts() ) :
		the_post();
		if ( '' !== trim( get_the_content() ) ) :
			?>
			<section class="home-section home-section--content">
				<div class="container container--narrow entry__content">
					<?php the_content(); ?>
				</div>
			</section>
			<?php
		endif;
	endwhile;
endif;

$claimfairly_guides = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( $claimfairly_guides->have_posts() ) :
	?>
	<section class="home-section home-section--soft" aria-labelledby="home-guides-title">
		<div class="container">
			<h2 id="home-guides-title" class="home-section__title"><?php esc_html_e( 'Plain-English claim guides', 'claimfairly' ); ?></h2>
			<ul class="cf-cards">
				<?php
				while ( $claimfairly_guides->have_posts() ) :
					$claimfairly_guides->the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<section class="home-section" aria-labelledby="home-how-title">
	<div class="container container--narrow">
		<h2 id="home-how-title" class="home-section__title"><?php esc_html_e( 'How ClaimFairly works', 'claimfairly' ); ?></h2>
		<ol class="home-steps">
			<li><strong><?php esc_html_e( 'You enter your numbers.', 'claimfairly' ); ?></strong> <?php esc_html_e( 'Everything is calculated in your browser. Nothing is sent to us or stored.', 'claimfairly' ); ?></li>
			<li><strong><?php esc_html_e( 'You get a range, not a promise.', 'claimfairly' ); ?></strong> <?php esc_html_e( 'Every result shows a low-to-high estimate, the exact formula used and its assumptions.', 'claimfairly' ); ?></li>
			<li><strong><?php esc_html_e( 'You see what can change it.', 'claimfairly' ); ?></strong> <?php esc_html_e( 'State rules, fault, policy limits and evidence all matter. We explain each one and link to official sources.', 'claimfairly' ); ?></li>
		</ol>
		<?php echo claimfairly_get_disclaimer(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
	</div>
</section>
<?php
get_footer();
