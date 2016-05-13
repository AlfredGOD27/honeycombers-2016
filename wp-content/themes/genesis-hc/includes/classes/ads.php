<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Ads {
	public function __construct() {

		$this->ads = array();
		add_action( 'wp', array($this, 'setup_applicable_ads') );
		add_action( 'wp_head', array($this, 'head_script'), 99 );

	}

	private function sanitize_ad_id( $id ) {

		$id = str_replace( 'div-gpt-ad-', '', $id );
		if( empty($id) )
			return;
		$id = explode('-', $id);
		if( !isset($id[1]) )
			$id[] = 0;
		$id    = implode('-', $id);

		return $id;

	}

	public function setup_applicable_ads() {

		global $post;

		if( is_singular('page') ) {
			switch( get_page_template_slug($post->ID) ) {
				case 'page_templates/page_home.php':
					// Home Ads
					break;
				case 'page_templates/page_calendar.php':
					// Calendar Ads
					break;
			}
		} elseif( is_search() ) {
			// Search Ads

			$ads = get_field( '_hc_search_mpu_1', 'option' );
			if( !empty($ads) ) {
				$ad                 = array_pop($ads);
				$this->ads['mpu-1'] = array(
					'id'   => $this->sanitize_ad_id( $ad['id'] ),
					'name' => $ad['name'],
				);
			}
		} elseif( is_author() ) {
			// Author Ads
		} elseif( is_archive() ) {
			// Archive Ads
		}

	}

	public function head_script() {

		if( empty($this->ads) )
			return;

		ob_start();
		?>
		<script>
			var googletag = googletag || {};
			googletag.cmd = googletag.cmd || [];
			(function() {
				var gads = document.createElement('script');
				gads.async = true;
				gads.type = 'text/javascript';
				var useSSL = 'https:' == document.location.protocol;
				gads.src = (useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js';
				var node = document.getElementsByTagName('script')[0];
				node.parentNode.insertBefore(gads, node);
			})();

			googletag.cmd.push(function() {
				<?php foreach( $this->ads as $ad ) {
					?>
					if( document.getElementById('div-gpt-ad-<?php echo $ad['id']; ?>') ) {
						googletag.defineSlot(
							'<?php echo $ad['name']; ?>',
							'fluid',
							'div-gpt-ad-<?php echo $ad['id']; ?>'
						).addService( googletag.pubads() );
					}
				<?php } ?>
				googletag.pubads().enableSingleRequest();
				googletag.pubads().collapseEmptyDivs();
				googletag.enableServices();
			});
		</script>

		<?php
		$output = ob_get_clean();
		echo preg_replace( '/\s+/', ' ', $output ) . "\n";

	}

	private function get_ad_in_position( $position ) {

		if( isset($this->ads[$position]) )
			return $this->ads[$position];

		return false;

	}

	public function get_ad_container( $position ) {

		$ad = $this->get_ad_in_position( $position );
		if( empty($ad) )
			return;

		ob_start();
		?>
		<div id="div-gpt-ad-<?php echo $ad['id']; ?>" class="ad <?php echo $position; ?>-ad clearfix">
			<script>
				(function() {
					googletag.cmd.push(function() {googletag.display('div-gpt-ad-<?php echo $ad['id']; ?>')});
				})();
			</script>
		</div>
		<?php
		$output = ob_get_clean();

		return preg_replace( '/\s+/', ' ', $output ) . "\n";

	}

}

return new HC_Ads();
