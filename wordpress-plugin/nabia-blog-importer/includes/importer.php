<?php
/**
 * Turning pack articles into posts: schedule, links, cover images, FAQ and SEO details.
 *
 * Links in articles are placeholders that become real addresses:
 *   {{service:web-design}}  a service page       {{page:pricing}}  pricing, audit, about ...
 *   {{post:slug}}           another article      {{contact}}  {{portfolio}}
 * A link only appears once its page or article is live; until then it is plain text.
 * Every time an article goes live, the other imported articles that you have not edited
 * are refreshed, so scheduled articles start linking to each other by themselves.
 *
 * @package Nabia_Blog_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publish dates for a number of articles.
 *
 * @param int    $count    How many.
 * @param string $start    Local date and time "Y-m-d H:i".
 * @param int    $every    Gap between articles.
 * @param string $unit     days or weeks.
 * @param int[]  $weekdays Allowed weekdays (1 = Monday ... 7 = Sunday).
 * @param bool   $vary     Vary the time by up to 45 minutes so posts look natural.
 * @return string[] Local "Y-m-d H:i:s" dates.
 */
function nbi_schedule_dates( $count, $start, $every, $unit, $weekdays, $vary ) {
	$tz       = wp_timezone();
	$weekdays = array_values( array_intersect( array_map( 'intval', (array) $weekdays ), range( 1, 7 ) ) );
	if ( ! $weekdays ) {
		$weekdays = range( 1, 7 );
	}
	$step = max( 1, (int) $every ) * ( 'weeks' === $unit ? 7 : 1 );
	try {
		$date = new DateTimeImmutable( $start, $tz );
	} catch ( Exception $e ) {
		$date = new DateTimeImmutable( 'tomorrow 09:00', $tz );
	}

	$dates = array();
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $i ) {
			$date = $date->modify( '+' . $step . ' days' );
		}
		// Move forward to the next allowed weekday.
		for ( $guard = 0; $guard < 7 && ! in_array( (int) $date->format( 'N' ), $weekdays, true ); $guard++ ) {
			$date = $date->modify( '+1 day' );
		}
		$when = $date;
		if ( $vary ) {
			$when = $when->modify( ( wp_rand( 0, 90 ) - 45 ) . ' minutes' );
			if ( $when->format( 'Y-m-d' ) !== $date->format( 'Y-m-d' ) ) {
				$when = $date;
			}
		}
		$dates[] = $when->format( 'Y-m-d H:i:s' );
	}
	return $dates;
}

/**
 * Address for a link placeholder, or '' when that page is not live.
 *
 * Uses the Nabia theme's lookups when the theme is active, otherwise finds pages by address.
 *
 * @param string $target e.g. "service:web-design", "page:pricing", "post:slug", "contact".
 * @return string
 */
function nbi_target_url( $target ) {
	list( $type, $key ) = array_pad( explode( ':', $target, 2 ), 2, '' );
	$key                = sanitize_title( $key );
	$page               = function ( $paths ) {
		foreach ( $paths as $path ) {
			$found = get_page_by_path( $path );
			if ( $found && 'publish' === $found->post_status ) {
				return get_permalink( $found );
			}
		}
		return '';
	};

	switch ( $type ) {
		case 'service':
			$url = function_exists( 'nabia_service_url' ) ? nabia_service_url( $key ) : '';
			return $url ? $url : $page( array( 'services/' . $key, $key ) );
		case 'page':
			$url = function_exists( 'nabia_page_url' ) ? nabia_page_url( $key ) : '';
			$alt = array(
				'audit'     => array( 'free-website-audit', 'audit' ),
				'portfolio' => array( 'portfolio', 'my-works', 'work' ),
				'contact'   => array( 'contact', 'contact-me', 'hire-me' ),
			);
			return $url ? $url : $page( isset( $alt[ $key ] ) ? $alt[ $key ] : array( $key ) );
		case 'post':
			$post = get_page_by_path( $key, OBJECT, 'post' );
			return ( $post && 'publish' === $post->post_status ) ? get_permalink( $post ) : '';
		case 'contact':
			$url = function_exists( 'nabia_hire_url' ) ? nabia_hire_url() : '';
			return $url ? $url : $page( array( 'contact', 'contact-me', 'hire-me' ) );
		case 'portfolio':
			$url = function_exists( 'nabia_portfolio_url' ) ? nabia_portfolio_url() : '';
			return $url ? $url : $page( array( 'portfolio', 'my-works', 'work' ) );
	}
	return '';
}

