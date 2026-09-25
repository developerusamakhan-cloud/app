<?php
/**
 * Website analyzer: fetches a page and scores Design, SEO, Content and Speed & Security.
 *
 * Every check returns: label, status (pass|warn|fail), weight (importance 1 to 3),
 * found (what we saw) and fix (what to do). Scores are weighted: pass = 1, warn = 0.5, fail = 0.
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category meta: title, weight in the overall score and a short "why it matters".
 *
 * @return array
 */
function nwa_categories() {
	return array(
		'design'  => array(
			'title'  => 'Design & User Experience',
			'short'  => 'Design',
			'weight' => 25,
			'why'    => 'Visitors decide in about 50 milliseconds whether a website feels trustworthy. A clear, mobile friendly design with obvious next steps turns visitors into enquiries.',
		),
		'seo'     => array(
			'title'  => 'SEO',
			'short'  => 'SEO',
			'weight' => 30,
			'why'    => 'SEO decides whether people can find you on Google at all. These are the on page basics Google reads first on every visit.',
		),
		'content' => array(
			'title'  => 'Content',
			'short'  => 'Content',
			'weight' => 20,
			'why'    => 'Good content answers questions, builds trust and gives Google something to rank. It is also what finally convinces a visitor to contact you.',
		),
		'speed'   => array(
			'title'  => 'Speed & Security',
			'short'  => 'Speed',
			'weight' => 25,
			'why'    => 'Slow or insecure websites lose visitors and rankings. Every extra second of loading time costs conversions, and browsers warn people about unsafe sites.',
		),
	);
}

/**
 * Normalise a URL typed by a visitor.
 *
 * @param string $url Raw input.
 * @return string Empty when invalid.
 */
function nwa_normalize_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	if ( ! preg_match( '#^https?://#i', $url ) ) {
		$url = 'https://' . $url;
	}
	$url   = esc_url_raw( $url, array( 'http', 'https' ) );
	$parts = wp_parse_url( $url );
	if ( empty( $parts['host'] ) || false === strpos( $parts['host'], '.' ) ) {
		return '';
	}
	return $url;
}

/**
 * Fetch a URL safely (no private or local addresses).
 *
 * @param string $url     URL.
 * @param int    $timeout Seconds.
 * @param int    $limit   Max bytes.
 * @return array|WP_Error
 */
function nwa_fetch( $url, $timeout = 20, $limit = 3145728 ) {
	$start    = microtime( true );
	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'             => $timeout,
			'redirection'         => 5,
			'limit_response_size' => $limit,
			'user-agent'          => 'Mozilla/5.0 (compatible; NabiaWebsiteAudit/' . NWA_VERSION . '; +' . home_url( '/' ) . ')',
			'headers'             => array(
				'Accept'          => 'text/html,application/xhtml+xml,*/*;q=0.8',
				'Accept-Encoding' => 'gzip, deflate',
			),
		)
	);
	$time     = microtime( true ) - $start;
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$final = $url;
	if ( isset( $response['http_response'] ) && is_object( $response['http_response'] ) && method_exists( $response['http_response'], 'get_response_object' ) ) {
		$obj = $response['http_response']->get_response_object();
		if ( ! empty( $obj->url ) ) {
			$final = $obj->url;
		}
	}
	return array(
		'code'    => (int) wp_remote_retrieve_response_code( $response ),
		'body'    => (string) wp_remote_retrieve_body( $response ),
		'headers' => wp_remote_retrieve_headers( $response ),
		'time'    => $time,
		'url'     => $final,
	);
}

/**
 * Build a check.
 *
 * @param string $label  Label.
 * @param string $status pass|warn|fail.
 * @param int    $weight Importance 1 to 3.
 * @param string $found  What we found.
 * @param string $fix    What to do (empty for passes).
 * @return array
 */
function nwa_check( $label, $status, $weight, $found, $fix = '' ) {
	return array(
		'label'  => $label,
		'status' => $status,
		'weight' => $weight,
		'found'  => $found,
		'fix'    => 'pass' === $status ? '' : $fix,
	);
}

/**
 * Run a full audit.
 *
 * @param string $url URL.
 * @return array { ok, error, url, final_url, fetched, stats, categories{ key => { score, checks } }, overall, grade }
 */
