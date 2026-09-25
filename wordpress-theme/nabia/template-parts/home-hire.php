<?php
/**
 * Small call-to-action for hiring through Fiverr or Upwork.
 *
 * Uses the Fiverr / Upwork links from Customize → Nabia Theme → Social Links.
 *
 * @package Nabia
 */

$nabia_platforms = array_filter(
	array(
		'fiverr' => array(
			'url'  => nabia_mod( 'social_fiverr' ),
			'note' => nabia_mod( 'fiverr_note' ),
		),
		'upwork' => array(
			'url'  => nabia_mod( 'social_upwork' ),
			'note' => nabia_mod( 'upwork_note' ),
		),
	),
	function ( $platform ) {
		return ! empty( $platform['url'] );
	}
);

$nabia_is_admin = current_user_can( 'edit_theme_options' );
if ( ! $nabia_platforms && ! $nabia_is_admin ) {
	return;
}
?>
<section class="hire" id="hire" aria-label="<?php esc_attr_e( 'Hire me on Fiverr or Upwork', 'nabia' ); ?>">
	<div class="container">
		<div class="hire-card" data-reveal>
			<div class="hire-copy">
				<p class="hire-kicker"><span class="pulse" aria-hidden="true"></span><?php esc_html_e( 'Also available on', 'nabia' ); ?></p>
				<h2 class="hire-title"><?php echo esc_html( nabia_mod( 'hire_title' ) ); ?></h2>
				<p class="hire-text"><?php echo esc_html( nabia_mod( 'hire_text' ) ); ?></p>
			</div>

			<div class="hire-platforms">
				<?php if ( ! $nabia_platforms ) : ?>
					<p class="admin-hint"><?php esc_html_e( 'Only you can see this: add your Fiverr and/or Upwork profile links in Customize → Nabia Theme → Social Links and this section appears for visitors.', 'nabia' ); ?></p>
				<?php endif; ?>

				<?php if ( isset( $nabia_platforms['fiverr'] ) ) : ?>
					<a class="platform platform-fiverr" href="<?php echo esc_url( $nabia_platforms['fiverr']['url'] ); ?>" target="_blank" rel="noopener noreferrer" data-magnetic>
						<span class="platform-logo" aria-label="Fiverr">fiverr<span>.</span></span>
						<span class="platform-note"><?php echo esc_html( $nabia_platforms['fiverr']['note'] ); ?></span>
						<span class="platform-arrow" aria-hidden="true"><?php nabia_icon( 'arrow-up' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( isset( $nabia_platforms['upwork'] ) ) : ?>
					<a class="platform platform-upwork" href="<?php echo esc_url( $nabia_platforms['upwork']['url'] ); ?>" target="_blank" rel="noopener noreferrer" data-magnetic>
						<span class="platform-logo" aria-label="Upwork">Upwork</span>
						<span class="platform-note"><?php echo esc_html( $nabia_platforms['upwork']['note'] ); ?></span>
						<span class="platform-arrow" aria-hidden="true"><?php nabia_icon( 'arrow-up' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
