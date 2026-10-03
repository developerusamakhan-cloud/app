<?php
/**
 * Latest guides for the visitor's country.
 *
 * Put a post in a category with the slug "pakistan", "india" or "bangladesh"
 * (or pk / in / bd) to show it only to that country. Posts without a country
 * category show everywhere. Until five posts exist for a country, the planned
 * cornerstone guides fill the grid.
 *
 * @package PowerBachat
 */

$powerbachat_country_cats = array(
	'pk' => array( 'pakistan', 'pk' ),
	'in' => array( 'india', 'in' ),
	'bd' => array( 'bangladesh', 'bd' ),
);

/**
 * Category IDs for one country.
 *
 * @param string[] $slugs Category slugs.
 * @return int[]
 */
$powerbachat_cat_ids = function ( $slugs ) {
	$ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_category_by_slug( $slug );
		if ( $term ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return $ids;
};

$powerbachat_planned = array(
	'pk' => array(
		array( 'Pakistan', 'Net metering vs net billing: what actually changes for your rooftop', 'The buy-back rate, the meter swap and the maths on a 10 kW system, before and after the new policy.', '/pk/net-metering-vs-net-billing/', 9 ),
		array( 'Pakistan', 'What is the FPA on my bill, and why does it change every month?', 'Fuel price adjustment explained without the jargon.', '/fuel-price-adjustment/', 5 ),
		array( 'Pakistan', 'Protected or not: how the 200-unit rule really works', 'Six months, one bad month, and what it costs you.', '/protected-consumer/', 5 ),
		array( 'Solar', 'Lithium or tubular: which battery is worth it for load-shedding?', 'Cost per cycle, real backup hours and when each one wins.', '/lithium-vs-tubular-battery/', 6 ),
		array( 'Basics', 'What exactly is “one unit” of electricity?', 'Watts, kilowatt-hours and how long your AC takes to burn through one.', '/what-is-one-unit-of-electricity/', 4 ),
	),
	'in' => array(
		array( 'India', 'How your electricity bill is calculated, line by line', 'Energy charge, fixed charge, duty and FPPCA, with a real bill taken apart.', '/in/how-electricity-bill-is-calculated/', 7 ),
		array( 'Solar', 'PM Surya Ghar, explained: who qualifies and what you actually get', 'The subsidy per kW, the paperwork and the real out-of-pocket cost.', '/pm-surya-ghar-yojana-explained/', 8 ),
		array( 'India', 'TNEB bi-monthly billing and the 500 unit trap', 'Why one extra unit in a two-month cycle can cost you hundreds.', '/tneb-bi-monthly-billing-explained/', 5 ),
		array( 'Solar', 'DCR or non-DCR panels: which should you buy?', 'Subsidy rules, price gap and output, compared honestly.', '/dcr-vs-non-dcr-solar-panels/', 6 ),
		array( 'India', 'Delhi\'s 200 free units: how the subsidy works', 'Who qualifies, what happens past 200 units and how to keep it.', '/delhi-200-units-free-electricity/', 5 ),
	),
	'bd' => array(
		array( 'Bangladesh', 'How to read your electricity bill', 'Slabs, demand charge and VAT, line by line.', '/how-to-read-electricity-bill-bangladesh/', 6 ),
		array( 'Bangladesh', 'Prepaid meter guide: recharge, deductions and hidden charges', 'What actually comes off your balance each month.', '/prepaid-meter-guide-bangladesh/', 5 ),
		array( 'Backup', 'IPS or solar: what keeps your fans running through load-shedding?', 'Backup hours, battery life and cost over five years.', '/ips-vs-solar-bangladesh/', 6 ),
		array( 'Bangladesh', 'How to reduce your electricity bill this summer', 'Fans, AC settings, fridges and the slab line that matters.', '/reduce-electricity-bill-bangladesh/', 6 ),
		array( 'Solar', 'Solar battery prices in Bangladesh', 'Lead acid, tubular and lithium, with what each one lasts.', '/bd/solar-battery-price/', 4 ),
	),
);
?>
<section class="section guides" id="guides">
	<div class="wrap">
		<header class="section__head section__head--split">
			<div>
				<p class="kicker"><span class="kicker__num" aria-hidden="true"></span><?php esc_html_e( 'Guides', 'powerbachat' ); ?></p>
				<h2 class="section__title"><?php esc_html_e( 'Read your bill like the meter reader does.', 'powerbachat' ); ?></h2>
			</div>
			<?php if ( get_option( 'page_for_posts' ) ) : ?>
				<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'All guides', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endif; ?>
		</header>

		<?php foreach ( $powerbachat_planned as $powerbachat_code => $powerbachat_fallback ) : ?>
			<?php
			$powerbachat_exclude = array();
			foreach ( $powerbachat_country_cats as $powerbachat_other => $powerbachat_slugs ) {
				if ( $powerbachat_other !== $powerbachat_code ) {
					$powerbachat_exclude = array_merge( $powerbachat_exclude, $powerbachat_cat_ids( $powerbachat_slugs ) );
				}
			}
			$powerbachat_query = new WP_Query(
				array(
					'posts_per_page'      => 5,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => false,
					'no_found_rows'       => true,
					'category__not_in'    => $powerbachat_exclude,
				)
			);
			$powerbachat_i     = 0;
			?>
			<div class="guide-list" data-only="<?php echo esc_attr( $powerbachat_code ); ?>">
				<?php
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

				foreach ( array_slice( $powerbachat_fallback, 0, max( 0, 5 - $powerbachat_i ) ) as $powerbachat_guide ) :
					?>
					<article class="guide reveal<?php echo 0 === $powerbachat_i ? ' guide--lead' : ''; ?>" style="--i:<?php echo (int) $powerbachat_i; ?>">
						<a href="<?php echo esc_url( home_url( $powerbachat_guide[3] ) ); ?>">
							<span class="guide__tag"><?php echo esc_html( $powerbachat_guide[0] ); ?></span>
							<span class="guide__title"><?php echo esc_html( $powerbachat_guide[1] ); ?></span>
							<span class="guide__text"><?php echo esc_html( $powerbachat_guide[2] ); ?></span>
							<span class="guide__meta">
								<?php
								/* translators: %d: minutes */
								printf( esc_html__( '%d min read', 'powerbachat' ), (int) $powerbachat_guide[4] );
								?>
							</span>
						</a>
					</article>
					<?php
					++$powerbachat_i;
				endforeach;
				?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
