<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Ad_Widget extends WP_Widget {
	public function __construct() {

		parent::__construct(false, $name = 'HC: Ad');

	}

	public function widget( $args, $instance ) {

		extract($args);

		$code = get_field( '_hc_ad_code', 'widget_' . $widget_id );
		if( empty($code) )
			return;

		echo $before_widget;
			echo $code;
		echo $after_widget;

	}

	public function form( $instance ) {

	}

}
