<?php
/**
 * Site header.
 *
 * @package PowerBachat
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#10201B">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'powerbachat' ); ?></a>

<div class="topbar">
	<div class="wrap topbar__inner">
		<p class="topbar__note">
			<span class="pulse" aria-hidden="true"></span>
			<?php
			printf(
				/* translators: %s: month and year */
				esc_html__( 'Tariffs checked %s', 'powerbachat' ),
				'<strong>' . esc_html( powerbachat_mod( 'pb_tariff_checked' ) ) . '</strong>'
			);
			?>
			<span class="topbar__sep" aria-hidden="true">/</span>
			<span class="topbar__src" data-only="pk">NEPRA uniform tariff</span>
			<span class="topbar__src" data-only="in">TNERC · KSERC orders</span>
			<span class="topbar__src" data-only="bd">BERC residential tariff</span>
		</p>
		<div class="country-switch" role="group" aria-label="<?php esc_attr_e( 'Choose your country', 'powerbachat' ); ?>">
			<?php foreach ( powerbachat_data()['countries'] as $code => $country ) : ?>
				<button type="button" class="country-switch__btn" data-set-country="<?php echo esc_attr( $code ); ?>">
					<span class="country-switch__code"><?php echo esc_html( strtoupper( $code ) ); ?></span>
					<span class="country-switch__name"><?php echo esc_html( $country['name'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<header class="site-header" data-header>
	<div class="wrap site-header__inner">
		<?php powerbachat_logo(); ?>

		<nav class="nav" id="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'powerbachat' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav__list',
					'depth'          => 2,
					'fallback_cb'    => 'powerbachat_primary_fallback',
				)
			);
			?>
			<form class="nav__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="nav-search"><?php esc_html_e( 'Search', 'powerbachat' ); ?></label>
				<input id="nav-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search “LESCO unit price”', 'powerbachat' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
				<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'powerbachat' ); ?>"><?php echo powerbachat_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</form>
		</nav>

		<div class="site-header__actions">
			<a class="btn btn--volt btn--sm" href="<?php echo esc_url( home_url( '/#calculator' ) ); ?>">
				<?php esc_html_e( 'Check my bill', 'powerbachat' ); ?>
			</a>
			<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" data-nav-toggle>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'powerbachat' ); ?></span>
				<?php echo powerbachat_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo powerbachat_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
		</div>
	</div>
</header>

<main id="main" class="site-main">
