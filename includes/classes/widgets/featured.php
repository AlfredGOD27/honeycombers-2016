<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Featured_Widget extends WP_Widget {
	public function __construct() {

		parent::__construct(false, $name = 'HC: Featured');

	}

	public function widget( $args, $instance ) {

		extract($args);

		$post_type = get_field( '_hc_post_type', 'widget_' . $widget_id );

		$query_args              = array();
		$query_args['post_type'] = $post_type;
		$query_args['fields']    = 'ids';

		switch( $post_type ) {
			case 'event':
				$post_ids = get_field( '_hc_event_ids', 'widget_' . $widget_id );
				if( empty($post_ids) )
					return;

				$query_args['post__in'] = array_map( 'absint', $post_ids );
				break;
			case 'listing':
				if( !is_archive() )
					return;

				$term = get_queried_object();
				if( empty($term) )
					return;

				$post_id = get_field( '_hc_category_sidebar_featured_venue_id', $term );
				if( empty($post_id) )
					return;

				$post_ids               = (array) $post_id;
				$query_args['post__in'] = array_map( 'absint', $post_ids );
				break;
			case 'post':
				$post_ids = get_field( '_hc_post_ids', 'widget_' . $widget_id );
				if( empty($post_ids) )
					return;

				$query_args['post__in'] = array_map( 'absint', $post_ids );
				break;
			default:
				return;
		}

		$posts = get_posts( $query_args );
		if( empty($posts) )
			return;

		echo $before_widget;
			$title = get_field( '_hc_title', 'widget_' . $widget_id );
			if( !empty($title) )
				echo $before_title . sanitize_text_field($title) . $after_title;

			switch( $post_type ) {
				case 'event':
					break;
				case 'listing':
				case 'post':
					foreach( $post_ids as $post_id ) {
						?>
						<div class="featured-item <?php echo $post_type; ?>">
							<a href="<?php echo get_permalink($post_id); ?>">
								<?php
								if( has_post_thumbnail($post_id) )
									echo wp_get_attachment_image( get_post_thumbnail_id($post_id), 'event-thumbnail' );
								?>

								<div class="inner">
									<h5><?php echo get_the_title($post_id); ?></h5>

									<?php
									if( 'listing' === $post_type ) {
										$terms = wp_get_object_terms( $post_id, 'listing-category' );
										if( !empty($terms) ) {
											$categories = array();
											foreach( $terms as $term )
												$categories[] = $term->name;

											?>
											<p><?php echo HC()->formatting->build_comma_separated_list($categories); ?></p>
											<?php
										}
									}
									?>
								</div>
							</a>
						</div>
						<?php
					}
					break;
			}
		echo $after_widget;

	}

	public function form( $instance ) {

	}

}
