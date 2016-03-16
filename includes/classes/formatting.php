<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Formatting {
	public function build_comma_separated_list( $items ) {

		$count = count($items);

		if( $count === 0 )
			return;

		if( $count === 1 )
			return $items[0];

		return implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items);

	}

	public function maybe_truncate( $content, $max_length = 250 ) {

		$content = strip_shortcodes( $content );
		$content = sanitize_text_field( $content );

		if( strlen($content) > $max_length ) {
			$content = genesis_truncate_phrase( $content, $max_length - 3 );
			$content = trim($content);

			return $content . '...';
		}

		return $content;

	}

	public function get_excerpt( $post, $max_length = 250 ) {

		if( !empty($post->post_excerpt) ) {
			$content = $post->post_excerpt;
		} else {
			$seo_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
			if( !empty($seo_description) ) {
				$content = $seo_description;
			} else {
				$content = $post->post_content;
			}
		}

		return $this->maybe_truncate( $content, $max_length );

	}

}

return new HC_Formatting();
