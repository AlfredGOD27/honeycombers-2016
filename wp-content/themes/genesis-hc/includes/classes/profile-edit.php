<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Profile_Edit_Form extends HC_Form_Abstract {
	public function __construct( $user ) {

		$this->user_object = $user;

		parent::__construct();

	}

	protected function setup_fields() {

		$this->fields = array();

		$this->fields[] = array(
			'slug'     => 'first_name',
			'label'    => 'First Name',
			'type'     => 'text',
			'table'    => 'users',
			'required' => true,
			'classes'  => array('first', 'one-third'),
		);
		$this->fields[] = array(
			'slug'     => 'last_name',
			'label'    => 'Last Name',
			'type'     => 'text',
			'table'    => 'users',
			'required' => true,
			'classes'  => array('one-third'),
		);
		$this->fields[] = array(
			'slug'     => 'user_email',
			'label'    => 'Email Address',
			'type'     => 'email',
			'table'    => 'users',
			'required' => true,
			'classes'  => array('one-third'),
		);
		$this->fields[] = array(
			'slug'     => 'user_pass',
			'label'    => 'New Password',
			'type'     => 'password',
			'table'    => 'users',
			'required' => false,
			'classes'  => array('one-third', 'first', 'use-zxcvbn'),
		);
		$this->fields[] = array(
			'slug'     => 'user_pass_2',
			'label'    => 'Confirm Password',
			'type'     => 'password',
			'table'    => 'users',
			'required' => false,
			'classes'  => array('one-third'),
		);
		$this->fields[] = array(
			'slug'               => '_thumbnail_id',
			'label'              => 'Profile Image (<1MB)',
			'type'               => 'file',
			'table'              => 'usermeta',
			'required'           => false,
			'allowed_mime_types' => array('image/jpg', 'image/jpeg'),
			'max_size'           => 1,
			'preview_type'       => 'image',
			'classes'            => array('first', 'one-third'),
		);

	}

	protected function set_nonce_key() {

		$this->nonce_key = 'edit_' . $this->user_object->ID;

	}

	protected function do_post_save() {

		HC()->messages->add( 'success', 'Profile saved.' );

	}

}
