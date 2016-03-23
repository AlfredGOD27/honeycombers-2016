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
