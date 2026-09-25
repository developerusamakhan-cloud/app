<?php
/**
 * Appearance → Nabia Setup: one click to create the service pages, pricing, free audit
 * page and a main menu. Existing pages are never overwritten.
 *
 * @package Nabia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the setup screen.
 */
function nabia_setup_menu() {
	add_theme_page( __( 'Nabia Setup', 'nabia' ), __( 'Nabia Setup', 'nabia' ), 'edit_pages', 'nabia-setup', 'nabia_setup_screen' );
}
add_action( 'admin_menu', 'nabia_setup_menu' );

/**
 * Pages the setup creates.
 *
 * @return array[] Each: role, title, slug, template, parent (role), service (slug).
 */
function nabia_setup_pages() {
	$pages = array(
		array(
			'role'     => 'services',
			'title'    => __( 'Services', 'nabia' ),
			'slug'     => 'services',
			'template' => 'template-services.php',
		),
	);
	foreach ( nabia_services() as $service ) {
		$pages[] = array(
			'role'     => 'service-' . $service['slug'],
			'title'    => $service['title'],
			'slug'     => $service['slug'],
			'template' => 'template-service.php',
			'parent'   => 'services',
			'service'  => $service['slug'],
		);
	}
	$pages[] = array(
		'role'     => 'pricing',
		'title'    => __( 'Pricing', 'nabia' ),
		'slug'     => 'pricing',
		'template' => 'template-pricing.php',
	);
	$pages[] = array(
		'role'     => 'audit',
		'title'    => __( 'Free Website Audit', 'nabia' ),
		'slug'     => 'free-website-audit',
		'template' => 'template-audit.php',
	);
	return $pages;
}

/**
 * Find a page created by the setup.
 *
 * @param string $role Page role.
 * @return WP_Post|null
 */
function nabia_setup_find( $role ) {
	$found = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => '_nabia_page', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $role, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return $found ? $found[0] : null;
}

/**
 * Create missing pages.
 *
 * @return array Log lines.
 */
function nabia_setup_create_pages() {
	$log = array();
	$ids = array();

	foreach ( nabia_setup_pages() as $page ) {
		$existing = nabia_setup_find( $page['role'] );
		if ( $existing ) {
			$ids[ $page['role'] ] = $existing->ID;
			/* translators: %s: page title */
			$log[] = sprintf( __( 'Already there: %s', 'nabia' ), $page['title'] );
			continue;
		}

		$parent = ( ! empty( $page['parent'] ) && isset( $ids[ $page['parent'] ] ) ) ? $ids[ $page['parent'] ] : 0;

		// Don't clash with a page you made yourself at the same address.
		$path = $parent ? get_page_uri( $parent ) . '/' . $page['slug'] : $page['slug'];
		if ( get_page_by_path( $path ) ) {
			/* translators: %s: page address */
			$log[] = sprintf( __( 'Skipped: a page already exists at /%s/', 'nabia' ), $path );
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $page['title'],
				'post_name'   => $page['slug'],
				'post_parent' => $parent,
				'menu_order'  => count( $ids ),
			)
		);
		if ( is_wp_error( $id ) || ! $id ) {
			/* translators: %s: page title */
			$log[] = sprintf( __( 'Could not create: %s', 'nabia' ), $page['title'] );
			continue;
		}
		update_post_meta( $id, '_wp_page_template', $page['template'] );
		update_post_meta( $id, '_nabia_page', $page['role'] );
		if ( ! empty( $page['service'] ) ) {
			update_post_meta( $id, '_nabia_service', $page['service'] );
		}
		$ids[ $page['role'] ] = $id;
		/* translators: %s: page title */
		$log[] = sprintf( __( 'Created: %s', 'nabia' ), $page['title'] );
	}

	return $log;
}

/**
 * Build a "Main menu" with a Services dropdown and assign it to the header.
 *
 * @return string Result message.
 */
