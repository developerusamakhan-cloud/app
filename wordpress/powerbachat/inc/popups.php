<?php
/**
 * Cookie consent popup and the "Need a website like this?" lead popup.
 *
 * Cookie consent
 * - Essential is always on; Analytics and Marketing are off until the visitor
 *   turns them on. The choice is kept in the pb_consent cookie for 6 months.
 * - Google Consent Mode v2 defaults are set to "denied" before any tag loads and
 *   updated when the visitor chooses, so Google Analytics or Site Kit respect it.
 * - Other scripts can listen for the "pb:consent" event or read window.pbConsent.
 * - "Cookie settings" in the footer opens the popup again.
 *
 * Lead popup
 * - Shown after the visitor has spent a set time on the site (default 75 seconds,
 *   counted across pages, only while the tab is visible), at most once every 24
 *   hours, and never on top of the cookie popup.
 * - Enquiries are saved under Leads in the admin and emailed to the contact address,
 *   then the visitor is taken to the studio's website in a new tab.
 *
 * @package PowerBachat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent Mode defaults, printed as early as possible in <head>.
 */
function powerbachat_consent_defaults() {
	if ( ! powerbachat_mod( 'pb_cookie_banner' ) ) {
		return;
	}
	?>
	<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}(function(){var m=/(?:^|;\s*)pb_consent=([^;]+)/.exec(document.cookie),v=m?decodeURIComponent(m[1]):'',a=/a=1/.test(v),k=/m=1/.test(v);window.pbConsent={set:!!m,analytics:a,marketing:k};gtag('consent','default',{analytics_storage:a?'granted':'denied',ad_storage:k?'granted':'denied',ad_user_data:k?'granted':'denied',ad_personalization:k?'granted':'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});})();</script>
	<?php
}
add_action( 'wp_head', 'powerbachat_consent_defaults', 0 );

/**
 * Lead post type (private, admin only).
 */
function powerbachat_register_leads() {
	register_post_type(
		'pb_lead',
		array(
			'labels'          => array(
				'name'          => __( 'Leads', 'powerbachat' ),
				'singular_name' => __( 'Lead', 'powerbachat' ),
				'menu_name'     => __( 'Leads', 'powerbachat' ),
				'not_found'     => __( 'No leads yet.', 'powerbachat' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-megaphone',
			'menu_position'   => 28,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'powerbachat_register_leads' );

/**
 * Lead list columns.
 *
 * @return array
 */
function powerbachat_lead_columns() {
	return array(
		'cb'         => '<input type="checkbox">',
		'title'      => __( 'Email or WhatsApp', 'powerbachat' ),
		'pb_country' => __( 'Country', 'powerbachat' ),
		'pb_page'    => __( 'Page', 'powerbachat' ),
		'date'       => __( 'Date', 'powerbachat' ),
	);
}
add_filter( 'manage_pb_lead_posts_columns', 'powerbachat_lead_columns' );

/**
 * Lead column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Post.
 */
function powerbachat_lead_column( $column, $post_id ) {
	if ( 'pb_country' === $column ) {
		echo esc_html( strtoupper( get_post_meta( $post_id, 'pb_country', true ) ) );
	} elseif ( 'pb_page' === $column ) {
		$page = get_post_meta( $post_id, 'pb_page', true );
		if ( $page ) {
			printf( '<a href="%s" target="_blank">%s</a>', esc_url( home_url( $page ) ), esc_html( $page ) );
		}
	}
}
add_action( 'manage_pb_lead_posts_custom_column', 'powerbachat_lead_column', 10, 2 );

/**
 * Hide "Private" next to each lead.
 *
 * @param array   $states States.
 * @param WP_Post $post   Post.
 * @return array
 */
function powerbachat_lead_states( $states, $post ) {
	if ( 'pb_lead' === $post->post_type ) {
		unset( $states['private'] );
	}
	return $states;
}
add_filter( 'display_post_states', 'powerbachat_lead_states', 10, 2 );

/**
 * REST route for the lead form.
 */
function powerbachat_lead_route() {
	register_rest_route(
		'powerbachat/v1',
		'/lead',
		array(
			'methods'             => 'POST',
			'callback'            => 'powerbachat_rest_lead',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'powerbachat_lead_route' );

/**
 * Save a lead.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function powerbachat_rest_lead( $request ) {
	if ( '' !== trim( (string) $request['website'] ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}
	$contact = sanitize_text_field( (string) $request['contact'] );
	$is_mail = is_email( $contact );
	$digits  = preg_replace( '/[^0-9]/', '', $contact );
	if ( ! $is_mail && ( strlen( $digits ) < 7 || strlen( $digits ) > 15 ) ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => __( 'Please enter an email address or a WhatsApp number.', 'powerbachat' ),
			),
			400
		);
	}
	if ( ! powerbachat_rate_ok( 'lead', 5 ) ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => __( 'Too many attempts. Please try again later.', 'powerbachat' ),
			),
			429
		);
	}
	$page    = '/' . ltrim( wp_parse_url( (string) $request['page'], PHP_URL_PATH ) ?: '', '/' );
	$country = sanitize_key( (string) $request['country'] );
	$id      = wp_insert_post(
		array(
			'post_type'   => 'pb_lead',
			'post_status' => 'private',
			'post_title'  => $contact,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 500 );
	}
	update_post_meta( $id, 'pb_page', sanitize_text_field( $page ) );
	update_post_meta( $id, 'pb_country', in_array( $country, POWERBACHAT_COUNTRIES, true ) ? $country : '' );

	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	wp_mail(
		powerbachat_contact_address(),
		/* translators: %s: site name */
		sprintf( __( '[%s] New website enquiry', 'powerbachat' ), $site ),
		sprintf( "%s\n%s: %s\n%s: %s\n\n%s", $contact, __( 'Page', 'powerbachat' ), home_url( $page ), __( 'Country', 'powerbachat' ), strtoupper( $country ), admin_url( 'edit.php?post_type=pb_lead' ) ),
		array_filter( array( 'Content-Type: text/plain; charset=UTF-8', $is_mail ? 'Reply-To: ' . $contact : '' ) )
	);
	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/**
 * Delete leads older than 12 months.
 */
function powerbachat_leads_cleanup() {
	$old = get_posts(
		array(
			'post_type'      => 'pb_lead',
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'date_query'     => array( array( 'before' => '12 months ago' ) ),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'powerbachat_daily', 'powerbachat_leads_cleanup' );

/**
 * The studio link with campaign tags, so the studio can see where visitors came from.
 *
 * @param string $content utm_content value.
 * @return string
 */
function powerbachat_lead_url( $content = 'popup' ) {
	$url = powerbachat_mod( 'pb_lead_url' );
	if ( ! $url ) {
		return '';
	}
	return add_query_arg(
		array(
			'utm_source'   => 'powerbachat',
			'utm_medium'   => 'referral',
			'utm_campaign' => 'website_lead',
			'utm_content'  => $content,
		),
		$url
	);
}

/**
 * Popup markup, printed in the footer.
 */
function powerbachat_popups() {
	if ( is_admin() ) {
		return;
	}
	if ( powerbachat_mod( 'pb_cookie_banner' ) ) :
		?>
		<div class="consent" id="pb-consent" role="dialog" aria-modal="false" aria-labelledby="pb-consent-title" aria-describedby="pb-consent-text" hidden>
			<div class="consent__head">
				<span class="consent__icon" aria-hidden="true">
					<svg viewBox="0 0 48 48"><path d="M24 4a20 20 0 1 0 19.6 16.1 6 6 0 0 1-7.6-5.8 6 6 0 0 1-6.3-6.3A6 6 0 0 1 24 4Z" fill="#f5b82e"/><circle cx="17" cy="20" r="2.6" fill="#7a5410"/><circle cx="27" cy="30" r="2.6" fill="#7a5410"/><circle cx="16" cy="31" r="1.9" fill="#7a5410"/><circle cx="33" cy="22" r="1.7" fill="#7a5410"/></svg>
				</span>
				<div>
					<p class="consent__title" id="pb-consent-title"><?php esc_html_e( 'A few cookies, if that is okay?', 'powerbachat' ); ?></p>
					<p class="consent__text" id="pb-consent-text">
						<?php esc_html_e( 'We use cookies to keep this site working and, with your okay, to see which pages help people most. You choose what is on.', 'powerbachat' ); ?>
						<a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie policy', 'powerbachat' ); ?></a>
					</p>
				</div>
			</div>
			<div class="consent__options">
				<div class="consent__row">
					<div>
						<p class="consent__name"><?php esc_html_e( 'Essential', 'powerbachat' ); ?></p>
						<p class="consent__desc"><?php esc_html_e( 'Keeps the site, your country and your choices working.', 'powerbachat' ); ?></p>
					</div>
					<span class="consent__always"><?php esc_html_e( 'Always on', 'powerbachat' ); ?></span>
				</div>
				<label class="consent__row">
					<span>
						<span class="consent__name"><?php esc_html_e( 'Analytics', 'powerbachat' ); ?></span>
						<span class="consent__desc"><?php esc_html_e( 'Anonymous stats that show which pages help people most.', 'powerbachat' ); ?></span>
					</span>
					<input class="consent__switch" type="checkbox" data-consent="analytics">
				</label>
				<label class="consent__row">
					<span>
						<span class="consent__name"><?php esc_html_e( 'Marketing', 'powerbachat' ); ?></span>
						<span class="consent__desc"><?php esc_html_e( 'Measure ads and show more relevant offers.', 'powerbachat' ); ?></span>
					</span>
					<input class="consent__switch" type="checkbox" data-consent="marketing">
				</label>
			</div>
			<div class="consent__actions">
				<button type="button" class="btn btn--volt" data-consent-action="all"><?php esc_html_e( 'Accept all', 'powerbachat' ); ?></button>
				<button type="button" class="btn consent__ghost" data-consent-action="essential"><?php esc_html_e( 'Essential only', 'powerbachat' ); ?></button>
				<button type="button" class="btn consent__ghost" data-consent-action="save"><?php esc_html_e( 'Save choices', 'powerbachat' ); ?></button>
			</div>
		</div>
		<?php
	endif;

	$url = powerbachat_lead_url( 'popup' );
	if ( powerbachat_mod( 'pb_lead_popup' ) && $url && ! is_page( 'contact-us' ) ) :
		$studio = powerbachat_mod( 'pb_credit_name' ) ? powerbachat_mod( 'pb_credit_name' ) : 'Vyntic Studio';
		?>
		<div class="lead" id="pb-lead" role="dialog" aria-modal="true" aria-labelledby="pb-lead-title" hidden
			data-delay="<?php echo esc_attr( max( 10, (int) powerbachat_mod( 'pb_lead_delay' ) ) ); ?>"
			data-every="<?php echo esc_attr( max( 1, (int) powerbachat_mod( 'pb_lead_every' ) ) ); ?>"
			data-url="<?php echo esc_url( $url ); ?>"
			data-thanks="<?php esc_attr_e( 'Thank you. We have opened our website in a new tab and will be in touch within 24 hours.', 'powerbachat' ); ?>"
			data-invalid="<?php esc_attr_e( 'Please enter an email address or a WhatsApp number.', 'powerbachat' ); ?>">
			<div class="lead__backdrop" data-lead-close></div>
			<div class="lead__box">
				<button type="button" class="lead__close" data-lead-close aria-label="<?php esc_attr_e( 'Close', 'powerbachat' ); ?>"><?php echo powerbachat_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<div class="lead__art" aria-hidden="true">
					<div class="lead__browser">
						<div class="lead__bar"><i></i><i></i><i></i><span>yourbusiness.com</span></div>
						<div class="lead__page">
							<span class="lead__line lead__line--title"></span>
							<span class="lead__line"></span>
							<span class="lead__line lead__line--short"></span>
							<span class="lead__btn"></span>
							<div class="lead__cards"><span></span><span></span><span></span></div>
						</div>
					</div>
					<div class="lead__score">
						<svg viewBox="0 0 120 120"><circle cx="60" cy="60" r="50" class="lead__ring-bg"/><circle cx="60" cy="60" r="50" class="lead__ring"/></svg>
						<strong>98</strong><small>/100</small>
					</div>
					<ul class="lead__stats">
						<li><span><?php esc_html_e( 'Speed', 'powerbachat' ); ?></span><b style="--w:96%"></b></li>
						<li><span><?php esc_html_e( 'SEO', 'powerbachat' ); ?></span><b style="--w:92%"></b></li>
						<li><span><?php esc_html_e( 'Mobile', 'powerbachat' ); ?></span><b style="--w:98%"></b></li>
						<li><span><?php esc_html_e( 'Leads', 'powerbachat' ); ?></span><b style="--w:88%"></b></li>
					</ul>
					<p class="lead__chip"><?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Built like this site', 'powerbachat' ); ?></p>
				</div>
				<div class="lead__body">
					<p class="lead__pill"><?php echo powerbachat_icon( 'bolt' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( sprintf( /* translators: %s: studio name */ __( 'Made by %s', 'powerbachat' ), $studio ) ); ?></p>
					<h2 class="lead__title" id="pb-lead-title"><?php echo esc_html( powerbachat_mod( 'pb_lead_title' ) ); ?></h2>
					<p class="lead__text"><?php echo esc_html( powerbachat_mod( 'pb_lead_text' ) ); ?></p>
					<form class="lead__form" data-lead-form novalidate>
						<label class="screen-reader-text" for="pb-lead-contact"><?php esc_html_e( 'Email or WhatsApp number', 'powerbachat' ); ?></label>
						<div class="lead__field">
							<?php echo powerbachat_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<input id="pb-lead-contact" name="contact" type="text" inputmode="email" autocomplete="email" placeholder="<?php esc_attr_e( 'Email or WhatsApp number', 'powerbachat' ); ?>" required>
						</div>
						<span class="contact-form__trap" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></span>
						<button type="submit" class="btn lead__cta"><?php esc_html_e( 'Get a free quote', 'powerbachat' ); ?> <?php echo powerbachat_icon( 'arrow-ne' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
						<p class="lead__status" data-lead-status role="status" aria-live="polite" hidden></p>
					</form>
					<ul class="lead__ticks">
						<li><?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Free consultation', 'powerbachat' ); ?></li>
						<li><?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Reply within 24 hours', 'powerbachat' ); ?></li>
						<li><?php echo powerbachat_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'No obligation', 'powerbachat' ); ?></li>
					</ul>
					<p class="lead__alt"><?php esc_html_e( 'Just looking?', 'powerbachat' ); ?> <a href="<?php echo esc_url( powerbachat_lead_url( 'popup_work' ) ); ?>" target="_blank" rel="noopener" data-lead-visit><?php esc_html_e( 'See our work', 'powerbachat' ); ?></a></p>
				</div>
			</div>
		</div>
		<?php
	endif;
}
add_action( 'wp_footer', 'powerbachat_popups', 5 );
