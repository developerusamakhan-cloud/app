<?php
/**
 * Styles, fonts and scripts.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Fonts: Fraunces (display), Hanken Grotesk (text), JetBrains Mono (figures).
 * The Urdu accent is requested with the `text` parameter so only those glyphs download.
 *
 * @return string[]
 */
function powerbachat_font_urls() {
	return array(
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT@0,9..144,300..700,50;1,9..144,300..700,50&family=Hanken+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap',
		'https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@500&display=swap&text=' . rawurlencode( 'بجلی بچت' ),
	);
}

function powerbachat_enqueue_assets() {
	foreach ( powerbachat_font_urls() as $i => $url ) {
		wp_enqueue_style( 'powerbachat-fonts-' . $i, $url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	wp_enqueue_style(
		'powerbachat-main',
		POWERBACHAT_URI . '/assets/css/main.css',
		array(),
		filemtime( POWERBACHAT_DIR . '/assets/css/main.css' )
	);

	wp_enqueue_script(
		'powerbachat-main',
		POWERBACHAT_URI . '/assets/js/main.js',
		array(),
		filemtime( POWERBACHAT_DIR . '/assets/js/main.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$config = array(
		'data'           => powerbachat_js_data(),
		'defaultCountry' => powerbachat_mod( 'pb_default_country' ),
		'tariffChecked'  => powerbachat_mod( 'pb_tariff_checked' ),
		'geoUrl'         => esc_url_raw( rest_url( 'powerbachat/v1/country' ) ),
		'subscribeUrl'   => esc_url_raw( rest_url( 'powerbachat/v1/subscribe' ) ),
		'contactUrl'     => esc_url_raw( rest_url( 'powerbachat/v1/contact' ) ),
		'formError'      => __( 'Something went wrong. Please check your connection and try again.', 'powerbachat' ),
	);
	wp_add_inline_script( 'powerbachat-main', 'window.PowerBachat = ' . wp_json_encode( $config ) . ';', 'before' );

}
add_action( 'wp_enqueue_scripts', 'powerbachat_enqueue_assets' );

function powerbachat_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'powerbachat_resource_hints', 10, 2 );

/**
 * Pick the visitor's country before first paint.
 *
 * URL country (/pk/, /in/, /bd/) always wins. Otherwise ?pb_country= (testing),
 * then the pb_cc cookie, then what the server detected. With no cookie yet,
 * main.js confirms the country over REST, which keeps cached pages correct.
 */
function powerbachat_country_bootstrap() {
	$forced  = powerbachat_forced_country();
	$current = powerbachat_current_country();
	?>
	<script>(function(){var d=document.documentElement,f=<?php echo wp_json_encode( $forced ); ?>,c='',q=/[?&]pb_country=(pk|in|bd)\b/.exec(location.search),m=/(?:^|;\s*)pb_cc=(pk|in|bd)/.exec(document.cookie);d.classList.add('js');if(q){document.cookie='pb_cc='+q[1]+';path=/;max-age=2592000;SameSite=Lax';m=q;}c=f||(m&&m[1])||d.getAttribute('data-country')||<?php echo wp_json_encode( $current ); ?>;d.setAttribute('data-country',c);window.pbGeoPending=!f&&!m;})();</script>
	<?php
}
add_action( 'wp_head', 'powerbachat_country_bootstrap', 1 );

function powerbachat_block_editor_fonts() {
	foreach ( powerbachat_font_urls() as $i => $url ) {
		wp_enqueue_style( 'powerbachat-editor-fonts-' . $i, $url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
}
add_action( 'enqueue_block_editor_assets', 'powerbachat_block_editor_fonts' );
