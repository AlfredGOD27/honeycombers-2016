<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Entry {
	public function __construct() {

		add_action( 'wp', array($this, 'init') );

	}

	public function init() {

		global $post;

		if( !is_singular() )
			return;

		add_filter( 'body_class', array($this, 'body_class') );

		remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

		remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
		add_action( 'genesis_entry_header', array($this, 'display_header'), 12 );

		$this->header_type = get_post_meta( $post->ID, '_hc_post_header_type', true );
		switch( $this->header_type ) {
			case 'default':
				add_action( 'genesis_entry_content', array($this, 'display_image'), 8 );
				add_action( 'genesis_entry_content', array($this, 'display_excerpt'), 8 );
				break;
			case 'slideshow':
				add_action( 'genesis_after_header', array($this, 'display_slideshow'), 14 );
				break;
			case 'video':
				add_action( 'genesis_entry_content', array($this, 'display_video'), 8 );
				break;
		}

	}

	public function body_class( $classes ) {

		$classes[] = 'header-' . $this->header_type;

		return $classes;

	}

	public function display_header() {

		global $post;

		switch( $this->header_type ) {
			case 'slideshow':
				?>
				<div class="clearfix inner">
					<div class="three-fifths first header-left">
						<?php genesis_do_post_title(); ?>

						<div class="date-row">
							<?php echo do_shortcode('[post_date]'); ?>
						</div>

						<?php $this->display_excerpt(); ?>
					</div>


					<div class="two-fifths header-right">
						<div class="author header-top clearfix">
							<?php
							$user_id = get_the_author_meta( 'ID' );
							?>

							<div class="author-left">
								<?php echo get_avatar( $user_id, 90 ); ?>
							</div>

							<div class="author-right">
								<?php echo do_shortcode( '[post_author_posts_link]' ); ?>

								<?php

								$job_title = HC()->users->get_title( $user_id );
								if( !empty($job_title) )
									echo '<p>' . $job_title . '</p>';
								?>
							</div>
						</div>

						<div class="share header-bottom">
							<?php HC()->favorites->display( $post->ID ); ?>
							<?php HC()->share->display( $post->ID ); ?>
						</div>
					</div>
				</div>
				<?php
				break;
			default:
				genesis_do_post_title();
				?>
				<div class="entry-meta">
					<div class="date-row">
						<?php echo do_shortcode('[post_date]'); ?>
					</div>

					<div class="author-share-row clearfix">
						<div class="left">
							<?php

							$lines   = array();
							$lines[] = do_shortcode( __( 'By', CHILD_THEME_TEXT_DOMAIN ) . ' [post_author_posts_link]' );

							$title = HC()->users->get_title( $post->post_author );
							if( !empty($title) )
								$lines[] = $title;

							echo '<p>' . implode( ', ', $lines ) . '</p>';
							?>
						</div>

						<div class="right">
							<?php HC()->favorites->display( $post->ID ); ?>
							<?php HC()->share->display( $post->ID ); ?>
						</div>
					</div>
				</div>
				<?php
				break;
		}

	}

	public function display_slideshow() {

		$slides = get_field( '_hc_post_slides' );
		if( empty($slides) )
			return;

		?>
		<div class="entry-slideshow hide-no-js">
			<?php
			foreach( $slides as $slide ) {
				echo '<div>';
					echo wp_get_attachment_image( $slide['ID'], 'featured' );
				echo '</div>';
			}
			?>
		</div>
		<?php

	}

	public function display_image() {

		global $post;

		?>
		<div class="featured-image-container">
			<?php
			$atts          = genesis_parse_attr( 'entry-image', array('alt' => get_the_title()) );
			$atts['class'] = 'alignnone';

			echo genesis_get_image(
				array(
					'format' => 'html',
					'size'   => 'featured',
					'attr'   => $atts,
				)
			);

			$sponsored = get_post_meta( $post->ID, '_hc_post_is_sponsored', true );
			if( !empty($sponsored) ) {
				?>
				<span class="spon-tag">Sponsored</span>
				<?php
			}
			?>
		</div>
		<?php

	}

	public function display_excerpt() {

		global $post;

		if( empty($post->post_excerpt) )
			return;

		?>
		<div class="entry-excerpt" itemprop="description">
			<?php the_excerpt(); ?>
		</div>
		<?php

	}

	public function display_video() {

		global $post;

		$video_url = get_post_meta( $post->ID, '_hc_post_video_url', true );
		if( empty($video_url) )
			return;

		?>
		<div class="entry-video">
			<?php echo wp_oembed_get( $video_url ); ?>
		</div>
		<?php

	}

}

return new HC_Entry();
