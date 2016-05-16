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

	private function ad_field_to_array( $field ) {

		if( empty($field[0]['id']) )
			return false;

		return array(
			'id'   => $this->sanitize_ad_id( $field[0]['id'] ),
			'name' => $field[0]['name'],
		);

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
			$ad  = $this->ad_field_to_array( $ads );
			if( false !== $ad )
				$this->ads['mpu-1'] = $ad;
		} elseif( is_author() ) {
			// Author Ads
			$ads = get_field( '_hc_author_mpu_1', 'option' );
			$ad  = $this->ad_field_to_array( $ads );
			if( false !== $ad )
				$this->ads['mpu-1'] = $ad;
		} elseif( is_singular('post') ) {
			// Post Ads
			$ads = array(
				'mpu-1' => '_hc_mpu_1',
				'mpu-2' => '_hc_mpu_2',
			);

			foreach( $ads as $position => $key ) {
				$ads    = get_field( $key );
				$result = $this->ad_field_to_array( $ads );
				if( false !== $result ) {
					$this->ads[$position] = $result;
				} else {
					$term = HC()->utilities->get_primary_term( $post->ID, 'category' );
					if( !empty($term) ) {
						$ads    = get_field( $key, $term );
						$result = $this->ad_field_to_array( $ads );
						if( false !== $result ) {
							$this->ads[$position] = $result;
						} elseif( $term->parent > 0 ) {
							$parent = get_term_by( 'id', $term->parent, $term->taxonomy );
							$ads    = get_field( $key, $parent );
							$result = $this->ad_field_to_array( $ads );
							if( false !== $result )
								$this->ads[$position] = $result;
						}
					}
				}
			}
		} elseif( is_archive() ) {
			// Archive Ads
			$term = get_queried_object();

			$ads = array(
				'mpu-1' => '_hc_mpu_1',
				'mpu-2' => '_hc_mpu_2',
			);

			foreach( $ads as $position => $key ) {
				$ads    = get_field( $key, $term );
				$result = $this->ad_field_to_array( $ads );
				if( false !== $result ) {
					$this->ads[$position] = $result;
				} elseif( $term->parent > 0 ) {
					$parent = get_term_by( 'id', $term->parent, $term->taxonomy );
					$ads    = get_field( $key, $parent );
					$result = $this->ad_field_to_array( $ads );
					if( false !== $result )
						$this->ads[$position] = $result;
				}
			}
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
