<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Home {
	public function __construct() {

		add_action( 'wp', array($this, 'init') );

		add_action( 'wp_ajax_hc_get_home_next_page_html', array($this, 'get_next_page_html') );
		add_action( 'wp_ajax_nopriv_hc_get_home_next_page_html', array($this, 'get_next_page_html') );

	}

	public function init() {

		if( 'page_templates/page_home.php' !== get_page_template_slug() )
			return;

		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		remove_action( 'genesis_before_loop', 'hc_do_breadcrumbs' );

		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_loop', array($this, 'do_slider') );
		add_action( 'genesis_loop', array($this, 'do_mobile_buttons') );
		add_action( 'genesis_loop', array($this, 'do_featured_posts') );
		add_action( 'genesis_loop', array($this, 'do_featured_video_and_listings') );
		add_action( 'genesis_loop', array($this, 'do_featured_events_and_join') );
		add_action( 'genesis_loop', array($this, 'do_trending') );
		add_action( 'genesis_loop', array($this, 'do_latest_posts') );

	}

	public function do_slider() {

		global $post;

		$enable = get_post_meta( $post->ID, '_hc_home_enable_slider', true );
		if( empty($enable) )
			return;

		$post_ids = get_post_meta( $post->ID, '_hc_home_slider_post_ids', true );

		$args = array(
			'post_type' => 'post',
			'post__in'  => $post_ids,
			'orderby'   => 'post__in',
			'fields'    => 'ids',
		);

		HC()->sliders->display( $args );

	}

	public function do_mobile_buttons() {

		$buttons = get_field( '_hc_home_mobile_links' );
		if( empty($buttons) )
			return;

		?>
		<section class="home-section home-section-mobile-buttons show-phone">
			<div class="wrap">
				<?php
				foreach( $buttons as $button ) {
					echo '<a href="' . esc_url($button['link']) . '" class="btn">' . sanitize_text_field($button['label']) . '</a>';
				}
				?>
			</div>
		</section>
		<?php

	}

	private function display_editors_pick_other_posts( $post_ids ) {

		global $post;

		$ad_code = get_post_meta( $post->ID, '_hc_home_picks_ad_code', true );

		$i = 1;
		foreach( $post_ids as $post_id ) {
			echo 1 === $i % 2 ? '<div class="one-half first item item-' . $i . '">' : '<div class="one-half item item-' . $i . '">';
				if( 2 === $i && !empty($ad_code) ) {
						echo $ad_code;
					echo '</div>';
					++$i;
					echo 1 === $i % 2 ? '<div class="one-half first item item-' . $i . '">' : '<div class="one-half item item-' . $i . '">';
				}

				?>
				<div class="other-pick">
					<?php HC()->bookmarks->display( $post_id, true ); ?>
					<?php
					if( has_post_thumbnail($post_id) )
						echo get_the_post_thumbnail($post_id, 'archive-small' );
					?>

					<h3><a href="<?php echo get_permalink($post_id); ?>"><?php echo get_the_title($post_id); ?></a></h3>
				</div>
				<?php
			echo '</div>';
			++$i;
		}

	}

	public function do_featured_posts() {

		global $post;

		$enable = get_post_meta( $post->ID, '_hc_home_enable_featured_posts', true );
		if( empty($enable) )
			return;

		$main_post_ids = get_post_meta( $post->ID, '_hc_home_picks_main_post_id', true );
		$main_post_id  = $main_post_ids[0];
		$post_ids      = get_post_meta( $post->ID, '_hc_home_picks_post_ids', true );

		?>
		<section class="home-section home-section-featured-posts">
			<div class="wrap">
				<?php
				$heading = get_post_meta( $post->ID, '_hc_home_picks_heading', true );
				echo '<h2>' . sanitize_text_field($heading) . '</h2>';

				$main_post = get_post( $main_post_id );
				?>
				<div class="one-half first">
					<div class="main-pick">
						<?php HC()->bookmarks->display( $main_post_id, true ); ?>

						<?php
						if( has_post_thumbnail($main_post_id) ) {
							?>
							<div class="top">
								<?php echo get_the_post_thumbnail($main_post_id, 'archive-large' ); ?>
							</div>
							<?php
						}
						?>

						<a href="<?php echo get_permalink($main_post_id); ?>" class="bottom clearfix">
							<div class="left">
								<h3><?php echo $main_post->post_title; ?></h3>

								<?php
								if( !empty($main_post->post_excerpt) )
									echo '<p>' . $main_post->post_excerpt . '</p>';
								?>
							</div>

							<div class="right">
								<?php echo get_avatar( $main_post->post_author, 90 ); ?>

								<?php
								$user = get_user_by( 'id', $main_post->post_author );
								echo '<p>' . $user->display_name . '</p>';
								?>
							</div>
						</a>
					</div>
				</div>
				<div class="one-half other-picks">
					<?php
					$this->display_editors_pick_other_posts( $post_ids );
					?>
				</div>
			</div>
		</section>
		<?php

	}

	public function do_featured_video_and_listings() {

		global $post;

		$enable_video    = get_post_meta( $post->ID, '_hc_home_enable_featured_video', true );
		$enable_listings = get_post_meta( $post->ID, '_hc_home_enable_featured_listings', true );
		if( empty($enable_video) && empty($enable_listings) )
			return;

		?>
		<section class="home-section home-section-featured-video-listings">
			<div class="wrap">
				<?php
				if( !empty($enable_video) ) {
					$video_page_id   = get_field( '_hc_video_page_id', 'option' );
					$video_page_link = get_permalink($video_page_id);
					?>
					<div class="left">
						<?php
						$heading = get_post_meta( $post->ID, '_hc_home_watch_this_heading', true );
						echo '<h2><a href="' . $video_page_link . '">' . sanitize_text_field($heading) . '</a></h2>';

						$image_id = get_post_meta( $post->ID, '_hc_home_watch_this_video_thumbnail_id', true );
						$src      = get_post_meta( $post->ID, '_hc_home_watch_this_video_url', true );
						$src      = esc_url($src);
						echo '<a href="' . $src . '" class="open-video-link">';
							echo wp_get_attachment_image( $image_id, 'archive' );
						echo '</a>';
						?>

						<div class="mobile-bar show-phone">
							<a href="<?php echo $video_page_link; ?>" class="btn btn-icon"><span>More Videos</span> <i class="ico-arrow-right"></i></a>
						</div>
					</div>
					<?php
				}
				?>

				<?php
				if( !empty($enable_listings) ) {
					$directory_page_id   = get_field( '_hc_directory_page_id', 'option' );
					$directory_page_link = get_permalink($directory_page_id);

					?>
					<div class="right hide-no-js">
						<?php
						$heading = get_post_meta( $post->ID, '_hc_home_tables_heading', true );
						echo '<h2><a href="' . $directory_page_link . '">' . sanitize_text_field($heading) . '</a></h2>';
						?>

						<?php
						$listing_ids = get_post_meta( $post->ID, '_hc_home_tables_listing_ids', true );
						$args        = array(
							'post_type'      => 'listing',
							'post__in'       => $listing_ids,
							'orderby'        => 'post__in',
							'posts_per_page' => -1,
							'fields'         => 'ids',
						);

						$listings = get_posts( $args );
						?>
						<div class="listings-slider">
							<div class="listing-slider-for">
								<?php
								foreach( $listings as $post_id ) {
									?>
									<div>
										<?php
										echo '<a href="' . get_permalink($post_id) . '">';
											echo get_the_post_thumbnail( $post_id, 'archive' );
										echo '</a>';
										?>

										<div class="slide-content show-phone">
											<h3><?php echo get_the_title( $post_id ); ?></h3>

											<?php
											$categories = wp_get_object_terms( $post_id, 'directories' );
											if( !empty($categories) ) {
												$category_links = array();

												foreach( $categories as $category )
													$category_links[] = '<a href="' . get_term_link($category) . '">' . $category->name . '</a>';

												echo '<p>' . HC()->formatting->build_comma_separated_list($category_links) . '</p>';
											}
											?>
										</div>
									</div>
									<?php
								}
								?>
							</div>

							<div class="listing-slider-nav">
								<?php
								foreach( $listings as $post_id ) {
									?>
									<div>
										<div class="listing-slide-default">
											<div class="inner">
												<div class="left">
													<h3><?php echo get_the_title( $post_id ); ?></h3>
												</div>

												<div class="right">
													<i class="ico-pin-filled"></i>
												</div>
											</div>
										</div>

										<div class="listing-slide-active">
											<div class="inner">
												<div class="left">
													<i class="ico-pin-filled"></i>
												</div>

												<div class="right">
													<div class="address">
														<?php
														$address = get_post_meta( $post_id, '_hc_listing_address_text', true );
														if( !empty($address) )
															echo sanitize_text_field($address);
														?>
													</div>

													<div class="contact">
														<?php
														$contact = get_post_meta( $post_id, '_hc_listing_phone', true );
														if( !empty($contact) )
															echo sanitize_text_field($contact);
														?>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								?>
							</div>

							<div class="listings-more">
								<?php
								$page_id = get_field( '_hc_directory_page_id', 'option' );
								?>
								<a href="<?php echo get_permalink($page_id); ?>" class="inner">
									<div class="left">
										<?php
										$heading = get_post_meta( $post->ID, '_hc_home_more_listings_label', true );
										?>
										<h3><?php echo $heading; ?></h3>
									</div>

									<div class="right">
										<i class="ico-arrow-right"></i>
									</div>
								</a>
							</div>
						</div>

						<div class="mobile-bar show-phone">
							<a href="<?php echo $directory_page_link; ?>" class="btn btn-icon"><span>More Restaurants</span> <i class="ico-arrow-right"></i></a>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</section>
		<?php

	}

	public function do_featured_events_and_join() {

		global $post;

		$enable = get_post_meta( $post->ID, '_hc_home_enable_featured_events', true );
		if( empty($enable) )
			return;

		$events = get_post_meta( $post->ID, '_hc_home_featured_event_ids', true );

		$events_page_id   = get_field( '_hc_calendar_page_id', 'option' );
		$events_page_link = get_permalink($events_page_id);

		?>
		<section class="home-section home-section-events-join">
			<div class="wrap">
				<?php
				$heading = get_post_meta( $post->ID, '_hc_home_featured_events_heading', true );
				echo '<h2><a href="' . $events_page_link . '">' . sanitize_text_field($heading) . '</a></h2>';
				?>

				<div class="three-fourths first left hide-no-js">
					<div class="event-slider-for">
						<?php
						foreach( $events as $post_id ) {
							$date = HC()->events->get_event_date_info( $post_id );
							?>
							<div>
								<?php
								echo '<a href="' . get_permalink($post_id) . '">';
									echo get_the_post_thumbnail( $post_id, 'slide' );
								echo '</a>';
								?>

								<div class="slide-content show-phone">
									<div class="info">
										<span class="m"><?php echo date('M', $date['start_date']); ?></span>
										<span class="d"><?php echo date('j', $date['start_date']); ?></span>
									</div>

									<div class="name">
										<?php
										$categories = wp_get_object_terms( $post_id, 'event-category' );
										if( !empty($categories) )
											echo '<span class="cat">' . $categories[0]->name . '</span>';

										echo '<span class="title">' . get_the_title( $post_id ) . '</span>';
										?>
									</div>

									<?php
									if( !empty($categories) )
										echo HC()->utilities->get_category_icon_html( $categories[0] );
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>

					<div class="event-slider-nav">
						<?php
						foreach( $events as $post_id ) {
							$date = HC()->events->get_event_date_info( $post_id );
							?>
							<div>
								<div class="outer">
									<?php
									echo get_the_post_thumbnail( $post_id, 'slide-thumbnail' );
									?>

									<div class="inner">
										<div class="info">
											<span class="m"><?php echo date('M', $date['start_date']); ?></span>
											<span class="d"><?php echo date('j', $date['start_date']); ?></span>
										</div>

										<div class="name">
											<?php
											$categories = wp_get_object_terms( $post_id, 'event-category' );
											if( !empty($categories) )
												echo '<span class="cat">' . $categories[0]->name . '</span>';

											echo '<span class="title">' . get_the_title( $post_id ) . '</span>';
											?>
										</div>

										<?php
										if( !empty($categories) )
											echo HC()->utilities->get_category_icon_html( $categories[0] );
										?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>

					<div class="mobile-bar show-phone">
						<?php  ?>
						<a href="<?php echo $events_page_link; ?>" class="btn btn-icon"><span>More Events</span> <i class="ico-arrow-right"></i></a>
					</div>
				</div>

				<?php
				if( !is_user_logged_in() ) {
					$enable = get_post_meta( $post->ID, '_hc_home_join_enable', true );
					if( !empty($enable) ) {
						$heading = get_post_meta( $post->ID, '_hc_home_join_heading', true );
						$top     = get_post_meta( $post->ID, '_hc_home_join_top_text', true );
						$bottom  = get_post_meta( $post->ID, '_hc_home_join_bottom_text', true );
						?>
						<div class="one-fourth right join">
							<i class="ico-circle-mail"></i>

							<h3><?php echo $heading; ?></h3>

							<p class="top"><?php echo sanitize_text_field($top); ?></p>

							<a href="<?php echo HC()->utilities->get_page_link('_hc_profile_page_id'); ?>" class="btn open-popup-link" data-mfp-src="#login-popup">Sign Up <i class="ico-exit"></i></a>

							<p class="bottom"><?php echo sanitize_text_field($bottom); ?></p>
						</div>
						<?php
					}
				}
				?>
			</div>
		</section>
		<?php

	}

	public function do_trending() {

		global $post;

		$enable = get_post_meta( $post->ID, '_hc_home_enable_trending', true );
		if( empty($enable) )
			return;

		?>
		<section class="home-section home-section-trending">
			<div class="wrap">
				<?php
				$heading = get_post_meta( $post->ID, '_hc_home_trending_heading', true );
				echo '<h2>' . sanitize_text_field($heading) . '</h2>';
				?>

				<div class="clearfix trending-slider hide-no-js">
					<?php
					$i        = 1;
					$post_ids = get_post_meta( $post->ID, '_hc_home_trending_post_ids', true );
					foreach( $post_ids as $post_id ) {
						if( !has_post_thumbnail($post_id) )
							continue;

						?>
						<div>
							<a href="<?php echo get_permalink($post_id); ?>">
								<?php
								echo get_the_post_thumbnail( $post_id, 'archive-small' );
								?>

								<i class="ico-hexagon"></i>
								<span><?php echo $i; ?></span>

								<div class="overlay">
									<div>
										<?php
										$categories = wp_get_post_terms( $post_id, 'category' );
										if( !empty($categories) )
											echo HC()->utilities->get_category_icon_html( $categories[0] );
										?>

										<h3><?php echo get_the_title($post_id); ?></h3>
									</div>
								</div>
							</a>
						</div>
						<?php
						++$i;
					}
					?>
				</div>
			</div>
		</section>
		<?php

	}

	private function display_posts( $offset = 0 ) {

		$args = array(
			'posts_per_page'         => 8,
			'offset'                 => $offset,
			'post_type'              => 'post',
			'update_post_term_cache' => false,
		);
		$posts = get_posts( $args );

		$i = 1;
		foreach( $posts as $post ) {
			echo 1 === $i % 4 ? '<div class="one-fourth first">' : '<div class="one-fourth">';
				if( has_post_thumbnail($post->ID) ) {
					?>
					<div class="top">
						<?php
						$categories = wp_get_post_terms( $post->ID, 'category' );
						if( !empty($categories) )
							echo HC()->utilities->get_category_icon_html( $categories[0] );
						?>
						<?php HC()->bookmarks->display( $post->ID, true ); ?>
						<?php echo get_the_post_thumbnail( $post->ID, 'archive-small' ); ?>
					</div>
					<?php
				}
				?>

				<div class="bottom">
					<a href="<?php echo get_permalink( $post->ID ); ?>">
						<h3><?php echo get_the_title( $post->ID ); ?></h3>
						<span class="author">
							By
							<?php
							$author = get_user_by( 'id', $post->post_author );
							echo $author->display_name;
							?>
						</span>
					</a>
				</div>
				<?php
			echo '</div>';
			++$i;
		}

	}

	public function get_next_page_html() {

		global $wp_query;

		if( !isset($_POST['offset']))
			wp_die();

		$offset = absint($_POST['offset']);
		$this->display_posts( $offset );

		wp_die();

	}

	public function do_latest_posts() {

		global $post;

		$enable = get_post_meta( $post->ID, '_hc_home_enable_slider_latest_posts', true );
		if( empty($enable) )
			return;

		?>
		<section class="home-section home-section-latest-posts">
			<div class="wrap">
				<?php
				$ad = get_post_meta( $post->ID, '_hc_home_banner_ad', true );
				if( !empty($ad) )
					echo '<div class="banner">' . $ad . '</div>';
				?>

				<h2>
					<a href="<?php echo HC()->utilities->get_page_link('_hc_blog_page_id'); ?>">
						<?php
						$heading = get_post_meta( $post->ID, '_hc_home_latest_posts_heading', true );
						echo '<span>' . sanitize_text_field($heading) . '</span>';
						?>

						<i class="ico-arrow-right-circle"></i>
					</a>
				</h2>

				<div class="block clearfix" data-offset="8" data-total="100">
					<?php
					$this->display_posts();
					?>
				</div>
			</div>
		</section>
		<?php

	}

}

return new HC_Home();
