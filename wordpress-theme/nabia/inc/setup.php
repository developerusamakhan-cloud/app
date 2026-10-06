<?php
/**
 * Appearance → Nabia Setup: one click to create the service pages, pricing, free audit
 * page, the ready-made blog articles and a main menu. Existing pages are never overwritten.
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
 * A setup page that already exists: made by the setup, or your own page at the usual address.
 *
 * @param array $page Page from nabia_setup_pages().
 * @return WP_Post|null
 */
function nabia_setup_locate( $page ) {
	$found = nabia_setup_find( $page['role'] );
	if ( $found ) {
		return $found;
	}
	if ( ! empty( $page['service'] ) ) {
		return nabia_service_page_by_path( $page['service'] );
	}
	$paths = array(
		'services' => array( 'services' ),
		'pricing'  => array( 'pricing', 'prices' ),
		'audit'    => array( 'free-website-audit', 'website-audit', 'audit' ),
	);
	foreach ( isset( $paths[ $page['role'] ] ) ? $paths[ $page['role'] ] : array( $page['slug'] ) as $path ) {
		$own = get_page_by_path( $path );
		if ( $own && 'trash' !== $own->post_status ) {
			return $own;
		}
	}
	return null;
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
		$existing = nabia_setup_locate( $page );
		if ( $existing ) {
			$ids[ $page['role'] ] = $existing->ID;
			/* translators: 1: page title, 2: address */
			$log[] = sprintf( __( 'Already there: %1$s (/%2$s/)', 'nabia' ), $page['title'], get_page_uri( $existing ) );
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
	if ( (int) get_option( 'page_for_posts' ) ) {
		$add(
			__( 'Blog', 'nabia' ),
			array(
				'menu-item-object-id' => (int) get_option( 'page_for_posts' ),
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
			)
		);
	}
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
		if ( in_array( $action, array( 'pages', 'all', 'seo' ), true ) ) {
			$log[] = nabia_setup_fill_seo();
		}
		if ( ( 'shots' === $action || 'shots_force' === $action ) && current_user_can( 'upload_files' ) ) {
			$log[] = nabia_shots_enabled() ? nabia_shots_run_all( 'shots_force' === $action ) : __( 'Automatic screenshots are off. Turn them on in Customize → Nabia Theme → Portfolio → Project thumbnails.', 'nabia' );
		}
		if ( ( 'posts' === $action || 'all' === $action ) && current_user_can( 'publish_posts' ) && current_user_can( 'upload_files' ) ) {
			$log = array_merge( $log, nabia_setup_import_posts() );
		}
		if ( ( 'menu' === $action || 'all' === $action ) && current_user_can( 'edit_theme_options' ) ) {
			$log[] = nabia_setup_create_menu();
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Nabia Setup', 'nabia' ); ?></h1>
		<p><?php esc_html_e( 'Create your service pages, pricing page, free audit page and the ready-made blog articles in one click. Each page is filled automatically with the theme’s content, and you can add your own text in the page editor too. Existing pages are never changed.', 'nabia' ); ?></p>

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
					<?php $existing = nabia_setup_locate( $page ); ?>
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

		<h2><?php esc_html_e( 'Blog articles', 'nabia' ); ?></h2>
		<p><?php esc_html_e( 'Articles written around what people search for most about websites, each linked to the matching service page, with a cover image, FAQ and SEO details. Articles you have edited are never overwritten.', 'nabia' ); ?></p>
		<table class="widefat striped" style="max-width:760px;margin:12px 0 20px">
			<thead><tr><th><?php esc_html_e( 'Article', 'nabia' ); ?></th><th><?php esc_html_e( 'Search term', 'nabia' ); ?></th><th><?php esc_html_e( 'Status', 'nabia' ); ?></th></tr></thead>
			<tbody>
				<?php $nabia_status = nabia_blog_status(); ?>
				<?php foreach ( nabia_blog_library() as $nabia_item ) : ?>
					<?php list( $nabia_post, $nabia_state ) = $nabia_status[ $nabia_item['slug'] ]; ?>
					<tr>
						<td><?php echo esc_html( $nabia_item['title'] ); ?></td>
						<td><code><?php echo esc_html( $nabia_item['keyword'] ); ?></code></td>
						<td>
							<?php if ( $nabia_post ) : ?>
								<a href="<?php echo esc_url( get_permalink( $nabia_post ) ); ?>" target="_blank"><?php esc_html_e( 'View', 'nabia' ); ?></a> |
								<a href="<?php echo esc_url( get_edit_post_link( $nabia_post ) ); ?>"><?php esc_html_e( 'Edit', 'nabia' ); ?></a>
								<?php echo 'edited' === $nabia_state ? '<em>(' . esc_html__( 'your version', 'nabia' ) . ')</em>' : ''; ?>
							<?php else : ?>
								<em><?php esc_html_e( 'Not imported yet', 'nabia' ); ?></em>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" style="display:flex;gap:10px;flex-wrap:wrap">
			<?php wp_nonce_field( 'nabia_setup' ); ?>
			<button class="button button-primary button-hero" name="nabia_setup_action" value="all"><?php esc_html_e( 'Create pages + articles + main menu', 'nabia' ); ?></button>
			<button class="button button-hero" name="nabia_setup_action" value="pages"><?php esc_html_e( 'Create pages only', 'nabia' ); ?></button>
			<button class="button button-hero" name="nabia_setup_action" value="posts"><?php esc_html_e( 'Import blog articles only', 'nabia' ); ?></button>
			<button class="button button-hero" name="nabia_setup_action" value="menu"><?php esc_html_e( 'Rebuild main menu only', 'nabia' ); ?></button>
			<button class="button button-hero" name="nabia_setup_action" value="seo"><?php esc_html_e( 'Fill in missing SEO titles & descriptions', 'nabia' ); ?></button>
		</form>
		<p class="description" style="margin-top:12px"><?php esc_html_e( 'The menu adds: Services (with every service in a dropdown), Work, Pricing, Blog, About, Free audit and Contact. You can change it any time in Appearance → Menus.', 'nabia' ); ?></p>

		<h2 style="margin-top:36px"><?php esc_html_e( 'Portfolio thumbnails', 'nabia' ); ?></h2>
		<p><?php esc_html_e( 'Every project card shows a desktop and phone mockup with real screenshots of the project’s live link. Screenshots are taken in the background and saved on your site; new ones can take a few minutes to appear.', 'nabia' ); ?></p>
		<table class="widefat striped" style="max-width:760px;margin:12px 0 20px">
			<thead><tr><th><?php esc_html_e( 'Project', 'nabia' ); ?></th><th><?php esc_html_e( 'Live link', 'nabia' ); ?></th><th><?php esc_html_e( 'Desktop', 'nabia' ); ?></th><th><?php esc_html_e( 'Phone', 'nabia' ); ?></th></tr></thead>
			<tbody>
				<?php
				$nabia_states = array(
					'ready'    => '✓ ' . __( 'Sharp', 'nabia' ),
					'updating' => __( 'Updating (older, softer version shown)', 'nabia' ),
					'waiting'  => __( 'Waiting (featured image shown)', 'nabia' ),
					'failed'   => __( 'Website blocks screenshots (featured image shown)', 'nabia' ),
					'none'     => __( 'Not needed', 'nabia' ),
				);
				?>
				<?php foreach ( nabia_shots_status() as $nabia_row ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $nabia_row['post'] ) ); ?>"><?php echo esc_html( get_the_title( $nabia_row['post'] ) ); ?></a></td>
						<td><?php echo $nabia_row['live'] ? esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $nabia_row['live'] ) ) ) : '<em>' . esc_html__( 'No live link: add one to get a screenshot', 'nabia' ) . '</em>'; ?></td>
						<?php foreach ( array( 'desktop', 'mobile' ) as $nabia_device ) : ?>
							<td><?php echo esc_html( $nabia_states[ $nabia_row[ $nabia_device ] ] ); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<form method="post" style="display:flex;gap:10px;flex-wrap:wrap">
			<?php wp_nonce_field( 'nabia_setup' ); ?>
			<button class="button button-primary" name="nabia_setup_action" value="shots"><?php esc_html_e( 'Take missing screenshots now', 'nabia' ); ?></button>
			<button class="button" name="nabia_setup_action" value="shots_force"><?php esc_html_e( 'Retake all screenshots', 'nabia' ); ?></button>
		</form>
	</div>
	<?php
}

