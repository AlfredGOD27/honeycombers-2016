<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Folders {
	public function __construct() {

		$this->folder_slug = 'folder';

		add_action( 'init', array($this, 'register_post_type') );
		add_action( 'init', array($this, 'rewrites'), 1 );
		add_action( 'wp', array($this, 'init') );

	}

	public function register_post_type() {

		register_post_type(
			'folder',
			array(
				'labels' => array(
					'name'               => __( 'Folders', 'post type general name', CHILD_THEME_TEXT_DOMAIN ),
					'singular_name'      => __( 'Folder', 'post type singular name', CHILD_THEME_TEXT_DOMAIN ),
					'add_new'            => __( 'Add New', 'custom post type item', CHILD_THEME_TEXT_DOMAIN ),
					'add_new_item'       => __( 'Add New Folder', CHILD_THEME_TEXT_DOMAIN ),
					'edit'               => __( 'Edit', CHILD_THEME_TEXT_DOMAIN ),
					'edit_item'          => __( 'Edit Folder', CHILD_THEME_TEXT_DOMAIN ),
					'new_item'           => __( 'New Folder', CHILD_THEME_TEXT_DOMAIN ),
					'view_item'          => __( 'View Folder', CHILD_THEME_TEXT_DOMAIN ),
					'search_items'       => __( 'Search Folders', CHILD_THEME_TEXT_DOMAIN ),
					'not_found'          => __( 'Nothing found in the database.', CHILD_THEME_TEXT_DOMAIN ),
					'not_found_in_trash' => __( 'Nothing found in Trash', CHILD_THEME_TEXT_DOMAIN ),
					'parent_item_colon'  => '',
				),
				'public'    => true,
				'show_ui'   => 101028 === get_current_user_id() && current_user_can('manage_options'),
				'menu_icon' => 'dashicons-portfolio',
				'rewrite'   => array('slug' => $this->folder_slug),
				'supports'  => array('title', 'author'),
		 	)
		);

	}

	// http://wordpress.stackexchange.com/questions/26388/how-to-create-custom-url-routes
	public function rewrites() {

		$page_id = get_option( 'options__hc_profile_page_id' );

		add_rewrite_tag( '%hc_folder_slug%', '([^&]+)' );
		add_rewrite_tag( '%hc_folder_action%', '([^&]+)' );

		add_rewrite_rule(
			'^' . $this->folder_slug . '/new/?$',
			'index.php?p=' . $page_id . '&&hc_folder_action=add',
			'top'
		);

		add_rewrite_rule(
			'^' . $this->folder_slug . '/([^/]+)/edit/?$',
			'index.php?p=' . $page_id . '&hc_folder_slug=$matches[1]&hc_folder_action=edit',
			'top'
		);

	}

	public function init() {

		global $wp_query;

		$this->action = get_query_var( 'hc_folder_action' );
		if( empty($this->action) || !in_array($this->action, array('add', 'edit'), true ) )
			return;

		if( !is_user_logged_in() )
			return;

		$this->user_id = get_current_user_id();
		$this->user    = get_user_by( 'id', $this->user_id );

		switch( $this->action ) {
			case 'add':
				$wp_query->is_404 = false;
				status_header(200);
				break;
			case 'edit':
				$this->slug = get_query_var( 'hc_folder_slug' );
				if( empty($this->slug) )
					return;

				$args = array(
					'posts_per_page' => 1,
					'post_type'      => 'folder',
					'name'           => sanitize_title($this->slug),
					'fields'         => 'ids',
				);

				if( !current_user_can('manage_options') )
					$args['post_author'] = get_current_user_id();

				$items = get_posts( $args );
				if( empty($items) )
					return;

				$wp_query->is_404 = false;
				status_header(200);

				HC()->profiles->user_id = $this->user_id;
				HC()->profiles->user    = $this->user;

				$this->form = new HC_Folder_Editor( $items[0] );

				add_action( 'wp_enqueue_scripts', array(HC()->profiles, 'load_assets') );
				add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
				add_filter( 'body_class', array(HC()->profiles, 'body_classes') );
				add_action( 'genesis_loop', array(HC()->profiles, 'display_heading') );
				add_action( 'genesis_loop', array(HC()->messages, 'display') );
				add_action( 'genesis_loop', array($this->form, 'display_form') );
				remove_action( 'genesis_loop', 'genesis_do_loop' );

				$this->item = $items[0];
				break;
		}

	}

	public function display_add_button( $post_id, $icon_only = false ) {

		?>
		<button class="bookmarks-button btn btn-icon">
			<i class="ico-heart"></i>
			<?php
			if( !$icon_only ) {
				?>
				<span>+ Save to Favorites</span>
				<?php
			}
			?>
		</button>
		<?php

	}

	public function create_default_folders_for_user( $user_id ) {

		$folders = array();

		$folders[] = array(
			'name'        => 'Favourites',
			'description' => 'Save articles to come back to again and again!',
			'icon'        => 'heart',
		);

		$folders[] = array(
			'name'        => 'Reading List',
			'description' => 'Don’t have time to read it now, save it here for later!',
			'icon'        => 'book',
		);

		$folders[] = array(
			'name'        => 'Itineraries',
			'description' => 'Planning a trip? Save your favourite travel articles here!',
			'icon'        => 'globe',
		);

		foreach( $folders as $folder ) {
			$args = array(
				'post_title'   => $folder['name'],
				'post_content' => $folder['description'],
				'post_status'  => 'publish',
				'post_author'  => $user_id,
				'post_type'    => 'folder',
			);
			$post_id = wp_insert_post( $args );

			if( isset($folder['icon']) )
				update_post_meta( $post_id, '_hc_folder_icon', $folder['icon'] );

		}

	}

	public function is_public( $folder_id ) {

		$is_public = get_post_meta( $folder_id, '_hc_folder_is_public', true );

		return 'Yes' === $is_public;

	}

	public function can_view( $folder_id ) {

		if( current_user_can( 'edit_post', $folder_id ) )
			return true;

		return $this->is_public( $folder_id );

	}

	public function get_user_folder_ids( $user_id, $public_only = false ) {

		$args = array(
			'post_type'      => 'folder',
			'posts_per_page' => -1,
			'author'         => $user_id,
			'fields'         => 'ids',
		);

		if( $public_only ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_hc_folder_is_public',
					'value' => 'Yes',
				),
			);
		}

		$folders = get_posts( $args );

		return $folders;

	}

	public function get_add_url() {

		$url = get_bloginfo('url');
		$url = trailingslashit($url);
		$url .= $this->folder_slug . '/';
		$url .= 'new/';

		return $url;

	}

	private function get_edit_url( $item_id ) {

		$url = get_permalink( $item_id );
		$url = trailingslashit($url) . 'edit/';

		return $url;

	}

	public function add_item_to_folder( $item_id, $folder_id ) {

		$item_id = absint($item_id);

		// Make sure item is a valid post object
		$post = get_post( $item_id );
		if(
			empty($post) ||
			!in_array( $post->post_type, array('post', 'event', 'listing'), true )
		)
			return;

		$items   = get_post_meta( $folder_id, '_hc_folder_item_ids', true );
		$items   = !empty($items) ? array_map( 'absint', $items ) : array();
		$items[] = $item_id;
		$items   = array_unique($items);
		update_post_meta( $folder_id, '_hc_folder_item_ids', $items );

	}

}

return new HC_Folders();
