<?php
/**
 * Shared layout pieces: the inner page header used on every page, related content
 * and automatic internal links.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The same header design for every inner page.
 *
 * @param array $a {
 *     @type string $eyebrow Small label above the title.
 *     @type string $icon    Optional icon name for the label.
 *     @type string $title   Page title (required).
 *     @type string $intro   Intro paragraph.
 *     @type array  $crumbs  Breadcrumbs between Home and the current page: array of [ label, url ].
 *     @type array  $meta    Small chips under the intro (plain strings).
 *     @type string $actions HTML for buttons.
 *     @type string $aside   HTML for the right-hand visual.
 *     @type string $class   Extra class.
 * }
 */
function nabia_page_header( $a ) {
	$a = wp_parse_args(
		$a,
		array(
			'eyebrow' => '',
			'icon'    => '',
			'title'   => '',
			'intro'   => '',
			'crumbs'  => array(),
			'meta'    => array(),
			'actions' => '',
			'aside'   => '',
			'class'   => '',
		)
	);
	?>
	<section class="inner-hero <?php echo esc_attr( $a['class'] ); ?><?php echo $a['aside'] ? ' has-aside' : ''; ?>">
		<div class="hero-bg" aria-hidden="true"><span class="blob blob-1"></span><span class="blob blob-2"></span><span class="grid-lines"></span></div>
		<div class="container inner-hero-grid">
			<div class="inner-hero-copy">
				<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nabia' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nabia' ); ?></a>
					<?php foreach ( $a['crumbs'] as $crumb ) : ?>
						<span aria-hidden="true">/</span>
						<a href="<?php echo esc_url( $crumb[1] ); ?>"><?php echo esc_html( $crumb[0] ); ?></a>
					<?php endforeach; ?>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $a['title'] ), 8 ) ); ?></span>
				</nav>

				<?php if ( $a['eyebrow'] ) : ?>
					<p class="inner-eyebrow">
						<?php if ( $a['icon'] ) : ?>
							<span class="inner-eyebrow-icon"><?php nabia_icon( $a['icon'] ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( $a['eyebrow'] ); ?>
					</p>
				<?php endif; ?>

				<h1 class="inner-title"><?php echo nabia_tight( $a['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>

				<?php if ( $a['intro'] ) : ?>
					<p class="inner-intro"><?php echo esc_html( $a['intro'] ); ?></p>
				<?php endif; ?>

				<?php if ( $a['meta'] ) : ?>
					<ul class="inner-meta">
						<?php foreach ( $a['meta'] as $item ) : ?>
							<li><?php echo esc_html( $item ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $a['actions'] ) : ?>
					<div class="inner-actions"><?php echo $a['actions']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by theme templates with escaped parts. ?></div>
				<?php endif; ?>
			</div>

			<?php if ( $a['aside'] ) : ?>
				<div class="inner-aside"><?php echo $a['aside']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by theme templates with escaped parts. ?></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * A button as HTML (for page header actions).
 *
 * @param string $label Label.
 * @param string $url   URL.
 * @param string $style accent|ghost.
 * @param bool   $new   Open in new tab.
 * @return string
 */
function nabia_button( $label, $url, $style = 'accent', $new = false ) {
	return sprintf(
		'<a class="btn btn-%1$s btn-lg" href="%2$s"%3$s data-magnetic><span>%4$s</span>%5$s</a>',
		esc_attr( $style ),
		esc_url( $url ),
		$new ? ' target="_blank" rel="noopener noreferrer"' : '',
		esc_html( $label ),
		'accent' === $style ? nabia_get_icon( 'arrow' ) : ''
	);
}

/**
 * A small card for the header aside: title + list of [ label, value ] rows.
 *
 * @param string $title Card title.
 * @param array  $rows  Rows.
 * @param string $extra Extra HTML at the bottom.
 * @return string
 */
function nabia_aside_card( $title, $rows, $extra = '' ) {
	$html = '<div class="aside-card">';
	if ( $title ) {
		$html .= '<p class="aside-card-title">' . esc_html( $title ) . '</p>';
	}
	$html .= '<dl class="aside-rows">';
	foreach ( $rows as $row ) {
		$html .= '<div><dt>' . esc_html( $row[0] ) . '</dt><dd>' . esc_html( $row[1] ) . '</dd></div>';
	}
	$html .= '</dl>' . $extra . '</div>';
	return $html;
}

/**
 * Should the free audit form appear on this service? Only website services, not branding.
 *
 * @param string $slug Service slug.
 * @return bool
 */
function nabia_service_has_audit( $slug ) {
	return ! in_array( $slug, apply_filters( 'nabia_services_without_audit', array( 'branding-graphic-design' ) ), true );
}

/**
 * Related blog posts (same category first, then latest).
 *
 * @param int $post_id Current post (0 for none).
 * @param int $count   How many.
 * @return WP_Post[]
 */
function nabia_related_posts( $post_id = 0, $count = 3 ) {
	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post__not_in'        => $post_id ? array( $post_id ) : array(),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$cats = $post_id ? wp_get_post_categories( $post_id ) : array();
	$posts = $cats ? get_posts( $args + array( 'category__in' => $cats ) ) : array();
	if ( count( $posts ) < $count ) {
		$exclude = array_merge( $post_id ? array( $post_id ) : array(), wp_list_pluck( $posts, 'ID' ) );
		$more    = get_posts( array_merge( $args, array( 'posts_per_page' => $count - count( $posts ), 'post__not_in' => $exclude ) ) );
		$posts   = array_merge( $posts, $more );
	}
	return $posts;
}

/**
 * Related portfolio items (same category first).
 *
 * @param int $post_id Current item (0 for none).
 * @param int $count   How many.
 * @return WP_Post[]
 */
function nabia_related_websites( $post_id = 0, $count = 3 ) {
	$type = nabia_portfolio_type();
	$tax  = nabia_portfolio_taxonomy();
	$args = array(
		'post_type'      => $type,
		'posts_per_page' => $count,
		'post__not_in'   => $post_id ? array( $post_id ) : array(),
		'no_found_rows'  => true,
	);
	$posts = array();
	if ( $post_id && $tax ) {
		$terms = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'ids' ) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$posts = get_posts(
				$args + array(
					'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'taxonomy' => $tax,
							'terms'    => $terms,
						),
					),
				)
			);
		}
	}
	if ( count( $posts ) < $count ) {
		$exclude = array_merge( $post_id ? array( $post_id ) : array(), wp_list_pluck( $posts, 'ID' ) );
		$posts   = array_merge( $posts, get_posts( array_merge( $args, array( 'posts_per_page' => $count - count( $posts ), 'post__not_in' => $exclude ) ) ) );
	}
	return $posts;
}

