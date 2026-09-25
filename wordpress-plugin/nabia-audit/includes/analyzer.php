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
 * Seconds left for this audit (hosts often stop PHP after 30 seconds).
 *
 * @param int|null $reset Start a new budget of this many seconds.
 * @return float
 */
function nwa_time_left( $reset = null ) {
	static $deadline = 0;
	if ( null !== $reset ) {
		$deadline = microtime( true ) + $reset;
	}
	if ( ! $deadline ) {
		$deadline = microtime( true ) + 25;
	}
	return $deadline - microtime( true );
}

/**
 * Time budget for one audit, based on the PHP time limit.
 *
 * @return int Seconds.
 */
function nwa_time_budget() {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	$max = (int) ini_get( 'max_execution_time' );
	if ( $max <= 0 ) {
		return 90;
	}
	// Keep a few seconds for saving, the PDF and the emails.
	return max( 15, min( 90, $max - 8 ) );
}

/**
 * Is this host the website the plugin runs on (with or without www)?
 *
 * @param string $host Host.
 * @return bool
 */
function nwa_is_own_host( $host ) {
	$own = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	return $own && strtolower( preg_replace( '/^www\./', '', (string) $host ) ) === strtolower( $own );
}

/**
 * Fetch a URL like a normal browser would.
 *
 * Other websites go through wp_safe_remote_get (no private or local addresses). The
 * site's own domain may resolve to an internal address on many hosts, so it uses a
 * normal request. When the SSL certificate chain is incomplete it retries without
 * verification (we only read a public page).
 *
 * @param string $url     URL.
 * @param int    $timeout Seconds.
 * @param int    $limit   Max bytes.
 * @return array|WP_Error
 */
function nwa_fetch( $url, $timeout = 15, $limit = 3145728 ) {
	$timeout = max( 3, min( $timeout, (int) floor( nwa_time_left() ) - 2 ) );
	$own     = nwa_is_own_host( wp_parse_url( $url, PHP_URL_HOST ) );
	$args    = array(
		'timeout'             => $timeout,
		'redirection'         => 5,
		'limit_response_size' => $limit,
		'user-agent'          => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
		'headers'             => array(
			'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language' => 'en-US,en;q=0.9',
			'Accept-Encoding' => 'gzip, deflate',
			'Cache-Control'   => 'no-cache',
		),
	);
	$start    = microtime( true );
	$response = $own ? wp_remote_get( $url, $args ) : wp_safe_remote_get( $url, $args );
	if ( is_wp_error( $response ) && preg_match( '/ssl|certificate|curl error (35|51|58|60|77)/i', $response->get_error_message() ) && nwa_time_left() > 5 ) {
		$args['sslverify'] = false;
		$args['timeout']   = max( 3, min( $timeout, (int) floor( nwa_time_left() ) - 2 ) );
		$response          = $own ? wp_remote_get( $url, $args ) : wp_safe_remote_get( $url, $args );
	}
	$time = microtime( true ) - $start;
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
 * Does a response look like a real web page (not a firewall challenge)?
 *
 * @param array|WP_Error $page Response.
 * @return bool
 */
function nwa_is_real_page( $page ) {
	if ( ! is_array( $page ) || is_wp_error( $page ) || $page['code'] >= 400 || strlen( trim( $page['body'] ) ) < 200 ) {
		return false;
	}
	$head = strtolower( substr( $page['body'], 0, 20000 ) );
	if ( false === strpos( $head, '<html' ) && false === strpos( $head, '<body' ) && false === strpos( $head, '<head' ) ) {
		return false;
	}
	// Bot protection pages (Cloudflare, Sucuri, Imunify and similar).
	return ! preg_match( '/(just a moment\.\.\.|cf-browser-verification|cf_chl_|challenge-platform|attention required! \| cloudflare|sucuri website firewall|imunify360|ddos protection by|checking your browser)/', $head );
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
		'why'    => 'pass' === $status ? '' : nwa_impact( $label ),
		'fix'    => 'pass' === $status ? '' : $fix,
	);
}

/**
 * Run a full audit. Never fails: when the page can not be read directly it tries
 * other addresses, then Google PageSpeed, and finally builds a quick base report.
 *
 * @param string $url URL.
 * @return array { ok, url, final_url, fetched, mode, notes, stats, categories{ key => { score, checks } }, overall, grade }
 */
function nwa_run_audit( $url ) {
	nwa_time_left( nwa_time_budget() );
	$notes = array();
	$host  = (string) wp_parse_url( $url, PHP_URL_HOST );

	// 1. Direct scan and Google PageSpeed at the same time.
	$blocked  = false;
	$page     = null;
	$has_key  = (bool) trim( (string) nwa_opt( 'psi_key' ) );
	$parallel = nwa_parallel_scan( $url, $has_key );
	$psi      = null;
	if ( $parallel ) {
		$response = $parallel['page'];
		$psi      = $parallel['psi'];
		// A redirect (for example to https or www) is followed with the safe WordPress request.
		if ( is_array( $response ) && $response['code'] >= 300 && $response['code'] < 400 ) {
			$response = nwa_fetch( $url, 10 );
		}
	} else {
		$response = nwa_fetch( $url, 10 );
	}
	if ( nwa_is_real_page( $response ) ) {
		$page = $response;
	} elseif ( null !== $response ) {
		$notes[] = nwa_note( $url, $response );
		$blocked = ! is_wp_error( $response );
	}
	// cURL multi can not use SSL fallbacks: retry once with the WordPress request.
	if ( ! $page && is_wp_error( $response ) && nwa_time_left() > 12 ) {
		$retry = nwa_fetch( $url, 8 );
		if ( nwa_is_real_page( $retry ) ) {
			$page = $retry;
		}
	}

	// 2. Other versions of the address, only for connection problems (not firewalls) and only when quick.
	if ( ! $page && is_wp_error( $response ) && ( ! $psi || is_wp_error( $psi ) ) && nwa_time_left() > 25 ) {
		$path  = (string) wp_parse_url( $url, PHP_URL_PATH );
		$bare  = preg_replace( '/^www\./', '', $host );
		$tries = array_diff(
			array_unique(
				array(
					'https://' . $host . ( $path ? $path : '/' ),
					'https://' . ( $bare === $host ? 'www.' . $host : $bare ) . ( $path ? $path : '/' ),
					'http://' . $host . ( $path ? $path : '/' ),
				)
			),
			array( $url )
		);
		foreach ( $tries as $try ) {
			if ( nwa_time_left() < 35 ) {
				break;
			}
			$response = nwa_fetch( $try, 6 );
			if ( nwa_is_real_page( $response ) ) {
				$page = $response;
				break;
			}
			$notes[] = nwa_note( $try, $response );
		}
	}

	$psi_error = '';
	if ( $page ) {
		$result = nwa_analyze_page( $page, $url );
		$result['mode'] = 'full';
		// Add Google's real speed numbers when they arrived.
		if ( ! $psi && $has_key && nwa_time_left() > 30 ) {
			$psi = nwa_pagespeed( $url );
		}
		if ( $psi && ! is_wp_error( $psi ) ) {
			$result = nwa_merge_pagespeed( $result, $psi );
		} elseif ( is_wp_error( $psi ) && $has_key ) {
			$notes[] = 'PageSpeed (extra metrics): ' . $psi->get_error_message();
		}
	} else {
		// 3. Google PageSpeed Insights reads the site from Google's servers.
		if ( ! $psi || ( is_wp_error( $psi ) && nwa_time_left() > 25 ) ) {
			$psi = nwa_time_left() > 12 ? nwa_pagespeed( $url ) : new WP_Error( 'nwa_time', 'Not enough time left on this server for PageSpeed (PHP time limit).' );
		}
		if ( ! is_wp_error( $psi ) ) {
			$result = nwa_audit_from_pagespeed( $psi, $url );
		} else {
			$notes[]   = 'PageSpeed: ' . $psi->get_error_message();
			$psi_error = $psi->get_error_message();
			// 4. Quick base report, never an error.
			$result = nwa_base_report( $url, $blocked, nwa_google_blocked( $psi_error ) );
		}
	}

	// Domain, SSL and email checks work even when a firewall blocks the page.
	if ( nwa_time_left() > 4 ) {
		$result = nwa_add_checks( $result, nwa_domain_checks( $result['final_url'] ? $result['final_url'] : $url ) );
	}
	$result['notes'] = $notes;
	if ( $psi_error ) {
		$result['psi_error'] = $psi_error;
	}
	return $result;
}

