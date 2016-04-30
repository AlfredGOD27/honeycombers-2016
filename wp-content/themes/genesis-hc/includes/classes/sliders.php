<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Sliders {
	private function display_post_content( $post_id, $max_chars = false ) {

		$terms = false;
		switch( get_post_type($post_id) ) {
			case 'post':
				$terms = wp_get_object_terms( $post_id, 'category' );
				break;
			case 'event':
				$terms = wp_get_object_terms( $post_id, 'event-category' );
				break;
			case 'listing':
				$terms = wp_get_object_terms( $post_id, 'directories' );
				break;
		}

		$icon = false;
		if( !empty($terms) )
			$icon = get_field( '_hc_category_icon', $terms[0] );

		if( !empty($icon) )
			echo '<i class="ico-' . $icon . '"></i>';

		if( false === $max_chars ) {
			echo '<span>' . get_the_title( $post_id ) . '</span>';
		} else {
			echo '<span>' . HC()->formatting->maybe_truncate( get_the_title( $post_id ), $max_chars ) . '</span>';
		}

	}

	public function display( $args ) {

		$posts = get_posts( $args );
		if( empty($posts) )
			return;

		?>
		<section class="archive-slider-container hide-no-js">
			<div class="wrap">
				<div class="slider-for">
					<?php
					foreach( $posts as $post_id ) {
						?>
						<div>
							<?php
							$header_type = get_post_meta( $post_id, '_hc_post_header_type', true );
							switch( $header_type ) {
								case 'video':
									$video_url = get_post_meta( $post_id, '_hc_post_video_url', true );
									if( !empty($video_url) )
										echo wp_oembed_get($video_url);
									break;
								default:
									echo '<a href="' . get_permalink($post_id) . '">';
										echo get_the_post_thumbnail( $post_id, 'slide' );
									echo '</a>';
									break;
							}

							?>
							<div class="slide-content">
								<?php
								$this->display_post_content( $post_id );
								?>
							</div>
						</div>
						<?php
					}
					?>
				</div>

				<div class="slider-nav">
					<?php
					foreach( $posts as $post_id ) {
						?>
						<div>
							<div class="outer">
								<?php
								echo get_the_post_thumbnail( $post_id, 'slide-thumbnail' );
								?>

								<div class="inner">
									<?php
									$this->display_post_content( $post_id, 50 );
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</section>
		<?php

	}
}

return new HC_Sliders();
