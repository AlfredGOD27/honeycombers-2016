<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Share {
	public function display( $post_id ) {

        $blog_id = get_current_blog_id();

        switch( $blog_id ) {
	        case 2:
		        $data_id = 'wid-vmsg7u0o';
	        	break;
	        case 3:
		        $data_id = 'wid-t7qeueqr';
	        	break;
	        case 4:
		        $data_id = 'wid-4t5arscc';
	        	break;
        }

        if( empty($blog_id) )
        	return;

		?>
		<button class="share-button btn btn-icon" id="postshare"><i class="ico-share"></i><span>Share</span></button>
        <div class="pw-server-widget" data-id="<?php echo $data_id; ?>"></div>
		<?php

	}
}

return new HC_Share();
