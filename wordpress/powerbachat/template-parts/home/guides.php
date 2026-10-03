<?php
/**
 * Latest guides. Falls back to the planned cornerstone guides until posts exist.
 *
 * @package PowerBachat
 */

$powerbachat_query = new WP_Query(
	array(
		'posts_per_page'      => 5,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => false,
		'no_found_rows'       => true,
	)
);

$powerbachat_planned = array(
	array(
		'tag'   => 'Pakistan',
		'title' => 'Net metering vs net billing: what actually changes for your rooftop',
		'text'  => 'The buy-back rate, the meter swap and the maths on a 10 kW system — before and after the new policy.',
		'url'   => '/pk/net-metering-vs-net-billing/',
		'mins'  => 9,
	),
	array(
		'tag'   => 'India',
		'title' => 'How your electricity bill is calculated, line by line',
		'text'  => 'Energy charge, fixed charge, duty and FPPCA — with a real bill taken apart.',
		'url'   => '/in/how-electricity-bill-is-calculated/',
		'mins'  => 7,
	),
	array(
		'tag'   => 'Pakistan',
		'title' => 'What is the FPA on my bill, and why does it change every month?',
		'text'  => 'Fuel price adjustment explained without the jargon.',
		'url'   => '/pk/fuel-price-adjustment/',
		'mins'  => 5,
	),
	array(
		'tag'   => 'Solar',
		'title' => 'Lithium or tubular: which battery is worth it for load-shedding?',
		'text'  => 'Cost per cycle, real backup hours and when each one wins.',
		'url'   => '/lithium-vs-tubular-battery/',
		'mins'  => 6,
	),
	array(
		'tag'   => 'Basics',
		'title' => 'What exactly is “one unit” of electricity?',
		'text'  => 'Watts, kilowatt-hours and how long your AC takes to burn through one.',
		'url'   => '/what-is-one-unit-of-electricity/',
		'mins'  => 4,
	),
);
?>
<section class="section guides" id="guides">
	<div class="wrap">
		<header class="section__head section__head--split">
			<div>
				<p class="kicker"><span class="kicker__num">05</span><?php esc_html_e( 'Guides', 'powerbachat' ); ?></p>
				<h2 class="section__title"><?php esc_html_e( 'Read your bill like the meter reader does.', 'powerbachat' ); ?></h2>
			</div>
			<?php if ( get_option( 'page_for_posts' ) ) : ?>
				<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'All guides', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endif; ?>
		</header>

		<div class="guide-list">
			<?php
			$powerbachat_i = 0;
			while ( $powerbachat_query->have_posts() ) :
				$powerbachat_query->the_post();
				$powerbachat_cat = get_the_category();
				$powerbachat_tag = ( $powerbachat_cat && 'uncategorized' !== $powerbachat_cat[0]->slug ) ? $powerbachat_cat[0]->name : __( 'Guide', 'powerbachat' );
				?>
				<article class="guide reveal<?php echo 0 === $powerbachat_i ? ' guide--lead' : ''; ?>" style="--i:<?php echo (int) $powerbachat_i; ?>">
					<a href="<?php the_permalink(); ?>">
						<?php if ( 0 === $powerbachat_i && has_post_thumbnail() ) : ?>
							<span class="guide__img"><?php the_post_thumbnail( 'powerbachat-card' ); ?></span>
						<?php endif; ?>
						<span class="guide__tag"><?php echo esc_html( $powerbachat_tag ); ?></span>
						<span class="guide__title"><?php the_title(); ?></span>
						<span class="guide__text"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></span>
						<span class="guide__meta">
							<?php
							/* translators: %d: minutes */
							printf( esc_html__( '%d min read', 'powerbachat' ), (int) powerbachat_reading_time() );
							?>
						</span>
					</a>
				</article>
				<?php
				++$powerbachat_i;
			endwhile;
			wp_reset_postdata();

			// Until there are five real posts, the planned cornerstone guides fill the grid.
			foreach ( array_slice( $powerbachat_planned, 0, max( 0, 5 - $powerbachat_i ) ) as $powerbachat_guide ) :
				?>
				<article class="guide reveal<?php echo 0 === $powerbachat_i ? ' guide--lead' : ''; ?>" style="--i:<?php echo (int) $powerbachat_i; ?>">
					<a href="<?php echo esc_url( home_url( $powerbachat_guide['url'] ) ); ?>">
						<span class="guide__tag"><?php echo esc_html( $powerbachat_guide['tag'] ); ?></span>
						<span class="guide__title"><?php echo esc_html( $powerbachat_guide['title'] ); ?></span>
						<span class="guide__text"><?php echo esc_html( $powerbachat_guide['text'] ); ?></span>
						<span class="guide__meta">
							<?php
							/* translators: %d: minutes */
							printf( esc_html__( '%d min read', 'powerbachat' ), (int) $powerbachat_guide['mins'] );
							?>
						</span>
					</a>
				</article>
				<?php
				++$powerbachat_i;
			endforeach;
			?>
		</div>
	</div>
</section>
