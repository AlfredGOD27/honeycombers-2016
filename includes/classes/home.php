<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Home {
	public function __construct() {

		add_action( 'wp', array($this, 'init') );

	}

	public function init() {

		if( 'page_templates/page_home.php' !== get_page_template_slug() )
			return;

		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );

		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_loop', array($this, 'do_slider') );
		add_action( 'genesis_loop', array($this, 'do_editors_pick') );
		add_action( 'genesis_loop', array($this, 'do_watch_and_tables') );
		add_action( 'genesis_loop', array($this, 'do_events_and_join') );
		add_action( 'genesis_loop', array($this, 'do_trending') );
		add_action( 'genesis_loop', array($this, 'do_latest_posts') );

	}

	public function do_slider() {

		global $post;

		$post_ids = get_post_meta( $post->ID, '_hc_home_slider_post_ids', true );
		if( empty($post_ids) )
			return;

		$args = array(
			'post_type' => 'post',
			'post__in'  => $post_ids,
			'orderby'   => 'post__in',
			'fields'    => 'ids',
		);

		HC()->sliders->display( $args );

	}

	public function do_editors_pick() {

		global $post;

		?>
		<section class="home-section home-section-editors-pick">
			<div class="wrap">

			</div>
		</section>
		<?php

	}

	public function do_watch_and_tables() {

		global $post;

		?>
		<section class="home-section home-section-watch-tables">
			<div class="wrap">

			</div>
		</section>
		<?php

	}

	public function do_events_and_join() {

		global $post;

		?>
		<section class="home-section home-section-events-join">
			<div class="wrap">

			</div>
		</section>
		<?php

	}

	public function do_trending() {

		global $post;

		?>
		<section class="home-section home-section-trending">
			<div class="wrap">

			</div>
		</section>
		<?php

	}

	public function do_latest_posts() {

		global $post;

		?>
		<section class="home-section home-section-posts">
			<div class="wrap">

			</div>
		</section>
		<?php

	}

}

return new HC_Home();
