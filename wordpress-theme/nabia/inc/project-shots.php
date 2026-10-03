<?php
/**
 * Portfolio thumbnails: automatic screenshots of each project's live website, shown in a
 * browser and phone mockup on the project cards (Customize → Nabia Theme → Portfolio).
 *
 * How it works:
 * - Your server asks WordPress.com's free screenshot service (mShots) for a desktop and a
 *   mobile screenshot of the project's "Live link". The first request starts the capture,
 *   so the theme checks back every few minutes in the background until both are ready.
 * - Screenshots are saved in your Media uploads, so visitors only load images from your
 *   own site. They refresh every 30 days and whenever the live link changes.
 * - Until a screenshot is ready, the card shows the featured image inside the mockup.
 *
 * Use another screenshot service with the `nabia_screenshot_service_url` filter.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capture sizes: viewport (vpw x vph) and saved image (w x h).
 *
 * @return array device => size args.
 */
function nabia_shot_sizes() {
	return apply_filters(
		'nabia_shot_sizes',
		array(
			'desktop' => array(
				'vpw' => 1280,
				'vph' => 2400,
				'w'   => 800,
				'h'   => 1500,
			),
			'mobile'  => array(
				'vpw' => 390,
				'vph' => 844,
				'w'   => 390,
				'h'   => 844,
			),
		)
	);
}

/**
 * Are automatic screenshots switched on?
 *
 * @return bool
 */
function nabia_shots_enabled() {
	return 'showcase' === nabia_mod( 'portfolio_thumbs' );
}

/**
 * Screenshot service address for a website.
 *
 * @param string $url    Live website.
 * @param string $device desktop or mobile.
 * @return string
 */
function nabia_screenshot_service_url( $url, $device ) {
	$sizes = nabia_shot_sizes();
	$size  = isset( $sizes[ $device ] ) ? $sizes[ $device ] : $sizes['desktop'];
	$base  = 'https://s.wordpress.com/mshots/v1/' . rawurlencode( $url );
	return apply_filters( 'nabia_screenshot_service_url', add_query_arg( $size, $base ), $url, $device, $size );
}

/**
 * Saved screenshot for a project, or '' when there is none yet (a capture is then queued).
 *
 * @param int    $post_id Project ID.
 * @param string $device  desktop or mobile.
 * @return string Image URL.
 */
function nabia_project_shot( $post_id, $device = 'desktop' ) {
	$live = nabia_project_live_url( $post_id );
	if ( ! $live || ! nabia_shots_enabled() ) {
		return '';
	}
	$shot  = get_post_meta( $post_id, '_nabia_shot_' . $device, true );
	$valid = is_array( $shot ) && ! empty( $shot['url'] ) && isset( $shot['src'] ) && $shot['src'] === $live && ! empty( $shot['file'] ) && file_exists( $shot['file'] );
	if ( ! $valid || ( time() - (int) $shot['time'] ) > 30 * DAY_IN_SECONDS ) {
		nabia_shots_queue( $post_id );
	}
	return $valid ? $shot['url'] : '';
}

/**
 * Queue a background capture for a project.
 *
 * @param int $post_id Project ID.
 * @param int $delay   Seconds from now.
 */
function nabia_shots_queue( $post_id, $delay = 5 ) {
	// After 12 failed attempts (site down, blocked or very slow), wait a day before trying again.
	if ( (int) get_post_meta( $post_id, '_nabia_shot_tries', true ) >= 12 ) {
		if ( time() - (int) get_post_meta( $post_id, '_nabia_shot_last', true ) < DAY_IN_SECONDS ) {
			return;
		}
		update_post_meta( $post_id, '_nabia_shot_tries', 0 );
	}
	$args = array( (int) $post_id );
	if ( ! wp_next_scheduled( 'nabia_fetch_shots', $args ) ) {
		wp_schedule_single_event( time() + $delay, 'nabia_fetch_shots', $args );
	}
}

/**
 * Does this project still need a (fresh) screenshot for a device?
 *
 * @param int    $post_id Project ID.
 * @param string $device  Device.
 * @param string $live    Live URL.
 * @return bool
 */
