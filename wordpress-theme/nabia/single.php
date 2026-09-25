<?php
/**
 * Single blog post.
 *
 * @package Nabia
 */

get_header();

while ( have_posts() ) :
	the_post();
	$nabia_cats    = get_the_category();
	$nabia_minutes = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
	$nabia_blog    = nabia_blog_url();
	// Home / Blog / current post (no category).
	$nabia_crumbs = array( array( __( 'Blog', 'nabia' ), $nabia_blog ) );
	$nabia_author = get_the_author();
	$nabia_aside  = '<div class="aside-card author-card">'
		. '<span class="author-avatar">' . nabia_author_photo( 64 ) . '</span>'
		. '<p class="aside-card-title">' . esc_html__( 'Written by', 'nabia' ) . '</p>'
		. '<p class="author-name">' . esc_html( $nabia_author ) . '</p>'
		. '<p class="author-role">' . esc_html__( 'WordPress developer & graphic designer', 'nabia' ) . '</p>'
		. '<dl class="aside-rows"><div><dt>' . esc_html__( 'Published', 'nabia' ) . '</dt><dd>' . esc_html( get_the_date() ) . '</dd></div>'
		. '<div><dt>' . esc_html__( 'Reading time', 'nabia' ) . '</dt><dd>' . esc_html(
			/* translators: %d: minutes */
			sprintf( _n( '%d minute', '%d minutes', $nabia_minutes, 'nabia' ), $nabia_minutes )
		) . '</dd></div></dl></div>';

	nabia_page_header(
		array(
			'eyebrow' => __( 'Article', 'nabia' ),
			'icon'    => 'pen',
			'title'   => get_the_title(),
			'intro'   => has_excerpt() ? get_the_excerpt() : '',
			'crumbs'  => $nabia_crumbs,
			'meta'    => array(
				get_the_date(),
				/* translators: %d: minutes */
				sprintf( _n( '%d min read', '%d min read', $nabia_minutes, 'nabia' ), $nabia_minutes ),
			),
			'aside'   => $nabia_aside,
			'class'   => 'is-post',
		)
	);
	?>
	<article <?php post_class( 'section section-tight single-article' ); ?>>
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="featured-media featured-wide" data-reveal><?php the_post_thumbnail( 'full' ); ?></figure>
			<?php endif; ?>

			<div class="article-layout">
				<div class="article-main">
					<div class="entry-content" data-toc-source>
						<?php
						the_content();
						wp_link_pages();
						?>
					</div>

					<footer class="entry-footer">
						<?php the_tags( '<ul class="tags"><li>', '</li><li>', '</li></ul>' ); ?>
						<div class="author-box">
							<span class="author-avatar"><?php echo nabia_author_photo( 72 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<div>
								<p class="author-name"><?php echo esc_html( $nabia_author ); ?></p>
								<p><?php echo esc_html( nabia_mod( 'footer_text' ) ); ?></p>
								<?php nabia_social_links(); ?>
							</div>
						</div>
					</footer>

					<?php
					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-label">' . esc_html__( 'Previous', 'nabia' ) . '</span><span class="nav-title">%title</span>',
							'next_text' => '<span class="nav-label">' . esc_html__( 'Next', 'nabia' ) . '</span><span class="nav-title">%title</span>',
						)
					);

					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>

				<aside class="article-side">
					<nav class="toc" data-toc hidden aria-label="<?php esc_attr_e( 'On this page', 'nabia' ); ?>">
						<p class="toc-title"><?php esc_html_e( 'On this page', 'nabia' ); ?></p>
						<ol></ol>
					</nav>
					<div class="side-cta">
						<p class="side-cta-title"><?php esc_html_e( 'Need help with your website?', 'nabia' ); ?></p>
						<p><?php esc_html_e( 'Get a free audit or hire me to build, fix or speed up your site.', 'nabia' ); ?></p>
						<a class="btn btn-accent" href="#audit"><span><?php esc_html_e( 'Free audit', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
						<a class="side-cta-link" href="<?php echo esc_url( nabia_hire_url() ); ?>"><?php esc_html_e( 'Or hire me', 'nabia' ); ?> &rarr;</a>
					</div>
				</aside>
			</div>
		</div>
	</article>
	<?php
	nabia_related_posts_section( __( 'Keep reading', 'nabia' ), get_the_ID() );
	get_template_part( 'template-parts/home', 'audit' );
endwhile;

get_footer();
