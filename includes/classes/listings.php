<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Listings {
	public function __construct() {

		add_action( 'init', array($this, 'register') );
		add_action( 'wp', array($this, 'init') );

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
				'supports'        => array('title', 'thumbnail', 'editor'),
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
			'rewrite'           => array('slug' => 'listing-category'),
		);

		register_taxonomy( 'listing-category', array('listing'), $args );

	}

	public function init() {

		if( !is_singular('listing') )
			return;

		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );
		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_loop', array($this, 'do_single_listing') );

	}

	public function do_single_listing() {

		global $post;

		printf( '<article %s>', genesis_attr( 'entry' ) );
			?>
			<div class="two-fifths first">
				<?php
				genesis_entry_header_markup_open();
					genesis_do_post_title();

					$categories = wp_get_object_terms( $post->ID, 'listing-category' );
					if( !empty($categories) ) {
						$category_links = array();

						foreach( $categories as $category )
							$category_links[] = '<a href="' . get_term_link($category) . '">' . $category->name . '</a>';

						echo '<p class="entry-meta">' . implode( ', ', $category_links ) . '</p>';
					}
				genesis_entry_header_markup_close();
				?>

				<?php
				$lines = array();

				// Address
				$address = get_post_meta( $post->ID, '_hc_listing_address', true );
				if( !empty($address) )
					$lines['Address'] = sanitize_text_field($address);

				// Hours
				$hours = get_post_meta( $post->ID, '_hc_event_hours', true );
				if( !empty($hours) )
					$lines['Hours'] = sanitize_text_field($hours);

				// Contact
				$contact = get_post_meta( $post->ID, '_hc_listing_contact', true );
				if( !empty($contact) )
					$lines['Contact'] = sanitize_text_field($contact);

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
					<?php HC()->favorites->display( $post->ID ); ?>
					<?php HC()->share->display( $post->ID ); ?>
					<button type="button" class="calendar-button btn btn-icon"><i class="ico-star-o"></i> <span>Rate</span></button>
				</div>

				<?php printf( '<div %s>', genesis_attr( 'entry-content' ) ); ?>
					<?php the_content(); ?>
				</div>
			</div>

			<div class="three-fifths">
				<?php
				if( has_post_thumbnail() )
					the_post_thumbnail( 'featured', array('class' => 'aligncenter') );

				$map_address = get_post_meta( $post->ID, '_hc_listing_map_address', true );
				if( !empty($map_address) )
					HC()->formatting->display_map($map_address, 790, 380);
				?>
			</div>
		</article>
		<?php

	}

}

new HC_Listings();
