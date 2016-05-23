<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Menu {
	public function __construct() {

		$this->categories     = array();
		$this->subcategories  = array();
		$this->transient_name = 'hc_main_menu_html';

		remove_action( 'genesis_after_header', 'genesis_do_nav' );
		remove_action( 'genesis_after_header', 'genesis_do_subnav' );

		add_action( 'save_post', array($this, 'clear_transient') );
		add_action( 'acf/save_post', array($this, 'clear_transient'), 12 );
		add_action( 'genesis_after_header', array($this, 'open'), 8 );
		add_action( 'genesis_after_header', array($this, 'close'), 12 );
		add_action( 'genesis_after_header', array($this, 'display') );

	}

	public function clear_transient( $post_id ) {

		delete_transient($this->transient_name);

	}

	public function open() {

		?>
		<div class="nav-primary-wrapper">
			<div class="wrap">
			<?php

	}

	public function close() {

			?>
			</div>
		</div>
		<?php

	}

	private function maybe_display_posts_menu( $subcategory ) {

		$args = array(
			'posts_per_page' => 2,
			'post_type'      => 'post',
			'tax_query'      => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => array($subcategory->term_id),
				),
			),
			'fields' => 'ids',
		);

		$posts = get_posts( $args );
		if( empty($posts) )
			return;

		?>
		<ul class="menu-col-post-container">
			<?php
			foreach( $posts as $post_id ) {
				?>
				<li class="menu-col menu-col-post clearfix">
					<?php
					$title = HC()->entry->get_headline_title($post_id);
					?>
					<div class="left">
						<span><?php echo $subcategory->name; ?></span>

						<a href="<?php echo get_permalink($post_id); ?>"><?php echo $title; ?></a>
					</div>

					<div class="right">
						<?php
						if( has_post_thumbnail($post_id) ) {
							$src = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'archive-small' );

							$atts = array(
								'src'    => $src[0],
								'alt'    => $title,
								'width'  => $src[1],
								'height' => $src[2],
							);
							echo HC()->utilities->get_async_image_placeholder( $atts, 'skip-image-on-mobile' );
						}
						?>
					</div>
				</li>
				<?php
			}
			?>
		</ul>
		<?php

	}

	private function display_links_menu( $top_item_id ) {

		?>
		<div class="sub-menu">
			<ul class="clearfix">
				<li class="menu-col menu-col-links clearfix">
					<div class="left">
						<?php
						echo HC()->utilities->get_category_icon_html( $this->categories[$top_item_id], 'large' );
						?>
					</div>

					<div class="right">
						<ul class="subcategory-list">
							<?php
							foreach( $this->subcategories[$top_item_id] as $subcategory ) {
								?>
								<li>
									<a href="<?php echo get_term_link($subcategory); ?>" class="subcategory-link subcategory-<?php echo $subcategory->slug; ?>"><?php echo $subcategory->name; ?></a>
									<?php
									$this->maybe_display_posts_menu( $subcategory );
									?>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
				</li>

				<?php
				$args = array(
					'posts_per_page' => 2,
					'post_type'      => 'post',
					'tax_query'      => array(
						array(
							'taxonomy' => 'category',
							'field'    => 'term_id',
							'terms'    => array($top_item_id),
						),
					),
					'fields' => 'ids',
				);

				$posts = get_posts( $args );
				if( !empty($posts) ) {
					foreach( $posts as $post_id ) {
						?>
						<li class="menu-col menu-col-post clearfix">
							<?php
							$title = HC()->entry->get_headline_title($post_id);
							?>
							<div class="left">
								<span><?php echo $subcategory->name; ?></span>

								<a href="<?php echo get_permalink($post_id); ?>"><?php echo $title; ?></a>
							</div>

							<div class="right">
								<?php
								if( has_post_thumbnail($post_id) ) {
									$src = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'archive-small' );

									$atts = array(
										'src'    => $src[0],
										'alt'    => $title,
										'width'  => $src[1],
										'height' => $src[2],
									);
									echo HC()->utilities->get_async_image_placeholder( $atts, 'skip-image-on-mobile' );
								}
								?>
							</div>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<?php

	}

	public function display() {

		printf( '<h2 class="screen-reader-text">%s</h2>', __( 'Main navigation', 'genesis' ) );

		$transient = get_transient( $this->transient_name );

		if( false === $transient ) {
			$menu = get_field( '_hc_main_menu_category_ids', 'option' );
			if( empty($menu) )
				return;

			ob_start();
			?>
			<nav class="nav-primary" itemscope itemtype="http://schema.org/SiteNavigationElement" aria-label="Main navigation">
				<div class="wrap">
					<ul class="main-menu clearfix">
						<?php
						foreach( $menu as $top_item_id ) {
							if( !isset($this->categories[$top_item_id]))
								$this->categories[$top_item_id] = get_term_by( 'id', $top_item_id, 'category' );

							if( !isset($this->subcategories[$top_item_id]) ) {
								$args = array(
									'parent'   => $top_item_id,
									'taxonomy' => 'category',
								);
								$this->subcategories[$top_item_id] = get_terms($args);
							}

							echo !empty($this->subcategories[$top_item_id]) ? '<li class="menu-item has-children">' : '<li class="menu-item">';
								$label = sanitize_text_field($top_item['label']);

								if( !empty($this->subcategories[$top_item_id]) ) {
									echo '<a href="' . get_term_link($this->categories[$top_item_id]) . '" class="menu-item-link">' . $this->categories[$top_item_id]->name . '</a>';
									echo '<button type="button" class="inactive-link"><span>' . $this->categories[$top_item_id]->name . '</span> <i class="ico-arrow-down"></i> <i class="ico-arrow-up"></i></button>';

									$this->display_links_menu( $top_item_id );

								} else {
									echo '<a href="' . get_term_link($this->categories[$top_item_id]) . '" class="menu-item-link">' . $this->categories[$top_item_id]->name . '</a>';
								}
							echo '</li>';
						}
						?>
					</ul>

					<div class="icon-nav">
						<?php $page_id = get_field( '_hc_calendar_page_id', 'option' ); ?>
						<a href="<?php echo get_permalink($page_id); ?>" class="btn btn-icon"><i class="ico-calendar"></i> <span>Calendar</span></a>

						<?php $page_id = get_field( '_hc_directory_page_id', 'option' ); ?>
						<a href="<?php echo get_permalink($page_id); ?>" class="btn btn-icon"><i class="ico-pin"></i> <span>Directory</span></a>

						<?php $page_id = get_field( '_hc_video_page_id', 'option' ); ?>
						<a href="<?php echo get_permalink($page_id); ?>" class="btn btn-icon"><i class="ico-play"></i> <span>Video</span></a>
					</div>

					<div class="show-phone mobile-social-nav">
						<span><span>Follow Us</span></span>
						<?php hc_do_social(); ?>
					</div>
				</div>
			</nav>
			<?php
			$transient = ob_get_clean();

			set_transient( $this->transient_name, $transient );
		}

		echo $transient;

	}

}

return new HC_Menu();