/**
 * Print a section of related portfolio items.
 *
 * @param string $title   Section title.
 * @param int    $post_id Current item to exclude.
 */
function nabia_related_websites_section( $title, $post_id = 0 ) {
	$posts = nabia_related_websites( $post_id, 3 );
	if ( ! $posts ) {
		return;
	}
	global $post;
	?>
	<section class="section section-tight related work">
		<div class="container">
			<div class="section-head-row">
				<?php nabia_section_head( __( 'Portfolio', 'nabia' ), $title ); ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( nabia_portfolio_url() ); ?>"><span><?php esc_html_e( 'All projects', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
			</div>
			<div class="work-grid">
				<?php
				foreach ( $posts as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					setup_postdata( $post );
					get_template_part( 'template-parts/project', 'card' );
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Print a section of related blog posts.
 *
 * @param string $title   Section title.
 * @param int    $post_id Current post to exclude.
 */
function nabia_related_posts_section( $title, $post_id = 0 ) {
	$posts = nabia_related_posts( $post_id, 3 );
	if ( ! $posts ) {
		return;
	}
	global $post;
	$blog = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/?post_type=post' );
	?>
	<section class="section section-tight related">
		<div class="container">
			<div class="section-head-row">
				<?php nabia_section_head( __( 'Blog', 'nabia' ), $title ); ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( $blog ); ?>"><span><?php esc_html_e( 'All articles', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></a>
			</div>
			<div class="post-grid">
				<?php
				foreach ( $posts as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					setup_postdata( $post );
					get_template_part( 'template-parts/content', 'card' );
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Keywords that link to each service page inside blog posts and project descriptions.
 *
 * @return array slug => keywords (most specific first).
 */
function nabia_autolink_keywords() {
	return apply_filters(
		'nabia_autolink_keywords',
		array(
			'website-maintenance'     => array( 'website maintenance', 'WordPress maintenance', 'maintenance plan' ),
			'shopify-woocommerce'     => array( 'Shopify', 'WooCommerce', 'online store', 'ecommerce', 'e-commerce' ),
			'wix-webflow-squarespace' => array( 'Webflow', 'Squarespace', 'Wix' ),
			'ai-website-solutions'    => array( 'AI chatbot', 'chatbot', 'AI' ),
			'speed-seo'               => array( 'page speed', 'Core Web Vitals', 'SEO' ),
			'branding-graphic-design' => array( 'logo design', 'branding', 'graphic design' ),
			'custom-websites'         => array( 'custom-coded', 'custom code', 'hand-coded' ),
			'wordpress-development'   => array( 'WordPress developer', 'Elementor', 'WordPress' ),
			'web-design'              => array( 'web design', 'website design', 'UI design' ),
		)
	);
}

/**
 * Link the first mention of a service keyword in post content to that service page.
 * At most 4 links per post, one per service; never inside existing links or headings.
 *
 * @param string $content Post content.
 * @return string
 */
function nabia_autolink_content( $content ) {
	if ( ! is_singular( array( 'post', nabia_portfolio_type() ) ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$targets = array();
	foreach ( nabia_autolink_keywords() as $slug => $words ) {
		$url = nabia_service_url( $slug );
		if ( $url ) {
			$targets[ $slug ] = array(
				'url'   => $url,
				'words' => $words,
			);
		}
	}
	if ( ! $targets ) {
		return $content;
	}

	$parts   = preg_split( '/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	$skip    = 0;
	$done    = array();
	$max     = 4;
	$skipped = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'code', 'pre', 'button', 'script', 'style', 'figcaption' );

	foreach ( $parts as $i => $part ) {
		if ( '' === $part ) {
			continue;
		}
		if ( '<' === $part[0] ) {
			if ( preg_match( '#^<(/?)([a-z0-9]+)#i', $part, $tag ) && in_array( strtolower( $tag[2] ), $skipped, true ) ) {
				$skip += '/' === $tag[1] ? -1 : 1;
				$skip  = max( 0, $skip );
			}
			continue;
		}
		if ( $skip || count( $done ) >= $max ) {
			continue;
		}
		// One link per text block, so a new link never ends up inside another.
		foreach ( $targets as $slug => $target ) {
			if ( isset( $done[ $slug ] ) ) {
				continue;
			}
			foreach ( $target['words'] as $word ) {
				$pattern = '/(?<![\w-])(' . preg_quote( $word, '/' ) . ')(?![\w-])/' . ( 'AI' === $word ? '' : 'i' );
				if ( preg_match( $pattern, $parts[ $i ] ) ) {
					$parts[ $i ]   = preg_replace( $pattern, '<a class="auto-link" href="' . esc_url( $target['url'] ) . '">$1</a>', $parts[ $i ], 1 );
					$done[ $slug ] = true;
					continue 3;
				}
			}
		}
	}
	return implode( '', $parts );
}
add_filter( 'the_content', 'nabia_autolink_content', 20 );
