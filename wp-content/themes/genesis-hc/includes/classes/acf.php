<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_ACF {
	public function __construct() {

		add_filter('acf/load_field/name=_hc_category_icon', array($this, 'load_icon_choices') );
		add_filter('acf/load_field/name=icon', array($this, 'load_icon_choices'));

	}

	public function load_icon_choices( $field ) {

		$field['choices'] = array();

		if( file_exists(CHILD_DIR . '/sass/_animations.scss') ) {
			$css = file_get_contents(CHILD_DIR . '/sass/_animations.scss');

			preg_match_all( '/\.animation-(.+?)-#\{\$color\} /', $css, $matches );

			if( !empty($matches[1]) ) {
				foreach( $matches[1] as $slug ) {
					if( in_array($slug, array('small', 'large'), true) )
						continue;

					$field['choices'][$slug] = ucfirst($slug);
				}
			}
		}

		return $field;

	}

}

return new HC_ACF();
