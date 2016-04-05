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

	private function display_editors_pick_other_posts( $post_ids ) {

		global $post;

		$ad_code = get_post_meta( $post->ID, '_hc_home_picks_ad_code', true );

		$i = 1;
		foreach( $post_ids as $post_id ) {
			echo 1 === $i % 2 ? '<div class="one-half first ">' : '<div class="one-half">';
				if( 2 === $i && !empty($ad_code) ) {
						echo $ad_code;
					echo '</div>';
					++$i;
					echo 1 === $i % 2 ? '<div class="one-half first ">' : '<div class="one-half">';
				}

				?>
				<a href="<?php echo get_permalink($post_id); ?>" class="other-pick">
					<?php
					if( has_post_thumbnail($post_id) )
						echo get_the_post_thumbnail($post_id, 'archive-small' );
					?>

					<h3><?php echo get_the_title($post_id); ?></h3>
				</a>
				<?php
			echo '</div>';
			++$i;
		}

	}

	public function do_editors_pick() {

		global $post;

		$main_post_id = get_post_meta( $post->ID, '_hc_home_picks_main_post_id', true );
		$post_ids     = get_post_meta( $post->ID, '_hc_home_picks_post_ids', true );

		if( empty($main_post_id) && empty($post_ids) )
			return;

		?>
		<section class="home-section home-section-editors-pick">
			<div class="wrap">
				<?php
				$heading = get_post_meta( $post->ID, '_hc_home_picks_heading', true );
				echo '<h2>' . sanitize_text_field($heading) . '</h2>';
				?>

				<?php
				if( !empty($main_post_id) ) {
					$main_post = get_post( $main_post_id );
					?>
					<div class="one-half first">
						<a href="<?php echo get_permalink($main_post_id); ?>" class="main-pick">
							<?php
							if( has_post_thumbnail($main_post_id) )
								echo get_the_post_thumbnail($main_post_id, 'archive-large' );
							?>

							<div class="bottom clearfix">
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
							</div>
						</a>
					</div>
					<div class="one-half">
						<?php
						$this->display_editors_pick_other_posts( $post_ids );
						?>
					</div>
					<?php
				} else {
					$this->display_editors_pick_other_posts( $post_ids );
				}
				?>
			</div>
		</section>
		<?php

	}

	public function do_watch_and_tables() {

		global $post;

		?>
		<section class="home-section home-section-watch-tables">
			<div class="wrap">
				<div class="left">
					<?php
					$heading = get_post_meta( $post->ID, '_hc_home_watch_this_heading', true );
					echo '<h2>' . sanitize_text_field($heading) . '</h2>';

					$image_id = get_post_meta( $post->ID, '_hc_home_watch_this_video_thumbnail_id', true );
					$src      = get_post_meta( $post->ID, '_hc_home_watch_this_video_url', true );
					$src      = esc_url($src);
					echo '<a href="' . $src . '" class="open-video-link">';
						echo wp_get_attachment_image( $image_id, 'event' );
					echo '</a>';
					?>
				</div>

				<div class="right hide-no-js">
					<?php
					$heading = get_post_meta( $post->ID, '_hc_home_tables_heading', true );
					echo '<h2>' . sanitize_text_field($heading) . '</h2>';
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
									<h3>More Restaurants</h3>
								</div>

								<div class="right">
									<i class="ico-arrow-right"></i>
								</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php

	}

	public function do_events_and_join() {

		global $post;

		$events = get_post_meta( $post->ID, '_hc_home_featured_event_ids', true );

		?>
		<section class="home-section home-section-events-join">
			<div class="wrap">
				<h2>Featured Events</h2>

				<div class="three-fourths first left hide-no-js">
					<div class="event-slider-for">
						<?php
						foreach( $events as $post_id ) {
							?>
							<div>
								<?php
								echo '<a href="' . get_permalink($post_id) . '">';
									echo get_the_post_thumbnail( $post_id, 'slide' );
								echo '</a>';
								?>
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
										if( !empty($categories) ) {
											$icon = get_field( '_hc_category_icon', $categories[0] );
											if( !empty($icon) )
												echo '<i class="ico-' . $icon . '"></i>';
										}
										?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>

				<div class="one-fourth right join">
					<i class="ico-circle-mail"></i>

					<h3>JOIN THE HONEYCOMBERS</h3>

					<p class="top">“What was that bar again?”</p>

					<a href="#" class="btn">Sign Up <i class="ico-exit"></i></a>

					<p class="bottom">Save all the new spots you have to try and stories for when you have the time.</p>
				</div>
			</div>
		</section>
		<?php

	}

	public function do_trending() {

		global $post;

		$post_ids = get_post_meta( $post->ID, '_hc_home_trending_post_ids', true );

		?>
		<section class="home-section home-section-trending">
			<div class="wrap">
				<h2>Trending</h2>

				<div class="clearfix">
					<?php
					$i = 1;
					foreach( $post_ids as $post_id ) {
						if( !has_post_thumbnail($post_id) )
							continue;

						?>
						<a href="<?php echo get_permalink($post_id); ?>">
							<?php
							echo get_the_post_thumbnail( $post_id, 'archive-small' );
							?>

							<i class="ico-hexagon"></i>
							<span><?php echo $i; ?></span>
						</a>
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
			'posts_per_page' => 8,
			'offset'         => $offset,
			'post_type'      => 'post',
			'fields'         => 'ids',
		);
		$posts = get_posts( $args );

		$i = 1;
		foreach( $posts as $post_id ) {
			echo 1 === $i % 4 ? '<div class="one-fourth first">' : '<div class="one-fourth">';
				?>
				<a href="<?php echo get_permalink( $post_id ); ?>">
					<?php
					if( has_post_thumbnail($post_id) )
						echo get_the_post_thumbnail( $post_id, 'archive-small' );
					?>

					<h3><?php echo get_the_title( $post_id ); ?></h3>
				</a>
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

		$args = array(
			'posts_per_page' => -1,
			'post_type'      => 'post',
			'fields'         => 'ids',
		);
		$posts = get_posts( $args );

		?>
		<section class="home-section home-section-posts">
			<div class="wrap">
				<?php
				$ad = get_post_meta( $post->ID, '_hc_home_banner_ad', true );
				if( !empty($ad) )
					echo '<div class="banner">' . $ad . '</div>';
				?>

				<h2>
					<a href="#">
						<span>Latest Stories</span>
						<i class="ico-arrow-right-circle"></i>
					</a>
				</h2>

				<div class="block clearfix" data-offset="8" data-total="<?php echo count($posts); ?>">
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
