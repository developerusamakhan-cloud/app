<?php
/**
 * Frequently asked questions (FAQPage schema is printed in inc/schema.php).
 *
 * @package PowerBachat
 */

?>
<section class="section faq" id="faq">
	<div class="wrap faq__inner">
		<header class="faq__head">
			<p class="kicker"><span class="kicker__num">07</span><?php esc_html_e( 'Questions', 'powerbachat' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Things people ask us every billing week.', 'powerbachat' ); ?></h2>
		</header>
		<div class="faq__list">
			<?php foreach ( powerbachat_faqs() as $powerbachat_i => $powerbachat_faq ) : ?>
				<details class="faq__item"<?php echo 0 === $powerbachat_i ? ' open' : ''; ?>>
					<summary>
						<span><?php echo esc_html( $powerbachat_faq['q'] ); ?></span>
						<i class="faq__plus" aria-hidden="true"></i>
					</summary>
					<div class="faq__answer"><p><?php echo esc_html( $powerbachat_faq['a'] ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
