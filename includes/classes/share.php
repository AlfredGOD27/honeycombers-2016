<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Share {
	public function display( $post_id ) {
		
		/*
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
		*/
		?>
		<?php /* <button class="share-button btn btn-icon" id="postshare"><i class="ico-share"></i><span>Share</span></button> */ ?>
        <?php /* <div class="pw-server-widget" data-id="<?php echo $data_id; ?>"></div> */ ?>
        <div class="share-icons">
        	<a class="whatsapp-share" href="whatsapp://send?text=<?php the_permalink(); ?>" data-action="share/whatsapp/share" target="_blank"><i class="fa fa-whatsapp" aria-hidden="true"></i></a>
            <a class="facebook-share" href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank"><i class="fa fa-facebook" aria-hidden="true"></i></a>
            <a class="twitter-share" href="https://twitter.com/home?status=<?php the_permalink(); ?>" target="_blank"><i class="fa fa-twitter" aria-hidden="true"></i></a>
            <a class="pinterest-share" href="https://pinterest.com/pin/create/button/?url=&media=<?php the_permalink(); ?>&description=" target="_blank"><i class="fa fa-pinterest" aria-hidden="true"></i></a>
            <a class="email-share" href="mailto:?&body=<?php the_permalink(); ?>" target="_blank"><i class="fa fa-envelope" aria-hidden="true"></i></a>
        </div>
		<?php

	}
}

return new HC_Share();
