<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Join_Widget extends WP_Widget {
	public function __construct() {

		parent::__construct(false, $name = 'HC: Join');

	}

	public function widget( $args, $instance ) {

		if( is_user_logged_in() )
			return;

		extract($args);

		echo $before_widget;
			$title = get_field( '_hc_title', 'widget_' . $widget_id );
			if( !empty($title) )
				echo $before_title . sanitize_text_field($title) . $after_title;

			$above_text = get_field( '_hc_above_text', 'widget_' . $widget_id );
			if( !empty($above_text) )
				echo '<div class="above-text">' . wpautop( wp_kses_data($above_text) ) . '</div>';

			?>
			<a href="<?php echo HC()->utilities->get_page_link('_hc_profile_page_id'); ?>" class="btn open-popup-link" data-mfp-src="#login-popup">Sign In <i class="ico-exit"></i></a>
			<?php

			$below_text = get_field( '_hc_below_text', 'widget_' . $widget_id );
			if( !empty($below_text) )
				echo '<div class="below-text">' . wpautop( wp_kses_data($below_text) ) . '</div>';
		echo $after_widget;

	}

	public function form( $instance ) {

	}

}
