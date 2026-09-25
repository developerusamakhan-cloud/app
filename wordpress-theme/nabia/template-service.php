<?php
/**
 * Template Name: Service page (automatic: page slug = service)
 * Template Post Type: page
 *
 * Complete service page. The "Service: …" templates in /page-templates use this file.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	$nabia_service = nabia_current_service();

	if ( ! $nabia_service ) {
		// Not linked to a service: behave like a normal page.
		nabia_page_header( array( 'title' => get_the_title() ) );
		echo '<div class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></div>';
		continue;
	}

	$nabia_slug     = $nabia_service['slug'];
	$nabia_audit    = nabia_service_has_audit( $nabia_slug );
	$nabia_services = nabia_page_url( 'services' );

	$nabia_get = '<div class="aside-card get-card"><p class="aside-card-title">' . esc_html__( 'What you get', 'nabia' ) . '</p><ul class="check-list">';
	foreach ( $nabia_service['get'] as $nabia_item ) {
		$nabia_get .= '<li><span aria-hidden="true">&#10003;</span>' . esc_html( $nabia_item ) . '</li>';
	}
	$nabia_get .= '</ul>';
	if ( ! empty( $nabia_service['tags'] ) ) {
		$nabia_get .= '<ul class="tags">';
		foreach ( $nabia_service['tags'] as $nabia_tag ) {
			$nabia_get .= '<li>' . esc_html( $nabia_tag ) . '</li>';
		}
		$nabia_get .= '</ul>';
	}
	$nabia_get .= '</div>';

	nabia_page_header(
		array(
			'eyebrow' => $nabia_service['title'],
			'icon'    => $nabia_service['icon'],
			'title'   => $nabia_service['headline'],
			'intro'   => $nabia_service['intro'],
			'crumbs'  => array( array( __( 'Services', 'nabia' ), $nabia_services ? $nabia_services : home_url( '/#services' ) ) ),
			'actions' => ( $nabia_audit ? nabia_button( __( 'Get a free audit', 'nabia' ), '#audit' ) : nabia_button( __( 'Hire me', 'nabia' ), nabia_hire_url() ) )
				. ( ! empty( $nabia_service['plans'] ) ? nabia_button( __( 'See prices', 'nabia' ), '#pricing', 'ghost' ) : nabia_button( __( 'See my work', 'nabia' ), nabia_portfolio_url(), 'ghost' ) ),
			'aside'   => $nabia_get,
			'class'   => 'is-service',
		)
	);
	?>

	<section class="section section-tight service-features">
		<div class="container">
			<?php nabia_section_head( __( 'Why it works', 'nabia' ), __( 'Built to help your business grow', 'nabia' ) ); ?>
			<div class="feature-grid">
				<?php foreach ( $nabia_service['features'] as $nabia_index => $nabia_feature ) : ?>
					<article class="feature" data-reveal style="--i:<?php echo (int) $nabia_index % 3; ?>">
						<span class="feature-num"><?php echo esc_html( sprintf( '%02d', $nabia_index + 1 ) ); ?></span>
						<h3><?php echo esc_html( $nabia_feature[0] ); ?></h3>
						<p><?php echo esc_html( $nabia_feature[1] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="service-for" data-reveal>
				<h3><?php esc_html_e( 'Perfect for', 'nabia' ); ?></h3>
				<ul>
					<?php foreach ( $nabia_service['for'] as $nabia_item ) : ?>
						<li><?php echo esc_html( $nabia_item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>

	<?php
	get_template_part( 'template-parts/why' );

	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}

	// Real examples (internal links to portfolio).
	nabia_related_websites_section( __( 'Recent work', 'nabia' ) );

	if ( ! empty( $nabia_service['plans'] ) ) {
		get_template_part( 'template-parts/home', 'pricing', array( 'only' => $nabia_service['plans'] ) );
	}

	// Dark video reviews sit between two light sections.
	get_template_part( 'template-parts/home', 'videos' );
	?>

	<section class="section faq">
		<div class="container faq-grid">
			<?php nabia_section_head( __( 'FAQ', 'nabia' ), __( 'Questions? Answers.', 'nabia' ) ); ?>
			<div class="faq-list">
				<?php foreach ( array_merge( $nabia_service['faq'], array_map( 'array_values', array_slice( nabia_faq(), 0, 2 ) ) ) as $nabia_index => $nabia_item ) : ?>
					<details class="faq-item" data-reveal <?php echo 0 === $nabia_index ? 'open' : ''; ?>>
						<summary><span><?php echo esc_html( $nabia_item[0] ); ?></span><span class="faq-icon" aria-hidden="true"><?php nabia_icon( 'plus' ); ?></span></summary>
						<div class="faq-answer"><p><?php echo esc_html( $nabia_item[1] ); ?></p></div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php
	if ( $nabia_audit ) {
		get_template_part( 'template-parts/home', 'audit' );
	}
	?>

	<section class="section section-tight more-services">
		<div class="container">
			<?php nabia_section_head( __( 'More services', 'nabia' ), __( 'Need something else?', 'nabia' ) ); ?>
			<ul class="service-links">
				<?php foreach ( nabia_services() as $nabia_other ) : ?>
					<?php
					if ( $nabia_other['slug'] === $nabia_slug ) {
						continue;
					}
					$nabia_url = nabia_service_url( $nabia_other['slug'] );
					?>
					<li>
						<a href="<?php echo esc_url( $nabia_url ? $nabia_url : home_url( '/#services' ) ); ?>">
							<span class="service-icon"><?php nabia_icon( $nabia_other['icon'] ); ?></span>
							<span><?php echo esc_html( $nabia_other['title'] ); ?></span>
							<?php nabia_icon( 'arrow' ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php
	nabia_related_posts_section( __( 'Helpful articles', 'nabia' ) );
endwhile;

get_footer();
