<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use ZxcvbnPhp\Zxcvbn;

abstract class HC_Form_Abstract {
	public function __construct() {

		$this->setup_fields();
		$this->set_nonce_key();
		$this->maybe_save();

		// Enforce max lengths
		foreach( $this->fields as $idx => $field ) {
			switch( $field['type'] ) {
				case 'text':
				case 'email':
				case 'url':
				case 'number':
				case 'textarea':
					// Set default maxlengths
					if( !isset($field['maxlength']) )
						$this->fields[$idx]['maxlength'] = 250;
					break;
			}
		}

	}

	protected function get_field_by_slug( $slug ) {

		foreach( $this->fields as $field ) {
			if( $slug === $field['slug'] )
				return $field;
		}

		return false;

	}

	protected function sanitize_email( $value ) {

		return filter_var( $value, FILTER_VALIDATE_EMAIL ) ? sanitize_email( $value ) : false;

	}

	protected function sanitize_value( $field, $value ) {

		$maxlength = isset($field['maxlength']) ? $field['maxlength'] : 2500;

		switch( $field['type'] ) {
			case 'text':
				$value = substr( $value, 0, $maxlength );
				$value = sanitize_text_field( $value );
				break;
			case 'email':
				$value = substr( $value, 0, $maxlength );
				$value = $this->sanitize_email( $value );
				break;
			case 'url':
				$value = substr( $value, 0, $maxlength );
				$value = esc_url( $value );
				break;
			case 'password':
				// Sanitized by wp_insert_user
				break;
			case 'number':
				$value = substr( $value, 0, $maxlength );
				$value = floatval( $value );
			case 'textarea':
				$value = substr( $value, 0, $maxlength );
				$value = wp_kses_data( $value );
				break;
			case 'select':
			case 'radio':
				$value = in_array( $value, $field['options'], true ) ? $value : false;
				break;
			case 'boolean':
				$value = !empty($value) ? 'yes' : false;
				break;
			case 'subscriptions':
				$value              = (array) $value;
				$whitelisted_values = array();
				foreach( $value as $interest ) {
					if( isset($field['interests'][$interest]) )
						$whitelisted_values[] = $interest;
				}

				$value = $whitelisted_values;
				break;
		}

		return $value;

	}

	protected function get_field_value( $field ) {

		$value = false;
		if( isset($_POST[ $field['slug'] ]) ) {
			$value = $_POST[ $field['slug'] ];
		} else {
			if( 'subscriptions' === $field['type'] ) {
				$email_field = $this->get_field_by_slug('user_email');
				$email       = $this->get_field_value($email_field);
				if( !empty($email) )
					$value = HC()->subscriptions->get_subscriber_interests( $email );
			} else {
				switch( $field['table'] ) {
					case 'posts':
						$value = $this->target_object->{$field['slug']};
						break;
					case 'postmeta':
						$value = get_post_meta( $this->target_id, $field['slug'], true );
						break;
					case 'users':
						$value = $this->user_object->{$field['slug']};
						break;
					case 'usermeta':
						$value = get_user_meta( $this->user_object->ID, $field['slug'], true );
						break;
				}
			}
		}

		if( false !== $value )
			$value = $this->sanitize_value( $field, $value );

		return $value;

	}

