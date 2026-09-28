<?php
/**
 * Homepage.
 *
 * @package ClaimFairly
 */

get_header();

$claimfairly_tools     = claimfairly_get_tools();
$claimfairly_estimator = claimfairly_tool_page_url( 'settlement-estimator' );
$claimfairly_dv        = claimfairly_tool_page_url( 'diminished-value' );
$claimfairly_states    = get_page_by_path( 'states' );
$claimfairly_guides_id = (int) get_option( 'page_for_posts' );
$claimfairly_founder   = claimfairly_founder_name();
$claimfairly_photo     = (int) claimfairly_opt( 'cf_founder_photo' );
?>

<section class="hero">
	<div class="wrap hero__grid">
		<div class="hero__copy">
			<p class="badge"><span class="badge__dot" aria-hidden="true"></span><?php echo esc_html( claimfairly_opt( 'cf_hero_badge' ) ); ?></p>
			<h1 class="hero__title">
				<?php echo esc_html( claimfairly_opt( 'cf_hero_before' ) ); ?>
				<mark class="hl"><?php echo esc_html( claimfairly_opt( 'cf_hero_mark' ) ); ?></mark>
			</h1>
			<p class="hero__text"><?php echo esc_html( claimfairly_opt( 'cf_hero_text' ) ); ?></p>
			<div class="hero__actions">
				<a class="btn btn--brand btn--lg" href="<?php echo esc_url( $claimfairly_estimator ); ?>"><?php esc_html_e( 'Estimate my claim', 'claimfairly' ); ?> <?php echo claimfairly_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="btn btn--ghost btn--lg" href="#tools"><?php esc_html_e( 'See all 5 tools', 'claimfairly' ); ?></a>
			</div>
			<ul class="hero__proof">
				<li><?php echo claimfairly_icon( 'check', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Formula shown on every result', 'claimfairly' ); ?></li>
				<li><?php echo claimfairly_icon( 'check', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Rules for all 50 states + DC', 'claimfairly' ); ?></li>
			</ul>
		</div>

		<div class="hero__visual" aria-hidden="true">
			<div class="mock">
				<div class="mock__bar"><span></span><span></span><span></span><em>claimfairly.com/diminished-value-calculator</em></div>
				<div class="mock__body">
					<p class="mock__title"><span class="icon-sq icon-sq--sm c-violet"><?php echo claimfairly_icon( 'car-down', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span> Diminished Value Calculator</p>
					<div class="mock__fields">
						<div><small>Value before the accident</small><b>$30,000</b></div>
						<div><small>Mileage</small><b>35,000</b></div>
					</div>
					<div class="mock__choice"><span class="mock__radio"></span>Moderate structural and panel damage</div>
					<div class="mock__result">
						<span class="mock__pill">What the 17c formula gives</span>
						<b class="mock__big">$1,200</b>
						<code>$30,000 x 10% x 0.50 x 0.80</code>
					</div>
				</div>
			</div>
			<div class="float-card float-card--a"><span class="icon-sq icon-sq--sm c-orange"><?php echo claimfairly_icon( 'pie', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><small>You keep from $50k</small><b>$33,335</b></span></div>
			<div class="float-card float-card--b"><span class="icon-sq icon-sq--sm c-green"><?php echo claimfairly_icon( 'lock', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><small>Stored on our servers</small><b>Nothing</b></span></div>
		</div>
	</div>
</section>

<?php if ( $claimfairly_tools ) : ?>
<section class="section section--tight" id="tools" aria-labelledby="tools-title">
	<div class="wrap">
		<div class="section__head">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Free calculators', 'claimfairly' ); ?></p>
				<h2 id="tools-title" class="section__title"><?php esc_html_e( 'Pick the question you need answered', 'claimfairly' ); ?></h2>
			</div>
		</div>
		<ul class="tool-cards">
			<?php
			foreach ( $claimfairly_tools as $claimfairly_tool ) :
				$claimfairly_style = claimfairly_page_style( $claimfairly_tool->ID );
				$claimfairly_key   = (string) get_post_meta( $claimfairly_tool->ID, '_cf_tool_key', true );
				$claimfairly_meta  = claimfairly_tool_styles();
				$claimfairly_tag   = isset( $claimfairly_meta[ $claimfairly_key ]['label'] ) ? $claimfairly_meta[ $claimfairly_key ]['label'] : __( 'Free', 'claimfairly' );
				?>
				<li class="tool-card c-<?php echo esc_attr( $claimfairly_style['color'] ); ?>">
					<a href="<?php echo esc_url( get_permalink( $claimfairly_tool ) ); ?>">
						<span class="tool-card__top">
							<span class="tool-card__icon"><?php echo claimfairly_icon( $claimfairly_style['icon'], 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span class="tool-card__tag"><?php echo esc_html( $claimfairly_tag ); ?></span>
						</span>
						<span class="tool-card__title"><?php echo esc_html( get_the_title( $claimfairly_tool ) ); ?></span>
						<span class="tool-card__text"><?php echo esc_html( claimfairly_card_summary( $claimfairly_tool ) ); ?></span>
						<span class="tool-card__go"><?php esc_html_e( 'Open', 'claimfairly' ); ?> <?php echo claimfairly_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<ul class="stats">
			<li><b class="t-blue">5</b><span><?php esc_html_e( 'free calculators', 'claimfairly' ); ?></span></li>
			<li><b class="t-violet">51</b><span><?php esc_html_e( 'state fault rules built in', 'claimfairly' ); ?></span></li>
			<li><b class="t-orange">0</b><span><?php esc_html_e( 'sign-ups or phone numbers', 'claimfairly' ); ?></span></li>
			<li><b class="t-green">100%</b><span><?php esc_html_e( 'runs in your browser', 'claimfairly' ); ?></span></li>
		</ul>
	</div>
</section>
<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
	<div class="wrap"><p class="notice-editor"><?php esc_html_e( 'No tool pages yet. Go to Appearance > ClaimFairly Setup and import the pages. (Only admins see this.)', 'claimfairly' ); ?></p></div>
<?php endif; ?>

<section class="section section--mint" aria-labelledby="how-title">
	<div class="wrap">
		<div class="section__head section__head--center">
			<p class="eyebrow"><?php esc_html_e( 'How it works', 'claimfairly' ); ?></p>
			<h2 id="how-title" class="section__title"><?php esc_html_e( 'Two minutes, three steps, no strings', 'claimfairly' ); ?></h2>
		</div>
		<ol class="steps">
			<li class="step">
				<span class="step__num">1</span>
				<h3><?php esc_html_e( 'Type in your numbers', 'claimfairly' ); ?></h3>
				<p><?php esc_html_e( 'Medical bills, repair cost, the offer you got. Everything is calculated right on your device and nothing is sent to us.', 'claimfairly' ); ?></p>
			</li>
			<li class="step">
				<span class="step__num">2</span>
				<h3><?php esc_html_e( 'Get a range and the math', 'claimfairly' ); ?></h3>
				<p><?php esc_html_e( 'You see a low and high estimate, the exact formula, and the assumptions behind it. No black box, no "call us to find out."', 'claimfairly' ); ?></p>
			</li>
			<li class="step">
				<span class="step__num">3</span>
				<h3><?php esc_html_e( 'Walk in prepared', 'claimfairly' ); ?></h3>
				<p><?php esc_html_e( 'Take the numbers to the adjuster, turn them into a demand letter, or bring them to a lawyer so the first meeting goes further.', 'claimfairly' ); ?></p>
			</li>
		</ol>
	</div>
</section>

<section class="section" aria-labelledby="why-title">
	<div class="wrap">
		<div class="split">
			<div class="split__intro">
				<p class="eyebrow"><?php esc_html_e( 'Why people trust it', 'claimfairly' ); ?></p>
				<h2 id="why-title" class="section__title"><?php esc_html_e( 'Straight answers, even when they are not what you hoped', 'claimfairly' ); ?></h2>
				<p class="lead"><?php esc_html_e( 'Most claim calculators are lead forms in disguise. Ours show the same math an adjuster uses, including the parts that work against you.', 'claimfairly' ); ?></p>
				<a class="text-link" href="<?php echo esc_url( home_url( '/methodology/' ) ); ?>"><?php esc_html_e( 'Read our methodology', 'claimfairly' ); ?> <?php echo claimfairly_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
			<ul class="features">
				<li class="feature"><span class="icon-sq c-blue"><?php echo claimfairly_icon( 'range', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><h3><?php esc_html_e( 'Ranges, not promises', 'claimfairly' ); ?></h3><p><?php esc_html_e( 'Claims settle across a range. We never say "you will get $X."', 'claimfairly' ); ?></p></li>
				<li class="feature"><span class="icon-sq c-violet"><?php echo claimfairly_icon( 'formula', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><h3><?php esc_html_e( 'Formula on every result', 'claimfairly' ); ?></h3><p><?php esc_html_e( 'Every number comes with the math, so you can check it or argue it.', 'claimfairly' ); ?></p></li>
				<li class="feature"><span class="icon-sq c-orange"><?php echo claimfairly_icon( 'doc', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><h3><?php esc_html_e( 'Sources and review dates', 'claimfairly' ); ?></h3><p><?php esc_html_e( 'State rules link to official sources and show when we last checked them.', 'claimfairly' ); ?></p></li>
				<li class="feature"><span class="icon-sq c-green"><?php echo claimfairly_icon( 'lock', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><h3><?php esc_html_e( 'Private by design', 'claimfairly' ); ?></h3><p><?php esc_html_e( 'No accounts, no forms that go anywhere, no tracking cookies.', 'claimfairly' ); ?></p></li>
			</ul>
		</div>
	</div>
</section>

<?php
$claimfairly_guides = get_posts(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
	)
);
if ( $claimfairly_guides ) :
	?>
<section class="section section--soft" aria-labelledby="guides-title">
	<div class="wrap">
		<div class="section__head">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Guides', 'claimfairly' ); ?></p>
				<h2 id="guides-title" class="section__title"><?php esc_html_e( 'Plain-English answers to the questions adjusters dodge', 'claimfairly' ); ?></h2>
			</div>
			<?php if ( $claimfairly_guides_id ) : ?>
				<a class="text-link" href="<?php echo esc_url( get_permalink( $claimfairly_guides_id ) ); ?>"><?php esc_html_e( 'All guides', 'claimfairly' ); ?> <?php echo claimfairly_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endif; ?>
		</div>
		<ul class="guide-grid">
			<?php
			foreach ( $claimfairly_guides as $claimfairly_guide ) {
				echo claimfairly_guide_card( $claimfairly_guide ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function.
			}
			?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php
$claimfairly_state_pages = $claimfairly_states ? get_pages(
	array(
		'parent'      => $claimfairly_states->ID,
		'post_status' => 'publish',
		'sort_column' => 'menu_order,post_title',
		'number'      => 12,
	)
) : array();
if ( $claimfairly_state_pages ) :
	?>
<section class="section" aria-labelledby="states-title">
	<div class="wrap">
		<div class="states-band">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'State rules', 'claimfairly' ); ?></p>
				<h2 id="states-title" class="section__title"><?php esc_html_e( 'Your state can change the answer completely', 'claimfairly' ); ?></h2>
				<p><?php esc_html_e( 'In some states, being 1% at fault means you get nothing. In others you can recover even at 90%. Check yours.', 'claimfairly' ); ?></p>
			</div>
			<ul class="chips">
				<?php foreach ( $claimfairly_state_pages as $claimfairly_state ) : ?>
					<li><a class="chip" href="<?php echo esc_url( get_permalink( $claimfairly_state ) ); ?>"><?php echo esc_html( preg_replace( '/\s+Car Accident Claim.*$/', '', get_the_title( $claimfairly_state ) ) ); ?></a></li>
				<?php endforeach; ?>
				<li><a class="chip chip--dark" href="<?php echo esc_url( get_permalink( $claimfairly_states ) ); ?>"><?php esc_html_e( 'All states', 'claimfairly' ); ?></a></li>
			</ul>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section section--lavender" aria-labelledby="founder-title">
	<div class="wrap">
		<div class="founder">
			<div class="founder__photo">
				<?php if ( $claimfairly_photo ) : ?>
					<?php echo wp_get_attachment_image( $claimfairly_photo, 'medium', false, array( 'alt' => $claimfairly_founder ) ); ?>
				<?php else : ?>
					<span class="founder__initials" aria-hidden="true"><?php echo esc_html( claimfairly_initials( $claimfairly_founder ? $claimfairly_founder : 'Claim Fairly' ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="founder__body">
				<p class="eyebrow"><?php esc_html_e( 'A note from the person who built this', 'claimfairly' ); ?></p>
				<h2 id="founder-title" class="founder__title"><?php echo esc_html( $claimfairly_founder ? sprintf( /* translators: %s: name. */ __( 'Hi, I\'m %s.', 'claimfairly' ), strtok( $claimfairly_founder, ' ' ) ) : __( 'Hi there.', 'claimfairly' ) ); ?></h2>
				<p><?php echo esc_html( claimfairly_opt( 'cf_founder_note' ) ); ?></p>
				<p class="founder__sign"><?php echo esc_html( $claimfairly_founder ); ?><span><?php esc_html_e( 'Founder, ClaimFairly', 'claimfairly' ); ?></span></p>
				<a class="text-link" href="<?php echo esc_url( claimfairly_about_url() ); ?>"><?php esc_html_e( 'More about me and the site', 'claimfairly' ); ?> <?php echo claimfairly_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="faq-title">
	<div class="wrap">
		<div class="section__head section__head--center">
			<p class="eyebrow"><?php esc_html_e( 'FAQ', 'claimfairly' ); ?></p>
			<h2 id="faq-title" class="section__title"><?php esc_html_e( 'Questions people ask before they use it', 'claimfairly' ); ?></h2>
		</div>
		<div class="faq faq--2col">
			<?php
			$claimfairly_faqs = claimfairly_home_faqs();
			foreach ( $claimfairly_faqs as $claimfairly_faq ) {
				echo '<details class="faq__item"><summary><h3 class="faq__q">' . esc_html( $claimfairly_faq[0] ) . '</h3><span class="faq__icon" aria-hidden="true"></span></summary><div class="faq__a"><p>' . esc_html( $claimfairly_faq[1] ) . '</p></div></details>';
			}
			?>
		</div>
	</div>
</section>

<section class="section section--tight">
	<div class="wrap">
		<div class="cta-band">
			<div>
				<h2 class="cta-band__title"><?php esc_html_e( 'Got an offer from the insurer?', 'claimfairly' ); ?></h2>
				<p><?php esc_html_e( 'Check it against your real numbers before you say yes. It takes about two minutes.', 'claimfairly' ); ?></p>
			</div>
			<div class="cta-band__actions">
				<a class="btn btn--light btn--lg" href="<?php echo esc_url( $claimfairly_estimator ); ?>"><?php esc_html_e( 'Check my offer', 'claimfairly' ); ?></a>
				<a class="btn btn--outline-light btn--lg" href="<?php echo esc_url( $claimfairly_dv ); ?>"><?php esc_html_e( 'Diminished value', 'claimfairly' ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
