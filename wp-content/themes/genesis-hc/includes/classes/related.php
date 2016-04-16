<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Related {
	public function display_related_content( $post, $context, $count = 4 ) {

		switch( $post->post_type ) {
			case 'post':
				$args = array();

				$terms = wp_get_post_terms( $post->ID, 'post_tag' );
				if( !empty($terms) ) {
					$taxonomy = 'post_tag';
				} else {
					$taxonomy = 'category';
					$terms    = wp_get_post_terms( $post->ID, 'category' );
				}
				break;
			case 'event':
				$args = HC()->events->get_date_query_args();

				$taxonomy = 'event-category';
				$terms    = wp_get_post_terms( $post->ID, $taxonomy );
				break;
			case 'listing':
				$args            = array();
				$args['orderby'] = 'rand';

				$terms = wp_get_post_terms( $post->ID, 'directories' );
				if( !empty($terms) ) {
					$taxonomy = 'directories';
				} else {
					$taxonomy = 'locations';
					$terms    = wp_get_post_terms( $post->ID, 'locations' );
				}
				break;
		}

		if( empty($terms) )
			return;

		$term_ids = array();
		foreach( $terms as $term )
			$term_ids[] = $term->term_id;

		$args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'IN',
			),
		);

		$args['posts_per_page']         = $count;
		$args['post_type']              = $post->post_type;
		$args['post__not_in']           = array($post->ID);
		$args['update_post_term_cache'] = false;

		$posts = get_posts( $args );
		if( empty($posts) )
			return;

		switch( $context ) {
			case 'section':
				?>
				<section class="related">
					<div class="wrap">
				<?php
				break;
			case 'aside':
				?>
				<aside class="related">
				<?php
				break;
		}

		?>
		<div class="block">
			<h2>You May Also Like</h2>
			<?php

			$i = 1;
			foreach( $posts as $post ) {
				echo 1 === $i % 4 ? '<div class="one-fourth first">' : '<div class="one-fourth">';
					if( has_post_thumbnail($post->ID) ) {
						?>
						<div class="top">
							<?php
							echo HC()->utilities->get_category_icon_html( $terms[0] );
							?>
							<?php echo get_the_post_thumbnail( $post->ID, 'archive-small' ); ?>
						</div>
						<?php
					}
					?>

					<div class="bottom">
						<a href="<?php echo get_permalink( $post->ID ); ?>">
							<h3><?php echo get_the_title( $post->ID ); ?></h3>
						</a>
					</div>
					<?php
				echo '</div>';
				++$i;
			}
			?>
		</div>
		<?php

		switch( $context ) {
			case 'section':
				?>
					</div>
				</section>
				<?php
				break;
			case 'aside':
				?>
				</aside>
				<?php
				break;
		}

	}
}

return new HC_Related();
