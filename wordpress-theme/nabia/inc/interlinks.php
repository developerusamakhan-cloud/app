<?php
/**
 * Internal links between all pages.
 *
 * 1. Contextual links: keywords inside theme text (homepage sections, page intros,
 *    service details, FAQ answers) and inside your own page / post content link to
 *    the matching service page or main page (pricing, free audit, portfolio, about,
 *    contact, services).
 *    - Every target is linked only once per page (first mention wins).
 *    - A page never links to itself.
 *    - Only real, published pages are linked (no links to pages you have not created).
 * 2. "Keep exploring" block: cross-links to the other main pages at the end of inner pages.
 *
 * Change keywords with the `nabia_interlink_keywords` filter, switch the contextual
 * links off with `add_filter( 'nabia_interlinks_enabled', '__return_false' );`.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keywords for the main pages (service keywords come from nabia_autolink_keywords()).
 *
 * @return array role => keywords (most specific first).
 */
function nabia_interlink_page_keywords() {
	return apply_filters(
		'nabia_interlink_keywords',
		array(
			'audit'     => array( 'free website audit', 'free audit', 'website audit', 'instant score' ),
			'pricing'   => array( 'how much does a website cost', 'website cost', 'website packages', 'fixed prices', 'fixed price', 'fixed quote', 'clear quote', 'honest pricing', 'pricing', 'a quote' ),
			'portfolio' => array( 'my portfolio', 'my work', 'recent projects', 'portfolio', 'projects delivered' ),
			'about'     => array( 'about me', 'years of experience', 'full-time WordPress developer', 'freelance web designer' ),
			'contact'   => array( 'get in touch', 'contact me', 'a quick call', 'a quick chat', 'tell me about your project', 'within 24 hours' ),
			'services'  => array( 'all my services', 'my services' ),
		)
	);
}

/**
 * Normalise a URL for "is this the same page?" checks.
 *
 * @param string $url URL.
 * @return string
 */
function nabia_interlink_key( $url ) {
	$url = strtok( (string) $url, '#?' );
	$url = preg_replace( '#^https?://(www\.)?#i', '', (string) $url );
	return untrailingslashit( strtolower( $url ) );
}

/**
 * URL of the page being viewed.
 *
 * @return string
 */
function nabia_interlink_current_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_query_var( 'post_type' ) );
	}
	return '';
}

/**
 * All link targets with their keywords, without the current page.
 *
 * @return array[] Each: url, words.
 */
function nabia_interlink_targets() {
	static $targets = null;
	if ( null !== $targets ) {
		return $targets;
	}
	$targets = array();
	$current = nabia_interlink_key( nabia_interlink_current_url() );
	$home    = nabia_interlink_key( home_url( '/' ) );

	$add = function ( $key, $url, $words ) use ( &$targets, $current, $home ) {
		if ( ! $url || ! $words || false !== strpos( $url, '#' ) ) {
			return;
		}
		$norm = nabia_interlink_key( $url );
		if ( $norm === $current || $norm === $home ) {
			return;
		}
		$targets[ $key ] = array(
			'url'   => $url,
			'words' => $words,
		);
	};

	// Service pages first: their keywords are the most specific.
	foreach ( nabia_autolink_keywords() as $slug => $words ) {
		if ( in_array( $slug, array( 'branding-graphic-design', 'wordpress-development' ), true ) ) {
			// "graphic designer" / "WordPress developer" also describe the person.
			$words = array_merge( $words, 'branding-graphic-design' === $slug ? array( 'graphic designer' ) : array( 'WordPress websites' ) );
		}
		$add( 'service-' . $slug, nabia_service_url( $slug ), $words );
	}

	foreach ( nabia_interlink_page_keywords() as $role => $words ) {
		switch ( $role ) {
			case 'portfolio':
				$url = nabia_portfolio_url();
				break;
			case 'contact':
				$url = nabia_hire_url();
				break;
			default:
				$url = nabia_page_url( $role );
		}
		$add( 'page-' . $role, $url, $words );
	}

	return $targets;
}

/**
 * Remember which targets are already linked on this page.
 *
 * @param string|null $key Target key to mark as used, or null to read.
 * @return array
 */
function nabia_interlink_used( $key = null ) {
	static $used = array();
	if ( null !== $key ) {
		$used[ $key ] = true;
	}
	return $used;
}

/**
 * Link keywords inside a piece of HTML. Never inside links, headings, buttons or code.
 *
 * @param string $html HTML (already safe).
 * @param int    $max  Maximum new links in this piece.
 * @return string
 */