function nabia_shot_needed( $post_id, $device, $live ) {
	$shot = get_post_meta( $post_id, '_nabia_shot_' . $device, true );
	return ! is_array( $shot ) || empty( $shot['file'] ) || ! file_exists( $shot['file'] ) || ! isset( $shot['src'] ) || $shot['src'] !== $live || ( time() - (int) $shot['time'] ) > 30 * DAY_IN_SECONDS;
}

/**
 * Download the screenshots of one project. Retries every 3 minutes (up to 12 times)
 * while the service is still preparing them.
 *
 * @param int $post_id Project ID.
 * @return array device => true (saved) | false (not ready yet).
 */
function nabia_fetch_shots( $post_id ) {
	$post_id = (int) $post_id;
	$live    = nabia_project_live_url( $post_id );
	$result  = array();
	if ( ! $live ) {
		return $result;
	}

	// A new live link starts a fresh round of attempts.
	if ( get_post_meta( $post_id, '_nabia_shot_for', true ) !== $live ) {
		update_post_meta( $post_id, '_nabia_shot_for', $live );
		update_post_meta( $post_id, '_nabia_shot_tries', 0 );
	}

	update_post_meta( $post_id, '_nabia_shot_last', time() );
	foreach ( array_keys( nabia_shot_sizes() ) as $device ) {
		if ( ! nabia_shot_needed( $post_id, $device, $live ) ) {
			$result[ $device ] = true;
			continue;
		}
		$result[ $device ] = nabia_save_shot( $post_id, $device, $live );
	}

	if ( in_array( false, $result, true ) ) {
		$tries = (int) get_post_meta( $post_id, '_nabia_shot_tries', true ) + 1;
		update_post_meta( $post_id, '_nabia_shot_tries', $tries );
		if ( $tries < 12 ) {
			nabia_shots_queue( $post_id, 3 * MINUTE_IN_SECONDS );
		}
	} else {
		update_post_meta( $post_id, '_nabia_shot_tries', 0 );
	}
	return $result;
}
add_action( 'nabia_fetch_shots', 'nabia_fetch_shots' );

/**
 * Download one screenshot and keep it in the uploads folder.
 *
 * @param int    $post_id Project ID.
 * @param string $device  Device.
 * @param string $live    Live URL.
 * @return bool Saved.
 */
function nabia_save_shot( $post_id, $device, $live ) {
	$response = wp_remote_get(
		nabia_screenshot_service_url( $live, $device ),
		array(
			'timeout'     => 30,
			'redirection' => 0, // While capturing, the service redirects to a "loading" image.
		)
	);
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}
	$type = (string) wp_remote_retrieve_header( $response, 'content-type' );
	$body = wp_remote_retrieve_body( $response );
	if ( ! preg_match( '#^image/(jpe?g|png|webp)#i', $type, $m ) || strlen( $body ) < 8000 ) {
		return false;
	}
	$ext    = 'png' === strtolower( $m[1] ) ? 'png' : ( 'webp' === strtolower( $m[1] ) ? 'webp' : 'jpg' );
	$upload = wp_upload_bits( 'project-' . $post_id . '-' . $device . '-' . time() . '.' . $ext, null, $body );
	if ( ! empty( $upload['error'] ) ) {
		return false;
	}

	$old = get_post_meta( $post_id, '_nabia_shot_' . $device, true );
	if ( is_array( $old ) && ! empty( $old['file'] ) && $old['file'] !== $upload['file'] && file_exists( $old['file'] ) ) {
		wp_delete_file( $old['file'] );
	}
	update_post_meta(
		$post_id,
		'_nabia_shot_' . $device,
		array(
			'url'  => $upload['url'],
			'file' => $upload['file'],
			'src'  => $live,
			'time' => time(),
		)
	);
	return true;
}

/**
 * Capture again when a project is saved (for example with a new live link).
 *
 * @param int $post_id Post ID.
 */
