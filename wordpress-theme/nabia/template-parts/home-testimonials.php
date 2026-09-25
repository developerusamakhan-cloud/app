<?php
/**
 * Testimonials slider.
 *
 * @package Nabia
 */

$nabia_query = new WP_Query(
	array(
		'post_type'      => 'testimonial',
		'posts_per_page' => 12,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	)
);

$nabia_items = array();
if ( $nabia_query->have_posts() ) {
	while ( $nabia_query->have_posts() ) {
		$nabia_query->the_post();
		$nabia_items[] = array(
			'name'   => get_the_title(),
			'role'   => get_post_meta( get_the_ID(), '_nabia_author_role', true ),
			'quote'  => wp_strip_all_tags( get_the_content() ),
			'rating' => (int) get_post_meta( get_the_ID(), '_nabia_rating', true ),
			'avatar' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
		);
	}
	wp_reset_postdata();
} else {
	$nabia_items = nabia_demo_testimonials();
}
?>
<section class="section testimonials" id="testimonials">
	<div class="container">
		<div class="section-head-row">
			<?php nabia_section_head( '05', __( 'Testimonials', 'nabia' ), nabia_mod( 'testimonials_title' ) ); ?>
			<div class="slider-controls">
				<button class="slider-btn" type="button" data-slide="prev" aria-label="<?php esc_attr_e( 'Previous testimonial', 'nabia' ); ?>"><?php nabia_icon( 'arrow' ); ?></button>
				<button class="slider-btn" type="button" data-slide="next" aria-label="<?php esc_attr_e( 'Next testimonial', 'nabia' ); ?>"><?php nabia_icon( 'arrow' ); ?></button>
			</div>
		</div>

		<div class="slider" data-slider>
			<?php foreach ( $nabia_items as $nabia_item ) : ?>
				<?php $nabia_rating = ! empty( $nabia_item['rating'] ) ? min( 5, max( 1, $nabia_item['rating'] ) ) : 5; ?>
				<figure class="quote-card">
					<div class="stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating */ __( '%d out of 5 stars', 'nabia' ), $nabia_rating ) ); ?>">
						<?php for ( $nabia_s = 0; $nabia_s < $nabia_rating; $nabia_s++ ) : ?>
							<?php nabia_icon( 'star' ); ?>
						<?php endfor; ?>
					</div>
					<blockquote><p><?php echo esc_html( $nabia_item['quote'] ); ?></p></blockquote>
					<figcaption>
						<?php if ( ! empty( $nabia_item['avatar'] ) ) : ?>
							<img class="avatar" src="<?php echo esc_url( $nabia_item['avatar'] ); ?>" alt="" width="48" height="48" loading="lazy">
						<?php else : ?>
							<span class="avatar avatar-initial" aria-hidden="true"><?php echo esc_html( mb_substr( $nabia_item['name'], 0, 1 ) ); ?></span>
						<?php endif; ?>
						<span><strong><?php echo esc_html( $nabia_item['name'] ); ?></strong><?php echo esc_html( $nabia_item['role'] ); ?></span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