function nabia_link_html( $html, $max = 3 ) {
	if ( ! apply_filters( 'nabia_interlinks_enabled', true ) || is_admin() || is_feed() || '' === trim( (string) $html ) ) {
		return $html;
	}
	$targets = nabia_interlink_targets();
	if ( ! $targets ) {
		return $html;
	}

	$parts   = preg_split( '/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$skip    = 0;
	$added   = 0;
	$skipped = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'code', 'pre', 'button', 'script', 'style', 'figcaption', 'summary', 'label', 'select', 'textarea', 'svg' );

	foreach ( $parts as $i => $part ) {
		if ( '' === $part ) {
			continue;
		}
		if ( '<' === $part[0] ) {
			if ( preg_match( '#^<(/?)([a-z0-9]+)#i', $part, $tag ) && in_array( strtolower( $tag[2] ), $skipped, true ) && '/>' !== substr( $part, -2 ) ) {
				$skip += '/' === $tag[1] ? -1 : 1;
				$skip  = max( 0, $skip );
			}
			continue;
		}
		if ( $skip ) {
			continue;
		}
		$pieces = array( $part );
		// Link the earliest mention first (longest keyword wins on a tie), then look again.
		while ( $added < $max ) {
			$best = null;
			$used = nabia_interlink_used();
			foreach ( $targets as $key => $target ) {
				if ( isset( $used[ $key ] ) ) {
					continue;
				}
				foreach ( $target['words'] as $word ) {
					$pattern = '/(?<![\w-])(' . preg_quote( $word, '/' ) . ')(?![\w-])/' . ( 'AI' === $word ? '' : 'i' );
					foreach ( $pieces as $p => $piece ) {
						if ( 0 === strpos( $piece, '<a ' ) || ! preg_match( $pattern, $piece, $m, PREG_OFFSET_CAPTURE ) ) {
							continue;
						}
						$rank = array( $p, $m[1][1], -strlen( $m[1][0] ) );
						if ( null === $best || $rank < $best['rank'] ) {
							$best = array(
								'rank'  => $rank,
								'key'   => $key,
								'url'   => $target['url'],
								'piece' => $p,
								'pos'   => $m[1][1],
								'text'  => $m[1][0],
							);
						}
						break;
					}
				}
			}
			if ( null === $best ) {
				break;
			}
			$piece = $pieces[ $best['piece'] ];
			array_splice(
				$pieces,
				$best['piece'],
				1,
				array(
					substr( $piece, 0, $best['pos'] ),
					'<a class="auto-link" href="' . esc_url( $best['url'] ) . '">' . $best['text'] . '</a>',
					substr( $piece, $best['pos'] + strlen( $best['text'] ) ),
				)
			);
			nabia_interlink_used( $best['key'] );
			++$added;
		}
		$parts[ $i ] = implode( '', $pieces );
	}
	return implode( '', $parts );
}

/**
 * Escape plain text and link its keywords.
 *
 * @param string $text Plain text.
 * @param int    $max  Maximum links.
 * @return string Safe HTML.
 */
function nabia_link_text( $text, $max = 2 ) {
	return nabia_link_html( esc_html( $text ), $max );
}

/**
 * Link keywords in your own page and post content (blocks / classic editor).
 *
 * @param string $content Content.
 * @return string
 */
function nabia_interlink_content( $content ) {
	if ( ! is_singular() || ! in_the_loop() || ! is_main_query() || (int) get_the_ID() !== (int) get_queried_object_id() ) {
		return $content;
	}
	return nabia_link_html( $content, is_page() ? 4 : 5 );
}
add_filter( 'the_content', 'nabia_interlink_content', 20 );

/**
 * The main pages for the "Keep exploring" block (current page left out).
 *
 * @return array[] Each: icon, title, text, url.
 */
function nabia_explore_links() {
	$home    = is_front_page() ? '' : home_url( '/' );
	$blog    = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : '';
	$current = nabia_interlink_key( nabia_interlink_current_url() );
	$links   = array(
		array( 'layout', __( 'Services', 'nabia' ), __( 'Everything I can build for you', 'nabia' ), nabia_page_url( 'services' ) ? nabia_page_url( 'services' ) : $home . '#services' ),
		array( 'grid', __( 'Portfolio', 'nabia' ), __( 'Websites I have designed and built', 'nabia' ), nabia_portfolio_url() ),
		array( 'star', __( 'Pricing', 'nabia' ), __( 'Website packages and maintenance', 'nabia' ), nabia_page_url( 'pricing' ) ? nabia_page_url( 'pricing' ) : $home . '#pricing' ),
		array( 'search', __( 'Free website audit', 'nabia' ), __( 'An instant score and a PDF report', 'nabia' ), nabia_page_url( 'audit' ) ? nabia_page_url( 'audit' ) : $home . '#audit' ),
		array( 'sparkles', __( 'About me', 'nabia' ), __( 'The person behind your website', 'nabia' ), nabia_page_url( 'about' ) ? nabia_page_url( 'about' ) : $home . '#about' ),
		$blog ? array( 'pen', __( 'Blog', 'nabia' ), __( 'Tips for a better website', 'nabia' ), $blog ) : null,
		array( 'mail', __( 'Contact', 'nabia' ), __( 'Tell me about your project', 'nabia' ), nabia_hire_url() ),
	);
	$out = array();
	foreach ( array_filter( $links ) as $link ) {
		if ( $current && nabia_interlink_key( $link[3] ) === $current ) {
			continue;
		}
		$out[] = $link;
	}
	return apply_filters( 'nabia_explore_links', $out );
}

/**
 * Print the "Keep exploring" block.
 *
 * @param string $title Section title.
 */
function nabia_explore_section( $title = '' ) {
	$links = nabia_explore_links();
	if ( ! $links ) {
		return;
	}
	?>
	<section class="section section-tight explore" aria-label="<?php esc_attr_e( 'Keep exploring', 'nabia' ); ?>">
		<div class="container">
			<?php nabia_section_head( __( 'Keep exploring', 'nabia' ), $title ? $title : __( 'Where to next?', 'nabia' ) ); ?>
			<ul class="quick-links explore-links">
				<?php foreach ( $links as $nabia_index => $link ) : ?>
					<li data-reveal style="--i:<?php echo (int) $nabia_index % 4; ?>">
						<a href="<?php echo esc_url( $link[3] ); ?>">
							<span class="quick-icon"><?php nabia_icon( $link[0] ); ?></span>
							<strong><?php echo esc_html( $link[1] ); ?></strong>
							<span><?php echo esc_html( $link[2] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}