	protected function display_field( $field ) {

		$field_id = sanitize_title( $field['slug'] );
		$field_id = esc_attr($field_id);

		$value = $this->get_field_value( $field );

		$disabled = isset($field['disabled']) && $field['disabled'] ? 'disabled' : '';

		$classes   = array();
		$classes[] = 'field';
		$classes[] = 'field-' . $field['type'];
		$classes[] = 'clearfix';
		if( isset($field['classes']) )
			$classes = array_merge( $classes, $field['classes'] );

		if( $field['required'] ) {
			$required      = 'required';
			$required_text = '<span class="required">*</span>';
			$classes[]     = 'is-required';
		} else {
			$required      = '';
			$required_text = '';
		}

		echo '<div class="' . implode( ' ', $classes ) . '" data-slug="' . $field['slug'] . '">';

			if( 'boolean' !== $field['type'] )
				echo '<label for="field-' . $field_id . '">' . $field['label'] . ' ' . $required_text . '</label>';

			switch( $field['type'] ) {
				case 'text':
				case 'email':
				case 'url':
					echo '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $field['slug'] . '" value="' . $value . '" ' . $required . ' ' . $disabled . ' maxlength="' . $field['maxlength'] . '">';
					break;
				case 'password':
					echo '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $field['slug'] . '" autocomplete="off">';
					break;
				case 'number':
					echo '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $field['slug'] . '" value="' . $value . '" ' . $required . ' ' . $disabled . '>';
					break;
				case 'file':
					$html = '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $field['slug'] . '" accept="' . implode(',', $field['allowed_mime_types']) . '">';

					if( !empty($value) ) {
						echo '<div class="image-preview">';
							switch( $field['preview_type'] ) {
								case 'image':
									echo '<div class="one-half first">';
										echo wp_get_attachment_image( $value, 'avatar' );
									echo '</div>';

									echo '<div class="one-half">';
										echo $html;
									echo '</div>';
									break;
								case 'link':
									$attachment = get_attached_file( (int) $value );
									$url        = wp_get_attachment_url( (int) $value );
									echo '<a href="' . $url . '" target="_blank">' . basename($attachment) . '</a>';
									echo $html;
									break;
							}

						echo '</div>';
					} else {
						echo $html;
					}
					break;
				case 'textarea':
					echo '<textarea id="field-' . $field['slug'] . '" name="' . $field['slug'] . '" ' . $required . ' ' . $disabled . ' maxlength="' . $field['maxlength'] . '">' . $value . '</textarea>';
					break;
				case 'select':
					$placeholder = isset($field['placeholder']) ? $field['placeholder'] : 'Select ' . $field['label'];

					echo '<div>';
						echo '<select id="field-' . $field['slug'] . '" name="' . $field['slug'] . '" ' . $required . ' ' . $disabled . ' class="styled">';
							echo '<option value="">' . $placeholder . '</option>';
							foreach( $field['options'] as $option ) {
								$option_value = esc_attr($option);
								echo '<option value="' . $option_value . '" ' . selected( $value, $option_value, false ) . '>' . $option . '</option>';
							}
						echo '</select>';
						echo '<i class="ico-arrow-down"></i>';
					echo '</div>';
					break;
				case 'radio':
					echo '<div class="radio-list">';
						foreach( $field['options'] as $option ) {
							$option_value = esc_attr($option);
							echo '<label class="radio">';
								echo '<input type="radio" name="' . $field['slug'] . '" value="' . $option_value . '" ' . checked( $value, $option_value, false ) . ' required>';
								echo $option;
							echo '</label>';
						}
					echo '</div>';
					break;
				case 'boolean':
					$checked = !empty($value) ? 'checked' : '';
					echo '<div class="checkbox-list">';
						echo '<label class="checkbox">';
							echo '<input id="field-' . $field['slug'] . '" type="checkbox" name="' . $field['slug'] . '" value="yes" ' . $checked . '>';
							echo $field['label'] . ' ' . $required_text;
						echo '</label>';
					echo '</div>';
					break;
				case 'subscriptions':
					echo '<div class="checkbox-list">';
						foreach( $field['interests'] as $interest_id => $interest_name ) {
							$checked = in_array($interest_id, $value, true) ? 'checked' : '';
							echo '<label class="checkbox">';
								echo '<input type="checkbox" name="' . $field['slug'] . '[]" value="' . $interest_id . '" ' . $checked . '>';
								echo $interest_name;
							echo '</label>';
						}
					echo '</div>';
					break;
			}
		echo '</div>';

	}

	protected function check_nonce() {

		return wp_verify_nonce( $_POST['_wpnonce'], $this->nonce_key );

	}

	protected function check_required( $args ) {

		$empty_fields = array();
		foreach( $this->fields as $field ) {
			if( !$field['required'] )
				continue;

			switch( $field['type'] ) {
				case 'text':
				case 'email':
				case 'url':
				case 'number':
				case 'textarea':
				case 'select':
				case 'radio':
				case 'subscriptions':
					// These are empty if they have no content
					if(
						!isset($args[ $field['table'] ][ $field['slug'] ]) ||
						0 === strlen($args[ $field['table'] ][ $field['slug'] ])
					)
						$empty_fields[] = $field;
					break;
				case 'password':
					// Passwords are empty if the PW or the repeater field has no content
					if(
						!isset($args[ $field['table'] ][ $field['slug'] ]) ||
						0 === strlen($args[ $field['table'] ][ $field['slug'] ]) ||
						!isset($_POST[ $field['slug'] . '_2' ]) ||
						0 === strlen($_POST[ $field['slug'] . '_2' ])
					)
						$empty_fields[] = $field;
					break;
				case 'file':
					// Files are empty if $_FILES has no content
					if(
						!isset($_FILES[ $field['slug'] ])
					)
						$empty_fields[] = $field;
					break;
				case 'boolean':
					// Booleans are empty if not 'yes'
					if(
						!isset($args[ $field['table'] ][ $field['slug'] ]) ||
						'yes' !== $args[ $field['table'] ][ $field['slug'] ]
					)
						$empty_fields[] = $field;
					break;
			}
		}

		return $empty_fields;

	}

