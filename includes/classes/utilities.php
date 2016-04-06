<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Utilities {
	public function get_page_link( $key ) {

		$page_id = get_option( 'options_', $key );
		if( empty($page_id) )
			return;

		return get_permalink($page_id);

	}

}

return new HC_Utilities();
