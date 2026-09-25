<?php
/**
 * Reviews: Google rating summary + a wall of Google reviews and Testimonials posts.
 *
 * Also used by the [nabia_google_reviews] shortcode.
 *
 * @package Nabia
 */

$nabia_data    = nabia_reviews_items();
$nabia_google  = $nabia_data['google'];
$nabia_items   = $nabia_data['items'];
$nabia_visible = 9;
$nabia_summary = $nabia_google && ! empty( $nabia_google['rating'] );
?>
<section class="section testimonials" id="testimonials">
	<div class="container">
		<div class="reviews-head">
			<?php nabia_section_head( __( 'Testimonials', 'nabia' ), nabia_mod( 'testimonials_title' ) ); ?>

			<?php if ( $nabia_summary ) : ?>
				<div class="rating-card" data-reveal>
					<div class="rating-card-top">
						<?php echo nabia_google_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Google Reviews', 'nabia' ); ?></span>
					</div>
					<div class="rating-card-score">
						<strong><?php echo esc_html( number_format_i18n( $nabia_google['rating'], 1 ) ); ?></strong>
						<span>
							<span class="stars stars-lg" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: rating */ __( 'Rated %s out of 5', 'nabia' ), $nabia_google['rating'] ) ); ?>" style="--rating:<?php echo esc_attr( $nabia_google['rating'] ); ?>"></span>
							<span class="rating-card-total">
								<?php
								/* translators: %s: number of reviews */
								echo esc_html( sprintf( _n( 'Based on %s review', 'Based on %s reviews', $nabia_google['total'], 'nabia' ), number_format_i18n( $nabia_google['total'] ) ) );
								?>
							</span>
						</span>
					</div>
					<div class="rating-card-actions">
						<a class="btn btn-accent btn-sm" href="<?php echo esc_url( $nabia_google['write_url'] ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Write a review', 'nabia' ); ?></span></a>
						<?php if ( $nabia_google['maps_url'] ) : ?>
							<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( $nabia_google['maps_url'] ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'See all on Google', 'nabia' ); ?></span></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
			<?php if ( ! nabia_google_configured() ) : ?>
				<p class="admin-hint"><?php esc_html_e( 'Only you can see this: connect your Google reviews in Customize → Nabia Theme → Google Reviews (Place ID + API key) and they will appear here automatically.', 'nabia' ); ?></p>
			<?php elseif ( $nabia_google && ! empty( $nabia_google['error'] ) ) : ?>
				<p class="admin-hint">
					<?php
					/* translators: %s: error message from Google */
					echo esc_html( sprintf( __( 'Only you can see this: Google reviews could not be loaded (%s). Check the Place ID and that “Places API (New)” is enabled for your API key.', 'nabia' ), $nabia_google['error'] ) );
					?>
				</p>
			<?php endif; ?>
		<?php endif; ?>

		<div class="review-wall" data-review-wall>
			<?php foreach ( $nabia_items as $nabia_index => $nabia_item ) : ?>
				<?php $nabia_rating = ! empty( $nabia_item['rating'] ) ? min( 5, max( 1, (int) $nabia_item['rating'] ) ) : 5; ?>
				<figure class="review-card<?php echo $nabia_index >= $nabia_visible ? ' is-extra' : ''; ?>">
					<figcaption class="review-author">
						<?php if ( ! empty( $nabia_item['avatar'] ) ) : ?>
							<img class="avatar" src="<?php echo esc_url( $nabia_item['avatar'] ); ?>" alt="" width="44" height="44" loading="lazy" referrerpolicy="no-referrer">
						<?php else : ?>
							<span class="avatar avatar-initial" aria-hidden="true"><?php echo esc_html( mb_substr( $nabia_item['name'] ? $nabia_item['name'] : '★', 0, 1 ) ); ?></span>
						<?php endif; ?>
						<span class="review-who">
							<strong><?php echo esc_html( $nabia_item['name'] ); ?></strong>
							<span><?php echo esc_html( $nabia_item['role'] ); ?></span>
						</span>
						<?php if ( ! empty( $nabia_item['google'] ) ) : ?>
							<?php if ( ! empty( $nabia_item['url'] ) ) : ?>
								<a class="review-source" href="<?php echo esc_url( $nabia_item['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'View on Google', 'nabia' ); ?>"><?php echo nabia_google_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
							<?php else : ?>
								<span class="review-source"><?php echo nabia_google_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endif; ?>
						<?php endif; ?>
					</figcaption>
					<div class="stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating */ __( '%d out of 5 stars', 'nabia' ), $nabia_rating ) ); ?>">
						<?php for ( $nabia_s = 0; $nabia_s < 5; $nabia_s++ ) : ?>
							<span class="<?php echo $nabia_s < $nabia_rating ? 'is-on' : ''; ?>"><?php nabia_icon( 'star' ); ?></span>
						<?php endfor; ?>
					</div>
					<blockquote class="review-text" data-review-text><p><?php echo nl2br( esc_html( $nabia_item['quote'] ) ); ?></p></blockquote>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $nabia_items ) > $nabia_visible ) : ?>
			<div class="review-more">
				<button class="btn btn-ghost" type="button" data-review-more>
					<span>
						<?php
						/* translators: %d: number of hidden reviews */
						echo esc_html( sprintf( __( 'Show %d more reviews', 'nabia' ), count( $nabia_items ) - $nabia_visible ) );
						?>
					</span>
				</button>
			</div>
		<?php endif; ?>

		<?php if ( $nabia_summary ) : ?>
			<p class="review-attribution"><?php echo nabia_google_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Reviews from Google', 'nabia' ); ?></p>
		<?php endif; ?>
	</div>
</section>
