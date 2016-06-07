<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Ad_Widget extends WP_Widget {
	public function __construct() {

		parent::__construct(false, $name = 'HC: Ad');

	}

	public function widget( $args, $instance ) {

		extract($args);

		$position = get_field( '_hc_ad_position', 'widget_' . $widget_id );
		if( empty($position) )
			return;

		$html = HC()->ads->get_ad_container( $position );
		if( empty($html) )
			return;

		echo $before_widget;
			echo $html;
		echo $after_widget;

	}

	public function form( $instance ) {

	}

}
