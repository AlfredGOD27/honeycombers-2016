<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Users {
	public function __construct() {

		add_filter( 'get_avatar', array($this, 'get_avatar'), 10, 5 );

		remove_action( 'genesis_after_entry', 'genesis_do_author_box_single', 8 );
		add_action( 'genesis_after_entry', array($this, 'do_author_box_single'), 8 );

	}

	public function get_title( $user_id ) {

		$title = get_user_meta( $user_id, '_hc_job_title', true );
		if( empty($title) )
			return;

		return sanitize_text_field($title);

	}

	public function get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

		$user = false;
		if( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
			$user    = get_user_by( 'id', $user_id );
		} elseif( is_object( $id_or_email ) ) {
			if( !empty( $id_or_email->user_id ) ) {
				$user_id = (int) $id_or_email->user_id;
				$user    = get_user_by( 'id', $user_id );
			}
		} else {
			$user = get_user_by( 'email', $id_or_email );
		}

		if( !empty($user) && is_object( $user ) ) {
			$user_image_id = get_user_meta( $user->ID, '_hc_profile_image_id', true );
			if( !empty($user_image_id) ) {
				$avatar = wp_get_attachment_image(
					$user_image_id,
					array($size, $size),
					'',
					array(
						'title' => $user->display_name,
						'alt'   => $user->display_name,
						'class' => 'avatar',
					)
				);
			}
		}

		return $avatar;

	}

	private function do_author_box( $context, $echo = true ) {

		global $authordata;

		$user_id = get_the_author_meta( 'ID' );

		$gravatar_size = apply_filters( 'genesis_author_box_gravatar_size', 120, $context );
		$authordata    = is_object( $authordata ) ? $authordata : get_userdata( get_query_var( 'author' ) );
		$gravatar      = get_avatar( get_the_author_meta( 'email' ), $gravatar_size );
		$description   = wpautop( get_the_author_meta( 'description' ) );

		$title     = '<span itemprop="name">' . get_the_author() . '</span>';
		$job_title = $this->get_title( $user_id );
		if( !empty($job_title) )
			$title .= ' ' . $job_title;

		$title = apply_filters( 'genesis_author_box_title', $title, $context );

		if( 'single' === $context && !genesis_get_seo_option( 'semantic_headings' ) ) {
			$heading_element = 'h4';
		} elseif( genesis_a11y( 'headings' ) || get_the_author_meta( 'headline', (int) get_query_var( 'author' ) ) ) {
			$heading_element = 'h4';
		} else {
			$heading_element = 'h1';
		}

		$html = sprintf( '<section %s>', genesis_attr( 'author-box' ) );
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
					$html .= '<' . $heading_element . ' class="author-box-title">' . $title . '</' . $heading_element . '>';
					$html .= '<div class="author-box-content entry-content" itemprop="description">';
						$html .= $description;

						if( 'single' === $context )
							$html .= '<p class="read-more">Read more from <a href="' . get_the_author_meta('url') . '">' . get_the_author() . '</a></p>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
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

return new HC_Users();
