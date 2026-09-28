<?php
/**
 * Appearance > ClaimFairly Setup: one-click import of every page, guide,
 * state draft, category and menu from the Markdown files in /content.
 *
 * Safe to run more than once: existing pages are skipped unless you tick
 * "update", and even then only pages you have not edited since import
 * are touched.
 *
 * @package ClaimFairly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page.
 */
function claimfairly_setup_menu() {
	add_theme_page(
		__( 'ClaimFairly Setup', 'claimfairly' ),
		__( 'ClaimFairly Setup', 'claimfairly' ),
		'edit_theme_options',
		'claimfairly-setup',
		'claimfairly_setup_page'
	);
}
add_action( 'admin_menu', 'claimfairly_setup_menu' );

/**
 * Nudge admins to run setup right after activating the theme.
 */
function claimfairly_setup_notice() {
	if ( get_option( 'claimfairly_imported' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_claimfairly-setup' === $screen->id ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'ClaimFairly theme is active.', 'claimfairly' ) . '</strong> ' . esc_html__( 'Import all pages, guides and menus in one click:', 'claimfairly' ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=claimfairly-setup' ) ) . '">' . esc_html__( 'Run ClaimFairly Setup', 'claimfairly' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'claimfairly_setup_notice' );

/**
 * Render the setup screen and handle the form.
 */
function claimfairly_setup_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$report = null;
	if ( isset( $_POST['claimfairly_setup_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['claimfairly_setup_nonce'] ) ), 'claimfairly_setup' ) ) {
		$report = claimfairly_run_import( ! empty( $_POST['cf_update'] ) );
	}
	$plugin_ok = function_exists( 'cft_states' );
	$files     = claimfairly_content_files();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ClaimFairly Setup', 'claimfairly' ); ?></h1>
		<p style="max-width:720px"><?php esc_html_e( 'This creates every page from your plan: the 6 trust pages, 5 tool pages, 12 guides, settlement amount pages, injury and insurer pages, the States hub with 40 state pages (as drafts, so you can verify each one before publishing), categories, all four menus, the homepage and the guides page.', 'claimfairly' ); ?></p>

		<?php if ( ! $plugin_ok ) : ?>
			<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Activate the ClaimFairly Tools plugin first.', 'claimfairly' ); ?></strong> <?php esc_html_e( 'The tool pages need it, and the 40 state pages are built from its state data.', 'claimfairly' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $report ) : ?>
			<div class="notice notice-success inline">
				<p><strong><?php esc_html_e( 'Done.', 'claimfairly' ); ?></strong>
				<?php
				printf(
					/* translators: 1: created, 2: updated, 3: skipped. */
					esc_html__( '%1$d created, %2$d updated, %3$d left as they were.', 'claimfairly' ),
					(int) $report['created'],
					(int) $report['updated'],
					(int) $report['skipped']
				);
				?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View the site', 'claimfairly' ); ?></a></p>
				<?php if ( $report['notes'] ) : ?>
					<ul style="list-style:disc;padding-left:20px"><?php foreach ( $report['notes'] as $note ) : ?><li><?php echo esc_html( $note ); ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:20px">
			<?php wp_nonce_field( 'claimfairly_setup', 'claimfairly_setup_nonce' ); ?>
			<p><label><input type="checkbox" name="cf_update" value="1"> <?php esc_html_e( 'Also refresh pages that came from this importer and that you have not edited since.', 'claimfairly' ); ?></label></p>
			<p><button class="button button-primary button-hero"><?php esc_html_e( 'Import everything', 'claimfairly' ); ?></button></p>
		</form>

		<h2><?php esc_html_e( 'After importing', 'claimfairly' ); ?></h2>
		<ol style="max-width:720px">
			<li><?php esc_html_e( 'Users > Profile: set your real display name and a short bio. The author box and About page use them.', 'claimfairly' ); ?></li>
			<li><?php esc_html_e( 'Appearance > Customize > ClaimFairly settings: add your photo for the homepage founder note.', 'claimfairly' ); ?></li>
			<li><?php esc_html_e( 'Pages > States: open each draft, check the facts box against the official source, then publish. Start with the first 10.', 'claimfairly' ); ?></li>
			<li><?php esc_html_e( 'Install Rank Math or Yoast (one only) for titles, meta descriptions and the sitemap. The SEO title and description for every page are already written in each page\'s excerpt.', 'claimfairly' ); ?></li>
		</ol>
		<p><?php
		/* translators: %d: number of files. */
		printf( esc_html__( '%d content files found in the theme.', 'claimfairly' ), count( $files ) );
		?></p>
	</div>
	<?php
}

/**
 * All Markdown content files.
 *
 * @return string[]
 */
function claimfairly_content_files() {
	$files = array();
	$dir   = CLAIMFAIRLY_DIR . '/content';
	if ( ! is_dir( $dir ) ) {
		return $files;
	}
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $file ) {
		if ( 'md' === strtolower( $file->getExtension() ) ) {
			$files[] = $file->getPathname();
		}
	}
	sort( $files );
	return $files;
}

/**
 * Split a Markdown file into front matter and body.
 *
 * Front matter is a small YAML subset: "key: value" and lists of "- item".
 *
 * @param string $raw File contents.
 * @return array{meta:array,body:string}
 */
function claimfairly_parse_content_file( $raw ) {
	$raw  = str_replace( "\r\n", "\n", $raw );
	$meta = array();
	$body = $raw;
	if ( 0 === strpos( $raw, "---\n" ) ) {
		$end = strpos( $raw, "\n---\n", 4 );
		if ( false !== $end ) {
			$head = substr( $raw, 4, $end - 4 );
			$body = substr( $raw, $end + 5 );
			$key  = null;
			foreach ( explode( "\n", $head ) as $line ) {
				if ( preg_match( '/^\s+-\s+(.*)$/', $line, $m ) && $key ) {
					if ( ! is_array( $meta[ $key ] ) ) {
						$meta[ $key ] = array();
					}
					$meta[ $key ][] = trim( $m[1] );
				} elseif ( preg_match( '/^([a-z_]+):\s*(.*)$/', $line, $m ) ) {
					$key          = $m[1];
					$value        = trim( $m[2] );
					$meta[ $key ] = ( '' === $value ) ? array() : trim( $value, '"' );
				}
			}
		}
	}
	return array(
		'meta' => $meta,
		'body' => trim( $body ),
	);
}

/**
 * Inline Markdown: links, bold, italic, code. Input is escaped first.
 *
 * @param string $text Text.
 * @return string
 */
function claimfairly_md_inline( $text ) {
	$text = esc_html( $text );
	$text = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $text );
	$text = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text );
	$text = preg_replace( '/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/', '<em>$1</em>', $text );
	$text = preg_replace_callback(
		'/\[([^\]]+)\]\(([^)\s]+)\)/',
		static function ( $m ) {
			$url = html_entity_decode( $m[2] );
			if ( 0 === strpos( $url, '/' ) ) {
				$url = home_url( $url );
			}
			$external = 0 !== strpos( $url, home_url() ) && 0 !== strpos( $url, 'mailto:' );
			return '<a href="' . esc_url( $url ) . '"' . ( $external ? ' target="_blank" rel="noopener"' : '' ) . '>' . $m[1] . '</a>';
		},
		$text
	);
	return $text;
}

