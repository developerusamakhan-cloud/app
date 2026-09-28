<?php
/**
 * Site header.
 *
 * @package ClaimFairly
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#0f2a47">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'claimfairly' ); ?></a>

<header class="site-header">
	<div class="container site-header__inner">
		<div class="site-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<svg class="site-brand__mark" width="28" height="28" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
						<path d="M16 2 4 6.5v8.6c0 7.3 5 13.4 12 14.9 7-1.5 12-7.6 12-14.9V6.5L16 2Z" fill="currentColor"/>
						<path d="m10.5 16.2 3.8 3.8 7.4-7.6" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span class="site-brand__name"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
			<span class="nav-toggle__bar" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'claimfairly' ); ?></span>
		</button>

		<nav id="primary-nav" class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'claimfairly' ); ?>">
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
		</nav>
	</div>
</header>

<main id="main" class="site-main" tabindex="-1">
