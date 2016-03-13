<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Share {
	public function display( $post_id ) {

		?>
		<button class="share-button"><i class="ico-share"></i> <span>Share</span></button>
		<?php


	}
}

return new HC_Share();