/**
 * Short note for the admin about a failed attempt.
 *
 * @param string         $url      URL.
 * @param array|WP_Error $response Response.
 * @return string
 */
function nwa_note( $url, $response ) {
	if ( is_wp_error( $response ) ) {
		return $url . ': ' . $response->get_error_message();
	}
	return $url . ': HTTP ' . $response['code'] . ( $response['code'] < 400 ? ' (firewall challenge or empty page)' : ' (blocked)' );
}

/**
 * Append checks to a result and recalculate all scores.
 *
 * @param array $result Result.
 * @param array $extra  cat => checks.
 * @return array
 */
function nwa_add_checks( $result, $extra ) {
	$groups = array();
	foreach ( nwa_categories() as $key => $cat ) {
		$groups[ $key ] = isset( $result['categories'][ $key ]['checks'] ) ? $result['categories'][ $key ]['checks'] : array();
		if ( ! empty( $extra[ $key ] ) ) {
			$labels = wp_list_pluck( $groups[ $key ], 'label' );
			foreach ( $extra[ $key ] as $check ) {
				if ( ! in_array( $check['label'], $labels, true ) ) {
					$groups[ $key ][] = $check;
				}
			}
		}
	}
	return nwa_finish( $result, $groups );
}

/**
 * Add Google's measured speed metrics to a full scan.
 *
 * @param array $result Result.
 * @param array $lh     Lighthouse result.
 * @return array
 */
function nwa_merge_pagespeed( $result, $lh ) {
	$psi   = nwa_audit_from_pagespeed( $lh, $result['url'] );
	$want  = array(
		'speed'  => array( 'Main content load time (LCP)', 'First paint (FCP)', 'Responsiveness (TBT)', 'Google speed score (mobile)' ),
		'design' => array( 'Stable layout (CLS)', 'Readable colours', 'Easy to tap', 'Google accessibility score' ),
		'seo'    => array( 'Google SEO score' ),
	);
	$extra = array();
	foreach ( $want as $cat => $labels ) {
		foreach ( $psi['categories'][ $cat ]['checks'] as $check ) {
			if ( in_array( $check['label'], $labels, true ) ) {
				$extra[ $cat ][] = $check;
			}
		}
	}
	if ( ! empty( $psi['stats']['lcp'] ) ) {
		$result['stats']['lcp'] = $psi['stats']['lcp'];
	}
	$result['google'] = true;
	return nwa_add_checks( $result, $extra );
}

/**
 * Checks that do not need the page itself: SSL certificate, https redirect and email setup.
 *
 * @param string $url URL.
 * @return array cat => checks.
 */
