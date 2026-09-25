<?php
/**
 * Pricing plans (website packages + monthly maintenance).
 *
 * Plans are edited in Customize → Nabia Theme → Pricing as plain text:
 * line 1 = plan name, line 2 = price (leave empty for "Custom quote"),
 * then one feature per line. Start a feature with "-" to show it as not included.
 * Optional lines: "Was: $69.99" (crossed-out old price), "Subtitle: …", "Note: …".
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
	$plan = array(
		'name'     => $lines[0],
		'price'    => isset( $lines[1] ) ? $lines[1] : '',
		'was'      => '',
		'subtitle' => '',
		'note'     => '',
		'features' => array(),
	);
	foreach ( array_slice( $lines, 2 ) as $line ) {
		if ( '' === $line ) {
			continue;
		}
		// Optional labelled lines: "Was: $69.99", "Subtitle: 1 Day In A Month", "Note: 3 Days Support".
		if ( preg_match( '/^(was|subtitle|note):\s*(.+)$/i', $line, $m ) ) {
			$plan[ strtolower( $m[1] ) ] = $m[2];
			continue;
		}
		$excluded           = 0 === strpos( $line, '-' );
		$plan['features'][] = array(
			'text'     => $excluded ? ltrim( substr( $line, 1 ) ) : $line,
			'included' => ! $excluded,
		);
	}
	return $plan;
}

/**
 * "Save 34%" from an old and a new price, when both are numbers.
 *
 * @param string $was Old price.
 * @param string $now New price.
 * @return string
 */
function nabia_plan_saving( $was, $now ) {
	$old = (float) preg_replace( '/[^0-9.]/', '', $was );
	$new = (float) preg_replace( '/[^0-9.]/', '', $now );
	if ( $old <= 0 || $new <= 0 || $new >= $old ) {
		return '';
	}
	/* translators: %d: percentage saved */
	return sprintf( __( 'Save %d%%', 'nabia' ), round( ( 1 - $new / $old ) * 100 ) );
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
 * Where "Hire me" / "Let's talk" buttons go: the Customizer link, your Contact page
 * ("Contact / Hire me" template), a /hire-me/ or /contact/ page, or the contact section.
 *
 * @return string
 */
function nabia_hire_url() {
	$url = nabia_mod( 'hire_url' );
	if ( $url ) {
		return $url;
	}
	// A page using the "Contact / Hire me" template.
	$contact = function_exists( 'nabia_page_url' ) ? nabia_page_url( 'contact' ) : '';
	if ( $contact ) {
		return $contact;
	}
	$page = get_page_by_path( 'hire-me' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	$page = get_page_by_path( 'contact' );
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
	$popular = (int) nabia_mod( 'care' === $group ? 'care_popular' : 'plan_popular' ) - 1;
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
				<?php if ( $plan['subtitle'] ) : ?>
					<p class="plan-subtitle"><?php echo esc_html( $plan['subtitle'] ); ?></p>
				<?php endif; ?>
				<p class="plan-price">
					<?php if ( '' !== $plan['price'] ) : ?>
						<?php if ( $plan['was'] ) : ?>
							<del class="plan-was"><span class="screen-reader-text"><?php esc_html_e( 'Was', 'nabia' ); ?></span><?php echo esc_html( $plan['was'] ); ?></del>
						<?php endif; ?>
						<strong><?php echo esc_html( $plan['price'] ); ?></strong>
						<span><?php echo esc_html( $period ); ?></span>
						<?php $saving = nabia_plan_saving( $plan['was'], $plan['price'] ); ?>
						<?php if ( $saving ) : ?>
							<em class="plan-save"><?php echo esc_html( $saving ); ?></em>
						<?php endif; ?>
					<?php else : ?>
						<strong class="plan-quote"><?php esc_html_e( 'Custom quote', 'nabia' ); ?></strong>
					<?php endif; ?>
				</p>
				<ul class="plan-features">
					<?php foreach ( $plan['features'] as $feature ) : ?>
						<li class="<?php echo $feature['included'] ? 'is-in' : 'is-out'; ?>">
							<span class="tick plan-check" aria-hidden="true"><?php nabia_icon( $feature['included'] ? 'check' : 'close' ); ?></span>
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
				<?php if ( $plan['note'] ) : ?>
					<p class="plan-note"><?php nabia_icon( 'shield' ); ?><?php echo esc_html( $plan['note'] ); ?></p>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
	<?php
}