/**
 * Article HTML plus its FAQ section, with placeholders still in place.
 *
 * @param array $item Article.
 * @return string
 */
function nbi_article_source( $item ) {
	$html = $item['body'];
	if ( ! empty( $item['faq'] ) ) {
		$html .= "\n<h2>" . esc_html__( 'Frequently asked questions', 'nabia-blog-importer' ) . '</h2>';
		foreach ( $item['faq'] as $pair ) {
			if ( isset( $pair[0], $pair[1] ) ) {
				$html .= "\n<h3>" . esc_html( $pair[0] ) . "</h3>\n<p>" . esc_html( $pair[1] ) . '</p>';
			}
		}
	}
	return $html;
}

/**
 * Final post content: links resolved, split into editor blocks.
 *
 * @param string $source Article HTML with placeholders.
 * @return string
 */
function nbi_build_content( $source ) {
	$html = preg_replace_callback(
		'#<a href="\{\{([a-z0-9:_-]+)\}\}">(.*?)</a>#i',
		function ( $m ) {
			$url = nbi_target_url( $m[1] );
			return $url ? '<a href="' . esc_url( $url ) . '">' . $m[2] . '</a>' : $m[2];
		},
		$source
	);

	// One block per paragraph, heading and list, so everything is editable in the block editor.
	preg_match_all( '#<(p|h2|h3|h4|ul|ol|blockquote)\b[^>]*>.*?</\1>#is', $html, $found, PREG_SET_ORDER );
	$out = array();
	foreach ( $found as $el ) {
		$tag   = strtolower( $el[1] );
		$block = $el[0];
		if ( 'p' === $tag ) {
			$out[] = "<!-- wp:paragraph -->\n" . $block . "\n<!-- /wp:paragraph -->";
		} elseif ( in_array( $tag, array( 'h2', 'h3', 'h4' ), true ) ) {
			$level = (int) substr( $tag, 1 );
			$block = preg_replace( '#^<(h[234])>#i', '<$1 class="wp-block-heading">', $block );
			$out[] = ( 2 === $level ? '<!-- wp:heading -->' : '<!-- wp:heading {"level":' . $level . '} -->' ) . "\n" . $block . "\n<!-- /wp:heading -->";
		} elseif ( 'blockquote' === $tag ) {
			$out[] = "<!-- wp:html -->\n" . $block . "\n<!-- /wp:html -->";
		} else {
			$items = preg_replace( '#<li>(.*?)</li>#is', "<!-- wp:list-item -->\n<li>$1</li>\n<!-- /wp:list-item -->", $block );
			$out[] = ( 'ol' === $tag ? '<!-- wp:list {"ordered":true} -->' : '<!-- wp:list -->' ) . "\n" . $items . "\n<!-- /wp:list -->";
		}
	}
	return implode( "\n\n", $out );
}

/**
 * Has this article already been imported (or does a post use its address)?
 *
 * @param string $slug Article slug.
 * @return WP_Post|null
 */
function nbi_existing_post( $slug ) {
	$found = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => '_nbi_article', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( $found ) {
		return $found[0];
	}
	$post = get_page_by_path( $slug, OBJECT, 'post' );
	return ( $post && 'trash' !== $post->post_status ) ? $post : null;
}

/**
 * Category or tag IDs, created when missing.
 *
 * @param array  $names    Names.
 * @param string $taxonomy Taxonomy.
 * @return int[]
 */
