<?php
/**
 * Pricing plans (website packages + monthly maintenance).
 *
 * Plans are edited in Customize → Nabia Theme → Pricing as plain text:
 * line 1 = plan name, line 2 = price (leave empty for "Custom quote"),
 * then one feature per line. Start a feature with "-" to show it as not included.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse one plan from its Customizer text.
 *
 * @param string $key Theme mod key.
 * @return array|null { name, price, features: array[] { text, included } }
 */
function nabia_parse_plan( $key ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) nabia_mod( $key ) );
	$lines = array_map( 'trim', $lines );
	if ( empty( $lines[0] ) ) {
		return null;
	}
	$features = array();
	foreach ( array_slice( $lines, 2 ) as $line ) {
		if ( '' === $line ) {
			continue;
		}
		$excluded   = 0 === strpos( $line, '-' );
		$features[] = array(
			'text'     => $excluded ? ltrim( substr( $line, 1 ) ) : $line,
			'included' => ! $excluded,
		);
	}
	return array(
		'name'     => $lines[0],
		'price'    => isset( $lines[1] ) ? $lines[1] : '',
		'features' => $features,
	);
}

/**
 * Plans of a group.
 *
 * @param string $group "web" or "care".
 * @return array[]
 */
function nabia_plans( $group ) {
	$plans = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$plan = nabia_parse_plan( 'plan_' . $group . '_' . $i );
		if ( $plan ) {
			$plans[] = $plan;
		}
	}
	return $plans;
}

/**
 * Where "Hire me" buttons go: the Customizer link, an existing /hire-me/ page, or the contact section.
 *
 * @return string
 */
function nabia_hire_url() {
	$url = nabia_mod( 'hire_url' );
	if ( $url ) {
		return $url;
	}
	$page = get_page_by_path( 'hire-me' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return is_front_page() ? '#contact' : home_url( '/#contact' );
}

/**
 * Print pricing cards for a group.
 *
 * @param string $group "web" or "care".
 */
function nabia_render_plans( $group ) {
	$plans   = nabia_plans( $group );
	$popular = (int) nabia_mod( 'plan_popular' ) - 1;
	$period  = 'care' === $group ? nabia_mod( 'care_period' ) : __( 'one-time', 'nabia' );
	?>
	<div class="plan-grid">
		<?php foreach ( $plans as $index => $plan ) : ?>
			<?php $is_popular = $index === $popular; ?>
			<article class="plan<?php echo $is_popular ? ' is-popular' : ''; ?>" data-reveal style="--i:<?php echo (int) $index; ?>">
				<?php if ( $is_popular ) : ?>
					<span class="plan-badge"><?php esc_html_e( 'Most popular', 'nabia' ); ?></span>
				<?php endif; ?>
				<h3 class="plan-name"><?php echo esc_html( $plan['name'] ); ?></h3>
				<p class="plan-price">
					<?php if ( '' !== $plan['price'] ) : ?>
						<strong><?php echo esc_html( $plan['price'] ); ?></strong>
						<span><?php echo esc_html( $period ); ?></span>
					<?php else : ?>
						<strong class="plan-quote"><?php esc_html_e( 'Custom quote', 'nabia' ); ?></strong>
					<?php endif; ?>
				</p>
				<ul class="plan-features">
					<?php foreach ( $plan['features'] as $feature ) : ?>
						<li class="<?php echo $feature['included'] ? 'is-in' : 'is-out'; ?>">
							<span class="plan-check" aria-hidden="true"><?php echo $feature['included'] ? '&#10003;' : '&#10005;'; ?></span>
							<span>
								<?php if ( ! $feature['included'] ) : ?>
									<span class="screen-reader-text"><?php esc_html_e( 'Not included:', 'nabia' ); ?></span>
								<?php endif; ?>
								<?php echo esc_html( $feature['text'] ); ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
				<a class="btn <?php echo $is_popular ? 'btn-accent' : 'btn-ghost'; ?> plan-cta" href="<?php echo esc_url( nabia_hire_url() ); ?>" data-magnetic>
					<span><?php echo '' !== $plan['price'] ? esc_html__( 'Hire me', 'nabia' ) : esc_html__( 'Get a quote', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?>
				</a>
			</article>
		<?php endforeach; ?>
	</div>
	<?php
}
