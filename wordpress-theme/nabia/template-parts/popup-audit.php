<?php
/**
 * Free audit popup. Shown once every 24 hours per visitor (cookie), after a delay,
 * at half the page or on exit intent. Settings: Customize > Nabia Theme > Free audit.
 *
 * @package Nabia
 */

$nabia_target = nabia_page_url( 'audit' );
$nabia_anchor = shortcode_exists( 'nabia_website_audit' ) ? '#nabia-audit' : '#audit';
if ( ! $nabia_target ) {
	$nabia_target = home_url( '/' );
}
$nabia_wa = nabia_whatsapp_url();
?>
<dialog class="audit-pop" id="audit-pop" aria-labelledby="audit-pop-title" data-audit-pop data-delay="<?php echo esc_attr( max( 3, (int) nabia_mod( 'popup_delay' ) ) ); ?>">
	<button class="audit-pop-close" type="button" data-audit-pop-close aria-label="<?php esc_attr_e( 'Close', 'nabia' ); ?>"><?php nabia_icon( 'close' ); ?></button>
	<div class="audit-pop-grid">
		<div class="audit-pop-visual" aria-hidden="true">
			<div class="audit-pop-score">
				<svg viewBox="0 0 120 120"><circle class="track" cx="60" cy="60" r="50"/><circle class="fill" cx="60" cy="60" r="50"/></svg>
				<span><strong>?</strong><small>/100</small></span>
			</div>
			<ul class="audit-pop-bars">
				<li><span><?php esc_html_e( 'Design', 'nabia' ); ?></span><i style="--w:82%"></i></li>
				<li><span><?php esc_html_e( 'SEO', 'nabia' ); ?></span><i style="--w:58%"></i></li>
				<li><span><?php esc_html_e( 'Content', 'nabia' ); ?></span><i style="--w:71%"></i></li>
				<li><span><?php esc_html_e( 'Speed', 'nabia' ); ?></span><i style="--w:44%"></i></li>
			</ul>
			<p class="audit-pop-pdf"><?php nabia_icon( 'check' ); ?><span><?php esc_html_e( 'PDF report with every fix', 'nabia' ); ?></span></p>
		</div>
		<div class="audit-pop-body" tabindex="-1" autofocus>
			<p class="audit-pop-eyebrow"><?php nabia_icon( 'bolt' ); ?><span><?php esc_html_e( 'Free website audit', 'nabia' ); ?></span></p>
			<h2 class="audit-pop-title" id="audit-pop-title"><?php echo esc_html( nabia_mod( 'popup_title' ) ); ?></h2>
			<p class="audit-pop-text"><?php echo esc_html( nabia_mod( 'popup_text' ) ); ?></p>
			<form class="audit-pop-form" action="<?php echo esc_url( $nabia_target ); ?>" method="get" data-audit-pop-form data-anchor="<?php echo esc_attr( $nabia_anchor ); ?>">
				<label class="screen-reader-text" for="audit-pop-url"><?php esc_html_e( 'Your website', 'nabia' ); ?></label>
				<span class="audit-pop-field"><?php nabia_icon( 'layout' ); ?><input id="audit-pop-url" name="audit_url" type="text" inputmode="url" autocomplete="url" placeholder="yourwebsite.com" required></span>
				<button class="btn btn-accent audit-pop-submit" type="submit"><span><?php esc_html_e( 'Check my website', 'nabia' ); ?></span><?php nabia_icon( 'arrow' ); ?></button>
			</form>
			<ul class="audit-pop-trust">
				<li><?php nabia_icon( 'check' ); ?><?php esc_html_e( '100% free', 'nabia' ); ?></li>
				<li><?php nabia_icon( 'check' ); ?><?php esc_html_e( 'No sign up', 'nabia' ); ?></li>
				<li><?php nabia_icon( 'check' ); ?><?php esc_html_e( 'Results in 60 seconds', 'nabia' ); ?></li>
			</ul>
			<?php if ( $nabia_wa ) : ?>
				<p class="audit-pop-alt"><?php esc_html_e( 'Rather talk to a human?', 'nabia' ); ?> <a href="<?php echo esc_url( $nabia_wa ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Message me on WhatsApp', 'nabia' ); ?></a></p>
			<?php endif; ?>
		</div>
	</div>
</dialog>