/**
 * Convert the Markdown subset used in /content into block editor markup.
 *
 * Supports: ## and ### headings, paragraphs, - and 1. lists, > quotes,
 * pipe tables, and shortcode blocks (a paragraph that starts with "[cf_").
 *
 * @param string $md Markdown.
 * @return string
 */
function claimfairly_md_to_blocks( $md ) {
	$md     = claimfairly_fill_placeholders( $md );
	$chunks = preg_split( "/\n{2,}/", trim( $md ) );
	$out    = array();

	// Re-join shortcode enclosures that contain blank lines ([cf_faq] ... [/cf_faq]).
	$merged = array();
	$open   = null;
	foreach ( $chunks as $chunk ) {
		if ( null !== $open ) {
			$open .= "\n\n" . $chunk;
			if ( false !== strpos( $chunk, '[/cf_faq]' ) ) {
				$merged[] = $open;
				$open     = null;
			}
			continue;
		}
		if ( 0 === strpos( ltrim( $chunk ), '[cf_faq' ) && false === strpos( $chunk, '[/cf_faq]' ) ) {
			$open = $chunk;
			continue;
		}
		$merged[] = $chunk;
	}
	if ( null !== $open ) {
		$merged[] = $open;
	}

	foreach ( $merged as $chunk ) {
		$chunk = trim( $chunk );
		if ( '' === $chunk ) {
			continue;
		}
		$lines = explode( "\n", $chunk );

		if ( 0 === strpos( $chunk, '[cf_' ) ) {
			$out[] = "<!-- wp:shortcode -->\n" . $chunk . "\n<!-- /wp:shortcode -->";
		} elseif ( preg_match( '/^(#{2,4})\s+(.+)$/', $chunk, $m ) && 1 === count( $lines ) ) {
			$level = strlen( $m[1] );
			$attrs = 2 === $level ? '' : ' {"level":' . $level . '}';
			$out[] = '<!-- wp:heading' . $attrs . " -->\n<h" . $level . ' class="wp-block-heading">' . claimfairly_md_inline( $m[2] ) . '</h' . $level . ">\n<!-- /wp:heading -->";
		} elseif ( preg_match( '/^(\-|\d+\.)\s/', $lines[0] ) ) {
			$ordered = (bool) preg_match( '/^\d+\./', $lines[0] );
			$items   = array();
			foreach ( $lines as $line ) {
				if ( preg_match( '/^(?:\-|\d+\.)\s+(.*)$/', $line, $m ) ) {
					$items[] = $m[1];
				} elseif ( $items ) {
					$items[ count( $items ) - 1 ] .= ' ' . trim( $line );
				}
			}
			$tag  = $ordered ? 'ol' : 'ul';
			$html = '<!-- wp:list' . ( $ordered ? ' {"ordered":true}' : '' ) . " -->\n<" . $tag . ' class="wp-block-list">';
			foreach ( $items as $item ) {
				$html .= "<!-- wp:list-item -->\n<li>" . claimfairly_md_inline( $item ) . "</li>\n<!-- /wp:list-item -->";
			}
			$out[] = $html . '</' . $tag . ">\n<!-- /wp:list -->";
		} elseif ( 0 === strpos( $chunk, '|' ) && count( $lines ) >= 3 ) {
			$rows = array();
			foreach ( $lines as $i => $line ) {
				if ( 1 === $i && preg_match( '/^\|[\s:\-|]+\|?$/', $line ) ) {
					continue;
				}
				$rows[] = array_map( 'trim', explode( '|', trim( trim( $line ), '|' ) ) );
			}
			$html = "<!-- wp:table -->\n<figure class=\"wp-block-table\"><table class=\"has-fixed-layout\"><thead><tr>";
			foreach ( array_shift( $rows ) as $cell ) {
				$html .= '<th>' . claimfairly_md_inline( $cell ) . '</th>';
			}
			$html .= '</tr></thead><tbody>';
			foreach ( $rows as $row ) {
				$html .= '<tr>';
				foreach ( $row as $cell ) {
					$html .= '<td>' . claimfairly_md_inline( $cell ) . '</td>';
				}
				$html .= '</tr>';
			}
			$out[] = $html . "</tbody></table></figure>\n<!-- /wp:table -->";
		} elseif ( 0 === strpos( $chunk, '>' ) ) {
			$text  = trim( preg_replace( '/^>\s?/m', '', $chunk ) );
			$out[] = "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>" . claimfairly_md_inline( str_replace( "\n", ' ', $text ) ) . "</p>\n<!-- /wp:paragraph --></blockquote>\n<!-- /wp:quote -->";
		} else {
			$out[] = "<!-- wp:paragraph -->\n<p>" . claimfairly_md_inline( str_replace( "\n", ' ', $chunk ) ) . "</p>\n<!-- /wp:paragraph -->";
		}
	}
	return implode( "\n\n", $out );
}

