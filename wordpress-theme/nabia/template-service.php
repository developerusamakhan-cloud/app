<?php
/**
 * Template Name: Service page (automatic: page slug = service)
 * Template Post Type: page
 *
 * A complete service page: intro, benefits, deliverables, pricing, video reviews,
 * FAQ, free audit and links to the other services.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	$nabia_service = nabia_current_service();

	if ( ! $nabia_service ) {
		// Not linked to a service: behave like a normal page.
		echo '<section class="page-hero"><div class="container"><h1 class="page-title">' . esc_html( get_the_title() ) . '</h1></div></section><div class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></div>';
		continue;
	}
	?>
	<section class="service-hero">
		<div class="hero-bg" aria-hidden="true"><span class="blob blob-1"></span><span class="grid-lines"></span></div>
		<div class="container service-hero-inner">
			<div class="service-hero-copy">
				<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nabia' ); ?>" data-reveal>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nabia' ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( nabia_page_url( 'services' ) ? nabia_page_url( 'services' ) : home_url( '/#services' ) ); ?>"><?php esc_html_e( 'Services', 'nabia' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php echo esc_html( $nabia_service['title'] ); ?></span>
				</nav>
				<p class="service-kicker" data-reveal><span class="service-icon"><?php nabia_icon( $nabia_service['icon'] ); ?></span><?php echo esc_html( $nabia_service['title'] ); ?></p>
				<h1 class="service-title" data-split><?php echo esc_html( $nabia_service['headline'] ); ?></h1>
				<p class="service-intro" data-reveal><?php echo esc_html( $nabia_service['intro'] ); ?></p>
				<div class="hero-actions" data-reveal>
					<a class="btn btn-accent btn-lg" href="#audit" data-magnetic><span><?php esc_html_e( 'Get a free audit', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( nabia_hire_url() ); ?>" data-magnetic><span><?php esc_html_e( 'Hire me', 'nabia' ); ?></span></a>
				</div>
			</div>

			<aside class="service-get" data-reveal>
				<h2><?php esc_html_e( 'What you get', 'nabia' ); ?></h2>
				<ul>
					<?php foreach ( $nabia_service['get'] as $nabia_item ) : ?>
						<li><span aria-hidden="true">&#10003;</span><?php echo esc_html( $nabia_item ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( ! empty( $nabia_service['tags'] ) ) : ?>
					<ul class="tags">
						<?php foreach ( $nabia_service['tags'] as $nabia_tag ) : ?>
							<li><?php echo esc_html( $nabia_tag ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</aside>
		</div>
	</section>

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
	get_template_part( 'template-parts/home', 'process' );

	if ( ! empty( $nabia_service['plans'] ) ) {
		get_template_part( 'template-parts/home', 'pricing', array( 'only' => $nabia_service['plans'] ) );
	}

	if ( '' !== trim( get_the_content() ) ) {
		echo '<section class="section section-tight"><div class="container entry-content">';
		the_content();
		echo '</div></section>';
	}

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

	<?php get_template_part( 'template-parts/home', 'audit' ); ?>

	<section class="section section-tight more-services">
		<div class="container">
			<?php nabia_section_head( __( 'More services', 'nabia' ), __( 'Need something else?', 'nabia' ) ); ?>
			<ul class="service-links">
				<?php foreach ( nabia_services() as $nabia_other ) : ?>
					<?php
					if ( $nabia_other['slug'] === $nabia_service['slug'] ) {
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
endwhile;

get_footer();
