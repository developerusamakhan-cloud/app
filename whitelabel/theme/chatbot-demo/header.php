<?php
/**
 * Header.
 *
 * @package Chatbot_Demo
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'chatbot-demo' ); ?></a>
<header class="site-header">
	<div class="wrap">
		<?php if ( has_custom_logo() ) : ?>
			<div class="brand"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="brand__mark"><?php echo wp_kses( chatbot_demo_icon( 'chat' ), chatbot_demo_svg_kses() ); ?></span>
				<span><?php bloginfo( 'name' ); ?></span>
			</a>
		<?php endif; ?>
		<button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false"><?php esc_html_e( 'Menu', 'chatbot-demo' ); ?></button>
		<nav id="main-nav" class="main-nav" aria-label="<?php esc_attr_e( 'Main menu', 'chatbot-demo' ); ?>">
			<?php chatbot_demo_nav(); ?>
		</nav>
	</div>
</header>
<main id="main">