	protected function check_passwords_match() {

		foreach( $this->fields as $field ) {
			if( 'password' !== $field['type'] )
				continue;

			// Skip the '_2' version
			if( substr($field['slug'], -2) === '_2' )
				continue;

			if(
				!isset($_POST[ $field['slug'] ]) ||
				!isset($_POST[ $field['slug'] . '_2' ]) ||
				$_POST[ $field['slug'] ] !== $_POST[ $field['slug'] . '_2' ]
			)
				return false;
		}

		return true;

	}

	protected function check_password_strength() {

		foreach( $this->fields as $field ) {
			if( 'password' !== $field['type'] )
				continue;

			// Skip the '_2' version
			if( substr($field['slug'], -2) === '_2' )
				continue;

			if( !empty($_POST[ $field['slug'] ]) ) {
				$zxcvbn   = new Zxcvbn();
				$strength = $zxcvbn->passwordStrength( $_POST[ $field['slug'] ] );

				if( $strength['score'] < $this->minimum_password_strength )
					return false;
			}
		}

		return true;

	}

	protected function check_uploads() {

		$has_errors = false;
		foreach( $this->fields as $field ) {
			if( 'file' !== $field['type'] )
				continue;

			if( !isset($_FILES[ $field['slug'] ]) )
				continue;

			if( empty($_FILES[ $field['slug'] ]['size']) )
				continue;

			$file = $_FILES[ $field['slug'] ];

			if( !in_array($file['type'], $field['allowed_mime_types'], true) ) {
				HC()->messages->add(
					'error',
					sprintf( '<strong>%s</strong> is not an allowed format', $field['label'])
				);
				$has_errors = true;
				continue;
			}

			if( $file['size'] > ($field['max_size'] * 1024 * 1024) ) {
				HC()->messages->add(
					'error',
					sprintf( '<strong>%s</strong> is too large. Please select a file under %sMB.', $field['label'], $field['max_size'])
				);
				$has_errors = true;
				continue;
			}
		}

		return $has_errors;

	}

	protected function get_new_item_hash() {

		$id   = array();
		$id[] = time();
		$id[] = wp_generate_password();
		$id   = implode( '', $id );
		$id   = hash( 'sha256', $id );
		$id   = substr( $id, 0, 6 );

		return $id;

	}

	public function filter_upload_name( $file ) {

		$file['name'] = $this->get_new_item_hash() . '-' . $file['name'];

		return $file;

	}

	protected function upload_files() {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$upload_overrides = array('test_form' => false);
		$wp_upload_dir    = wp_upload_dir();

		foreach( $this->fields as $field ) {
			if( 'file' !== $field['type'] )
				continue;

			if( !isset($_FILES[ $field['slug'] ]) )
				continue;

			if( empty($_FILES[ $field['slug'] ]['size']) )
				continue;

			$file = $_FILES[ $field['slug'] ];

			add_filter( 'wp_handle_upload_prefilter', array($this, 'filter_upload_name') );
			$moved_file = wp_handle_upload( $file, $upload_overrides );
			remove_filter( 'wp_handle_upload_prefilter', array($this, 'filter_upload_name') );

			if( $moved_file ) {
				// Check the type of tile. We'll use this as the 'post_mime_type'.
				$filetype = wp_check_filetype( basename( $moved_file['file'] ), null );

				// Prepare an array of post data for the attachment.
				$attachment = array(
					'guid'           => $wp_upload_dir['url'] . '/' . basename( $moved_file['file'] ),
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $moved_file['file'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);

				// Insert the attachment.
				$attach_id = wp_insert_attachment( $attachment, $moved_file['file'] );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $moved_file['file'] );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				switch( $field['table'] ) {
					case 'postmeta':
						update_post_meta( $this->target_id, $field['slug'], $attach_id );
						break;
					case 'usermeta':
						update_user_meta( $this->user_object->ID, $field['slug'], $attach_id );
						break;
				}
			}
		}

	}

