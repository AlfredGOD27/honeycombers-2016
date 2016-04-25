<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Event_Editor extends HC_Form_Abstract {
	public function __construct() {

		$this->post_type           = 'event';
		$this->level               = $this->get_user_level();
		$this->action              = 'add';
		$this->default_post_status = 'pending';

		parent::__construct();

	}

	public function get_user_level() {

		$user_id = get_current_user_id();

		$premium_credits = get_user_meta( $user_id, '_hc_' . $this->post_type . '_credits_premium', true );
		if( !empty($premium_credits) )
			return 'premium';

		$upgrade_credits = get_user_meta( $user_id, '_hc_' . $this->post_type . '_credits_upgrade', true );
		if( !empty($upgrade_credits) )
			return 'upgrade';

		return 'free';

	}

	public function setup_fields() {

		$this->fields = array();

		$this->fields[] = array(
			'slug'     => 'post_title',
			'label'    => 'Name',
			'type'     => 'text',
			'table'    => 'posts',
			'required' => true,
			'classes'  => array('first', 'one-half'),
		);

		switch( $this->level ) {
			case 'free':

				break;
			case 'upgrade':
				$description_word_limit = 300;
				$gallery_images         = 5;
				break;
			case 'premium':
				$description_word_limit = 0;
				$gallery_images         = 10;
				break;
			default:
				$description_word_limit = 50;
				$gallery_images         = 1;
				break;
		}

		$this->fields[] = array(
			'slug'       => 'post_content',
			'label'      => 'Description',
			'type'       => 'textarea',
			'table'      => 'posts',
			'word_limit' => 50,
			'required'   => false,
			'classes'    => array('first', 'one-half'),
		);

		$i = 1;
		while( $i <= $gallery_images ) {

			$this->fields[] = array(
				'slug'               => 1 === $i ? '_thumbnail_id' : '_hc_gallery_image_' . $i,
				'label'              => 'Image',
				'type'               => 'file',
				'table'              => 'postmeta',
				'required'           => false,
				'allowed_mime_types' => array('image/jpg', 'image/jpeg'),
				'max_size'           => 1,
				'preview_type'       => 'image',
				'preview_image_size' => 'archive-small',
				'classes'            => array('one-half', 'block-image'),
			);
			++$i;
		}

	}

	protected function set_nonce_key() {

		switch( $this->action ) {
			case 'add':
				$this->nonce_key = 'add_event_' . get_current_user_id();
				break;
		}

	}

	protected function subtract_point() {

		if( 'free' === $this->level )
			return;

		$user_id = get_current_user_id();
		$key     = '_hc_' . $this->post_type . '_credits_' . $this->level;
		$points  = get_user_meta( $user_id, $key, true );
		--$points;
		update_user_meta( $user_id, $key, $points );

		$data = array(
			'post_type'          => $this->post_type,
			'level'              => $this->level,
			'target_user_id'     => $user_id,
			'initiating_user_id' => $user_id,
			'ref_id'             => $this->post_id,
			'amount'             => -1,
		);

		HC()->logs->add( $data );

	}

	protected function do_after_save() {

		// Save level
		update_post_meta( $this->post_id, '_hc_' . $this->post_type . '_level', $this->level );

		// Remove point
		$this->subtract_point( $type );

		// Redirect
		$url = add_query_arg(
			array(
				'event_added' => true,
			),
			HC()->profiles->get_url()
		);

		wp_redirect( $url );
		exit;

	}

}
