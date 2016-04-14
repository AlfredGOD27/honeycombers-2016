<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Authors {
	public function __construct() {

		remove_action( 'genesis_after_entry', 'genesis_do_author_box_single', 8 );
		add_action( 'genesis_after_entry', array($this, 'do_author_box_single'), 8 );

	}

	public function get_title( $user_id ) {

		$title = get_user_meta( $user_id, '_hc_job_title', true );
		if( empty($title) )
			return;

		return sanitize_text_field($title);

	}

	public function do_author_box( $context, $echo = true ) {

		global $authordata;

		$user_id = get_the_author_meta( 'ID' );

		$authordata  = is_object( $authordata ) ? $authordata : get_userdata( get_query_var( 'author' ) );
		$description = wpautop( get_the_author_meta( 'description' ) );

		$title     = '<span itemprop="name">' . get_the_author() . '</span>';
		$job_title = $this->get_title( $user_id );
		if( !empty($job_title) )
			$title .= ' ' . $job_title;

		$title = apply_filters( 'genesis_author_box_title', $title, $context );

		$html = sprintf( '<section %s>', genesis_attr( 'author-box' ) );
			switch( $context ) {
				case 'single':
					$gravatar = get_avatar( get_the_author_meta( 'email' ), 120 );

					$html .= '<div class="clearfix">';
						$html .= '<div class="left one-fourth first">';
							$html .= '<figure>';
								$html .= $gravatar;

								$caption = get_user_meta( $user_id, '_hc_profile_image_caption', true );
								if( !empty($caption) )
									$html .= '<figcaption>' . sanitize_text_field($caption) . '</figcaption>';
							$html .= '</figure>';
						$html .= '</div>';

						$html .= '<div class="right three-fourths">';
							$html .= '<h4 class="author-box-title">' . $title . '</h4>';
							$html .= '<div class="author-box-content entry-content" itemprop="description">';
								$html .= $description;

								$html .= '<p class="read-more">Read more from <a href="' . get_the_author_meta('url') . '">' . get_the_author() . '</a></p>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';
					break;
				case 'archive':
					$gravatar = get_avatar( get_the_author_meta( 'email' ), 150 );

					$html .= '<div class="wrap">';
						$html .= '<div class="inner clearfix">';
							$html .= '<div class="left">';
								$html .= '<figure>';
									$html .= $gravatar;
								$html .= '</figure>';
							$html .= '</div>';

							$html .= '<div class="right">';
								$html .= '<h4 class="author-box-title">' . $title . '</h4>';
								$html .= '<div class="author-box-content entry-content" itemprop="description">';
									$html .= $description;
								$html .= '</div>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';
					break;
			}
		$html .= '</section>';

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}

	}

	public function do_author_box_single() {

		if( !is_single() )
			return;

		$user_id = get_the_author_meta( 'ID' );
		if( !get_the_author_meta( 'genesis_author_box_single', $user_id ) )
			return;

		$this->do_author_box( 'single' );

	}

}

return new HC_Authors();
