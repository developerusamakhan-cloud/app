<?php
/**
 * Template Name: Full width (builder friendly)
 * Template Post Type: page, post
 *
 * A blank canvas between header and footer — ideal for Elementor or the block editor.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	echo '<div class="entry-content entry-full">';
	the_content();
	echo '</div>';
endwhile;

get_footer();