	protected function save_subscriptions( $args ) {

		foreach( $this->fields as $field ) {
			if( 'subscriptions' !== $field['type'] )
				continue;

			$current_interests = isset($args[ $field['table'] ][ $field['slug'] ]) ? $args[ $field['table'] ][ $field['slug'] ] : array();

			$interests = array();
			foreach( $field['interests'] as $interest_id => $interest_name )
				$interests[$interest_id] = in_array($interest_id, $current_interests, true);

			HC()->subscriptions->subscribe( $args['users']['user_email'], $interests );
		}

	}

	protected function maybe_save() {

		if( !isset($_POST['hc_edit']) )
			return;

		// Stop if bad nonce
		if( !$this->check_nonce() ) {
			HC()->messages->add( 'error', 'Your session has expired. Please try again.' );

			return;
		}

		// Stop if mismatches PW fields
		$passwords_match = $this->check_passwords_match();
		if( !$passwords_match ) {
			HC()->messages->add( 'error', 'Your passwords don\'t match.' );

			return;
		}

		// Stop if mismatches PW fields
		$password_score = $this->check_password_strength();
		if( !$password_score ) {
			HC()->messages->add( 'error', 'You must choose a stronger password.' );

			return;
		}

		$args = array(
			'posts'    => array(),
			'postmeta' => array(),
			'users'    => array(),
			'usermeta' => array(),
		);

		// Populate fields
		foreach( $this->fields as $field ) {
			if( isset($field['disabled']) && $field['disabled'] )
				continue;

			switch( $field['type'] ) {
				case 'text':
				case 'email':
				case 'url':
				case 'number':
				case 'textarea':
				case 'select':
				case 'radio':
				case 'boolean':
				case 'subscriptions':
					if( isset($_POST[ $field['slug'] ]) )
						$args[ $field['table'] ][ $field['slug'] ] = $this->sanitize_value( $field, $_POST[ $field['slug'] ] );
				case 'password':
					// Skip the '_2' version
					if( substr($field['slug'], -2) === '_2' )
						continue;

					if( !empty($_POST[ $field['slug'] ]) )
						$args[ $field['table'] ][ $field['slug'] ] = $this->sanitize_value( $field, $_POST[ $field['slug'] ] );
					break;
			}
		}

		// Stop if empty required fields
		$empty_required_fields = $this->check_required( $args );
		if( count($empty_required_fields) > 0 ) {
			foreach( $empty_required_fields as $field )
				HC()->messages->add( 'error', '<strong>' . $field['label'] . '</strong> is a required field.' );

			return;
		}

		$has_upload_errors = $this->check_uploads();
		if( $has_upload_errors )
			return;

		// Validation passed. Save meta-type fields
		foreach( $this->fields as $field ) {
			if( !isset($args[ $field['table'] ][ $field['slug'] ]) )
				continue;

			switch( $field['table'] ) {
				case 'postmeta':
					update_post_meta( $this->target_id, $field['slug'], $args[ $field['table'] ][ $field['slug'] ] );
					break;
				case 'usermeta':
					update_user_meta( $this->user_object->ID, $field['slug'], $args[ $field['table'] ][ $field['slug'] ] );
					break;
			}
		}

		if( count($args['posts']) > 0 ) {
			$args['posts']['ID'] = $this->target_id;

			// Sync post_name to title
			if( isset($args['posts']['post_title']) )
				$args['posts']['post_name'] = sanitize_title($args['posts']['post_title']);

			wp_update_post( $args['posts'] );
		}

		if( count($args['users']) > 0 ) {
			$args['users']['ID'] = $this->user_object->ID;
			wp_update_user( $args['users'] );
		}

		// Save files
		$this->upload_files();

		// Save subcriptions
		$this->save_subscriptions( $args );

		$this->do_post_save();

	}

	public function display_form() {

		?>
		<form method="post" enctype="multipart/form-data" class="hc-form entry-content">
			<div class="form-body clearfix">
				<?php
				foreach( $this->fields as $field )
					$this->display_field( $field );
				?>
			</div>

			<div class="form-footer clearfix">
				<?php
				wp_nonce_field( $this->nonce_key );
				?>

				<button type="submit" name="hc_edit" class="btn btn-solid">Update</button>
			</div>
		</form>
		<?php

	}

}
