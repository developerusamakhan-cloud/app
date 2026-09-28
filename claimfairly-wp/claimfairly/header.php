<?php
/**
 * Site header.
 *
 * @package ClaimFairly
 */

$claimfairly_cta = claimfairly_tool_page_url( 'settlement-estimator' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#ffffff">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'claimfairly' ); ?></a>

<header class="site-header">
	<div class="wrap site-header__inner">
		<div class="brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php echo claimfairly_logo_mark(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
					<span class="brand__name"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<nav id="primary-nav" class="nav" aria-label="<?php esc_attr_e( 'Primary', 'claimfairly' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'claimfairly_menu_fallback',
				)
			);
			?>
			<a class="btn btn--dark nav__cta-mobile" href="<?php echo esc_url( $claimfairly_cta ); ?>"><?php esc_html_e( 'Estimate my claim', 'claimfairly' ); ?></a>
		</nav>

		<div class="site-header__actions">
			<a class="btn btn--dark btn--sm site-header__cta" href="<?php echo esc_url( $claimfairly_cta ); ?>"><?php esc_html_e( 'Estimate my claim', 'claimfairly' ); ?></a>
			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
				<span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'claimfairly' ); ?></span>
			</button>
		</div>
	</div>
</header>

<main id="main" class="site-main" tabindex="-1">
