<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_ACF {
	public function __construct() {

		add_filter('acf/load_field/name=_hc_category_icon', array($this, 'load_icon_choices') );
		add_filter('acf/load_field/name=icon', array($this, 'load_icon_choices'));

	}

	public function load_icon_choices( $field ) {

		$field['choices'] = array();

		$animations = array(
			'calendar',
			'connect',
			'directory',
			'drinks',
			'food',
			'hacks',
			'hitlist',
			'recharge',
			'shopping',
			'travel',
			'video',
			'win',
		);

		foreach( $animations as $animation )
			$field['choices'][$animation] = ucfirst($animation);

		return $field;

	}

}

return new HC_ACF();