function nwa_domain_checks( $url ) {
	$host   = (string) wp_parse_url( $url, PHP_URL_HOST );
	$domain = preg_replace( '/^www\./', '', $host );
	$out    = array( 'speed' => array() );
	if ( ! $host || filter_var( $host, FILTER_VALIDATE_IP ) ) {
		return $out;
	}
	$public = nwa_is_own_host( $host ) || wp_http_validate_url( 'https://' . $host . '/' );

	// SSL certificate: valid and not close to expiring.
	if ( $public && function_exists( 'stream_socket_client' ) && function_exists( 'openssl_x509_parse' ) && nwa_time_left() > 6 ) {
		$ctx  = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => true,
					'verify_peer_name'  => true,
					'SNI_enabled'       => true,
					'peer_name'         => $host,
				),
			)
		);
		$conn = @stream_socket_client( 'ssl://' . $host . ':443', $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( $conn ) {
			$params = stream_context_get_params( $conn );
			$cert   = isset( $params['options']['ssl']['peer_certificate'] ) ? openssl_x509_parse( $params['options']['ssl']['peer_certificate'] ) : false;
			fclose( $conn ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( $cert && ! empty( $cert['validTo_time_t'] ) ) {
				$days            = (int) floor( ( $cert['validTo_time_t'] - time() ) / DAY_IN_SECONDS );
				$issuer          = isset( $cert['issuer']['O'] ) ? $cert['issuer']['O'] : ( isset( $cert['issuer']['CN'] ) ? $cert['issuer']['CN'] : '' );
				$out['speed'][]  = $days > 14
					? nwa_check( 'SSL certificate', 'pass', 2, sprintf( 'Valid certificate%s, renews in %d days (%s).', $issuer ? ' from ' . $issuer : '', $days, gmdate( 'j M Y', $cert['validTo_time_t'] ) ) )
					: nwa_check( 'SSL certificate', $days >= 0 ? 'warn' : 'fail', 2, $days >= 0 ? sprintf( 'The certificate expires in %d days.', $days ) : 'The SSL certificate has expired.', 'Renew the SSL certificate (or turn on auto renewal with your host) before browsers start showing warnings.' );
			}
		} else {
			$out['speed'][] = nwa_check( 'SSL certificate', 'fail', 2, 'No valid SSL certificate was found for ' . $host . '.', 'Install a free SSL certificate (Let\'s Encrypt) with your host so the site loads securely.' );
		}
	}

	// http should redirect to https.
	if ( $public && nwa_time_left() > 6 ) {
		$args = array(
			'timeout'     => 5,
			'redirection' => 0,
			'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
		);
		$http = nwa_is_own_host( $host ) ? wp_remote_get( 'http://' . $host . '/', $args ) : wp_safe_remote_get( 'http://' . $host . '/', $args );
		if ( ! is_wp_error( $http ) && (int) wp_remote_retrieve_response_code( $http ) < 400 ) {
			$code     = (int) wp_remote_retrieve_response_code( $http );
			$location = (string) wp_remote_retrieve_header( $http, 'location' );
			$out['speed'][] = in_array( $code, array( 301, 302, 307, 308 ), true ) && 0 === stripos( $location, 'https://' )
				? nwa_check( 'Redirects to HTTPS', 'pass', 1, 'Visitors who type http:// are sent to the secure version.' )
				: nwa_check( 'Redirects to HTTPS', 'warn', 1, 'The http:// version does not redirect to https://.', 'Add a 301 redirect from http to https (your host, Cloudflare or a plugin like Really Simple SSL can do this).' );
		}
	}

	// Professional email and protection against spoofing.
	if ( function_exists( 'dns_get_record' ) && nwa_time_left() > 3 ) {
		$mx    = @dns_get_record( $domain, DNS_MX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$txt   = @dns_get_record( $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$dmarc = @dns_get_record( '_dmarc.' . $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$spf   = false;
		foreach ( (array) $txt as $record ) {
			if ( isset( $record['txt'] ) && 0 === stripos( $record['txt'], 'v=spf1' ) ) {
				$spf = true;
			}
		}
		$has_dmarc = false;
		foreach ( (array) $dmarc as $record ) {
			if ( isset( $record['txt'] ) && 0 === stripos( $record['txt'], 'v=DMARC1' ) ) {
				$has_dmarc = true;
			}
		}
		$a_rec = @dns_get_record( $domain, DNS_A ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( is_array( $mx ) && ( $mx || ! empty( $a_rec ) ) ) {
			$out['speed'][] = $mx
				? nwa_check( 'Business email', 'pass', 1, sprintf( 'Email is set up for @%s, which looks professional and builds trust.', $domain ) )
				: nwa_check( 'Business email', 'warn', 1, sprintf( 'No email (MX) records found for @%s.', $domain ), sprintf( 'If you do not have one yet, use an address like hello@%s instead of Gmail or Yahoo. It looks far more professional. If you do, check the MX records in your DNS.', $domain ) );
		}
		if ( $mx ) {
			$out['speed'][] = $spf
				? nwa_check( 'Email protection (SPF)', 'pass', 1, 'An SPF record tells inboxes which servers may send your email.' )
				: nwa_check( 'Email protection (SPF)', 'warn', 1, 'No SPF record found, so your emails are more likely to land in spam.', 'Add an SPF record in your DNS (your email provider gives you the exact text).' );
			$out['speed'][] = $has_dmarc
				? nwa_check( 'Anti spoofing (DMARC)', 'pass', 1, 'A DMARC policy protects your domain from fake emails.' )
				: nwa_check( 'Anti spoofing (DMARC)', 'warn', 1, 'No DMARC record, so scammers can send email pretending to be you.', 'Add a DMARC record, for example v=DMARC1; p=none; rua=mailto:you@yourdomain, then tighten it later.' );
		}
	}
	return $out;
}

/**
 * Analyse a fetched HTML page.
 *
 * @param array  $page Response from nwa_fetch().
 * @param string $url  Requested URL.
 * @return array
 */
function nwa_analyze_page( $page, $url ) {
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
	$robots_txt = nwa_time_left() > 6 ? nwa_fetch( $origin . '/robots.txt', 6, 204800 ) : new WP_Error( 'nwa_time', 'skipped' );
	$has_robots = ! is_wp_error( $robots_txt ) && 200 === $robots_txt['code'] && false !== stripos( $robots_txt['body'], 'user-agent' );
	$sitemap    = $has_robots && preg_match( '/^\s*sitemap:/im', $robots_txt['body'] );
	if ( ! $sitemap ) {
		foreach ( array( '/sitemap_index.xml', '/sitemap.xml', '/wp-sitemap.xml' ) as $path ) {
			if ( nwa_time_left() < 6 ) {
				break;
			}
			$map = nwa_fetch( $origin . $path, 6, 204800 );
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
	$seo[] = $has_robots && nwa_robots_blocks_all( $robots_txt['body'] )
		? nwa_check( 'robots.txt', 'fail', 3, 'robots.txt tells every search engine to stay out of the whole site (Disallow: /).', 'Remove "Disallow: /" from robots.txt. In WordPress also untick "Discourage search engines" under Settings, Reading.' )
		: ( $has_robots
			? nwa_check( 'robots.txt', 'pass', 1, 'A robots.txt file is in place.' )
			: nwa_check( 'robots.txt', 'warn', 1, 'No robots.txt file found.', 'Add a robots.txt file that points search engines to your sitemap.' ) );
	$analytics = preg_match( '/googletagmanager\.com|google-analytics\.com|gtag\(|fbq\(|connect\.facebook\.net|clarity\.ms|hotjar|plausible\.io|matomo|posthog|analytics\.tiktok|site-kit|usefathom|umami|snap\.licdn/i', $html );
	$seo[]     = $analytics
		? nwa_check( 'Visitor tracking (analytics)', 'pass', 2, 'Analytics or ad tracking is installed, so visits and leads can be measured.' )
		: nwa_check( 'Visitor tracking (analytics)', 'fail', 2, 'No analytics tool found (Google Analytics, Tag Manager, Meta Pixel, Clarity or Hotjar).', 'Install Google Analytics 4 (for example with Site Kit) and track form submissions as conversions.' );
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
	$has_form = $q( "//form[.//input[@type='email'] or .//textarea]" )->length
		|| preg_match( '/wpcf7|wpforms|gform_wrapper|elementor-form|hs-form|hbspt\.forms|typeform|calendly|fluentform|ninja-forms|jotform|tally\.so|nabia_website_audit|cf-form/i', $html );
	$design[] = $has_form
		? nwa_check( 'Lead capture form', 'pass', 3, 'Visitors can send an enquiry or book straight from this page.' )
		: nwa_check( 'Lead capture form', 'fail', 3, 'No contact form, quote form or booking widget on this page.', 'Add a short form (name, email, one question) or a booking widget near the top and again at the bottom.' );
	$design[] = $q( "//a[starts-with(@href,'tel:')]" )->length
		? nwa_check( 'Tap to call', 'pass', 2, 'Mobile visitors can call with one tap.' )
		: nwa_check( 'Tap to call', 'warn', 2, 'No clickable phone number (tel: link).', 'Add your phone number as a tel: link in the header and contact section.' );
	$design[] = preg_match( '/wa\.me\/|api\.whatsapp\.com|whatsapp:\/\/|tawk\.to|crisp\.chat|intercom|drift\.com|livechatinc|tidio|zendesk|freshchat|chatra|smartsupp|m\.me\//i', $html )
		? nwa_check( 'WhatsApp or live chat', 'pass', 1, 'Visitors can message instantly via WhatsApp or live chat.' )
		: nwa_check( 'WhatsApp or live chat', 'warn', 1, 'No WhatsApp button or live chat found.', 'Add a WhatsApp click to chat button or a free live chat like Tawk.to.' );
	if ( preg_match( '/user-scalable\s*=\s*(no|0)|maximum-scale\s*=\s*1(\.0)?\b/i', $viewport ) ) {
		$design[] = nwa_check( 'Pinch to zoom', 'warn', 1, 'Zooming is switched off on phones.', 'Remove user-scalable=no and maximum-scale=1 from the viewport tag.' );
	}
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
	$content[] = $q( "//a[contains(translate(@href,'PRIVACY','privacy'),'privacy') or contains(translate(.,'PRIVACY','privacy'),'privacy')]" )->length
		? nwa_check( 'Privacy policy', 'pass', 1, 'A privacy policy is linked, which builds trust when people share details.' )
		: nwa_check( 'Privacy policy', 'warn', 1, 'No privacy policy link found.', 'Publish a privacy policy and link it in the footer and next to your forms.' );
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
	$result['tech']      = nwa_detect_tech( $html, $page['headers'] );
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

/**
 * Turn groups of checks into category scores, an overall score and a grade.
 *
 * @param array $result Result (url, final_url, stats set).
 * @param array $groups design|seo|content|speed => checks.
 * @return array
 */
function nwa_finish( $result, $groups ) {
	$total  = 0;
	$weight = 0;
	foreach ( nwa_categories() as $key => $cat ) {
		$checks = ! empty( $groups[ $key ] ) ? $groups[ $key ] : array( nwa_check( $cat['short'] . ' review', 'warn', 1, 'This part needs a manual check.', 'I will review this part by hand and send you my notes.' ) );
		$score  = nwa_score( $checks );
		$result['categories'][ $key ] = array(
			'score'  => $score,
			'checks' => $checks,
		);
		$total  += $score * $cat['weight'];
		$weight += $cat['weight'];
	}
	$result['ok']      = true;
	$result['error']   = '';
	$result['overall'] = (int) round( $total / max( 1, $weight ) );
	$result['grade']   = nwa_grade( $result['overall'] );
	return $result;
}

/**
 * Ask Google PageSpeed Insights (Lighthouse) about a URL.
 *
 * @param string $url URL.
 * @return array|WP_Error Lighthouse result.
 */
function nwa_pagespeed( $url ) {
	$response = wp_safe_remote_get(
		nwa_psi_api( $url ),
		array(
			'timeout'             => max( 10, min( 70, (int) floor( nwa_time_left() ) - 4 ) ),
			'limit_response_size' => 20971520,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	return nwa_psi_parse( (int) wp_remote_retrieve_response_code( $response ), (string) wp_remote_retrieve_body( $response ) );
}

/**
 * PageSpeed Insights API address for a URL.
 *
 * @param string $url URL.
 * @return string
 */
function nwa_psi_api( $url ) {
	$api = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . rawurlencode( $url ) . '&strategy=mobile&category=performance&category=seo&category=accessibility&category=best-practices';
	$key = trim( (string) nwa_opt( 'psi_key' ) );
	return $key ? $api . '&key=' . rawurlencode( $key ) : $api;
}

/**
 * Read a PageSpeed API response.
 *
 * @param int    $code HTTP status.
 * @param string $body Body.
 * @return array|WP_Error Lighthouse result.
 */
function nwa_psi_parse( $code, $body ) {
	$data = json_decode( $body, true );
	if ( 200 !== $code || empty( $data['lighthouseResult']['audits'] ) ) {
		$message = isset( $data['error']['message'] ) ? $data['error']['message'] : ( $code ? 'HTTP ' . $code : 'No answer from Google in time' );
		return new WP_Error( 'nwa_psi', nwa_cut( $message, 200 ) );
	}
	return $data['lighthouseResult'];
}

/**
 * Fetch the page and ask Google PageSpeed at the same time (curl multi), so Google
 * gets the whole time budget even on hosts that stop PHP after 30 seconds.
 *
 * @param string $url       URL.
 * @param bool   $want_psi  Keep waiting for Google after the page arrived.
 * @return array|null { page: array|WP_Error|null, psi: array|WP_Error|null }, null when curl multi is missing.
 */
function nwa_parallel_scan( $url, $want_psi ) {
	if ( ! function_exists( 'curl_multi_init' ) ) {
		return null;
	}
	$host   = (string) wp_parse_url( $url, PHP_URL_HOST );
	$own    = nwa_is_own_host( $host );
	$ca     = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
	$mh     = curl_multi_init();
	$jobs   = array();
	$limit  = 3145728;
	$budget = max( 8, (int) floor( nwa_time_left() ) - 3 );

	// The page itself (redirects are followed later with the safe WordPress request).
	if ( $own || wp_http_validate_url( $url ) ) {
		$buffer  = '';
		$headers = array();
		$ch      = curl_init( $url );
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_RETURNTRANSFER => false,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_CONNECTTIMEOUT => 8,
				CURLOPT_TIMEOUT        => min( 15, $budget ),
				CURLOPT_ENCODING       => '',
				CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
				CURLOPT_HTTPHEADER     => array( 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'Accept-Language: en-US,en;q=0.9', 'Cache-Control: no-cache' ),
				CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
				CURLOPT_CAINFO         => $ca,
				CURLOPT_HEADERFUNCTION => function ( $h, $line ) use ( &$headers ) {
					$parts = explode( ':', $line, 2 );
					if ( 2 === count( $parts ) ) {
						$headers[ strtolower( trim( $parts[0] ) ) ] = trim( $parts[1] );
					}
					return strlen( $line );
				},
				CURLOPT_WRITEFUNCTION  => function ( $h, $data ) use ( &$buffer, $limit ) {
					$buffer .= $data;
					return strlen( $buffer ) > $limit ? 0 : strlen( $data );
				},
			)
		);
		curl_multi_add_handle( $mh, $ch );
		$jobs['page'] = array(
			'h'       => $ch,
			'buffer'  => &$buffer,
			'headers' => &$headers,
		);
	}

	// Google PageSpeed.
	$psi_body = '';
	$ph       = curl_init( nwa_psi_api( $url ) );
	curl_setopt_array(
		$ph,
		array(
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT        => min( 80, $budget ),
			CURLOPT_ENCODING       => '',
			CURLOPT_CAINFO         => $ca,
			CURLOPT_WRITEFUNCTION  => function ( $h, $data ) use ( &$psi_body ) {
				$psi_body .= $data;
				return strlen( $data );
			},
		)
	);
	curl_multi_add_handle( $mh, $ph );
	$jobs['psi'] = array( 'h' => $ph );

	$done    = array();
	$running = 0;
	do {
		$status = curl_multi_exec( $mh, $running );
		if ( $running ) {
			curl_multi_select( $mh, 0.5 );
		}
		while ( $info = curl_multi_info_read( $mh ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition, Generic.CodeAnalysis.AssignmentInCondition
			foreach ( $jobs as $name => $job ) {
				if ( $job['h'] === $info['handle'] ) {
					$done[ $name ] = $info['result'];
				}
			}
		}
		// Page is in and good: stop unless we want Google's extra numbers and have time.
		if ( isset( $done['page'] ) && ! isset( $done['psi'] ) && ( ! $want_psi || nwa_time_left() < 8 ) ) {
			$code = (int) curl_getinfo( $jobs['page']['h'], CURLINFO_RESPONSE_CODE );
			if ( 200 === $code && nwa_is_real_page( array( 'code' => 200, 'body' => $jobs['page']['buffer'] ) ) ) {
				break;
			}
		}
		if ( nwa_time_left() < 2 ) {
			break;
		}
	} while ( $running && CURLM_OK === $status );

	$out = array(
		'page' => null,
		'psi'  => null,
	);
	if ( isset( $jobs['page'] ) ) {
		$ch = $jobs['page']['h'];
		if ( isset( $done['page'] ) && CURLE_OK === $done['page'] ) {
			$out['page'] = array(
				'code'    => (int) curl_getinfo( $ch, CURLINFO_RESPONSE_CODE ),
				'body'    => $jobs['page']['buffer'],
				'headers' => $jobs['page']['headers'],
				'time'    => (float) curl_getinfo( $ch, CURLINFO_TOTAL_TIME ),
				'url'     => (string) curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL ),
			);
		} else {
			$out['page'] = new WP_Error( 'nwa_page', isset( $done['page'] ) ? curl_error( $ch ) . ' (curl ' . $done['page'] . ')' : 'No answer in time' );
		}
		curl_multi_remove_handle( $mh, $ch );
		curl_close( $ch );
	}
	if ( isset( $done['psi'] ) && CURLE_OK === $done['psi'] ) {
		$out['psi'] = nwa_psi_parse( (int) curl_getinfo( $ph, CURLINFO_RESPONSE_CODE ), $psi_body );
	} else {
		$out['psi'] = new WP_Error( 'nwa_psi', isset( $done['psi'] ) ? 'Connection to Google failed: ' . curl_error( $ph ) : 'Google did not finish within the time this server allows' );
	}
	curl_multi_remove_handle( $mh, $ph );
	curl_close( $ph );
	curl_multi_close( $mh );
	return $out;
}

/**
 * Build our report from a Lighthouse result.
 *
 * @param array  $lh  Lighthouse result.
 * @param string $url URL.
 * @return array
 */
function nwa_audit_from_pagespeed( $lh, $url ) {
	$audits = $lh['audits'];
	$map    = array(
		'is-on-https'               => array( 'speed', 'Secure connection (HTTPS)', 3, 'Install a free SSL certificate and redirect all pages to https.' ),
		'largest-contentful-paint'  => array( 'speed', 'Main content load time (LCP)', 3, 'Compress and resize your hero image, use page caching and good hosting so the main content shows within 2.5 seconds.' ),
		'first-contentful-paint'    => array( 'speed', 'First paint (FCP)', 2, 'Reduce render blocking CSS and JavaScript and turn on caching so something appears quickly.' ),
		'total-blocking-time'       => array( 'speed', 'Responsiveness (TBT)', 2, 'Remove heavy plugins and scripts, and delay third party scripts like chat widgets and trackers.' ),
		'speed-index'               => array( 'speed', 'Speed Index', 2, 'Optimise images and load only what is needed for the first screen.' ),
		'server-response-time'      => array( 'speed', 'Server response', 2, 'Use page caching and good hosting. Aim for under 0.6 seconds.' ),
		'render-blocking-resources' => array( 'speed', 'Render blocking files', 2, 'Defer non critical CSS and JavaScript so content appears sooner.' ),
		'uses-text-compression'     => array( 'speed', 'Compression', 1, 'Turn on GZIP or Brotli compression on the server or with a caching plugin.' ),
		'modern-image-formats'      => array( 'speed', 'Modern image formats', 2, 'Convert images to WebP. They are usually 30% smaller with the same quality.' ),
		'uses-optimized-images'     => array( 'speed', 'Optimised images', 1, 'Compress images before uploading them.' ),
		'offscreen-images'          => array( 'speed', 'Lazy loading', 1, 'Lazy load images further down the page.' ),
		'unused-javascript'         => array( 'speed', 'Unused JavaScript', 1, 'Remove plugins and scripts you do not need on this page.' ),
		'viewport'                  => array( 'design', 'Mobile friendly', 3, 'Make the site responsive. Over 60% of visitors browse on a phone.' ),
		'cumulative-layout-shift'   => array( 'design', 'Stable layout (CLS)', 2, 'Give images, ads and embeds a fixed size so the page does not jump while loading.' ),
		'color-contrast'            => array( 'design', 'Readable colours', 2, 'Increase the contrast between text and background so everyone can read it.' ),
		'target-size'               => array( 'design', 'Easy to tap', 2, 'Make buttons and links at least 48px tall with space between them.' ),
		'tap-targets'               => array( 'design', 'Easy to tap', 2, 'Make buttons and links at least 48px tall with space between them.' ),
		'button-name'               => array( 'design', 'Labelled buttons', 1, 'Give every button a clear text label.' ),
		'errors-in-console'         => array( 'design', 'No browser errors', 1, 'Fix the JavaScript errors so every feature works for visitors.' ),
		'document-title'            => array( 'seo', 'Page title', 3, 'Add a unique title of 50 to 60 characters with your main service and location.' ),
		'meta-description'          => array( 'seo', 'Meta description', 3, 'Write a 140 to 160 character description that sells the page and ends with a reason to click.' ),
		'is-crawlable'              => array( 'seo', 'Indexing', 3, 'Remove the noindex tag or robots block so Google can list this page.' ),
		'http-status-code'          => array( 'seo', 'Page status', 2, 'Make sure the homepage returns a normal 200 status.' ),
		'robots-txt'                => array( 'seo', 'robots.txt', 1, 'Fix the robots.txt file so it is valid and points to your sitemap.' ),
		'canonical'                 => array( 'seo', 'Canonical URL', 1, 'Add a valid canonical tag (any SEO plugin does this).' ),
		'hreflang'                  => array( 'seo', 'Language versions', 1, 'Fix the hreflang tags for your language versions.' ),
		'crawlable-anchors'         => array( 'seo', 'Crawlable links', 1, 'Use normal links with an href so Google can follow them.' ),
		'image-alt'                 => array( 'seo', 'Image alt text', 2, 'Describe every meaningful image in a few words.' ),
		'link-text'                 => array( 'content', 'Descriptive links', 2, 'Replace "click here" and "read more" with words that say where the link goes.' ),
		'heading-order'             => array( 'content', 'Heading order', 2, 'Use headings in order (H1, then H2, then H3) so the page is easy to scan.' ),
		'html-has-lang'             => array( 'content', 'Language set', 1, 'Add lang="en" (or your language) to the html tag.' ),
		'font-size'                 => array( 'content', 'Readable text size', 2, 'Use at least 16px body text on phones.' ),
		'dom-size'                  => array( 'content', 'Page structure', 1, 'Simplify the page: fewer nested sections and page builder wrappers.' ),
	);
	$groups = array(
		'design'  => array(),
		'seo'     => array(),
		'content' => array(),
		'speed'   => array(),
	);
	$clean  = function ( $text ) {
		$text = preg_replace( '/\s*\[Learn[^\]]*\]\([^)]*\)\.?/i', '', (string) $text );
		$text = preg_replace( '/\[([^\]]+)\]\([^)]*\)/', '$1', $text );
		return rtrim( trim( str_replace( '`', '', $text ) ), '. ' );
	};
	$seen   = array();
	foreach ( $map as $id => $info ) {
		if ( empty( $audits[ $id ] ) || isset( $seen[ $info[1] ] ) ) {
			continue;
		}
		$a    = $audits[ $id ];
		$mode = isset( $a['scoreDisplayMode'] ) ? $a['scoreDisplayMode'] : '';
		if ( ! isset( $a['score'] ) || null === $a['score'] || in_array( $mode, array( 'notApplicable', 'informative', 'manual', 'error' ), true ) ) {
			continue;
		}
		$seen[ $info[1] ] = true;
		$status           = $a['score'] >= 0.9 ? 'pass' : ( $a['score'] >= 0.5 ? 'warn' : 'fail' );
		$found            = $clean( $a['title'] ) . ( ! empty( $a['displayValue'] ) ? ': ' . $clean( $a['displayValue'] ) : '' ) . '.';
		$groups[ $info[0] ][] = nwa_check( $info[1], $status, $info[2], $found, $info[3] );
	}
	// Google's own category scores as extra checks.
	$cats  = isset( $lh['categories'] ) ? $lh['categories'] : array();
	$extra = array(
		'performance'    => array( 'speed', 'Google speed score (mobile)', 3, 'Work through the speed fixes above. Images, caching and fewer scripts make the biggest difference.' ),
		'accessibility'  => array( 'design', 'Google accessibility score', 2, 'Improve contrast, labels and tap targets so everyone can use the site.' ),
		'seo'            => array( 'seo', 'Google SEO score', 2, 'Fix the SEO items above to reach 90 or more.' ),
		'best-practices' => array( 'content', 'Google best practices score', 1, 'Fix browser errors, outdated libraries and insecure requests.' ),
	);
	foreach ( $extra as $id => $info ) {
		if ( isset( $cats[ $id ]['score'] ) && null !== $cats[ $id ]['score'] ) {
			$score                = (int) round( $cats[ $id ]['score'] * 100 );
			$status               = $score >= 90 ? 'pass' : ( $score >= 50 ? 'warn' : 'fail' );
			$groups[ $info[0] ][] = nwa_check( $info[1], $status, $info[2], sprintf( 'Google scores this %d out of 100.', $score ), $info[3] );
		}
	}

	$num    = function ( $id ) use ( $audits ) {
		return isset( $audits[ $id ]['numericValue'] ) ? (float) $audits[ $id ]['numericValue'] : null;
	};
	$final  = ! empty( $lh['finalDisplayedUrl'] ) ? $lh['finalDisplayedUrl'] : ( ! empty( $lh['finalUrl'] ) ? $lh['finalUrl'] : $url );
	$result = array(
		'url'       => $url,
		'final_url' => $final,
		'fetched'   => time(),
		'mode'      => 'pagespeed',
		'title'     => '',
		'stats'     => array(
			'load_time' => null !== $num( 'server-response-time' ) ? round( $num( 'server-response-time' ) / 1000, 2 ) : null,
			'size_kb'   => null !== $num( 'total-byte-weight' ) ? round( $num( 'total-byte-weight' ) / 1024 ) : null,
			'words'     => null,
			'images'    => null,
			'links'     => null,
			'files'     => isset( $audits['network-requests']['details']['items'] ) ? count( $audits['network-requests']['details']['items'] ) : null,
			'https'     => 0 === stripos( $final, 'https://' ),
			'lcp'       => null !== $num( 'largest-contentful-paint' ) ? round( $num( 'largest-contentful-paint' ) / 1000, 1 ) : null,
		),
	);
	return nwa_finish( $result, $groups );
}

/**
 * Quick base report when the website can not be scanned at all. Never an error.
 *
 * @param string $url     URL.
 * @param bool   $blocked Did a firewall answer?
 * @return array
 */
function nwa_base_report( $url, $blocked, $google = '' ) {
	$host   = (string) wp_parse_url( $url, PHP_URL_HOST );
	$dns    = filter_var( $host, FILTER_VALIDATE_IP ) || gethostbyname( $host ) !== $host;
	$groups = array(
		'design'  => array(
			nwa_check( 'Design and mobile review', 'warn', 2, 'Needs a manual check, our scanner could not open the page.', 'I will review your design, mobile layout and calls to action by hand and send you my notes.' ),
		),
		'seo'     => array(
			nwa_check( 'SEO review', 'warn', 2, 'Needs a manual check, our scanner could not open the page.', 'I will check your titles, descriptions, headings and Google indexing by hand.' ),
		),
		'content' => array(
			nwa_check( 'Content review', 'warn', 2, 'Needs a manual check, our scanner could not open the page.', 'I will review your text, trust signals and readability by hand.' ),
		),
		'speed'   => array(
			nwa_check( 'Domain', $dns ? 'pass' : 'fail', 2, $dns ? sprintf( '%s is online and resolves correctly.', $host ) : sprintf( '%s does not resolve. Check the spelling or your DNS settings.', $host ), 'Check the domain name and DNS settings with your domain provider.' ),
			$google
				? nwa_check( 'Google can load your homepage', 'fail', 3, 'Google\'s own test tool (PageSpeed Insights) could not load your homepage either: ' . $google . '. If Google can not see your page, it can not rank it well.', 'Check your firewall, Cloudflare bot settings or security plugin and make sure Google is allowed in. Then test the page at pagespeed.web.dev and in Google Search Console.' )
				: nwa_check(
					'Open to search engines and scanners',
					'warn',
					3,
					$blocked ? 'A firewall or security plugin blocked our automated scanner.' : 'The website did not answer our scanner in time.',
					$blocked ? 'Make sure Cloudflare, Wordfence or your host firewall does not block Google and other good bots, or you may lose rankings too.' : 'A slow server can also slow down Google. Check your hosting and caching.'
				),
		),
	);
	// Small files are often allowed even when the homepage is behind a firewall.
	$origin = ( 0 === stripos( $url, 'http://' ) ? 'http://' : 'https://' ) . $host;
	$get    = function ( $path ) use ( $origin ) {
		if ( nwa_time_left() < 8 ) {
			return null;
		}
		$r = nwa_fetch( $origin . $path, 4, 204800 );
		return is_wp_error( $r ) || 200 !== $r['code'] ? null : $r['body'];
	};
	if ( $dns ) {
		$robots  = $get( '/robots.txt' );
		$has_bot = $robots && false !== stripos( $robots, 'user-agent' );
		// Only confirmed findings count here: a blocked file says nothing about the site.
		if ( $has_bot ) {
			$groups['seo'][] = nwa_check( 'robots.txt', 'pass', 1, 'A robots.txt file guides search engines.' );
		}
		$map = $has_bot && preg_match( '/^\s*sitemap:/im', $robots );
		if ( ! $map ) {
			foreach ( array( '/sitemap_index.xml', '/sitemap.xml', '/wp-sitemap.xml' ) as $path ) {
				$body = $get( $path );
				if ( $body && preg_match( '/<(urlset|sitemapindex)\b/i', $body ) ) {
					$map = true;
					break;
				}
			}
		}
		if ( $map ) {
			$groups['seo'][] = nwa_check( 'XML sitemap', 'pass', 2, 'An XML sitemap helps Google find all your pages.' );
		}
		$icon = $get( '/favicon.ico' );
		if ( $icon ) {
			$groups['design'][] = nwa_check( 'Favicon', 'pass', 1, 'A browser tab icon is set.' );
		}
	}

	$result = array(
		'url'       => $url,
		'final_url' => $url,
		'fetched'   => time(),
		'mode'      => 'basic',
		'title'     => '',
		'stats'     => array(
			'load_time' => null,
			'size_kb'   => null,
			'words'     => null,
			'images'    => null,
			'links'     => null,
			'files'     => null,
			'https'     => 0 === stripos( $url, 'https://' ),
		),
	);
	return nwa_finish( $result, $groups );
}

/**
 * Did Google PageSpeed fail because the site blocked or could not serve the page?
 *
 * @param string $error PageSpeed error message.
 * @return string Empty when not, otherwise a short reason (or "yes").
 */
function nwa_google_blocked( $error ) {
	if ( ! $error || ! preg_match( '/(FAILED_DOCUMENT_REQUEST|ERRORED_DOCUMENT_REQUEST|NO_FCP|NO_NAVSTART|unable to reliably load|Status code: \d{3}|DNS_FAILURE|INSECURE_DOCUMENT_REQUEST)/i', $error ) ) {
		return '';
	}
	if ( preg_match( '/Status code: (\d{3})/i', $error, $m ) ) {
		return 'the server answered Google with error ' . $m[1] . ', so a firewall or security setting is refusing it';
	}
	if ( preg_match( '/NO_FCP|NO_NAVSTART/i', $error ) ) {
		return 'the page stayed blank for Google, so no content appeared while it loaded (often a loading screen, a heavy script or a bot challenge)';
	}
	if ( preg_match( '/DNS_FAILURE/i', $error ) ) {
		return 'Google could not find the domain (DNS problem)';
	}
	if ( preg_match( '/INSECURE_DOCUMENT_REQUEST/i', $error ) ) {
		return 'the SSL certificate was not accepted by Google';
	}
	return 'the page did not load for Google';
}

/**
 * Does robots.txt block all search engines from the whole site?
 *
 * @param string $body robots.txt.
 * @return bool
 */
function nwa_robots_blocks_all( $body ) {
	$agents = array();
	$rules  = false;
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $body ) as $line ) {
		$line = trim( preg_replace( '/#.*/', '', $line ) );
		if ( '' === $line || false === strpos( $line, ':' ) ) {
			continue;
		}
		list( $field, $value ) = array_map( 'trim', explode( ':', $line, 2 ) );
		$field                 = strtolower( $field );
		if ( 'user-agent' === $field ) {
			if ( $rules ) {
				$agents = array();
				$rules  = false;
			}
			$agents[] = $value;
		} elseif ( 'disallow' === $field || 'allow' === $field ) {
			$rules = true;
			if ( 'disallow' === $field && '/' === $value && in_array( '*', $agents, true ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Platforms and tools the website is built with.
 *
 * @param string $html    HTML.
 * @param array  $headers Response headers.
 * @return string[]
 */
function nwa_detect_tech( $html, $headers ) {
	$map  = array(
		'WordPress'          => '#/wp-content/|/wp-includes/|content="WordPress#i',
		'Elementor'          => '#/plugins/elementor/|class="[^"]*elementor-(kit|element|widget)#i',
		'WooCommerce'        => '#/plugins/woocommerce/|class="[^"]*woocommerce-(page|cart|js)#i',
		'Divi'               => '#et_pb_|/themes/Divi/#',
		'Shopify'            => '#cdn\.shopify\.com|Shopify\.theme#i',
		'Wix'                => '#static\.wixstatic\.com|_wixCIDX|wix-bolt#i',
		'Squarespace'        => '#static1\.squarespace\.com|squarespace-cdn\.com#i',
		'Webflow'            => '#data-wf-site|website-files\.com#i',
		'Framer'             => '#framerusercontent\.com#i',
		'Next.js'            => '#__NEXT_DATA__|/_next/static#',
		'Google Tag Manager' => '#googletagmanager\.com#i',
	);
	$tech = array();
	foreach ( $map as $name => $re ) {
		if ( preg_match( $re, $html ) ) {
			$tech[] = $name;
		}
	}
	$server = isset( $headers['server'] ) ? ( is_array( $headers['server'] ) ? implode( ' ', $headers['server'] ) : (string) $headers['server'] ) : '';
	if ( isset( $headers['cf-ray'] ) || false !== stripos( $server, 'cloudflare' ) ) {
		$tech[] = 'Cloudflare';
	}
	return $tech;
}

/**
 * Why a problem matters for the business (shown with every issue in the report).
 *
 * @param string $label Check label.
 * @return string
 */
function nwa_impact( $label ) {
	$map = array(
		'Mobile friendly'                     => 'Most visitors browse on a phone. A zoomed out desktop page makes them leave, and Google ranks the mobile version first.',
		'Clear call to action'                => 'Visitors need to be told what to do next. Pages without clear buttons turn far fewer visitors into enquiries.',
		'Easy to contact'                     => 'If people can not find a quick way to reach you, ready buyers simply go to a competitor.',
		'Lead capture form'                   => 'Interested visitors have to hunt for a way to reach you. Most will not bother, so ready buyers leave without a trace.',
		'Tap to call'                         => 'High intent mobile visitors often prefer to call. Few will copy a number by hand.',
		'WhatsApp or live chat'               => 'Many people will not fill in a form but will send a quick message. Instant chat captures leads that would otherwise leave.',
		'Pinch to zoom'                       => 'People with weaker eyesight can not enlarge the text, which is an accessibility failure.',
		'Favicon'                             => 'A missing icon in the browser tab looks unfinished and less trustworthy.',
		'Stable layout'                       => 'When the page jumps while loading, people tap the wrong thing and get annoyed. Google measures this too.',
		'Stable layout (CLS)'                 => 'When the page jumps while loading, people tap the wrong thing and get annoyed. Google measures this too.',
		'Consistent typography'               => 'Too many fonts look messy and slow the page down.',
		'Social proof links'                  => 'Active social profiles show you are a real, living business.',
		'Clean build'                         => 'A heavy page builder makes the site slower and harder to keep consistent.',
		'Readable colours'                    => 'Low contrast text is hard to read, especially on phones outside, so people skim less and leave sooner.',
		'Easy to tap'                         => 'Small buttons close together cause mis taps and frustration on phones.',
		'Labelled buttons'                    => 'Screen readers can not tell visitors what unlabeled buttons do.',
		'No browser errors'                   => 'Script errors can break forms, menus and tracking without you noticing.',
		'Google accessibility score'          => 'An accessible site reaches more customers and avoids legal risk in many countries.',
		'Page title'                          => 'The title is the blue headline in Google results. A weak title means fewer clicks, even when you rank.',
		'Meta description'                    => 'This is your advert text in Google. Without it Google picks random text and fewer people click.',
		'Main heading (H1)'                   => 'The H1 tells visitors and Google what the page is about in one line.',
		'Heading structure'                   => 'Clear sections help Google understand your services and help visitors find what they need.',
		'Image alt text'                      => 'Alt text brings visitors from Google Images and helps people using screen readers.',
		'Canonical URL'                       => 'Without it Google may split your ranking between duplicate addresses of the same page.',
		'Indexing'                            => 'A page Google may not index can never appear in search results.',
		'Language tag'                        => 'Helps Google show your page to people searching in the right language.',
		'Social sharing preview'              => 'Links shared on WhatsApp, Facebook and LinkedIn look plain and get fewer clicks.',
		'Structured data'                     => 'Schema can unlock rich results like stars, prices and opening hours that stand out in Google.',
		'robots.txt'                          => 'robots.txt guides search engines. A wrong rule can hide your whole site from Google.',
		'XML sitemap'                         => 'A sitemap helps Google find and index all your pages faster.',
		'Internal links'                      => 'Links between your pages help visitors explore and pass ranking power to key pages.',
		'Visitor tracking (analytics)'        => 'Without analytics you can not see visitors, sources or which pages bring leads, so marketing is guesswork.',
		'Page status'                         => 'Error pages are dropped from Google and lose visitors.',
		'Crawlable links'                     => 'Google can only follow real links, so hidden pages will not be found.',
		'Google SEO score'                    => 'This is Google\'s own view of your technical SEO basics.',
		'Amount of content'                   => 'Thin pages rarely rank. Helpful text answers questions and convinces visitors to contact you.',
		'Easy to read'                        => 'Hard text makes visitors leave. Clear, simple writing sells better.',
		'Scannable sections'                  => 'Most people scan instead of reading. Sub headings help them find what they came for.',
		'Short paragraphs'                    => 'Long blocks of text are tiring on a phone, so people skip important points.',
		'Visual content'                      => 'Real photos of your work build trust much faster than words alone.',
		'Trust signals'                       => 'People rarely contact a business they can not verify. Reviews are one of the strongest drivers of enquiries.',
		'Text to code ratio'                  => 'A lot of code with little text makes the page heavy and gives Google less to rank.',
		'Up to date'                          => 'An old date makes visitors wonder whether the business is still active.',
		'Privacy policy'                      => 'People hesitate to share details without one, and collecting data without a policy can break privacy laws.',
		'Descriptive links'                   => 'Links like "click here" tell neither visitors nor Google where they lead.',
		'Heading order'                       => 'A logical heading order makes the page easier to scan and understand.',
		'Language set'                        => 'Helps browsers and Google handle your page correctly.',
		'Readable text size'                  => 'Tiny text on phones makes people pinch and zoom, or leave.',
		'Page structure'                      => 'A very complex page is slower to load and harder for phones to handle.',
		'Google best practices score'         => 'Google checks for outdated code and security issues that can break the site.',
		'Secure connection (HTTPS)'           => 'Browsers label http sites "Not secure", which scares visitors away and hurts rankings.',
		'Server response'                     => 'A slow server delays everything else. Visitors leave and Google notices.',
		'Page weight (HTML)'                  => 'Heavy pages load slowly on mobile data, and slow pages lose visitors.',
		'Compression'                         => 'Compressed files load several times faster at no cost.',
		'Number of files'                     => 'Every extra file is another round trip, which slows the page down on phones.',
		'Render blocking scripts'             => 'Visitors stare at a blank screen while these files load first.',
		'Render blocking files'               => 'Visitors stare at a blank screen while these files load first.',
		'Modern image formats'                => 'Images are usually the heaviest part of a page. WebP makes them much lighter.',
		'Optimised images'                    => 'Oversized images waste data and slow the page, especially on phones.',
		'Lazy loading'                        => 'Loading every image at once slows the first screen people actually see.',
		'Unused JavaScript'                   => 'Code that is loaded but not used still slows the page down.',
		'Security headers'                    => 'These headers protect visitors against common attacks like clickjacking.',
		'No mixed content'                    => 'Insecure files on a secure page can be blocked by browsers and break the design.',
		'Software version hidden'             => 'Showing the exact version helps attackers look for known weaknesses.',
		'Main content load time (LCP)'        => 'This is when visitors see your main content. Over 2.5 seconds and many leave before they even see your offer.',
		'First paint (FCP)'                   => 'A long blank screen makes visitors think the site is broken.',
		'Responsiveness (TBT)'                => 'When the page freezes, taps and clicks do nothing and people give up.',
		'Speed Index'                         => 'Shows how quickly the page visibly fills in. Faster feels more professional.',
		'Google speed score (mobile)'         => 'This is Google\'s own speed score for phones, which affects rankings and conversions.',
		'SSL certificate'                     => 'An expired certificate shows a big security warning that stops almost every visitor.',
		'Redirects to HTTPS'                  => 'People who type the address without https land on an insecure version.',
		'Business email'                      => 'An email at your own domain looks far more professional than a free mailbox.',
		'Email protection (SPF)'              => 'Without SPF your emails, including quotes and invoices, land in spam more often.',
		'Anti spoofing (DMARC)'               => 'Scammers can send emails pretending to be you, which damages trust in your brand.',
		'Domain'                              => 'If the domain does not resolve, nobody can reach the website at all.',
		'Open to search engines and scanners' => 'If tools can not open your site, some search engines and link previews may struggle too.',
		'Google can load your homepage'       => 'If Google can not see your page, it can not rank it, and you lose free traffic from search.',
		'Design and mobile review'            => 'Design decides whether visitors trust you in the first seconds.',
		'SEO review'                          => 'SEO decides whether new customers can find you on Google.',
		'Content review'                      => 'Content is what finally convinces a visitor to get in touch.',
	);
	return isset( $map[ $label ] ) ? $map[ $label ] : '';
}
