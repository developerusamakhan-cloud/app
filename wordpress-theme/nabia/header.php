<?php
/**
 * Site header.
 *
 * @package Nabia
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'nabia' ); ?></a>

<?php if ( nabia_mod( 'enable_preloader' ) && is_front_page() ) : ?>
	<div class="preloader" aria-hidden="true">
		<div class="preloader-inner">
			<span class="preloader-count">0</span>
			<span class="preloader-word"><?php echo esc_html( nabia_mod( 'brand_name' ) ); ?></span>
		</div>
	</div>
<?php endif; ?>

<?php if ( nabia_mod( 'enable_cursor' ) ) : ?>
	<div class="cursor" aria-hidden="true"><span class="cursor-label"></span></div>
<?php endif; ?>

<div class="scroll-progress" aria-hidden="true"></div>

<header class="site-header" id="top">
	<div class="container header-inner">
		<div class="site-brand"><?php nabia_logo(); ?></div>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary', 'nabia' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'nabia_menu_fallback',
				)
			);
			?>
		</nav>

		<div class="header-actions">
			<a class="btn btn-accent btn-sm header-cta" href="<?php echo esc_url( is_front_page() ? '#contact' : home_url( '/#contact' ) ); ?>" data-magnetic>
				<span><?php esc_html_e( "Let's talk", 'nabia' ); ?></span>
				<?php nabia_icon( 'arrow-up' ); ?>
			</a>
			<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu">
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'nabia' ); ?></span>
				<span class="menu-toggle-lines" aria-hidden="true"><i></i><i></i></span>
			</button>
		</div>
	</div>
</header>

<div class="mobile-menu" id="mobile-menu" hidden>
	<div class="container">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'mobile-menu-list',
				'depth'          => 1,
				'fallback_cb'    => 'nabia_menu_fallback',
			)
		);
		?>
		<div class="mobile-menu-foot">
			<a href="mailto:<?php echo esc_attr( antispambot( nabia_mod( 'contact_email' ) ) ); ?>"><?php echo esc_html( antispambot( nabia_mod( 'contact_email' ) ) ); ?></a>
			<?php nabia_social_links(); ?>
		</div>
	</div>
</div>

<main id="main" class="site-main">
