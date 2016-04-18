<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Folder_Editor extends HC_Form_Abstract {
	public function __construct( $folder_id ) {

		$this->post_id     = $folder_id;
		$this->post_object = get_post( $folder_id );

		parent::__construct();

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
		$this->fields[] = array(
			'slug'     => '_hc_folder_is_public',
			'label'    => 'Public?',
			'type'     => 'radio',
			'table'    => 'postmeta',
			'required' => true,
			'options'  => array(
				'Yes',
				'No',
			),
			'classes' => array('one-half'),
		);
		$this->fields[] = array(
			'slug'     => 'post_content',
			'label'    => 'Description',
			'type'     => 'textarea',
			'table'    => 'posts',
			'required' => false,
			'classes'  => array('first', 'one-half'),
		);
		$this->fields[] = array(
			'slug'               => '_thumbnail_id',
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

	}

	protected function set_nonce_key() {

		$this->nonce_key = 'edit_' . $this->post_object->ID . '_' . get_current_user_id();

	}

	protected function do_post_save() {

		HC()->messages->add( 'success', 'Folder updated.' );

	}

}
