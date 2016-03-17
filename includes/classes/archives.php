<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Archives {
	public function __construct() {

		$this->count = 0;

		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		add_action( 'genesis_entry_header', 'genesis_do_post_image', 8 );

		add_action( 'wp', array($this, 'init') );

	}

	public function init() {

		if( !is_search() )
			return;

		add_action( 'body_class', array($this, 'body_class') );

		add_action( 'genesis_after_header', array($this, 'do_search_title'), 14 );

		remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );

		add_action( 'post_class', array($this, 'post_class') );
		remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
		add_action( 'genesis_entry_content', array($this, 'do_excerpt') );

	}

	public function body_class( $classes ) {

		$classes[] = 'infinite-scroll';

		return $classes;

	}

	public function do_search_title() {

		global $wp_query;

		echo sprintf(
			'<div class="archive-description"><div class="wrap"><h1 class="archive-title">Your search for "%s" found %d %s</h1></div></div>',
			get_search_query(),
			$wp_query->found_posts,
			_n( 'result', 'results', $wp_query->found_posts )
		);

	}

	public function post_class( $classes ) {

		global $post;

		++$this->count;

		$classes[] = 1 === $this->count % 2 ? 'one-half first' : 'one-half';

		return $classes;

	}

	public function do_excerpt() {

		global $post;

		?>
		<div class="entry-excerpt">
			<?php
			echo HC()->formatting->get_excerpt( $post, 140 );
			?>
		</div>

		<a href="<?php echo get_permalink(); ?>" class="more-link">Read more ></a>
		<?php

	}
}

return new HC_Archives();