function nbi_terms( $names, $taxonomy ) {
	$ids = array();
	foreach ( array_filter( (array) $names ) as $name ) {
		$term = term_exists( $name, $taxonomy );
		if ( ! $term ) {
			$term = wp_insert_term( $name, $taxonomy );
		}
		if ( $term && ! is_wp_error( $term ) ) {
			$ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}
	return $ids;
}

/**
 * Add the cover image as the featured image.
 *
 * @param int    $post_id Post ID.
 * @param string $file    Cover file in the pack.
 * @param string $title   Article title (alt text).
 */
function nbi_add_cover( $post_id, $file, $title ) {
	if ( ! $file || ! file_exists( $file ) || has_post_thumbnail( $post_id ) ) {
		return;
	}
	$upload = wp_upload_bits( wp_basename( $file ), null, file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		return;
	}
	$type       = wp_check_filetype( $upload['file'] );
	$attachment = wp_insert_attachment(
		array(
			'post_mime_type' => $type['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$post_id
	);
	if ( ! $attachment || is_wp_error( $attachment ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment, wp_generate_attachment_metadata( $attachment, $upload['file'] ) );
	update_post_meta( $attachment, '_wp_attachment_image_alt', wp_strip_all_tags( $title ) );
	set_post_thumbnail( $post_id, $attachment );
}

/**
 * Import articles from a pack.
 *
 * @param string $pack_id Pack ID.
 * @param array  $o       Options: slugs, mode (schedule|publish|draft), start, every, unit,
 *                        weekdays, vary, order (pack|shuffle), author, category (0 = article's),
 *                        comments (open|closed), cover (bool), seo (bool).
 * @return array[] Each: title, slug, id, status, date, note.
 */
function nbi_import( $pack_id, $o ) {
	$articles = nbi_pack_articles( $pack_id );
	$chosen   = array();
	foreach ( (array) $o['slugs'] as $slug ) {
		if ( isset( $articles[ $slug ] ) ) {
			$chosen[] = $articles[ $slug ];
		}
	}
	if ( 'shuffle' === $o['order'] ) {
		shuffle( $chosen );
	}

	$report = array();
	$todo   = array();
	foreach ( $chosen as $item ) {
		$existing = nbi_existing_post( $item['slug'] );
		if ( $existing ) {
			$report[] = array(
				'title'  => $item['title'],
				'slug'   => $item['slug'],
				'id'     => $existing->ID,
				'status' => $existing->post_status,
				'date'   => $existing->post_date,
				'note'   => __( 'Skipped: already on your site', 'nabia-blog-importer' ),
			);
			continue;
		}
		$todo[] = $item;
	}

	$dates = 'schedule' === $o['mode'] ? nbi_schedule_dates( count( $todo ), $o['start'], $o['every'], $o['unit'], $o['weekdays'], ! empty( $o['vary'] ) ) : array();
	$now   = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

	foreach ( $todo as $i => $item ) {
		$source   = nbi_article_source( $item );
		$category = (int) $o['category'] ? array( (int) $o['category'] ) : nbi_terms( array( $item['category'] ), 'category' );
		$postarr  = array(
			'post_type'      => 'post',
			'post_title'     => $item['title'],
			'post_name'      => $item['slug'],
			'post_excerpt'   => $item['excerpt'],
			'post_content'   => nbi_build_content( $source ),
			'post_author'    => (int) $o['author'],
			'post_category'  => $category,
			'tags_input'     => (array) $item['tags'],
			'comment_status' => 'open' === $o['comments'] ? 'open' : 'closed',
		);
		if ( 'schedule' === $o['mode'] ) {
			$postarr['post_date']     = $dates[ $i ];
			$postarr['post_date_gmt'] = get_gmt_from_date( $dates[ $i ] );
			$postarr['post_status']   = strtotime( $dates[ $i ] ) > $now ? 'future' : 'publish';
		} elseif ( 'publish' === $o['mode'] ) {
			// A minute apart, newest first in pack order.
			$postarr['post_date']   = wp_date( 'Y-m-d H:i:s', time() - $i * MINUTE_IN_SECONDS );
			$postarr['post_status'] = 'publish';
		} else {
			$postarr['post_status'] = 'draft';
		}

		$id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			$report[] = array(
				'title'  => $item['title'],
				'slug'   => $item['slug'],
				'id'     => 0,
				'status' => '',
				'date'   => '',
				'note'   => $id->get_error_message(),
			);
			continue;
		}

		update_post_meta( $id, '_nbi_article', $item['slug'] );
		update_post_meta( $id, '_nbi_pack', $pack_id );
		update_post_meta( $id, '_nbi_source', wp_slash( $source ) );
		update_post_meta( $id, '_nbi_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
		// Read by the Nabia theme: FAQ schema, related articles on service pages.
		update_post_meta( $id, '_nabia_faq', $item['faq'] );
		if ( $item['service'] ) {
			update_post_meta( $id, '_nabia_service', sanitize_key( $item['service'] ) );
		}
		if ( ! empty( $o['seo'] ) ) {
			if ( $item['keyword'] ) {
				update_post_meta( $id, '_nabia_keyword', $item['keyword'] );
				update_post_meta( $id, '_yoast_wpseo_focuskw', $item['keyword'] );
				update_post_meta( $id, 'rank_math_focus_keyword', $item['keyword'] );
			}
			if ( $item['excerpt'] ) {
				update_post_meta( $id, '_yoast_wpseo_metadesc', $item['excerpt'] );
				update_post_meta( $id, 'rank_math_description', $item['excerpt'] );
			}
		}
		if ( ! empty( $o['cover'] ) ) {
			nbi_add_cover( $id, $item['cover'], $item['title'] );
		}

		$post     = get_post( $id );
		$report[] = array(
			'title'  => $item['title'],
			'slug'   => $item['slug'],
			'id'     => $id,
			'status' => $post->post_status,
			'date'   => $post->post_date,
			'note'   => '',
		);
	}

	nbi_refresh_links();
	return $report;
}

/**
 * Rebuild the links of imported articles you have not edited, so they point to every
 * page and article that is live now.
 */
function nbi_refresh_links() {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => array( 'publish', 'future', 'draft' ),
			'posts_per_page' => 300,
			'meta_key'       => '_nbi_source', // phpcs:ignore WordPress.DB.SlowDBQuery
			'no_found_rows'  => true,
		)
	);
	foreach ( $posts as $post ) {
		if ( md5( $post->post_content ) !== get_post_meta( $post->ID, '_nbi_hash', true ) ) {
			continue; // Edited by you: leave it alone.
		}
		$content = nbi_build_content( (string) get_post_meta( $post->ID, '_nbi_source', true ) );
		if ( $content === $post->post_content ) {
			continue;
		}
		remove_action( 'transition_post_status', 'nbi_on_publish', 10 );
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => wp_slash( $content ),
			)
		);
		add_action( 'transition_post_status', 'nbi_on_publish', 10, 3 );
		update_post_meta( $post->ID, '_nbi_hash', md5( get_post_field( 'post_content', $post->ID, 'raw' ) ) );
	}
}