/**
 * Search titles, meta descriptions and focus keywords for the pages the setup creates,
 * written around the search terms people use most for each service.
 *
 * @return array role => array( title, description, keyword )
 */
function nabia_setup_seo() {
	$seo = array(
		'services'                        => array( __( 'Web Design & Development Services', 'nabia' ), __( 'Web design and development services for small businesses: WordPress, Shopify, WooCommerce, custom code, logos, SEO and monthly website maintenance.', 'nabia' ), 'web design services' ),
		'pricing'                         => array( __( 'Website Design Pricing & Maintenance Plans', 'nabia' ), __( 'Fixed website design prices and monthly maintenance plans, with nothing hidden. See exactly what each package includes and what it costs.', 'nabia' ), 'website design pricing' ),
		'audit'                           => array( __( 'Free Website Audit: SEO & Speed Score', 'nabia' ), __( 'Get a free website audit in about 60 seconds: scores for design, SEO, content and speed, plus a PDF report with clear, practical fixes.', 'nabia' ), 'free website audit' ),
		'service-web-design'              => array( __( 'Small Business Website Design Services', 'nabia' ), __( 'Custom small business website design that looks professional, works on every phone and turns visitors into enquiries. Fixed prices, clickable prototype first.', 'nabia' ), 'small business website design' ),
		'service-wordpress-development'   => array( __( 'Hire a WordPress Developer and Expert', 'nabia' ), __( 'Freelance WordPress developer and expert: custom themes, Elementor builds, plugin setup, fixes and speed. Fast, secure sites you can edit yourself.', 'nabia' ), 'wordpress developer' ),
		'service-shopify-woocommerce'     => array( __( 'Shopify & WooCommerce Development', 'nabia' ), __( 'Shopify and WooCommerce development for online stores that sell: store setup, custom features, payments, shipping, speed and secure checkout.', 'nabia' ), 'shopify development' ),
		'service-wix-webflow-squarespace' => array( __( 'Wix, Squarespace & Webflow Website Design', 'nabia' ), __( 'Wix, Squarespace and Webflow website design that looks custom, not like a template, set up so you can edit everything yourself.', 'nabia' ), 'wix website design' ),
		'service-custom-websites'         => array( __( 'Custom Website Development', 'nabia' ), __( 'Custom website development in HTML, CSS, JavaScript and PHP when you need unique features, top speed or a design no template can deliver.', 'nabia' ), 'custom website development' ),
		'service-ai-website-solutions'    => array( __( 'AI Chatbot for Your Website & AI Solutions', 'nabia' ), __( 'Add an AI chatbot to your website, trained on your own content, plus smart forms and automations that answer customers and save hours every week.', 'nabia' ), 'ai chatbot for website' ),
		'service-branding-graphic-design' => array( __( 'Logo Design Services & Brand Identity', 'nabia' ), __( 'Logo design services and brand identity design: original logos, colours, fonts and social media kits that look consistent everywhere.', 'nabia' ), 'logo design services' ),
		'service-speed-seo'               => array( __( 'WordPress Speed Optimization & SEO', 'nabia' ), __( 'WordPress speed optimization and technical SEO for small businesses: faster pages, better Core Web Vitals and a before and after report.', 'nabia' ), 'wordpress speed optimization' ),
		'service-website-maintenance'     => array( __( 'Website Maintenance & WordPress Care Plans', 'nabia' ), __( 'Website maintenance and WordPress care plans: updates, backups, security, speed checks and small edits every month. No long contract.', 'nabia' ), 'website maintenance' ),
		'service-church-websites'         => array( __( 'Church Website Design & Nonprofit Websites', 'nabia' ), __( 'Church website design and nonprofit websites that welcome first-time visitors, make giving simple and are easy for volunteers to update.', 'nabia' ), 'church website design' ),
	);
	return apply_filters( 'nabia_setup_seo', $seo );
}

