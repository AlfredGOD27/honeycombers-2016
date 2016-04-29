<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Listings {
	public function __construct() {

		$this->results_per_page = 24;

		add_action( 'init', array($this, 'register') );
		add_action( 'wp', array($this, 'init') );

		add_action( 'wp_ajax_hc_get_listings', array($this, 'ajax_get_listings') );
		add_action( 'wp_ajax_nopriv_hc_get_listings', array($this, 'ajax_get_listings') );

		add_action( 'save_post', array($this, 'save_coords'), 10, 3 );

	}

	public function register() {

		register_post_type( 'listing',
			array(
				'labels' => array(
					'name'               => __('Listings', 'post type general name'),
					'singular_name'      => __('Listing', 'post type singular name'),
					'add_new'            => __('Add New', 'custom post type item'),
					'add_new_item'       => __('Add New Listing'),
					'edit'               => __( 'Edit' ),
					'edit_item'          => __('Edit Listing'),
					'new_item'           => __('New Listing'),
					'view_item'          => __('View Listing'),
					'search_items'       => __('Search Listings'),
					'not_found'          => __('Nothing found in the Database.'),
					'not_found_in_trash' => __('Nothing found in Trash'),
					'parent_item_colon'  => '',
				),
				'public'          => true,
				'has_archive'     => false,
				'capability_type' => 'post',
				'hierarchical'    => false,
				'menu_icon'       => 'dashicons-building',
				'supports'        => array('title', 'editor'),
			)
		);

		$labels = array(
			'name'              => _x( 'Listing Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Listing Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Listing Categories' ),
			'all_items'         => __( 'All Listing Categories' ),
			'parent_item'       => __( 'Parent Listing Category' ),
			'parent_item_colon' => __( 'Parent Listing Category:' ),
			'edit_item'         => __( 'Edit Listing Category' ),
			'update_item'       => __( 'Update Listing Category' ),
			'add_new_item'      => __( 'Add New Listing Category' ),
			'new_item_name'     => __( 'New Listing Category Name' ),
			'menu_name'         => __( 'Listing Category' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'directories'),
		);

		register_taxonomy( 'directories', array('listing'), $args );

		$labels = array(
			'name'              => _x( 'Listing Locations', 'taxonomy general name' ),
			'singular_name'     => _x( 'Listing Location', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Listing Locations' ),
			'all_items'         => __( 'All Listing Locations' ),
			'parent_item'       => __( 'Parent Listing Location' ),
			'parent_item_colon' => __( 'Parent Listing Location:' ),
			'edit_item'         => __( 'Edit Listing Location' ),
			'update_item'       => __( 'Update Listing Location' ),
			'add_new_item'      => __( 'Add New Listing Location' ),
			'new_item_name'     => __( 'New Listing Location Name' ),
			'menu_name'         => __( 'Listing Location' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => false,
			'public'            => false,
		);

		register_taxonomy( 'locations', array('listing'), $args );

	}

	public function init() {

		if( is_singular('listing') ) {
			add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
			remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			add_action( 'genesis_loop', array($this, 'do_single_listing') );
		} else {
			if( 'page_templates/page_directory.php' === get_page_template_slug() ) {
				add_action( 'wp_enqueue_scripts', array($this, 'load_directory_assets') );
				add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
				remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );
				remove_action( 'genesis_loop', 'genesis_do_loop' );
				add_action( 'genesis_loop', array($this, 'do_directory') );

				remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );
				remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
				remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
				remove_action( 'genesis_footer', 'hc_do_footer' );
			}
		}

	}

	public function do_single_listing() {

		global $post;

		printf( '<article %s>', genesis_attr( 'entry' ) );
			?>
			<div class="two-fifths first">
				<?php
				genesis_entry_header_markup_open();
					genesis_do_post_title();

					$categories = wp_get_object_terms( $post->ID, 'directories' );
					if( !empty($categories) ) {
						$category_links = array();

						foreach( $categories as $category )
							$category_links[] = '<a href="' . get_term_link($category) . '">' . $category->name . '</a>';

						echo '<p class="entry-meta">' . implode( ', ', $category_links ) . '</p>';
					}
				genesis_entry_header_markup_close();

				HC()->ratings->display( $post->ID );
				?>

				<?php
				$lines = array();

				// Address
				$map_address = get_field( '_hc_listing_address_map' );
				$address     = get_post_meta( $post->ID, '_hc_listing_address_text', true );
				if( !empty($address) ) {
					$lines['Address'] = sanitize_text_field($address);
				} else {
					if( !empty($map_address['address']) )
						$lines['Address'] = sanitize_text_field($map_address['address']);
				}

				// Hours
				$hours = get_post_meta( $post->ID, '_hc_event_hours', true );
				if( !empty($hours) )
					$lines['Hours'] = sanitize_text_field($hours);

				// Phone
				$phone = get_post_meta( $post->ID, '_hc_listing_phone', true );
				if( !empty($phone) )
					$lines['Phone'] = sanitize_text_field($phone);

				// Email
				$email = get_post_meta( $post->ID, '_hc_listing_email', true );
				if( !empty($email) )
					$lines['Email'] = '<a href="mailto:' . sanitize_email($email) . '">' . sanitize_text_field($email) . '</a>';

				// Website
				$website = get_post_meta( $post->ID, '_hc_listing_website', true );
				if( !empty($website) )
					$lines['Website'] = HC()->formatting->get_linked_url( $website );

				// Good for
				$good_for = get_post_meta( $post->ID, '_hc_listing_good_for', true );
				if( !empty($good_for) )
					$lines['Good For'] = sanitize_text_field($good_for);

				if( count($lines) > 0 )
					HC()->formatting->display_data_list($lines);
				?>

				<div class="item-action-row">
					<?php HC()->folders->display_add_button( $post->ID ); ?>
					<?php HC()->share->display( $post->ID ); ?>
					<?php HC()->ratings->display_button( $post->ID ); ?>
				</div>

				<?php printf( '<div %s>', genesis_attr( 'entry-content' ) ); ?>
					<?php the_content(); ?>
				</div>
			</div>

			<div class="three-fifths">
				<?php
				if( has_post_thumbnail() )
					the_post_thumbnail( 'featured', array('class' => 'aligncenter') );

				if( !empty($map_address['address']) )
					HC()->formatting->display_map($map_address['address'], 790, 380);
				?>
			</div>

			<?php
			HC()->related->display_related_content( $post, 'aside' );
			?>
		</article>
		<?php

	}

	public function load_directory_assets() {

		$maps_url = add_query_arg(
			array(
				'callback' => 'hc_directory_maps',
				'v'        => '3.23',
				'key'      => get_field( '_hc_google_maps_api_key', 'option' ),
			),
			'//maps.googleapis.com/maps/api/js'
		);
		wp_enqueue_script( 'hc-google-maps', $maps_url, array(), null, true );

		$map = get_field( '_hc_directory_default_map_center' );
		wp_localize_script(
			'hc-google-maps',
			'hc_directory_coords',
			array(
				'lat' => round( $map['lat'], 3 ),
				'lng' => round( $map['lng'], 3 ),
			)
		);

	}

	public function ajax_get_listings() {

		$output = array();

		if( empty($_POST['type']) || !in_array( $_POST['type'], array('form', 'map'), true) ) {
			$output['status']  = 'error';
			$output['message'] = 'Search type not indicated.';
			echo json_encode($output);
			wp_die();
		}
		$type = $_POST['type'];

		$text = false;
		if( !empty($_POST['text']) )
			$text = sanitize_text_field($_POST['text']);

		$category_id = false;
		if( !empty($_POST['category_id']) )
			$category_id = absint($_POST['category_id']);

		$args                           = array();
		$args['post_type']              = 'listing';
		$args['posts_per_page']         = $this->results_per_page;
		$args['update_post_meta_cache'] = false;
		$args['update_post_term_cache'] = false;
		$args['tax_query']              = array();
		$args['tax_query']['relation']  = 'AND';

		if( !empty($text) )
			$args['s'] = $text;

		if( !empty($category_id) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'directories',
				'field'    => 'term_id',
				'terms'    => $category_id,
			);
		}

		switch( $type ) {
			case 'form':
				$location_id = false;
				if( !empty($_POST['location_id']) )
					$location_id = absint($_POST['location_id']);

				if( empty($location_id) ) {
					$output['status']  = 'error';
					$output['message'] = 'You must select a location';
					echo json_encode($output);
					wp_die();
				}

				if(
					empty($text) &&
					empty($location_id) &&
					empty($category_id)
				) {
					$output['status']  = 'error';
					$output['message'] = 'You must enter search text, a location, or a category';
					echo json_encode($output);
					wp_die();
				}

				if( !empty($location_id) ) {
					$args['tax_query'][] = array(
						'taxonomy' => 'locations',
						'field'    => 'term_id',
						'terms'    => $location_id,
					);
				}
				break;
			case 'map':
				$corners = array('ne', 'nw', 'se', 'sw');

				$coords = array();
				foreach( $corners as $key ) {
					if( empty($_POST[$key]) ) {
						$output['status']  = 'error';
						$output['message'] = 'Invalid coordinated';
						echo json_encode($output);
						wp_die();
					}

					$coords[$key] = array_map( 'floatval', (array) $_POST[$key] );
				}

				$args['meta_query']             = array();
				$args['meta_query']['relation'] = 'AND';

				$args['meta_query'][] = array(
					'key'     => '_hc_listing_lat',
					'value'   => array($coords['se'][0], $coords['ne'][0]),
					'compare' => 'BETWEEN',
				);

				$args['meta_query'][] = array(
					'key'     => '_hc_listing_lng',
					'value'   => array($coords['nw'][1], $coords['ne'][1]),
					'compare' => 'BETWEEN',
				);

				break;
		}

		$output['query'] = print_r($args, true);

		$listings = get_posts( $args );
		if( empty($listings) ) {
			$output['status']  = 'info';
			$output['message'] = 'No listings found';
			echo json_encode($output);
			wp_die();
		}

		$output['status'] = 'success';
		$output['items']  = array();
		$i                = 1;
		foreach( $listings as $listing ) {
			$map = get_field( '_hc_listing_address_map', $listing->ID );
			if( empty($map['lat']) || empty($map['lng']) )
				continue;

			$categories = array();
			$terms      = wp_get_object_terms( $listing->ID, 'directories' );
			foreach( $terms as $term )
				$categories[] = $term->name;

			$locations = array();
			$terms     = wp_get_object_terms( $listing->ID, 'locations' );
			foreach( $terms as $term )
				$locations[] = $term->name;

			$info_window_html = '<span class="result-title">' . $listing->post_title . '</span>';
			$info_window_html .= '<span class="result-category">' . HC()->formatting->build_comma_separated_list( $categories ) . '</span>';

			$result_html = 1 === $i % 2 ? '<a href="' . get_permalink( $listing->ID ) . '" class="listing-result one-half first">' : '<a href="' . get_permalink( $listing->ID ) . '" class="listing-result one-half">';
				if( has_post_thumbnail($listing->ID) )
					$result_html .= get_the_post_thumbnail($listing->ID, 'archive-small' );

				$result_html .= '<h3>' . $listing->post_title . '</h3>';

				$meta = array();
				if( !empty($locations) )
					$meta[] = HC()->formatting->build_comma_separated_list( $locations );

				$contact = get_post_meta( $listing->ID, '_hc_listing_contact', true );
				if( !empty($contact) )
					$meta[] = sanitize_text_field($contact);

				if( !empty($meta) )
					$result_html .= '<span class="meta">' . implode( ' | ', $meta ) . '</span>';

				$result_html .= wpautop( HC()->formatting->get_excerpt( $listing, 100) );

				$result_html .= '<span class="more-link">Read more ></span>';
			$result_html .= '</div>';

			$output['items'][] = array(
				'id'               => $listing->ID,
				'name'             => $listing->post_title,
				'lat'              => round( $map['lat'], 3 ),
				'lng'              => round( $map['lng'], 3 ),
				'info_window_html' => $info_window_html,
				'result_html'      => $result_html,
			);
			++$i;
		}

		$count             = count($output['items']);
		$count             = $this->results_per_page === $count ? $count . '+' : $count;
		$output['heading'] = $count . ' Directory ' .  _n('Result', 'Results', count($output['items']) );

		echo json_encode($output);
		wp_die();

	}

	public function do_directory() {

		?>
		<div class="directory-map-container">
			<div class="directory-map"></div>
		</div>

		<div class="directory-search">
			<form class="directory-search-form clearfix">
				<div class="row clearfix">
					<div class="three-fifths first">
						<h2>Search the Directory</h2>
					</div>

					<div class="two-fifths hide-phone">
						<a href="<?php echo HC()->utilities->get_page_link('_hc_claim_listing_page_id'); ?>" class="btn">Submit A Listing</a>
					</div>
				</div>

				<div class="first">
					<label for="directory-search-text">Search for...</label>
					<input id="directory-search-text" type="search" placeholder="Search for...">
				</div>

				<div class="row clearfix">
					<div class="two-fifths first select-container">
						<label for="directory-location">Location</label>
						<select id="directory-location" name="location" class="styled" required>
							<option value="">Location</option>
							<?php
							$terms = get_terms( 'locations' );
							foreach( $terms as $term ) {
								?>
								<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
								<?php
							}
							?>
						</select>
						<i class="ico-arrow-down"></i>
					</div>

					<div class="two-fifths select-container">
						<label for="directory-category">Category</label>
						<select id="directory-category" name="category" class="styled">
							<option value="">Category</option>
							<?php
							$terms = get_terms( 'directories' );
							foreach( $terms as $term ) {
								?>
								<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
								<?php
							}
							?>
						</select>
						<i class="ico-arrow-down"></i>
					</div>

					<div class="one-fifth">
						<button type="submit">Search</button>
					</div>
				</div>
			</form>

			<div class="directory-search-results clearfix"></div>
		</div>
		<?php

	}

	public function save_coords( $post_id, $post, $update ) {

		if( wp_is_post_revision( $post_id ) )
			return;

		if( 'listing' !== $post->post_type )
			return;

		$coords = get_post_meta( $post_id, '_hc_listing_address_map', true );

		if( !empty($coords['lat']) ) {
			$value = (float) $coords['lat'];
			update_post_meta( $post_id, '_hc_listing_lat', $value );
		} else {
			delete_post_meta( $post_id, '_hc_listing_lat' );
		}

		if( !empty($coords['lng']) ) {
			$value = (float) $coords['lng'];
			update_post_meta( $post_id, '_hc_listing_lng', $value );
		} else {
			delete_post_meta( $post_id, '_hc_listing_lng' );
		}

	}

}

return new HC_Listings();