/**
 * When any post or page goes live, refresh the links of imported articles.
 *
 * @param string  $new  New status.
 * @param string  $old  Old status.
 * @param WP_Post $post Post.
 */
function nbi_on_publish( $new, $old, $post ) {
	if ( 'publish' === $new && 'publish' !== $old && in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
		nbi_refresh_links();
	}
}
add_action( 'transition_post_status', 'nbi_on_publish', 10, 3 );

/**
 * Safety net for "Missed schedule": on low-traffic sites WordPress can miss the exact
 * moment. Every few minutes, publish imported articles whose time has passed.
 */
function nbi_publish_missed() {
	if ( get_transient( 'nbi_missed_check' ) ) {
		return;
	}
	set_transient( 'nbi_missed_check', 1, 5 * MINUTE_IN_SECONDS );
	$late = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'future',
			'posts_per_page' => 5,
			'meta_key'       => '_nbi_article', // phpcs:ignore WordPress.DB.SlowDBQuery
			'date_query'     => array(
				array(
					'column' => 'post_date_gmt',
					'before' => gmdate( 'Y-m-d H:i:s' ),
				),
			),
			'fields'         => 'ids',
		)
	);
	foreach ( $late as $id ) {
		wp_publish_post( $id );
	}
}
add_action( 'init', 'nbi_publish_missed', 99 );

/**
 * FAQ schema for imported articles when the active theme does not add it already.
 */
function nbi_faq_schema() {
	if ( ! is_singular( 'post' ) || function_exists( 'nabia_structured_data' ) ) {
		return;
	}
	$faq = get_post_meta( get_queried_object_id(), '_nabia_faq', true );
	if ( ! is_array( $faq ) || ! get_post_meta( get_queried_object_id(), '_nbi_article', true ) ) {
		return;
	}
	$items = array();
	foreach ( $faq as $pair ) {
		if ( isset( $pair[0], $pair[1] ) ) {
			$items[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $pair[0] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $pair[1] ),
				),
			);
		}
	}
	if ( $items ) {
		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $items,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'nbi_faq_schema', 5 );
