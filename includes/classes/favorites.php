<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Favorites {
	public function display( $post_id ) {

		?>
		<button class="favorites-button"><i class="ico-heart"></i> <span>+ Save to Favorites</span></button>
		<?php


	}
}

return new HC_Favorites();
