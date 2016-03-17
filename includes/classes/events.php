<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Events {
	public function __construct() {

		add_action( 'init', array($this, 'register') );
		add_action( 'wp', array($this, 'init') );

	}

	public function register() {

		register_post_type( 'event',
			array(
				'labels' => array(
					'name'               => __('Events', 'post type general name'),
					'singular_name'      => __('Event', 'post type singular name'),
					'add_new'            => __('Add New', 'custom post type item'),
					'add_new_item'       => __('Add New Event'),
					'edit'               => __( 'Edit' ),
					'edit_item'          => __('Edit Event'),
					'new_item'           => __('New Event'),
					'view_item'          => __('View Event'),
					'search_items'       => __('Search Events'),
					'not_found'          => __('Nothing found in the Database.'),
					'not_found_in_trash' => __('Nothing found in Trash'),
					'parent_item_colon'  => '',
				),
				'public'          => true,
				'has_archive'     => false,
				'capability_type' => 'post',
				'hierarchical'    => false,
				'menu_icon'       => 'dashicons-calendar-alt',
				'supports'        => array('title', 'thumbnail', 'editor'),
			)
		);

		$labels = array(
			'name'              => _x( 'Event Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Event Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Event Categories' ),
			'all_items'         => __( 'All Event Categories' ),
			'parent_item'       => __( 'Parent Event Category' ),
			'parent_item_colon' => __( 'Parent Event Category:' ),
			'edit_item'         => __( 'Edit Event Category' ),
			'update_item'       => __( 'Update Event Category' ),
			'add_new_item'      => __( 'Add New Event Category' ),
			'new_item_name'     => __( 'New Event Category Name' ),
			'menu_name'         => __( 'Event Category' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'event-category'),
		);

		register_taxonomy( 'event-category', array('event'), $args );

	}

	public function init() {

		if( !is_singular('event') )
			return;

		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );
		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_loop', array($this, 'do_single_event') );

	}

	private function get_event_date_info( $post_id ) {

		$info = array();

		$all_day         = get_post_meta( $post_id, '_hc_event_all_day', true );
		$info['all_day'] = !empty($all_day);

		$start_date         = get_post_meta( $post_id, '_hc_event_start_date', true );
		$info['start_date'] = !empty($start_date) ? strtotime($start_date) : false;

		if( !$info['all_day'] ) {
			$start_time         = get_post_meta( $post_id, '_hc_event_start_time', true );
			$info['start_time'] = !empty($start_time) ? strtotime($start_time) : false;
		}

		if( !$info['all_day'] && false !== $info['start_time'] ) {
			$info['start_datetime'] = strtotime( $start_date . ' ' . $start_time );
		} else {
			$info['start_datetime'] = $info['start_date'];
		}

		$end_date         = get_post_meta( $post_id, '_hc_event_end_date', true );
		$info['end_date'] = !empty($end_date) ? strtotime($end_date) : false;

		if( !$info['all_day'] ) {
			$end_time         = get_post_meta( $post_id, '_hc_event_end_time', true );
			$info['end_time'] = !empty($end_time) ? strtotime($end_time) : false;
		}

		if( !$info['all_day'] && false !== $info['end_time'] ) {
			$info['end_datetime'] = strtotime( $end_date . ' ' . $end_time );
		} else {
			$info['end_datetime'] = $info['end_date'];
		}

		return $info;

	}

	public function do_single_event() {

		global $post;

		printf( '<article %s>', genesis_attr( 'entry' ) );
			?>
			<div class="one-half first">
				<?php
				genesis_entry_header_markup_open();
					genesis_do_post_title();

					$categories = wp_get_object_terms( $post->ID, 'event-category' );
					if( !empty($categories) ) {
						$category_links = array();

						foreach( $categories as $category )
							$category_links[] = '<a href="' . get_term_link($category) . '">' . $category->name . '</a>';

						echo '<p class="entry-meta">' . HC()->formatting->build_comma_separated_list($category_links) . '</p>';
					}
				genesis_entry_header_markup_close();
				?>

				<?php
				$lines = array();

				// Date
				$date = $this->get_event_date_info( $post->ID );
				if( false !== $date['start_date'] && false !== $date['end_date'] ) {
					$start_date = date( 'l, F j', $date['start_date'] );
					$end_date   = date( 'l, F j', $date['end_date'] );

					if( $start_date !== $end_date ) {
						$lines['Date'] = $start_date . ' - ' . $end_date;
					} else {
						$lines['Date'] = $start_date;
					}
				} elseif( false !== $date['start_date'] ) {
					$start_date    = date( 'l, F j', $date['start_date'] );
					$lines['Date'] = $start_date;
				} elseif( false !== $date['end_date'] ) {
					$end_date      = date( 'l, F j', $date['end_date'] );
					$lines['Date'] = $end_date;
				}

				// Time
				if( !$date['all_day'] ) {
					if( false !== $date['start_time'] && false !== $date['end_time'] ) {
						$start_time = date( 'ga', $date['start_time'] );
						$end_time   = date( 'ga', $date['end_time'] );

						if( $start_time !== $end_time ) {
							$lines['Time'] = $start_time . ' - ' . $end_time;
						} else {
							$lines['Time'] = $start_time;
						}
					} elseif( false !== $date['start_time'] ) {
						$start_time    = date( 'ga', $date['start_time'] );
						$lines['Time'] = $start_time;
					} elseif( false !== $date['end_time'] ) {
						$end_time      = date( 'ga', $date['end_time'] );
						$lines['Time'] = $end_time;
					}
				}

				// Venue
				$venue = get_post_meta( $post->ID, '_hc_event_venue', true );
				if( !empty($venue) )
					$lines['Venue'] = sanitize_text_field($venue);

				// Price
				$price = get_post_meta( $post->ID, '_hc_event_price', true );
				if( strlen($price) > 0 )
					$lines['Price'] = !empty($price) ? number_format( (float) $price ) : 'Free';

				// Contact
				$contact = get_post_meta( $post->ID, '_hc_event_contact', true );
				if( !empty($contact) )
					$lines['Contact'] = '<a href="mailto:' . sanitize_email($contact) . '">' . sanitize_text_field($contact) . '</a>';

				// Website
				$website = get_post_meta( $post->ID, '_hc_event_website', true );
				if( !empty($website) )
					$lines['Website'] = HC()->formatting->get_linked_url( $website );

				if( count($lines) > 0 )
					HC()->formatting->display_data_list($lines);
				?>

				<div class="item-action-row">
					<?php HC()->favorites->display( $post->ID ); ?>
					<?php HC()->share->display( $post->ID ); ?>

					<?php
					$start = date( 'Ymd', $date['start_datetime'] );
					$start .= 'T';
					$start .= date( 'His', $date['start_datetime'] );

					if( !$date['all_day'] || $date['start_datetime'] !== $date['end_datetime'] ) {
						$end = date( 'Ymd', $date['end_datetime'] );
						$end .= 'T';
						$end .= date( 'His', $date['end_datetime'] );
					} else {
						$end = date( 'Ymd', $date['end_datetime'] + DAY_IN_SECONDS );
						$end .= 'T';
						$end .= date( 'His', $date['end_datetime'] );
					}

					$url = add_query_arg(
						array(
							'action'   => 'TEMPLATE',
							'text'     => urlencode( $post->post_title ),
							'details'  => urlencode( $post->post_excerpt ),
							'location' => $venue,
							'dates'    => $start . '/' . $end,
						),
						'http://www.google.com/calendar/event'
					);
					?>
					<a href="<?php echo $url; ?>" class="calendar-button btn btn-icon" target="_blank"><i class="ico-calendar"></i> <span>+ Calendar</span></a>
				</div>

				<?php printf( '<div %s>', genesis_attr( 'entry-content' ) ); ?>
					<?php the_content(); ?>
				</div>
			</div>

			<div class="one-half">
				<?php
				if( has_post_thumbnail() )
					the_post_thumbnail( 'featured', array('class' => 'aligncenter') );

				$map_address = get_post_meta( $post->ID, '_hc_event_map_address', true );
				if( !empty($map_address) )
					HC()->formatting->display_map($map_address, 630, 300);
				?>
			</div>
		</article>
		<?php

	}

}

new HC_Events();