/**
 * Replace {{placeholders}} in content.
 *
 * @param string $text Text.
 * @return string
 */
function claimfairly_fill_placeholders( $text ) {
	$name = claimfairly_founder_name();
	return strtr(
		$text,
		array(
			'{{founder}}' => $name ? $name : 'the founder',
			'{{email}}'   => claimfairly_opt( 'cf_contact_email' ),
			'{{site}}'    => get_bloginfo( 'name' ),
			'{{year}}'    => wp_date( 'Y' ),
			'{{today}}'   => wp_date( 'F j, Y' ),
		)
	);
}

/**
 * Create or update one item.
 *
 * @param array  $meta   Front matter.
 * @param string $blocks Block markup.
 * @param bool   $update Refresh untouched items.
 * @param array  $report Report (by reference).
 * @return int Post ID (0 on failure).
 */
function claimfairly_upsert( $meta, $blocks, $update, &$report ) {
	$post_type = ( isset( $meta['post_type'] ) && 'post' === $meta['post_type'] ) ? 'post' : 'page';
	$slug      = sanitize_title( $meta['slug'] );
	$key       = $post_type . ':' . ( isset( $meta['parent'] ) ? $meta['parent'] . '/' : '' ) . $slug;

	$existing = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => 1,
			'meta_key'       => '_cf_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( ! $existing ) {
		$path     = ( isset( $meta['parent'] ) ? $meta['parent'] . '/' : '' ) . $slug;
		$by_path  = 'page' === $post_type ? get_page_by_path( $path ) : get_page_by_path( $slug, OBJECT, 'post' );
		$existing = $by_path ? array( $by_path ) : array();
	}

	$parent_id = 0;
	if ( ! empty( $meta['parent'] ) ) {
		$parent    = get_page_by_path( $meta['parent'] );
		$parent_id = $parent ? $parent->ID : 0;
	}

	$data = array(
		'post_type'    => $post_type,
		'post_title'   => claimfairly_fill_placeholders( $meta['title'] ),
		'post_name'    => $slug,
		'post_content' => $blocks,
		'post_excerpt' => isset( $meta['excerpt'] ) ? claimfairly_fill_placeholders( $meta['excerpt'] ) : '',
		'post_status'  => isset( $meta['status'] ) ? $meta['status'] : 'publish',
		'post_parent'  => $parent_id,
		'menu_order'   => isset( $meta['order'] ) ? (int) $meta['order'] : 0,
		'post_author'  => get_current_user_id(),
	);

	// WordPress creates an unpublished "Privacy Policy" page on install. Replace
	// that default draft with ours instead of skipping it.
	$wp_default = $existing
		&& 'draft' === $existing[0]->post_status
		&& (int) get_option( 'wp_page_for_privacy_policy' ) === (int) $existing[0]->ID
		&& ! get_post_meta( $existing[0]->ID, '_cf_import_key', true );

	if ( $existing && $wp_default ) {
		$data['ID'] = $existing[0]->ID;
		$id         = wp_update_post( wp_slash( $data ), true );
		++$report['updated'];
	} elseif ( $existing ) {
		$post = $existing[0];
		$hash = get_post_meta( $post->ID, '_cf_import_hash', true );
		if ( ! $update || ! $hash || md5( $post->post_content ) !== $hash ) {
			++$report['skipped'];
			return $post->ID;
		}
		$data['ID']          = $post->ID;
		$data['post_status'] = $post->post_status;
		$id                  = wp_update_post( wp_slash( $data ), true );
		++$report['updated'];
	} else {
		$id = wp_insert_post( wp_slash( $data ), true );
		++$report['created'];
	}
	if ( is_wp_error( $id ) ) {
		$report['notes'][] = $meta['title'] . ': ' . $id->get_error_message();
		return 0;
	}

	$saved = get_post_field( 'post_content', $id );
	update_post_meta( $id, '_cf_import_key', $key );
	update_post_meta( $id, '_cf_import_hash', md5( $saved ) );

	$map = array(
		'page_type' => '_cf_page_type',
		'tool'      => '_cf_tool_key',
		'summary'   => '_cf_card_summary',
		'reviewed'  => '_cf_last_reviewed',
	);
	foreach ( $map as $field => $meta_key ) {
		if ( isset( $meta[ $field ] ) && ! is_array( $meta[ $field ] ) ) {
			update_post_meta( $id, $meta_key, claimfairly_fill_placeholders( $meta[ $field ] ) );
		}
	}
	foreach ( array( 'sources' => '_cf_sources', 'related' => '_cf_related' ) as $field => $meta_key ) {
		if ( ! empty( $meta[ $field ] ) && is_array( $meta[ $field ] ) ) {
			update_post_meta( $id, $meta_key, implode( "\n", $meta[ $field ] ) );
		}
	}
	if ( ! empty( $meta['hide_author'] ) ) {
		update_post_meta( $id, '_cf_hide_author', 1 );
	}
	if ( 'post' === $post_type && ! empty( $meta['category'] ) ) {
		$term = term_exists( $meta['category'], 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $meta['category'], 'category', array( 'slug' => sanitize_title( isset( $meta['category_slug'] ) ? $meta['category_slug'] : $meta['category'] ) ) );
		}
		if ( ! is_wp_error( $term ) ) {
			wp_set_post_categories( $id, array( (int) $term['term_id'] ) );
		}
	}
	// SEO plugin fields, if one is active: title and description from front matter.
	if ( ! empty( $meta['seo_title'] ) ) {
		update_post_meta( $id, 'rank_math_title', $meta['seo_title'] );
		update_post_meta( $id, '_yoast_wpseo_title', $meta['seo_title'] );
	}
	if ( ! empty( $meta['excerpt'] ) ) {
		update_post_meta( $id, 'rank_math_description', claimfairly_fill_placeholders( $meta['excerpt'] ) );
		update_post_meta( $id, '_yoast_wpseo_metadesc', claimfairly_fill_placeholders( $meta['excerpt'] ) );
	}
	if ( ! empty( $meta['focus_keyword'] ) ) {
		update_post_meta( $id, 'rank_math_focus_keyword', $meta['focus_keyword'] );
		update_post_meta( $id, '_yoast_wpseo_focuskw', $meta['focus_keyword'] );
	}
	return (int) $id;
}