function nabia_shots_on_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || get_post_type( $post_id ) !== nabia_portfolio_type() || 'publish' !== get_post_status( $post_id ) || ! nabia_shots_enabled() ) {
		return;
	}
	if ( nabia_project_live_url( $post_id ) ) {
		nabia_shots_queue( $post_id, 10 );
	}
}
add_action( 'save_post', 'nabia_shots_on_save', 20 );

/**
 * Delete saved screenshots with their project.
 *
 * @param int $post_id Post ID.
 */
function nabia_shots_on_delete( $post_id ) {
	foreach ( array_keys( nabia_shot_sizes() ) as $device ) {
		$shot = get_post_meta( $post_id, '_nabia_shot_' . $device, true );
		if ( is_array( $shot ) && ! empty( $shot['file'] ) && file_exists( $shot['file'] ) ) {
			wp_delete_file( $shot['file'] );
		}
	}
}
add_action( 'before_delete_post', 'nabia_shots_on_delete' );

/**
 * Everything the project card needs for its mockup.
 *
 * @param int $post_id Project ID.
 * @return array { desktop, tall, mobile, domain, hue }
 */
function nabia_project_showcase( $post_id ) {
	$desktop = nabia_project_shot( $post_id, 'desktop' );
	$tall    = (bool) $desktop;
	if ( ! $desktop && has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $img ) {
			$desktop = $img[0];
			// A long full-page image can scroll on hover too.
			$tall = $img[1] && ( $img[2] / $img[1] ) > 1.2;
		}
	}
	$live   = nabia_project_live_url( $post_id );
	$domain = $live ? preg_replace( '#^www\.#i', '', (string) wp_parse_url( $live, PHP_URL_HOST ) ) : '';
	$hues   = array( '#7c3aed', '#ec4899', '#f59e0b', '#2563eb', '#10b981', '#e34c26' );
	return array(
		'desktop' => $desktop,
		'tall'    => $tall,
		'mobile'  => nabia_project_shot( $post_id, 'mobile' ),
		'domain'  => $domain,
		'hue'     => $hues[ absint( $post_id ) % count( $hues ) ],
	);
}

/**
 * Admin: status of the screenshots, shown on the Nabia Setup screen.
 *
 * @return array[] Each: post, live, desktop, mobile.
 */
function nabia_shots_status() {
	$rows = array();
	foreach ( get_posts(
		array(
			'post_type'      => nabia_portfolio_type(),
			'post_status'    => 'publish',
			'posts_per_page' => 100,
		)
	) as $post ) {
		$live   = nabia_project_live_url( $post->ID );
		$rows[] = array(
			'post'    => $post,
			'live'    => $live,
			'desktop' => $live && ! nabia_shot_needed( $post->ID, 'desktop', $live ),
			'mobile'  => $live && ! nabia_shot_needed( $post->ID, 'mobile', $live ),
		);
	}
	return $rows;
}

/**
 * Admin: start capturing every project now (forced = take new screenshots).
 *
 * @param bool $force Replace existing screenshots.
 * @return string Log line.
 */
function nabia_shots_run_all( $force = false ) {
	$count = 0;
	foreach ( nabia_shots_status() as $row ) {
		if ( ! $row['live'] ) {
			continue;
		}
		if ( $force ) {
			foreach ( array_keys( nabia_shot_sizes() ) as $device ) {
				$shot = get_post_meta( $row['post']->ID, '_nabia_shot_' . $device, true );
				if ( is_array( $shot ) ) {
					$shot['time'] = 0; // Counts as expired; the old image stays until the new one arrives.
					update_post_meta( $row['post']->ID, '_nabia_shot_' . $device, $shot );
				}
			}
		}
		update_post_meta( $row['post']->ID, '_nabia_shot_tries', 0 );
		nabia_fetch_shots( $row['post']->ID );
		++$count;
	}
	/* translators: %d: number of projects */
	return sprintf( _n( 'Screenshots requested for %d project. New captures can take a few minutes; they are saved automatically in the background.', 'Screenshots requested for %d projects. New captures can take a few minutes; they are saved automatically in the background.', $count, 'nabia' ), $count );
}
