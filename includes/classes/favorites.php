<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Favorites {
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
}

return new HC_Favorites();