/**
 * Build the Markdown for one state page from the plugin's data.
 *
 * @param array $s State row.
 * @return array{meta:array,body:string}
 */
function claimfairly_state_page( $s ) {
	$name   = $s['name'];
	$code   = $s['code'];
	$years  = (int) $s['sol_injury_years'];
	$yrs    = $years . ' ' . ( 1 === $years ? 'year' : 'years' );
	$rule   = $s['rule'];
	$system = $s['fault_system'];

	$fault_para = array(
		'pure'         => "{$name} uses **pure comparative negligence**. You can recover damages even if you were mostly to blame, but your award shrinks by your share of fault. If your claim is worth \$40,000 and you were 25% at fault, you would recover about \$30,000.",
		'mod50'        => "{$name} uses **modified comparative negligence with a 50% bar**. Your damages are reduced by your share of fault, and if you are found 50% or more at fault you recover nothing. That makes the fault split one of the most argued points in any {$name} claim.",
		'mod51'        => "{$name} uses **modified comparative negligence with a 51% bar**. Your damages are reduced by your share of fault, and you recover nothing if you are found more than 50% at fault. At exactly 50/50, you can still recover half.",
		'mod51_noneco' => "{$name} splits it in two. Economic losses like medical bills and lost pay are reduced by your share of fault, but **pain and suffering is barred completely if you are more than 50% at fault**.",
		'contributory' => "{$name} is one of the few places that still uses **contributory negligence**. If you are found even slightly at fault, you can be barred from recovering anything from the other driver. There are narrow exceptions, like the \"last clear chance\" doctrine, which is why a local attorney is worth a call before you give up on a claim here.",
		'slight_gross' => "{$name} compares negligence as **\"slight\" versus \"gross\"** instead of using a percentage cutoff. You can recover only if your negligence was slight compared with the other driver's, and your award is reduced by your share. There is no fixed line, so outcomes vary more than in most states.",
	);
	$system_para = array(
		'at-fault' => "{$name} is an **at-fault state**. The driver who caused the crash, through their liability insurer, pays for the other people's injuries and property damage. You can file with your own insurer, the other driver's insurer, or both.",
		'no-fault' => "{$name} is a **no-fault state**. After a crash, your own personal injury protection (PIP) coverage pays your medical bills and some lost wages first, no matter who caused it. You can step outside no-fault and claim pain and suffering from the at-fault driver only when your injury meets the state's legal threshold.",
		'choice'   => "{$name} is a **choice no-fault state**. When you buy a policy you choose between a no-fault option, which limits your right to sue for pain and suffering, and a traditional tort option, which keeps it. Check your declarations page to see which one you have.",
	);

	$meta = array(
		'title'         => "{$name} Car Accident Claims: Fault Rules, Deadlines and Minimum Coverage",
		'slug'          => $s['slug'],
		'parent'        => 'states',
		'page_type'     => 'state',
		'status'        => 'draft',
		'order'         => 0,
		'reviewed'      => $s['reviewed'],
		'summary'       => "Fault rule, filing deadline and minimum insurance in {$name}, with a claim estimator preset for {$name} law.",
		'excerpt'       => "How car accident claims work in {$name}: the fault rule, the {$yrs} injury deadline, minimum insurance and what they mean for your settlement.",
		'focus_keyword' => strtolower( $name ) . ' car accident claim',
		'related'       => array(
			'Car Accident Settlement Calculator | /car-accident-settlement-calculator/',
			'Comparative vs Contributory Negligence, Explained | /comparative-vs-contributory-negligence/',
			'At-Fault vs No-Fault States | /at-fault-vs-no-fault-states/',
		),
	);

	$body  = "If you were in a car accident in {$name}, three state rules shape your claim more than anything else: who pays first, how shared fault is handled, and how long you have to file. Here is each one in plain English, plus a calculator already set to {$name} law.\n\n";
	$body .= "[cf_state_facts state=\"{$code}\"]\n\n";
	$body .= "## Who pays after a crash in {$name}?\n\n" . $system_para[ $system ] . "\n\n";
	$body .= "## How does shared fault work in {$name}?\n\n" . $fault_para[ $rule ] . "\n\n";
	$body .= "Insurers know this rule well. Expect the adjuster to look for any reason to assign you a share of the blame, like speed, a late brake or a phone in the car. Photos, a police report and witness names are your best defense.\n\n";
	$body .= "## How long do you have to file in {$name}?\n\n";
	$body .= "The general deadline to file a personal injury lawsuit after a car accident in {$name} is **{$yrs}**, usually counted from the date of the crash. Miss it and the court can throw out your case, no matter how strong it is. Property damage claims, claims against a government vehicle, and claims involving a minor can have different deadlines, so confirm yours early.\n\n";
	$body .= "The insurance claim itself should be reported much sooner. Most policies require you to report an accident promptly, often within days.\n\n";
	$body .= "## Minimum car insurance in {$name}\n\n";
	$body .= "{$name}'s minimum liability limits are **{$s['min_liability']}**. " . ( function_exists( 'cft_limits_words' ) ? 'That means ' . cft_limits_words( $s['min_liability'] ) . '.' : '' ) . " If the other driver carries only the minimum and your injuries are serious, the policy limit may be the most their insurer will pay. Your own uninsured or underinsured motorist coverage can help fill the gap.\n\n";
	$body .= "## Estimate your claim under {$name} law\n\n";
	$body .= "The calculator below is already set to {$name}'s fault rule. Enter your medical bills, lost wages and your share of fault to see a realistic range.\n\n";
	$body .= "[cf_tool name=\"settlement-estimator\" state=\"{$code}\"]\n\n";
	$body .= "## Can you claim diminished value in {$name}?\n\n";
	$body .= "In most states, including {$name}, you can ask the at-fault driver's insurer to pay for the value your car lost because it now has an accident on its record. Claims against your own insurer are harder and depend on your policy wording. Start with the [diminished value calculator](/diminished-value-calculator/) to see the insurer's likely opening number.\n\n";
	$body .= "[cf_faq]\n";
	$body .= "[cf_q q=\"Is {$name} a no-fault state?\"]" . ( 'at-fault' === $system ? "No. {$name} is an at-fault state, so the driver who caused the crash is responsible for the damage through their liability insurance." : ( 'choice' === $system ? "Partly. {$name} is a choice no-fault state, so it depends on the option you picked on your policy." : "Yes. {$name} is a no-fault state, so your own PIP coverage pays first for medical bills and some lost wages." ) ) . "[/cf_q]\n";
	$body .= "[cf_q q=\"How long do I have to sue after a car accident in {$name}?\"]Generally {$yrs} from the date of the accident for an injury lawsuit. Some situations have shorter or longer deadlines, so check with a local attorney if you are getting close.[/cf_q]\n";
	$body .= "[cf_q q=\"Can I still get paid if I was partly at fault in {$name}?\"]" . wp_strip_all_tags( function_exists( 'cft_rule_explainer' ) ? cft_rule_explainer( $rule ) : '' ) . "[/cf_q]\n";
	$body .= "[/cf_faq]";

	return array(
		'meta' => $meta,
		'body' => $body,
	);
}