function nwa_run_audit( $url ) {
	$result = array(
		'ok'         => false,
		'error'      => '',
		'url'        => $url,
		'final_url'  => $url,
		'fetched'    => time(),
		'stats'      => array(),
		'categories' => array(),
		'overall'    => 0,
		'grade'      => '',
	);

	$page = nwa_fetch( $url );
	if ( is_wp_error( $page ) && 0 === stripos( $url, 'https://' ) ) {
		// Some sites still only answer on http.
		$page = nwa_fetch( 'http://' . substr( $url, 8 ) );
	}
	if ( is_wp_error( $page ) ) {
		$result['error'] = 'We could not reach this website (' . $page->get_error_message() . '). Please check the address and try again.';
		return $result;
	}
	if ( $page['code'] >= 400 || '' === trim( $page['body'] ) ) {
		$result['error'] = sprintf( 'The website answered with an error (HTTP %d), so it could not be analysed. Please check the address and try again.', $page['code'] );
		return $result;
	}

	$html  = $page['body'];
	$final = $page['url'];
	$host  = (string) wp_parse_url( $final, PHP_URL_HOST );
	$https = 0 === stripos( $final, 'https://' );

	// Parse the HTML.
	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR );
	libxml_clear_errors();
	$xp = new DOMXPath( $dom );

	$q     = function ( $expr ) use ( $xp ) {
		$nodes = $xp->query( $expr );
		return false === $nodes ? $xp->query( '//*[false()]' ) : $nodes;
	};
	$first = function ( $expr, $attr = '' ) use ( $q ) {
		$nodes = $q( $expr );
		if ( ! $nodes->length ) {
			return '';
		}
		$node = $nodes->item( 0 );
		return trim( $attr ? $node->getAttribute( $attr ) : $node->textContent );
	};
	$meta  = function ( $name ) use ( $first ) {
		$lower = strtolower( $name );
		$value = $first( "//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='$lower']", 'content' );
		return $value ? $value : $first( "//meta[translate(@property,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='$lower']", 'content' );
	};

	// Visible text.
	$text_dom = $dom->cloneNode( true );
	$txp      = new DOMXPath( $text_dom );
	foreach ( array( '//script', '//style', '//noscript', '//svg', '//template' ) as $expr ) {
		$nodes = $txp->query( $expr );
		if ( $nodes ) {
			foreach ( iterator_to_array( $nodes ) as $node ) {
				$node->parentNode->removeChild( $node );
			}
		}
	}
	$body_nodes = $txp->query( '//body' );
	$text       = $body_nodes && $body_nodes->length ? $body_nodes->item( 0 )->textContent : $text_dom->textContent;
	$text       = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) ) );
	$words      = $text ? count( preg_split( '/\s+/u', $text ) ) : 0;

	$images      = $q( '//img' );
	$img_total   = $images->length;
	$img_alt     = 0;
	$img_sized   = 0;
	$img_lazy    = 0;
	$img_modern  = 0;
	$mixed       = 0;
	foreach ( $images as $img ) {
		// An empty alt="" is correct for decorative images, so only a missing alt counts.
		if ( $img->hasAttribute( 'alt' ) ) {
			++$img_alt;
		}
		if ( $img->getAttribute( 'width' ) && $img->getAttribute( 'height' ) ) {
			++$img_sized;
		}
		if ( 'lazy' === strtolower( $img->getAttribute( 'loading' ) ) || $img->getAttribute( 'data-src' ) || $img->getAttribute( 'data-lazy-src' ) ) {
			++$img_lazy;
		}
		$src = strtolower( $img->getAttribute( 'src' ) . ' ' . $img->getAttribute( 'srcset' ) . ' ' . $img->getAttribute( 'data-src' ) );
		if ( preg_match( '/\.(webp|avif)\b/', $src ) ) {
			++$img_modern;
		}
		if ( $https && 0 === strpos( trim( $img->getAttribute( 'src' ) ), 'http://' ) ) {
			++$mixed;
		}
	}
	$picture_sources = $q( "//picture/source[contains(@type,'webp') or contains(@type,'avif')]" )->length;
	if ( $picture_sources ) {
		$img_modern = max( $img_modern, min( $img_total, $picture_sources ) );
	}

	$links       = $q( '//a[@href]' );
	$internal    = 0;
	$external    = 0;
	$social      = 0;
	$contact     = 0;
	$cta         = 0;
	$cta_words   = '/\b(contact|get in touch|quote|book|call|buy|shop|order|get started|start|hire|enquire|inquire|subscribe|sign up|request|free|schedule|talk|whatsapp)\b/i';
	$social_host = '/(facebook|instagram|linkedin|twitter|x\.com|youtube|tiktok|pinterest|behance|dribbble|linktr\.ee)/i';
	foreach ( $links as $a ) {
		$href = trim( $a->getAttribute( 'href' ) );
		if ( '' === $href || '#' === $href[0] || 0 === stripos( $href, 'javascript:' ) ) {
			continue;
		}
		if ( preg_match( '/^(tel:|mailto:)|wa\.me|whatsapp\.com/i', $href ) ) {
			++$contact;
			continue;
		}
		$link_host = (string) wp_parse_url( $href, PHP_URL_HOST );
		if ( '' === $link_host || strcasecmp( preg_replace( '/^www\./', '', $link_host ), preg_replace( '/^www\./', '', $host ) ) === 0 ) {
			++$internal;
		} else {
			++$external;
			if ( preg_match( $social_host, $link_host ) ) {
				++$social;
			}
		}
		if ( preg_match( $cta_words, $a->textContent ) ) {
			++$cta;
		}
	}
	foreach ( $q( '//button' ) as $button ) {
		if ( preg_match( $cta_words, $button->textContent ) ) {
			++$cta;
		}
	}
	if ( preg_match( '/\+?\d[\d\s().-]{8,}\d/', $text ) || preg_match( '/[\w.+-]+@[\w-]+\.[\w.]+/', $text ) ) {
		++$contact;
	}

	$scripts        = $q( '//script[@src]' )->length;
	$inline_scripts = $q( '//script[not(@src)]' )->length;
	$styles         = $q( "//link[contains(translate(@rel,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'stylesheet')]" )->length;
	$blocking       = $q( '//head/script[@src][not(@async)][not(@defer)][not(@type="module")]' )->length;
	$fonts          = array();
	foreach ( $q( "//link[contains(@href,'fonts.googleapis.com')]" ) as $font_link ) {
		if ( preg_match_all( '/family=([^:&|]+)/', urldecode( $font_link->getAttribute( 'href' ) ), $m ) ) {
			foreach ( $m[1] as $family ) {
				$fonts[ strtolower( trim( $family ) ) ] = true;
			}
		}
	}
	foreach ( array( '//script[@src]', "//link[@rel='stylesheet'][@href]" ) as $expr ) {
		foreach ( $q( $expr ) as $node ) {
			$src = $node->getAttribute( 'src' ) ? $node->getAttribute( 'src' ) : $node->getAttribute( 'href' );
			if ( $https && 0 === strpos( $src, 'http://' ) ) {
				++$mixed;
			}
		}
	}

	$size_kb     = strlen( $html ) / 1024;
	$title       = $first( '//title' );
	$description = $meta( 'description' );
	$h1s         = $q( '//h1' );
	$h2          = $q( '//h2' )->length;
	$h3          = $q( '//h3' )->length;
	$paragraphs  = $q( '//p' );
	$headers     = $page['headers'];
	$header      = function ( $name ) use ( $headers ) {
		$value = isset( $headers[ $name ] ) ? $headers[ $name ] : '';
		return is_array( $value ) ? implode( ', ', $value ) : (string) $value;
	};
	$port        = wp_parse_url( $final, PHP_URL_PORT );
	$origin      = ( $https ? 'https://' : 'http://' ) . $host . ( $port ? ':' . $port : '' );

	// Robots.txt and sitemap.
	$robots_txt = nwa_fetch( $origin . '/robots.txt', 8, 204800 );
	$has_robots = ! is_wp_error( $robots_txt ) && 200 === $robots_txt['code'] && false !== stripos( $robots_txt['body'], 'user-agent' );
	$sitemap    = $has_robots && preg_match( '/^\s*sitemap:/im', $robots_txt['body'] );
	if ( ! $sitemap ) {
		foreach ( array( '/sitemap_index.xml', '/sitemap.xml', '/wp-sitemap.xml' ) as $path ) {
			$map = nwa_fetch( $origin . $path, 8, 204800 );
			if ( ! is_wp_error( $map ) && 200 === $map['code'] && preg_match( '/<(urlset|sitemapindex)\b/i', $map['body'] ) ) {
				$sitemap = true;
				break;
			}
		}
	}

	// Readability (Flesch reading ease, English).
	$sentences = max( 1, preg_match_all( '/[.!?]+(\s|$)/u', $text ) );
	$syllables = 0;
	foreach ( array_slice( preg_split( '/\s+/u', strtolower( $text ) ), 0, 3000 ) as $word ) {
		$word = preg_replace( '/[^a-z]/', '', $word );
		if ( '' === $word ) {
			continue;
		}
		$count      = preg_match_all( '/[aeiouy]+/', preg_replace( '/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $word ) );
		$syllables += max( 1, $count );
	}
	$sample_words = max( 1, min( $words, 3000 ) );
	$flesch       = $words ? round( 206.835 - 1.015 * ( $words / $sentences ) - 84.6 * ( $syllables / $sample_words ), 0 ) : 0;
	$flesch       = max( 0, min( 100, $flesch ) );

	$long_paragraphs = 0;
	foreach ( $paragraphs as $p ) {
		if ( str_word_count( $p->textContent ) > 120 ) {
			++$long_paragraphs;
		}
	}
	$text_ratio = strlen( $html ) ? round( strlen( $text ) / strlen( $html ) * 100, 1 ) : 0;
	$year       = (int) gmdate( 'Y' );
	preg_match_all( '/(?:©|&copy;|&#169;|copyright)\D{0,20}(\d{4})(?:\s*(?:-|to)\s*(\d{4}))?/iu', $html, $years );
	$copyright  = 0;
	foreach ( array_merge( $years[1], $years[2] ) as $y ) {
		$copyright = max( $copyright, (int) $y );
	}
	$trust = preg_match( '/\b(testimonial|review|rated|stars?|clients? say|trusted by|case stud)/i', $text );

	$pct = function ( $part, $total ) {
		return $total ? round( $part / $total * 100 ) : 100;
	};

	/* --------------------------------------------------------------- SEO */
	$seo   = array();
	$len   = mb_strlen( $title );
	$seo[] = ! $title
		? nwa_check( 'Page title', 'fail', 3, 'No title tag found.', 'Add a unique title of 50 to 60 characters with your main service and location, for example "Wedding Photographer in Leeds | Your Brand".' )
		: ( $len >= 30 && $len <= 65
			? nwa_check( 'Page title', 'pass', 3, sprintf( '"%s" (%d characters).', nwa_cut( $title, 70 ), $len ) )
			: nwa_check( 'Page title', 'warn', 3, sprintf( '"%s" is %d characters.', nwa_cut( $title, 70 ), $len ), $len < 30 ? 'Make the title longer (50 to 60 characters) and include your main keyword.' : 'Shorten the title to about 60 characters so Google does not cut it off.' ) );
	$len   = mb_strlen( $description );
	$seo[] = ! $description
		? nwa_check( 'Meta description', 'fail', 3, 'No meta description found. Google will pick random text from the page.', 'Write a 140 to 160 character description that sells the page and ends with a reason to click.' )
		: ( $len >= 70 && $len <= 165
			? nwa_check( 'Meta description', 'pass', 3, sprintf( '%d characters, a good length.', $len ) )
			: nwa_check( 'Meta description', 'warn', 3, sprintf( 'The description is %d characters.', $len ), 'Aim for 140 to 160 characters with your main keyword and a clear benefit.' ) );
	$seo[] = 1 === $h1s->length
		? nwa_check( 'Main heading (H1)', 'pass', 3, sprintf( 'One H1: "%s"', nwa_cut( rtrim( trim( preg_replace( '/\s+/', ' ', $h1s->item( 0 )->textContent ) ), '.!? ' ), 70 ) ) )
		: ( 0 === $h1s->length
			? nwa_check( 'Main heading (H1)', 'fail', 3, 'No H1 heading found.', 'Add one clear H1 that says what you do and for whom. It is the strongest on page signal after the title.' )
			: nwa_check( 'Main heading (H1)', 'warn', 3, sprintf( '%d H1 headings found.', $h1s->length ), 'Use exactly one H1 per page and turn the others into H2 headings.' ) );
	$seo[] = $h2 >= 2
		? nwa_check( 'Heading structure', 'pass', 2, sprintf( '%d H2 and %d H3 headings organise the page.', $h2, $h3 ) )
		: nwa_check( 'Heading structure', 'warn', 2, sprintf( 'Only %d H2 headings.', $h2 ), 'Break the page into sections with H2 headings that include the words people search for.' );
	$seo[] = $img_total
		? ( $pct( $img_alt, $img_total ) >= 90
			? nwa_check( 'Image alt text', 'pass', 2, sprintf( '%d of %d images have alt text.', $img_alt, $img_total ) )
			: nwa_check( 'Image alt text', $pct( $img_alt, $img_total ) >= 50 ? 'warn' : 'fail', 2, sprintf( 'Only %d of %d images have alt text (%d%%).', $img_alt, $img_total, $pct( $img_alt, $img_total ) ), 'Describe every meaningful image in a few words. It helps Google Images and visitors using screen readers.' ) )
		: nwa_check( 'Image alt text', 'warn', 1, 'No images found on the page.', 'Add a few relevant images with descriptive alt text.' );
	$canonical = $first( "//link[@rel='canonical']", 'href' );
	$seo[]     = $canonical
		? nwa_check( 'Canonical URL', 'pass', 1, 'A canonical tag tells Google which address is the main one.' )
		: nwa_check( 'Canonical URL', 'warn', 1, 'No canonical tag.', 'Add a canonical tag (any SEO plugin does this) to avoid duplicate content issues.' );
	$robots_meta = strtolower( $meta( 'robots' ) );
	$seo[]       = false !== strpos( $robots_meta, 'noindex' )
		? nwa_check( 'Indexing', 'fail', 3, 'The page tells Google NOT to index it (noindex).', 'Remove the noindex tag, unless you really want this page hidden from Google. In WordPress check Settings, Reading.' )
		: nwa_check( 'Indexing', 'pass', 3, 'Search engines are allowed to index this page.' );
	$seo[] = $first( '//html', 'lang' )
		? nwa_check( 'Language tag', 'pass', 1, sprintf( 'Language set to "%s".', $first( '//html', 'lang' ) ) )
		: nwa_check( 'Language tag', 'warn', 1, 'No language set on the page.', 'Add lang="en" (or your language) to the html tag.' );
	$og    = ( $meta( 'og:title' ) ? 1 : 0 ) + ( $meta( 'og:image' ) ? 1 : 0 ) + ( $meta( 'og:description' ) ? 1 : 0 );
	$seo[] = 3 === $og
		? nwa_check( 'Social sharing preview', 'pass', 1, 'Open Graph title, description and image are set.' )
		: nwa_check( 'Social sharing preview', 0 === $og ? 'fail' : 'warn', 1, 0 === $og ? 'No Open Graph tags. Shared links look plain.' : 'Some Open Graph tags are missing.', 'Add Open Graph title, description and image so links shared on WhatsApp, Facebook and LinkedIn look professional.' );
	$schema = $q( "//script[@type='application/ld+json']" )->length || $q( '//*[@itemtype]' )->length;
	$seo[]  = $schema
		? nwa_check( 'Structured data', 'pass', 2, 'Schema markup found, which helps rich results on Google.' )
		: nwa_check( 'Structured data', 'warn', 2, 'No structured data found.', 'Add LocalBusiness or Organization schema so Google can show your details, reviews and opening hours.' );
	$seo[] = $has_robots
		? nwa_check( 'robots.txt', 'pass', 1, 'A robots.txt file is in place.' )
		: nwa_check( 'robots.txt', 'warn', 1, 'No robots.txt file found.', 'Add a robots.txt file that points search engines to your sitemap.' );
	$seo[] = $sitemap
		? nwa_check( 'XML sitemap', 'pass', 2, 'An XML sitemap is available for search engines.' )
		: nwa_check( 'XML sitemap', 'fail', 2, 'No XML sitemap found.', 'Create an XML sitemap (Yoast, Rank Math or WordPress core) and submit it in Google Search Console.' );
	$seo[] = $internal >= 10
		? nwa_check( 'Internal links', 'pass', 1, sprintf( '%d links to other pages on your site.', $internal ) )
		: nwa_check( 'Internal links', 'warn', 1, sprintf( 'Only %d internal links.', $internal ), 'Link to your key service pages from the homepage so visitors and Google find them.' );

	/* ------------------------------------------------------------ Design */
	$design   = array();
	$viewport = $meta( 'viewport' );
	$design[] = false !== stripos( $viewport, 'width=device-width' )
		? nwa_check( 'Mobile friendly', 'pass', 3, 'The page is set up for mobile screens.' )
		: nwa_check( 'Mobile friendly', 'fail', 3, 'No mobile viewport tag. The site may look tiny on phones.', 'Make the site responsive. Over 60% of visitors browse on a phone.' );
	$design[] = $cta >= 2
		? nwa_check( 'Clear call to action', 'pass', 3, sprintf( '%d buttons or links ask the visitor to take a step.', $cta ) )
		: nwa_check( 'Clear call to action', $cta ? 'warn' : 'fail', 3, $cta ? 'Only one call to action found.' : 'No clear call to action found.', 'Add a bold button like "Get a free quote" near the top of the page and repeat it further down.' );
	$design[] = $contact
		? nwa_check( 'Easy to contact', 'pass', 2, 'Phone, email or WhatsApp contact options are visible.' )
		: nwa_check( 'Easy to contact', 'fail', 2, 'No phone number, email or WhatsApp link found on the page.', 'Show a clickable phone number, email or WhatsApp button in the header or footer.' );
	$design[] = $q( "//link[contains(@rel,'icon')]" )->length
		? nwa_check( 'Favicon', 'pass', 1, 'A browser tab icon is set.' )
		: nwa_check( 'Favicon', 'warn', 1, 'No favicon found.', 'Add a favicon so your brand shows in browser tabs and bookmarks.' );
	$design[] = ! $img_total || $pct( $img_sized, $img_total ) >= 80
		? nwa_check( 'Stable layout', 'pass', 2, 'Images reserve their space, so the page does not jump while loading.' )
		: nwa_check( 'Stable layout', 'warn', 2, sprintf( '%d of %d images have no width and height.', $img_total - $img_sized, $img_total ), 'Give images a width and height so the layout does not jump while the page loads (Google calls this CLS).' );
	$design[] = count( $fonts ) <= 3
		? nwa_check( 'Consistent typography', 'pass', 1, count( $fonts ) ? sprintf( '%d web font families, a tidy choice.', count( $fonts ) ) : 'No external font overload.' )
		: nwa_check( 'Consistent typography', 'warn', 1, sprintf( '%d different web font families.', count( $fonts ) ), 'Stick to two font families. It looks more professional and loads faster.' );
	$design[] = $social
		? nwa_check( 'Social proof links', 'pass', 1, sprintf( '%d social media links.', $social ) )
		: nwa_check( 'Social proof links', 'warn', 1, 'No social media links found.', 'Link your active social profiles so visitors can see you are real and active.' );
	$inline   = $q( '//*[@style]' )->length;
	$design[] = $inline <= 60
		? nwa_check( 'Clean build', 'pass', 1, 'The page code is reasonably clean.' )
		: nwa_check( 'Clean build', 'warn', 1, sprintf( '%d inline styles found, a sign of a heavy page builder.', $inline ), 'A lighter theme or cleaner build makes the site faster and easier to keep consistent.' );

	/* ----------------------------------------------------------- Content */
	$content   = array();
	$content[] = $words >= 500
		? nwa_check( 'Amount of content', 'pass', 3, sprintf( 'About %s words of text.', number_format( $words ) ) )
		: nwa_check( 'Amount of content', $words >= 250 ? 'warn' : 'fail', 3, sprintf( 'Only about %s words of text.', number_format( $words ) ), 'Aim for at least 500 useful words: what you do, who it is for, how it works, prices or FAQs.' );
	$content[] = $flesch >= 50
		? nwa_check( 'Easy to read', 'pass', 2, sprintf( 'Readability score %d out of 100, clear and easy.', $flesch ) )
		: nwa_check( 'Easy to read', $flesch >= 30 ? 'warn' : 'fail', 2, sprintf( 'Readability score %d out of 100, quite hard to read.', $flesch ), 'Use shorter sentences and everyday words. Write like you talk to a customer.' );
	$content[] = ( $h2 + $h3 ) >= 4
		? nwa_check( 'Scannable sections', 'pass', 2, sprintf( '%d sub headings make the page easy to scan.', $h2 + $h3 ) )
		: nwa_check( 'Scannable sections', 'warn', 2, 'Few sub headings, so the page is harder to scan.', 'Most visitors scan. Add clear sub headings every few paragraphs.' );
	$content[] = 0 === $long_paragraphs
		? nwa_check( 'Short paragraphs', 'pass', 1, 'Paragraphs are a comfortable length.' )
		: nwa_check( 'Short paragraphs', 'warn', 1, sprintf( '%d very long paragraphs.', $long_paragraphs ), 'Split long paragraphs into 2 to 4 sentences. It is much easier to read on a phone.' );
	$content[] = $img_total >= 3
		? nwa_check( 'Visual content', 'pass', 2, sprintf( '%d images support the text.', $img_total ) )
		: nwa_check( 'Visual content', 'warn', 2, sprintf( 'Only %d images.', $img_total ), 'Add real photos of your work, team or products. They build trust far better than stock photos.' );
	$content[] = $trust
		? nwa_check( 'Trust signals', 'pass', 2, 'Reviews or testimonials are mentioned on the page.' )
		: nwa_check( 'Trust signals', 'fail', 2, 'No reviews, testimonials or ratings found.', 'Show 3 to 6 real customer reviews, ideally with names and photos or your Google rating.' );
	$content[] = $text_ratio >= 10
		? nwa_check( 'Text to code ratio', 'pass', 1, sprintf( '%s%% of the page is readable text.', $text_ratio ) )
		: nwa_check( 'Text to code ratio', 'warn', 1, sprintf( 'Only %s%% of the page is readable text.', $text_ratio ), 'Too much code for little text. A leaner build and more helpful content both help.' );
	$content[] = ! $copyright || $copyright >= $year - 1
		? nwa_check( 'Up to date', 'pass', 1, $copyright ? sprintf( 'Copyright year %d looks current.', $copyright ) : 'No outdated dates spotted.' )
		: nwa_check( 'Up to date', 'warn', 1, sprintf( 'The footer says %d.', $copyright ), 'Update the copyright year. An old year makes visitors wonder if the business is still active.' );

	/* ----------------------------------------------------- Speed & Security */
	$speed    = array();
	$speed[]  = $https
		? nwa_check( 'Secure connection (HTTPS)', 'pass', 3, 'The site loads over HTTPS.' )
		: nwa_check( 'Secure connection (HTTPS)', 'fail', 3, 'The site does not use HTTPS. Browsers show "Not secure".', 'Install a free SSL certificate and redirect all pages to https.' );
	$ttfb     = round( $page['time'], 2 );
	$speed[]  = $ttfb <= 1.0
		? nwa_check( 'Server response', 'pass', 3, sprintf( 'The page arrived in %.2f seconds.', $ttfb ) )
		: nwa_check( 'Server response', $ttfb <= 2.5 ? 'warn' : 'fail', 3, sprintf( 'The page took %.2f seconds to arrive.', $ttfb ), 'Use page caching and good hosting. Aim for under 1 second.' );
	$speed[]  = $size_kb <= 150
		? nwa_check( 'Page weight (HTML)', 'pass', 2, sprintf( 'HTML size %s KB.', $size_kb < 10 ? number_format( $size_kb, 1 ) : (int) round( $size_kb ) ) )
		: nwa_check( 'Page weight (HTML)', $size_kb <= 400 ? 'warn' : 'fail', 2, sprintf( 'HTML size %d KB, quite heavy.', $size_kb ), 'Remove unused sections, plugins and inline code to make the page lighter.' );
	$encoding = strtolower( $header( 'content-encoding' ) );
	$speed[]  = $encoding
		? nwa_check( 'Compression', 'pass', 2, sprintf( 'Files are compressed (%s).', $encoding ) )
		: nwa_check( 'Compression', 'warn', 2, 'No compression detected.', 'Turn on GZIP or Brotli compression on the server or with a caching plugin.' );
	$speed[]  = ( $scripts + $styles ) <= 25
		? nwa_check( 'Number of files', 'pass', 2, sprintf( '%d scripts and %d stylesheets.', $scripts, $styles ) )
		: nwa_check( 'Number of files', ( $scripts + $styles ) <= 45 ? 'warn' : 'fail', 2, sprintf( '%d scripts and %d stylesheets load on this page.', $scripts, $styles ), 'Remove plugins you do not need and combine or defer files.' );
	$speed[]  = $blocking <= 2
		? nwa_check( 'Render blocking scripts', 'pass', 2, 'Scripts do not hold up the first paint.' )
		: nwa_check( 'Render blocking scripts', 'warn', 2, sprintf( '%d scripts block the page from showing.', $blocking ), 'Add defer or async to scripts in the head so content appears sooner.' );
	$speed[]  = ! $img_total || $pct( $img_modern, $img_total ) >= 50
		? nwa_check( 'Modern image formats', 'pass', 2, $img_total ? sprintf( '%d of %d images use WebP or AVIF.', $img_modern, $img_total ) : 'No images to optimise.' )
		: nwa_check( 'Modern image formats', 'warn', 2, sprintf( 'Only %d of %d images use WebP or AVIF.', $img_modern, $img_total ), 'Convert images to WebP. They are usually 30% smaller with the same quality.' );
	$speed[]  = $img_total < 6 || $img_lazy >= 3
		? nwa_check( 'Lazy loading', 'pass', 1, 'Images below the fold wait until they are needed.' )
		: nwa_check( 'Lazy loading', 'warn', 1, 'Images are not lazy loaded.', 'Lazy load images further down the page so the first screen loads faster.' );
	$security = ( $header( 'strict-transport-security' ) ? 1 : 0 ) + ( $header( 'x-content-type-options' ) ? 1 : 0 ) + ( $header( 'x-frame-options' ) || $header( 'content-security-policy' ) ? 1 : 0 );
	$speed[]  = $security >= 2
		? nwa_check( 'Security headers', 'pass', 1, sprintf( '%d of 3 key security headers are set.', $security ) )
		: nwa_check( 'Security headers', 'warn', 1, sprintf( '%d of 3 key security headers are set.', $security ), 'Add HSTS, X-Content-Type-Options and X-Frame-Options headers (a security plugin or your host can do this).' );
	$speed[]  = ! $mixed
		? nwa_check( 'No mixed content', 'pass', 1, 'Everything loads securely.' )
		: nwa_check( 'No mixed content', 'fail', 1, sprintf( '%d files load over insecure http.', $mixed ), 'Update these links to https so browsers do not block them.' );
	$generator = $meta( 'generator' );
	if ( preg_match( '/WordPress\s+[\d.]+/i', $generator ) ) {
		$speed[] = nwa_check( 'Software version hidden', 'warn', 1, sprintf( 'The page shows "%s".', $generator ), 'Hide the WordPress version and keep WordPress, themes and plugins updated.' );
	}

	$groups = array(
		'design'  => $design,
		'seo'     => $seo,
		'content' => $content,
		'speed'   => $speed,
	);
	$total  = 0;
	$weight = 0;
	foreach ( nwa_categories() as $key => $cat ) {
		$score = nwa_score( $groups[ $key ] );
		$result['categories'][ $key ] = array(
			'score'  => $score,
			'checks' => $groups[ $key ],
		);
		$total  += $score * $cat['weight'];
		$weight += $cat['weight'];
	}
	$result['ok']        = true;
	$result['final_url'] = $final;
	$result['overall']   = (int) round( $total / max( 1, $weight ) );
	$result['grade']     = nwa_grade( $result['overall'] );
	$result['title']     = $title;
	$result['stats']     = array(
		'load_time' => $ttfb,
		'size_kb'   => round( $size_kb, 1 ),
		'words'     => $words,
		'images'    => $img_total,
		'links'     => $internal + $external,
		'files'     => $scripts + $styles,
		'https'     => $https,
	);
	return $result;
}

/**
 * Weighted score of a list of checks, 0 to 100.
 *
 * @param array $checks Checks.
 * @return int
 */
function nwa_score( $checks ) {
	$got = 0;
	$max = 0;
	foreach ( $checks as $check ) {
		$value = 'pass' === $check['status'] ? 1 : ( 'warn' === $check['status'] ? 0.5 : 0 );
		$got  += $value * $check['weight'];
		$max  += $check['weight'];
	}
	return $max ? (int) round( $got / $max * 100 ) : 0;
}

/**
 * Letter grade and verdict.
 *
 * @param int $score Score.
 * @return string
 */
function nwa_grade( $score ) {
	if ( $score >= 90 ) {
		return 'A';
	}
	if ( $score >= 75 ) {
		return 'B';
	}
	if ( $score >= 60 ) {
		return 'C';
	}
	if ( $score >= 45 ) {
		return 'D';
	}
	return 'E';
}

/**
 * One line verdict for a score.
 *
 * @param int $score Score.
 * @return string
 */
function nwa_verdict( $score ) {
	if ( $score >= 90 ) {
		return 'Excellent! Your website is in great shape. A few polishing touches will make it even stronger.';
	}
	if ( $score >= 75 ) {
		return 'Good foundation. Fixing the points below can bring noticeably more visitors and enquiries.';
	}
	if ( $score >= 60 ) {
		return 'Decent, but you are leaving visitors and sales on the table. The action plan shows where to start.';
	}
	if ( $score >= 45 ) {
		return 'Your website needs attention. Several issues are costing you rankings and customers.';
	}
	return 'Your website is holding your business back. The good news: most of these fixes are quick wins.';
}

/**
 * Colour state for a score: good, ok or bad.
 *
 * @param int $score Score.
 * @return string
 */
function nwa_state( $score ) {
	return $score >= 75 ? 'good' : ( $score >= 50 ? 'ok' : 'bad' );
}

/**
 * Most important problems first: fails before warnings, heavier weight first.
 *
 * @param array $result Audit result.
 * @param int   $limit  Max items.
 * @return array
 */
function nwa_priorities( $result, $limit = 6 ) {
	$items = array();
	foreach ( $result['categories'] as $key => $cat ) {
		foreach ( $cat['checks'] as $check ) {
			if ( 'pass' !== $check['status'] ) {
				$check['cat']  = $key;
				$check['rank'] = ( 'fail' === $check['status'] ? 10 : 0 ) + $check['weight'] * 2;
				$items[]       = $check;
			}
		}
	}
	usort(
		$items,
		function ( $a, $b ) {
			return $b['rank'] - $a['rank'];
		}
	);
	return array_slice( $items, 0, $limit );
}

/**
 * Strong points: passed checks with the highest weight.
 *
 * @param array $result Audit result.
 * @param int   $limit  Max items.
 * @return array
 */
function nwa_strengths( $result, $limit = 5 ) {
	$items = array();
	foreach ( $result['categories'] as $key => $cat ) {
		foreach ( $cat['checks'] as $check ) {
			if ( 'pass' === $check['status'] ) {
				$check['cat'] = $key;
				$items[]      = $check;
			}
		}
	}
	usort(
		$items,
		function ( $a, $b ) {
			return $b['weight'] - $a['weight'];
		}
	);
	return array_slice( $items, 0, $limit );
}

/**
 * Shorten text.
 *
 * @param string $text Text.
 * @param int    $max  Max characters.
 * @return string
 */
function nwa_cut( $text, $max ) {
	$text = trim( preg_replace( '/\s+/u', ' ', (string) $text ) );
	return mb_strlen( $text ) > $max ? rtrim( mb_substr( $text, 0, $max - 3 ) ) . '...' : $text;
}
