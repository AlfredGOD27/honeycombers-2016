<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Utilities {
	public function get_page_link( $key ) {

		$page_id = get_option( 'options_', $key );
		if( empty($page_id) )
			return;

		return get_permalink($page_id);

	}

	public function get_category_icon_html( $category, $size = 'small', $color = 'orange' ) {

		$icon = get_field( '_hc_category_icon', $category );
		if( !empty($icon) )
			return '<i class="animation animation-' . $size . ' category-icon animation-' . $icon . '-' . $color . '"></i>';

	}

	public function atts_to_html( $atts ) {

		$html = array();
		foreach( $atts as $att => $value )
			$html[] = $att . '="' . esc_attr($value) . '"';

		return implode( ' ', $html );

	}

	public function get_async_image_placeholder( $atts, $placeholder_class = '' ) {

		$data_atts = array();
		foreach( $atts as $att => $value )
			$data_atts[ 'data-' . $att ] = $value;

		$data_atts['class'] = 'async-load-image ' . $placeholder_class;

		$atts_html = $this->atts_to_html( $data_atts );

		return '<span ' . $atts_html . '></span>';

	}

}

return new HC_Utilities();
