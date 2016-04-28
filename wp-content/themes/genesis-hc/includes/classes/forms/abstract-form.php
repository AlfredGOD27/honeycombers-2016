<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class HC_Form_Abstract {
	private static $disallowed_slugs = array(
		'new',
		'edit',
		'add',
		'delete',
	);

	public function __construct() {

		$this->setup_fields();
		$this->set_nonce_key();

		if( !isset($this->allow_delete) )
			$this->allow_delete = false;

		if( $this->allow_delete ) {
			$deleted = $this->maybe_delete();
			if( $deleted )
				return;
		}

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

	public function sanitize_value( $field, $value ) {

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
			case 'posts_list':
				$value              = (array) $value;
				$whitelisted_values = array();
				foreach( $value as $item_id ) {
					if( HC()->folders->item_can_be_bookmarked( $item_id ) )
						$whitelisted_values[] = $item_id;
				}
				$value = $whitelisted_values;
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
			} elseif( 'edit' === $this->action ) {
				switch( $field['table'] ) {
					case 'posts':
						$value = $this->post_object->{$field['slug']};
						break;
					case 'postmeta':
						$value = get_post_meta( $this->post_id, $field['slug'], true );
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

		// Don't show empty post lists
		if( 'posts_list' === $field['type'] && empty($value) )
			return;

		$disabled         = isset($field['disabled']) && $field['disabled'] ? 'disabled' : '';
		$placeholder_text = isset($field['placeholder']) && $field['placeholder'] ? $field['placeholder'] : '';

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
			if( !empty($placeholder_text) )
				$placeholder_text .= ' *';
		} else {
			$required      = '';
			$required_text = '';
		}

		echo '<div class="' . implode( ' ', $classes ) . '" data-slug="' . $field['slug'] . '">';

			if( 'boolean' !== $field['type'] )
				echo '<label for="field-' . $field_id . '">' . $field['label'] . ' ' . $required_text . '</label>';

			if( isset($field['description']) )
				echo '<div class="description">' . wpautop($field['description']) . '</div>';

			switch( $field['type'] ) {
				case 'text':
				case 'email':
				case 'url':
					echo '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $field['slug'] . '" value="' . $value . '" ' . $required . ' ' . $disabled . ' maxlength="' . $field['maxlength'] . '" placeholder="' . $placeholder_text . '">';
					break;
				case 'password':
					echo '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $field['slug'] . '" autocomplete="off" ' . $required . ' placeholder="' . $placeholder_text . '">';
					break;
				case 'number':
					echo '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $field['slug'] . '" value="' . $value . '" ' . $required . ' ' . $disabled . ' placeholder="' . $placeholder_text . '">';
					break;
				case 'file':
					$multiple = isset($field['multiple']) && $field['multiple'];
					$name     = $multiple ? $field['slug'] . '[]' : $field['slug'];

					$html = '<input id="field-' . $field['slug'] . '" type="' . $field['type'] . '" name="' . $name . '" accept="' . implode(',', $field['allowed_mime_types']) . '" ' . ($multiple ? 'multiple' : '' ) . '>';

					if( !$multiple && !empty($value) ) {
						echo '<div class="image-preview">';
							switch( $field['preview_type'] ) {
								case 'image':
									echo wp_get_attachment_image( $value, $field['preview_image_size'] );
									echo $html;
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
					echo '<textarea id="field-' . $field['slug'] . '" name="' . $field['slug'] . '" ' . $required . ' ' . $disabled . ' maxlength="' . $field['maxlength'] . '" placeholder="' . $placeholder_text . '">' . $value . '</textarea>';
					break;
				case 'select':

					echo '<div>';
						echo '<select id="field-' . $field['slug'] . '" name="' . $field['slug'] . '" ' . $required . ' ' . $disabled . ' class="styled">';
							echo '<option value="">' . $placeholder_text . '</option>';
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
				case 'posts_list':
					echo '<div class="checkbox-list">';
						foreach( $value as $item_id ) {
							echo '<label class="checkbox">';
								echo '<input type="checkbox" name="' . $field['slug'] . '[]" value="' . $item_id . '" checked>';
								echo get_the_title($item_id);
							echo '</label>';
						}
					echo '</label>';
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

	public function check_required( $args ) {

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
				case 'posts_list':
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

	public function check_word_limit( $args ) {

		$over_limit_fields = array();
		foreach( $this->fields as $field ) {
			switch( $field['type'] ) {
				case 'text':
				case 'textarea':
					if(
						isset($field['word_limit']) &&
						$field['word_limit'] > 0 &&
						isset($args[ $field['table'] ][ $field['slug'] ])
					) {
						$words = explode( ' ', $args[ $field['table'] ][ $field['slug'] ] );
						if( count($words) > $field['word_limit'] )
							$over_limit_fields[] = $field;
					}
					break;
			}
		}

		return $over_limit_fields;

	}

	public function check_passwords_match() {

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

	public function check_password_strength() {

		foreach( $this->fields as $field ) {
			if( 'password' !== $field['type'] )
				continue;

			// Skip the '_2' version
			if( substr($field['slug'], -2) === '_2' )
				continue;

			if( !empty($_POST[ $field['slug'] ]) ) {
				$valid = HC()->profiles->check_password_strength( $_POST[ $field['slug'] ], user_can( $this->user->ID, 'manage_options' ) );
				if( !$valid )
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

			$files = array();
			if( isset($field['multiple']) && $field['multiple'] ) {
				$max_files = count($_FILES[ $field['slug'] ]['name']);

				if( isset($field['max_files']) )
					$max_files = min( $max_files, $field['max_files'] );

				$i = 0;
				while( $i <= ($max_files - 1) ) {
					$files[] = array(
						'size' => $_FILES[ $field['slug'] ]['size'][$i],
						'type' => $_FILES[ $field['slug'] ]['type'][$i],
					);

					++$i;
				}
			} else {
				$files[] = $_FILES[ $field['slug'] ];
			}

			foreach( $files as $file ) {
				if( empty($file['size']) )
					continue;

				if( !in_array($file['type'], $field['allowed_mime_types'], true) ) {
					HC()->messages->add(
						'error',
						sprintf( '<strong>%s</strong> is not an allowed format', $field['label'])
					);
					$has_errors = true;
					break 1;
				}

				if( $file['size'] > ($field['max_size'] * 1024 * 1024) ) {
					HC()->messages->add(
						'error',
						sprintf( '<strong>%s</strong> is too large. Please select a file under %sMB.', $field['label'], $field['max_size'])
					);
					$has_errors = true;
					break 1;
				}
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

			$files    = array();
			$multiple = isset($field['multiple']) && $field['multiple'];
			if( !is_array($_FILES[ $field['slug'] ]['name']) ) {
				$files[] = $_FILES[ $field['slug'] ];
			} else {
				$max_files = count($_FILES[ $field['slug'] ]['name']);

				if( isset($field['max_files']) )
					$max_files = min( $max_files, $field['max_files'] );

				$i = 0;
				while( $i <= ($max_files - 1) ) {
					$files[] = array(
						'name'     => $_FILES[ $field['slug'] ]['name'][$i],
						'type'     => $_FILES[ $field['slug'] ]['type'][$i],
						'tmp_name' => $_FILES[ $field['slug'] ]['tmp_name'][$i],
						'error'    => $_FILES[ $field['slug'] ]['error'][$i],
						'size'     => $_FILES[ $field['slug'] ]['size'][$i],
					);

					++$i;
				}
			}

			$attachment_ids = array();
			foreach( $files as $file ) {
				if( empty($file['size']) )
					continue;

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

					$attachment_ids[] = $attach_id;
				}
			}

			if( !empty($attachment_ids) ) {
				$value = $multiple ? $attachment_ids : $attachment_ids[0];

				switch( $field['table'] ) {
					case 'postmeta':
						update_post_meta( $this->post_id, $field['slug'], $value );
						break;
					case 'usermeta':
						update_user_meta( $this->user_object->ID, $field['slug'], $value );
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

	protected function maybe_delete() {

		if( !isset($_POST['hc_delete']) )
			return;

		// Stop if bad nonce
		if( !$this->check_nonce() ) {
			HC()->messages->add( 'error', 'Your session has expired. Please try again.' );

			return;
		}

		wp_delete_post( $this->post_id, true );

		$this->do_after_delete();

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
				case 'posts_list':
					if( isset($_POST[ $field['slug'] ]) ) {
						$args[ $field['table'] ][ $field['slug'] ] = $this->sanitize_value( $field, $_POST[ $field['slug'] ] );
					} else {
						$args[ $field['table'] ][ $field['slug'] ] = array();
					}
					break;
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

		// Stop if over word limit fields
		$over_limit_fields = $this->check_word_limit( $args );
		if( count($over_limit_fields) > 0 ) {
			foreach( $over_limit_fields as $field )
				HC()->messages->add( 'error', '<strong>' . $field['label'] . '</strong> is too long. It must be under  <strong>' . $field['word_limit'] . '</strong> words.' );

			return;
		}

		$has_upload_errors = $this->check_uploads();
		if( $has_upload_errors )
			return;

		// Sync post_name to title
		if( isset($args['posts']['post_title']) ) {
			$args['posts']['post_name'] = sanitize_title($args['posts']['post_title']);

			if( in_array($args['posts']['post_name'], self::$disallowed_slugs, true) )
				$args['posts']['post_name'] .= '-' . rand(1, 99);
		}

		switch( $this->action ) {
			case 'add':
				if( count($args['posts']) > 0 ) {
					$args['posts']['post_type']   = $this->post_type;
					$args['posts']['post_status'] = $this->default_post_status;
					$args['posts']['post_author'] = get_current_user_id();

					$this->post_id     = wp_insert_post( $args['posts'] );
					$this->post_object = get_post($this->post_id);
				}

				if( count($args['users']) > 0 ) {
					$this->user_id     = wp_insert_user( $args['users'] );
					$this->user_object = get_user_by( 'id', $this->user_id );
				}
				break;
			case 'edit':
				if( count($args['posts']) > 0 ) {
					$args['posts']['ID'] = $this->post_id;
					wp_update_post( $args['posts'] );
				}

				if( count($args['users']) > 0 ) {
					$args['users']['ID'] = $this->user_object->ID;
					wp_update_user( $args['users'] );
				}
				break;
		}

		// Validation passed. Save meta-type fields
		foreach( $this->fields as $field ) {
			if( !isset($args[ $field['table'] ][ $field['slug'] ]) )
				continue;

			switch( $field['table'] ) {
				case 'postmeta':
					update_post_meta( $this->post_id, $field['slug'], $args[ $field['table'] ][ $field['slug'] ] );
					break;
				case 'usermeta':
					update_user_meta( $this->user_object->ID, $field['slug'], $args[ $field['table'] ][ $field['slug'] ] );
					break;
			}
		}

		// Save files
		$this->upload_files();

		// Save subcriptions
		$this->save_subscriptions( $args );

		$this->do_after_save();

	}

	public function display_form() {

		?>
		<form method="post" enctype="multipart/form-data" class="hc-form entry-content">
			<div class="form-header clearfix">
				<?php
				if( isset($this->editor) ) {
					switch( $this->action ) {
						case 'add':
							?>
							<h2>Add New <?php echo $this->editor->post_type_object->labels->singular_name; ?></h2>
							<?php
							break;
						case 'edit':
							?>
							<h2>Edit <?php echo $this->editor->post_type_object->labels->singular_name; ?></h2>
							<?php
							break;
					}
				}
				?>
			</div>

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

				<?php
				switch( $this->action ) {
					case 'add':
						?>
						<button type="submit" name="hc_edit" class="btn btn-solid">Submit</button>
						<?php
						break;
					case 'edit':
						?>
						<button type="submit" name="hc_edit" class="btn btn-solid">Update</button>
						<?php

						if( $this->allow_delete ) {
							?>
							<button type="submit" name="hc_delete" class="btn btn-link">Delete</button>
							<?php
						}
						break;
				}
				?>
			</div>
		</form>
		<?php

	}

}