/**
 * Fill in SEO title, description and focus keyword for setup pages where they are empty.
 * Works with Rank Math and Yoast; your own text is never replaced.
 *
 * @return string Log line.
 */
function nabia_setup_fill_seo() {
	$filled = 0;
	$pages  = array();
	foreach ( nabia_setup_pages() as $setup_page ) {
		$pages[ $setup_page['role'] ] = $setup_page;
	}
	foreach ( nabia_setup_seo() as $role => $meta ) {
		$page = isset( $pages[ $role ] ) ? nabia_setup_locate( $pages[ $role ] ) : null;
		if ( ! $page ) {
			continue;
		}
		list( $title, $desc, $keyword ) = $meta;
		$fields = array(
			'rank_math_title'         => $title . ' %sep% %sitename%',
			'rank_math_description'   => $desc,
			'rank_math_focus_keyword' => $keyword,
			'_yoast_wpseo_title'      => $title . ' %%sep%% %%sitename%%',
			'_yoast_wpseo_metadesc'   => $desc,
			'_yoast_wpseo_focuskw'    => $keyword,
		);
		$changed = false;
		foreach ( $fields as $key => $value ) {
			if ( '' === trim( (string) get_post_meta( $page->ID, $key, true ) ) ) {
				update_post_meta( $page->ID, $key, $value );
				$changed = true;
			}
		}
		$filled += $changed ? 1 : 0;
	}
	/* translators: %d: number of pages */
	return sprintf( _n( 'SEO title, description and focus keyword filled in on %d page (only where they were empty).', 'SEO title, description and focus keyword filled in on %d pages (only where they were empty).', $filled, 'nabia' ), $filled );
}

/**
 * One-time fix for articles imported before the Church & Nonprofit service existed:
 * connect them to that service so its page shows them as related articles.
 */
function nabia_retag_church_articles() {
	if ( get_option( 'nabia_church_retag' ) ) {
		return;
	}
	$slugs = array( 'church-website-design', 'church-websites', 'what-should-a-church-website-include', 'how-to-make-a-church-website', 'nonprofit-website-design' );
	foreach ( $slugs as $slug ) {
		foreach ( array( '_nbi_article', '_nabia_article' ) as $key ) {
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'post_status'    => array( 'publish', 'future', 'draft' ),
					'posts_per_page' => 5,
					'meta_key'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'     => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery
					'fields'         => 'ids',
				)
			);
			foreach ( $posts as $id ) {
				update_post_meta( $id, '_nabia_service', 'church-websites' );
			}
		}
	}
	update_option( 'nabia_church_retag', 1, false );
}
add_action( 'admin_init', 'nabia_retag_church_articles' );