/**
 * The first 40 states in the order from the plan (the 10 largest first, then by population).
 *
 * @return string[]
 */
function claimfairly_state_order() {
	return array( 'CA', 'TX', 'FL', 'NY', 'GA', 'PA', 'IL', 'OH', 'NC', 'MI', 'NJ', 'VA', 'WA', 'AZ', 'TN', 'MA', 'IN', 'MD', 'MO', 'WI', 'CO', 'MN', 'SC', 'AL', 'LA', 'KY', 'OR', 'OK', 'CT', 'UT', 'IA', 'NV', 'AR', 'KS', 'MS', 'NM', 'NE', 'ID', 'WV', 'HI' );
}

/**
 * Run the import.
 *
 * @param bool $update Refresh untouched imported items.
 * @return array Report.
 */
function claimfairly_run_import( $update = false ) {
	$report = array(
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'notes'   => array(),
	);

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions
	}

	// Parents first (pages without a parent), then children, then posts.
	$items = array();
	foreach ( claimfairly_content_files() as $file ) {
		$parsed = claimfairly_parse_content_file( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( empty( $parsed['meta']['title'] ) || empty( $parsed['meta']['slug'] ) ) {
			$report['notes'][] = basename( $file ) . ': missing title or slug, skipped.';
			continue;
		}
		$items[] = $parsed;
	}
	usort(
		$items,
		static function ( $a, $b ) {
			$rank = static function ( $i ) {
				if ( isset( $i['meta']['post_type'] ) && 'post' === $i['meta']['post_type'] ) {
					return 2;
				}
				return empty( $i['meta']['parent'] ) ? 0 : 1;
			};
			return $rank( $a ) <=> $rank( $b );
		}
	);

	$ids = array();
	foreach ( $items as $item ) {
		$id = claimfairly_upsert( $item['meta'], claimfairly_md_to_blocks( $item['body'] ), $update, $report );
		if ( $id ) {
			$ids[ $item['meta']['slug'] ] = $id;
		}
	}

	// State pages from the plugin's data (drafts).
	if ( function_exists( 'cft_state' ) ) {
		foreach ( claimfairly_state_order() as $i => $code ) {
			$state = cft_state( $code );
			if ( ! $state ) {
				continue;
			}
			$page                  = claimfairly_state_page( $state );
			$page['meta']['order'] = $i + 1;
			claimfairly_upsert( $page['meta'], claimfairly_md_to_blocks( $page['body'] ), $update, $report );
		}
		$report['notes'][] = __( '40 state pages were created as drafts. Verify each one, then publish.', 'claimfairly' );
	} else {
		$report['notes'][] = __( 'State pages were not created because the ClaimFairly Tools plugin is not active. Activate it and run setup again.', 'claimfairly' );
	}

	// Site name, if it is still a WordPress default.
	if ( in_array( get_option( 'blogname' ), array( '', 'My WordPress Website', 'My WordPress Blog', 'My Blog', 'WordPress' ), true ) ) {
		update_option( 'blogname', 'ClaimFairly' );
	}
	if ( in_array( get_option( 'blogdescription' ), array( '', 'Just another WordPress site' ), true ) ) {
		update_option( 'blogdescription', 'Free, honest car accident claim calculators' );
	}

	// Reading settings.
	if ( isset( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	if ( isset( $ids['guides'] ) ) {
		update_option( 'page_for_posts', $ids['guides'] );
	}
	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$report['notes'][] = __( 'Permalinks set to "Post name".', 'claimfairly' );
	}
	flush_rewrite_rules( false );

	// Remove the sample content WordPress ships with, if untouched.
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'publish' === $hello->post_status && false !== strpos( $hello->post_content, 'Welcome to WordPress' ) ) {
		wp_trash_post( $hello->ID );
	}
	$sample = get_page_by_path( 'sample-page' );
	if ( $sample && false !== strpos( $sample->post_content, 'This is an example page' ) ) {
		wp_trash_post( $sample->ID );
	}

	claimfairly_build_menus( $report );
	update_option( 'claimfairly_imported', time() );
	return $report;
}