function nabia_setup_create_menu() {
	$name = __( 'Nabia main menu', 'nabia' );
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}
	$menu_id = wp_create_nav_menu( $name );
	if ( is_wp_error( $menu_id ) ) {
		return $menu_id->get_error_message();
	}

	$add = function ( $title, $args ) use ( $menu_id ) {
		return wp_update_nav_menu_item(
			$menu_id,
			0,
			array_merge(
				array(
					'menu-item-title'  => $title,
					'menu-item-status' => 'publish',
				),
				$args
			)
		);
	};
	$page_item = function ( $role, $title, $parent = 0 ) use ( $add ) {
		$page = nabia_setup_find( $role );
		if ( ! $page ) {
			return 0;
		}
		return $add(
			$title,
			array(
				'menu-item-object-id' => $page->ID,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-parent-id' => $parent,
			)
		);
	};
	$link = function ( $title, $url ) use ( $add ) {
		return $add(
			$title,
			array(
				'menu-item-url'  => $url,
				'menu-item-type' => 'custom',
			)
		);
	};

	$services = $page_item( 'services', __( 'Services', 'nabia' ) );
	if ( $services ) {
		foreach ( nabia_services() as $service ) {
			$page_item( 'service-' . $service['slug'], $service['title'], $services );
		}
	}
	$link( __( 'Work', 'nabia' ), nabia_portfolio_url() );
	$page_item( 'pricing', __( 'Pricing', 'nabia' ) );
	$link( __( 'About', 'nabia' ), home_url( '/#about' ) );
	$page_item( 'audit', __( 'Free audit', 'nabia' ) );
	$link( __( 'Contact', 'nabia' ), home_url( '/#contact' ) );

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return __( 'Main menu created and shown in the header.', 'nabia' );
}

/**
 * Render the setup screen and handle its buttons.
 */
function nabia_setup_screen() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$log = array();
	if ( isset( $_POST['nabia_setup_action'] ) && check_admin_referer( 'nabia_setup' ) ) {
		$action = sanitize_key( wp_unslash( $_POST['nabia_setup_action'] ) );
		if ( 'pages' === $action || 'all' === $action ) {
			$log = array_merge( $log, nabia_setup_create_pages() );
		}
		if ( ( 'menu' === $action || 'all' === $action ) && current_user_can( 'edit_theme_options' ) ) {
			$log[] = nabia_setup_create_menu();
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Nabia Setup', 'nabia' ); ?></h1>
		<p><?php esc_html_e( 'Create your service pages, pricing page and free audit page in one click. Each page is filled automatically with the theme’s content, and you can add your own text in the page editor too. Existing pages are never changed.', 'nabia' ); ?></p>

		<?php if ( $log ) : ?>
			<div class="notice notice-success"><ul style="list-style:disc;padding-left:20px">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul></div>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:760px;margin:20px 0">
			<thead><tr><th><?php esc_html_e( 'Page', 'nabia' ); ?></th><th><?php esc_html_e( 'Status', 'nabia' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( nabia_setup_pages() as $page ) : ?>
					<?php $existing = nabia_setup_find( $page['role'] ); ?>
					<tr>
						<td><?php echo ! empty( $page['parent'] ) ? '&nbsp;&nbsp;&nbsp;&#8627; ' : ''; ?><?php echo esc_html( $page['title'] ); ?></td>
						<td>
							<?php if ( $existing ) : ?>
								<a href="<?php echo esc_url( get_permalink( $existing ) ); ?>" target="_blank"><?php esc_html_e( 'View', 'nabia' ); ?></a> |
								<a href="<?php echo esc_url( get_edit_post_link( $existing ) ); ?>"><?php esc_html_e( 'Edit', 'nabia' ); ?></a>
							<?php else : ?>
								<em><?php esc_html_e( 'Not created yet', 'nabia' ); ?></em>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" style="display:flex;gap:10px;flex-wrap:wrap">
			<?php wp_nonce_field( 'nabia_setup' ); ?>
			<button class="button button-primary button-hero" name="nabia_setup_action" value="all"><?php esc_html_e( 'Create pages + main menu', 'nabia' ); ?></button>
			<button class="button button-hero" name="nabia_setup_action" value="pages"><?php esc_html_e( 'Create pages only', 'nabia' ); ?></button>
			<button class="button button-hero" name="nabia_setup_action" value="menu"><?php esc_html_e( 'Rebuild main menu only', 'nabia' ); ?></button>
		</form>
		<p class="description" style="margin-top:12px"><?php esc_html_e( 'The menu adds: Services (with every service in a dropdown), Work, Pricing, About, Free audit and Contact. You can change it any time in Appearance → Menus.', 'nabia' ); ?></p>
	</div>
	<?php
}
