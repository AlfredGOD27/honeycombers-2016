<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Archives {
	public function __construct() {

		$this->count = 0;

		remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		add_action( 'genesis_entry_header', 'genesis_do_post_image', 8 );

		add_action( 'wp', array($this, 'init') );
		add_action( 'wp_ajax_hc_get_next_page_html', array($this, 'get_next_page_html') );
		add_action( 'wp_ajax_nopriv_hc_get_next_page_html', array($this, 'get_next_page_html') );

	}

	public function init() {

		$this->mode = false;
		if( is_search() ) {
			add_action( 'genesis_after_header', array($this, 'do_search_title'), 14 );
			$this->mode = 'infinite';
		} elseif( is_archive() ) {
			add_action( 'genesis_after_header', array($this, 'do_taxonomy_title_description'), 14 );

			$this->term = get_queried_object();
			if( !empty($this->term->parent) ) {
				$this->mode = 'infinite';
			} else {
				remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );
				$this->mode = 'sub-sections';
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

		switch( $this->mode ) {
			case 'infinite':
				break;
			case 'sub-sections':
				add_action( 'genesis_after_header', array($this, 'slider'), 16 );
				remove_action( 'genesis_loop', 'genesis_do_loop' );
				add_action( 'genesis_loop', array($this, 'subcategory_sections') );
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
			'post_type'      => 'any',
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

		add_action( 'post_class', array($this, 'post_class') );
		remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
		add_action( 'genesis_entry_content', array($this, 'do_excerpt') );
		remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );

		genesis_standard_loop();

		wp_reset_query();

		wp_die();

	}

	public function body_class( $classes ) {

		$classes[] = 'two-columns-archive';

		if( 'infinite' === $this->mode )
			$classes[] = 'infinite-scroll';

		if( 'sub-sections' === $this->mode )
			$classes[] = 'archive-sub-sections';

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
			'post_type' => 'any',
			'tax_query' => array(
				array(
					'taxonomy' => $this->term->taxonomy,
					'field'    => 'term_id',
					'terms'    => $this->term->term_id,
				),
			),
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_thumbnail_id',
					'value'   => '',
					'compare' => '!=',
				),
			),
			'fields' => 'ids',
		);

		$slider_mode = get_field( '_hc_category_slider_type', $this->term );
		switch( $slider_mode ) {
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

		$posts = get_posts( $args );
		if( empty($posts) )
			return;

		?>
		<section class="archive-slider-container hide-no-js">
			<div class="wrap">
				<div class="slider-for">
					<?php
					foreach( $posts as $post_id ) {
						?>
						<div>
							<?php echo wp_get_attachment_image( get_post_thumbnail_id( $post_id ), 'slide' ); ?>
						</div>
						<?php
					}
					?>
				</div>

				<div class="slider-nav">
					<?php
					foreach( $posts as $post_id ) {
						?>
						<div>
							<div class="outer">
								<?php echo wp_get_attachment_image( get_post_thumbnail_id( $post_id ), 'slide-thumbnail' ); ?>

								<div class="inner">
									<?php
									$terms = false;
									switch( get_post_type($post_id) ) {
										case 'post':
											$terms = wp_get_object_terms( $post_id, 'category' );
											break;
										case 'event':
											$terms = wp_get_object_terms( $post_id, 'event-category' );
											break;
										case 'listing':
											$terms = wp_get_object_terms( $post_id, 'listing-category' );
											break;
									}

									$icon = false;
									if( !empty($terms) )
										$icon = get_field( '_hc_category_icon', $terms[0] );

									if( !empty($icon) )
										echo '<i class="ico-' . $icon . '"></i>';

									echo '<span>' . get_the_title( $post_id ) . '</span>';
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</section>
		<?php

	}

	public function post_class( $classes ) {

		global $post;

		++$this->count;

		$classes[] = 1 === $this->count % 2 ? 'one-half first' : 'one-half';

		// Add entry class for AJAX fetch
		$classes[] = 'entry';

		return $classes;

	}

	public function subcategory_sections() {

		global $wp_query;

		$args = array(
			'parent' => $this->term->term_id,
		);
		$subcategories = get_terms( $this->term->taxonomy, $args );
		if( empty($subcategories) )
			return;

		foreach( $subcategories as $category ) {
			$args = array(
				'posts_per_page' => 4,
				'post_type'      => 'any',
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
			echo HC()->formatting->get_excerpt( $post, 140 );
			?>
		</div>

		<a href="<?php echo get_permalink(); ?>" class="more-link">Read more ></a>
		<?php

	}
}

return new HC_Archives();
