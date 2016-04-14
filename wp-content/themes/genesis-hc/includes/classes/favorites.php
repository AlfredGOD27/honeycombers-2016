<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Favorites {
	public function __construct() {

		$this->key = '_hc_folders';

	}

	public function display( $post_id, $icon_only = false ) {

		?>
		<button class="favorites-button btn btn-icon">
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

	public function reset_folders( $user_id ) {

		$folders = array();

		$folders[] = array(
			'name'        => 'Favourites',
			'slug'        => 'favourites',
			'description' => 'Save articles to come back to again and again!',
			'icon'        => 'heart',
		);

		$folders[] = array(
			'name'        => 'Reading List',
			'slug'        => 'reading-list',
			'description' => 'Don’t have time to read it now, save it here for later!',
			'icon'        => 'book',
		);

		$folders[] = array(
			'name'        => 'Itineraries',
			'slug'        => 'itineraries',
			'description' => 'Planning a trip? Save your favourite travel articles here!',
			'icon'        => 'globe',
		);

		update_user_meta( $user_id, $this->key, $folders );

	}

	public function get_folders( $user_id ) {

		$base_url = HC()->profiles->get_url('view-folder');

		$folders = get_user_meta( $user_id, $this->key, true );
		foreach( $folders as $idx => $folder ) {
			$folders[$idx]['url'] = add_query_arg(
				array(
					'folder' => $folder['slug'],
				),
				$base_url
			);
		}

		return !empty($folders) ? $folders : array();

	}
}

return new HC_Favorites();
