<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Subscribe_Widget extends WP_Widget {
	public function __construct() {

		parent::__construct(false, $name = 'HC: Subscribe');

	}

	public function widget( $args, $instance ) {

		extract($args);

		echo $before_widget;
			$title = get_field( '_hc_title', 'widget_' . $widget_id );
			if( !empty($title) )
				echo $before_title . sanitize_text_field($title) . $after_title;

			$above_text = get_field( '_hc_above_text', 'widget_' . $widget_id );
			if( !empty($above_text) )
				echo '<div class="above-text">' . wpautop( wp_kses_data($above_text) ) . '</div>';

			?>
			<form>
				<label for="<?php echo $widget_id; ?>-email">Email</label>

				<input id="<?php echo $widget_id; ?>-email" type="email">

				<button type="submit" class="btn">Sign Up</button>
			</form>
			<?php
		echo $after_widget;

	}

	public function form( $instance ) {

	}

}
