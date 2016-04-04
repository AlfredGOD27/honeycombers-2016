<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Archives {
	public function __construct() {

		$this->count      = 0;
		$this->post_types = array('page', 'post', 'event', 'listing');

		remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		add_action( 'genesis_entry_header', 'genesis_do_post_image', 8 );

		add_action( 'wp', array($this, 'init') );
		add_action( 'wp_ajax_hc_get_next_page_html', array($this, 'get_next_page_html') );
		add_action( 'wp_ajax_nopriv_hc_get_next_page_html', array($this, 'get_next_page_html') );

	}

	public function init() {

		$this->mode       = false;
		$this->has_slider = false;
		$this->post_style = 'half';
		if( is_search() ) {
			// If search, set to infinite mode and fix title
			add_action( 'genesis_after_header', array($this, 'do_search_title'), 14 );
			$this->mode = 'infinite';
		} elseif( is_author() ) {
			// If search, set to infinite mode and fix title
			add_action( 'genesis_after_header', array($this, 'do_author_box'), 14 );
			$this->mode       = 'infinite';
			$this->post_style = 'full';
		} elseif( is_archive() ) {
			add_action( 'genesis_after_header', array($this, 'do_taxonomy_title_description'), 14 );

			$this->term = get_queried_object();

			// If archive, check for slider settings. If present, show slider. Otherwise, show archive title.
			$this->slider_mode = get_field( '_hc_category_slider_type', $this->term );
			$page              = get_query_var( 'paged', 0 );
			if(
				in_array( $this->slider_mode, array('manual', 'recent'), true ) &&
				0 === $page
			) {
				$this->has_slider = true;
				add_action( 'genesis_after_header', array($this, 'slider'), 16 );
			}

			// If is top level category with subcategories, show sections. Otherwise, show infinite.
			if( !empty($this->term->parent) ) {
				$this->mode = 'infinite';
			} else {
				$args = array(
					'parent' => $this->term->term_id,
				);
				$this->subcategories = get_terms( $this->term->taxonomy, $args );
				if( count($this->subcategories) <= 1 ) {
					$this->mode = 'infinite';
				} else {
					$this->mode = 'sub-sections';
				}
			}
		}

		if( false === $this->mode )
			return;

		// General hooks
		add_action( 'body_class', array($this, 'body_class') );
		remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );
		add_action( 'post_class', array($this, 'post_class') );
		remove_action( 'genesis_entry_content', 'genesis_do_post_content' );

		add_action( 'genesis_entry_content', array($this, 'do_excerpt') );
		if( 'full' === $this->post_style ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_image', 8 );
			add_action( 'genesis_entry_header', array($this, 'full_width_markup_open'), 4 );
			add_action( 'genesis_entry_footer', array($this, 'full_width_markup_close'), 16 );

		}

		switch( $this->mode ) {
			case 'infinite':
				break;
			case 'sub-sections':
				remove_action( 'genesis_loop', 'genesis_do_loop' );
				add_action( 'genesis_loop', array($this, 'subcategory_sections') );
				remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );
				break;
		}

	}

	public function get_next_page_html() {

		global $wp_query;

		if( empty($_POST['term_id']))
			wp_die();

		if( empty($_POST['taxonomy']))
			wp_die();

		$term_id  = absint($_POST['term_id']);
		$taxonomy = sanitize_title($_POST['taxonomy']);
		$term     = get_term_by( 'term_id', $term_id, $taxonomy );
		if( empty($term) || is_wp_error($term) )
			wp_die();

		if( !isset($_POST['offset']))
			wp_die();

		$offset = absint($_POST['offset']);

		$args = array(
			'posts_per_page' => 4,
			'post_type'      => $this->post_types,
			'offset'         => $offset,
			'tax_query'      => array(
				array(
					'taxonomy' => $term->taxonomy,
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
		);
		$wp_query = new WP_Query( $args );

		$this->mode       = 'sub-sections';
		$this->post_style = 'half';

		add_action( 'post_class', array($this, 'post_class') );
		remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
		add_action( 'genesis_entry_content', array($this, 'do_excerpt') );
		remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );

		genesis_standard_loop();

		wp_reset_query();

		wp_die();

	}

	public function body_class( $classes ) {

		$classes[] = 'hc-archive';

		if( 'half' === $this->post_style )
			$classes[] = 'two-columns-archive';

		if( 'full' === $this->post_style )
			$classes[] = 'one-column-archive';

		if( 'infinite' === $this->mode )
			$classes[] = 'infinite-scroll';

		if( 'sub-sections' === $this->mode )
			$classes[] = 'archive-sub-sections';

		if( $this->has_slider )
			$classes[] = 'archive-has-slider';

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

	public function do_author_box() {

		$page = get_query_var( 'paged', 0 );
		if( !empty($page) )
			return;

		HC()->users->do_author_box( 'archive' );

	}

	public function do_taxonomy_title_description() {

		$headline = get_term_meta( $this->term->term_id, 'headline', true );
		if( !empty($headline) ) {
			$headline = sprintf( '<h1 %s>%s</h1>', genesis_attr( 'archive-title' ), strip_tags( $headline ) );
		} else {
			$headline = sprintf( '<h1 %s>%s</h1>', genesis_attr( 'archive-title' ), strip_tags( $this->term->name ) );
		}

		$intro_text = get_term_meta( $this->term->term_id, 'intro_text', true );
		if( !empty($intro_text) )
			$intro_text = apply_filters( 'genesis_term_intro_text_output', $intro_text );

		if( !empty($headline) || !empty($intro_text) )
			printf( '<div %s><div class="wrap">%s</div></div>', genesis_attr( 'taxonomy-archive-description' ), $headline . $intro_text );

	}

	public function slider() {

		$args = array(
			'post_type' => $this->post_types,
			'tax_query' => array(
				array(
					'taxonomy' => $this->term->taxonomy,
					'field'    => 'term_id',
					'terms'    => $this->term->term_id,
				),
			),
			'fields' => 'ids',
		);

		switch( $this->slider_mode ) {
			case 'manual':
				$args['post__in'] = get_field( '_hc_category_slider_post_ids', $this->term );
				$args['orderby']  = 'post__in';
				break;
			case 'recent':
				$post_count             = get_field( '_hc_category_slider_post_count', $this->term );
				$args['posts_per_page'] = absint($post_count);
				break;
			default:
				return;
		}

		HC()->sliders->display( $args );

	}

	public function post_class( $classes ) {

		global $post;

		if( 'half' === $this->post_style ) {
			++$this->count;

			$classes[] = 1 === $this->count % 2 ? 'one-half first post-half' : 'one-half post-half';
		}

		if( 'full' === $this->post_style ) {
//			$classes[] = 1 === $this->count % 2 ? 'one-half first' : 'one-half';
		}

		// Add entry class for AJAX fetch
		$classes[] = 'entry';

		return $classes;

	}

	public function subcategory_sections() {

		global $wp_query;

		foreach( $this->subcategories as $category ) {
			$args = array(
				'posts_per_page' => 4,
				'post_type'      => $this->post_types,
				'tax_query'      => array(
					array(
						'taxonomy' => $category->taxonomy,
						'field'    => 'term_id',
						'terms'    => $category->term_id,
					),
				),
			);
			$wp_query = new WP_Query( $args );

			?>
			<section class="subcategory" data-offset="8" data-total="<?php echo $wp_query->found_posts; ?>" data-term_id="<?php echo $category->term_id; ?>" data-taxonomy="<?php echo $category->taxonomy; ?>">
				<div class="subcategory-description">
					<a href="<?php echo get_term_link($category); ?>">
						<i class="ico-circle"></i>
						<h2 class="archive-title"><?php echo $category->name; ?></h2>
						<i class="ico-arrow-right-circle"></i>
					</a>
				</div>

				<?php
				genesis_standard_loop();
				?>
			</section>
			<?php

			wp_reset_query();
		}

	}

	public function do_excerpt() {

		global $post;

		?>
		<div class="entry-excerpt" itemprop="description">
			<?php
			echo '<p>' . HC()->formatting->get_excerpt( $post, 140 ) . '</p>';
			?>
		</div>

		<div class="read-more-share-bar">
			<a href="<?php echo get_permalink(); ?>" class="more-link">Read more ></a>

			<?php
			if( 'full' === $this->post_style ) {
				?>
				<div class="share">
					<?php HC()->favorites->display( $post->ID ); ?>
					<?php HC()->share->display( $post->ID ); ?>
				</div>
				<?php
			}
			?>
		</div>
		<?php

	}

	public function full_width_markup_open() {

		?>
		<div class="one-half first">
			<?php
			genesis_do_post_image();
			?>
		</div>

		<div class="one-half">
		<?php

	}

	public function full_width_markup_close() {

		?>
		</div>
		<?php

	}
}

return new HC_Archives();