/**
 * Create the four menus if they do not exist yet.
 *
 * @param array $report Report (by reference).
 */
function claimfairly_build_menus( &$report ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$page      = static function ( $path ) {
		$p = get_page_by_path( $path );
		return ( $p && 'publish' === $p->post_status ) ? $p->ID : 0;
	};
	$tools = array( 'car-accident-settlement-calculator', 'diminished-value-calculator', 'pain-and-suffering-calculator', 'settlement-calculator-take-home', 'demand-letter-generator' );

	$menus = array(
		'primary'      => array(
			'name'  => 'Primary',
			'items' => array(
				array( 'label' => 'Calculators', 'children' => $tools, 'url' => '/#tools' ),
				array( 'page' => 'guides', 'label' => 'Guides' ),
				array( 'page' => 'states', 'label' => 'States' ),
				array( 'page' => 'about', 'label' => 'About' ),
			),
		),
		'footer-tools' => array(
			'name'  => 'Footer: Calculators',
			'items' => array_map(
				static function ( $slug ) {
					return array( 'page' => $slug );
				},
				$tools
			),
		),
		'footer-learn' => array(
			'name'  => 'Footer: Guides',
			'items' => array(
				array( 'post' => 'what-is-diminished-value', 'label' => 'What is diminished value?' ),
				array( 'post' => '17c-formula-diminished-value', 'label' => 'The 17c formula explained' ),
				array( 'post' => 'how-insurance-adjusters-calculate-settlement', 'label' => 'How adjusters calculate offers' ),
				array( 'post' => 'what-to-do-after-a-car-accident', 'label' => 'After a crash: checklist' ),
				array( 'page' => 'states', 'label' => 'Rules by state' ),
				array( 'page' => 'guides', 'label' => 'All guides' ),
			),
		),
		'footer-site'  => array(
			'name'  => 'Footer: Company',
			'items' => array(
				array( 'page' => 'about', 'label' => 'About' ),
				array( 'page' => 'methodology', 'label' => 'Methodology' ),
				array( 'page' => 'editorial-policy', 'label' => 'Editorial policy' ),
				array( 'page' => 'disclaimer', 'label' => 'Disclaimer' ),
				array( 'page' => 'privacy-policy', 'label' => 'Privacy policy' ),
				array( 'page' => 'contact', 'label' => 'Contact' ),
			),
		),
	);

	foreach ( $menus as $location => $def ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			continue;
		}
		$existing = wp_get_nav_menu_object( $def['name'] );
		$menu_id  = $existing ? $existing->term_id : wp_create_nav_menu( $def['name'] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		if ( ! $existing ) {
			foreach ( $def['items'] as $pos => $item ) {
				$parent_item = claimfairly_add_menu_item( $menu_id, $item, 0, $pos, $page );
				if ( $parent_item && ! empty( $item['children'] ) ) {
					foreach ( $item['children'] as $cpos => $child ) {
						claimfairly_add_menu_item( $menu_id, array( 'page' => $child ), $parent_item, $cpos, $page );
					}
				}
			}
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	$report['notes'][] = __( 'Menus created and assigned.', 'claimfairly' );
}

/**
 * Add one menu item.
 *
 * @param int      $menu_id Menu ID.
 * @param array    $item    Item definition.
 * @param int      $parent  Parent menu item ID.
 * @param int      $pos     Position.
 * @param callable $page    Page lookup.
 * @return int Menu item ID or 0.
 */
function claimfairly_add_menu_item( $menu_id, $item, $parent, $pos, $page ) {
	$args = array(
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => $parent,
		'menu-item-position'  => $pos + 1,
	);
	if ( ! empty( $item['page'] ) ) {
		$id = $page( $item['page'] );
		if ( ! $id ) {
			return 0;
		}
		$args += array(
			'menu-item-object-id' => $id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
		);
		if ( ! empty( $item['label'] ) ) {
			$args['menu-item-title'] = $item['label'];
		}
	} elseif ( ! empty( $item['post'] ) ) {
		$post = get_page_by_path( $item['post'], OBJECT, 'post' );
		if ( ! $post ) {
			return 0;
		}
		$args += array(
			'menu-item-object-id' => $post->ID,
			'menu-item-object'    => 'post',
			'menu-item-type'      => 'post_type',
		);
		if ( ! empty( $item['label'] ) ) {
			$args['menu-item-title'] = $item['label'];
		}
	} else {
		$args += array(
			'menu-item-title' => $item['label'],
			'menu-item-url'   => 0 === strpos( $item['url'], '/' ) ? home_url( $item['url'] ) : $item['url'],
			'menu-item-type'  => 'custom',
		);
	}
	$id = wp_update_nav_menu_item( $menu_id, 0, $args );
	return is_wp_error( $id ) ? 0 : (int) $id;
}
