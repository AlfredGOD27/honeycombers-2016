<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Users {
	public function __construct() {

		$this->show_pw_reset_form = false;
		$this->show_login_form    = false;

		add_action( 'wp', array($this, 'init') );
		add_action( 'wp_ajax_nopriv_hc_ajax_register', array($this, 'ajax_register') );
		add_action( 'wp_ajax_nopriv_hc_ajax_login', array($this, 'ajax_login') );
		add_action( 'wp_ajax_nopriv_hc_ajax_reset_password', array($this, 'ajax_reset_password') );
		add_action( 'wp_ajax_nopriv_hc_ajax_facebook_register_or_login', array($this, 'ajax_facebook') );
		add_action( 'wp_footer', array($this, 'display_modal') );

	}

	public function init() {

		global $post;

		if( !isset($post->ID) )
			return;

		$account_page_id = get_option( 'options__hc_profile_page_id' );
		if( (int) $post->ID !== (int) $account_page_id )
			return;

		$this->handle_logout();

		$this->handle_password_reset();

		add_action( 'genesis_entry_content', array($this, 'user_page_content') );

	}

	public function get_display_name( $user_id ) {

		$user = get_user_by( 'id', $user_id );

		if( !empty($user->first_name))
			return $user->first_name;

		if( !empty($user->display_name))
			return $user->display_name;

		return $user->user_login;

	}

	private function handle_logout() {

		// Maybe logout
		if( !isset($_GET['logout']) )
			return;

		if( is_user_logged_in() ) {
			wp_logout();

			$url = add_query_arg(
				array(
					'logged_out' => true,
				),
				HC()->profiles->get_url()
			);
		} else {
			$url = HC()->profiles->get_url();
		}

		wp_redirect($url);
		exit;

	}

	private function check_password_reset_key() {

		if( !isset($_GET['key']) || !isset($_GET['login']) )
			return false;

		$check_key = check_password_reset_key( $_GET['key'], $_GET['login'] );

		return empty($check_key) || is_wp_error($check_key) ? false : $check_key;

	}

	private function do_password_reset( $user ) {

		// Reset PW action?
		if( !isset($_POST['pass1']) || !isset($_POST['pass2']) ) {
			HC()->messages->add( 'error', 'You must set and confirm a new password.' );
		} else {
			if( $_POST['pass1'] !== $_POST['pass2'] ) {
				HC()->messages->add( 'error', 'Your passwords don\'t match.' );
			} else {
				$require_strong = user_can( $user, 'edit_posts' );
				$strong         = $this->check_password_strength( $_POST['pass1'], $require_strong );
				if( !$strong ) {
					HC()->messages->add( 'error', 'You must choose a stronger password.' );
				} else {
					reset_password( $user, $_POST['pass1'] );

					$url = add_query_arg(
						array(
							'password_reset' => true,
						),
						HC()->profiles->get_url()
					);
					wp_redirect( $url );
					exit;
				}
			}
		}

	}

	private function handle_password_reset() {

		// PW reset form?
		if( !isset($_GET['reset_password']) )
			return;

		$user = $this->check_password_reset_key();

		if( false === $user ) {
			HC()->messages->add( 'error', 'Invalid password reset link. Please <a href="#password-popup" class="open-popup-link">try again</a>.' );

			return;
		}

		if( isset($_POST['do_reset']) )
			$this->do_password_reset( $user );

		$this->show_pw_reset_form = true;

	}

	private function password_reset_form() {

		$url = add_query_arg(
			array(
				'reset_password' => true,
				'key'            => $_GET['key'],
				'login'          => $_GET['login'],
			),
			HC()->profiles->get_url()
		);
		?>
		<form action="<?php echo $url; ?>" method="post" autocomplete="off" class="one-half first">
			<input type="hidden" id="user_login" value="<?php echo esc_attr( $_GET['login'] ); ?>">
			<input type="hidden" name="rp_key" value="<?php echo esc_attr( $_GET['key'] ); ?>">

			<div class="field">
				<label for="pass1">New password</label>
				<input type="password" name="pass1" id="pass1" required>
			</div>

			<div class="field">
				<label for="pass2">Confirm new password</label>
				<input type="password" name="pass2" id="pass2" required>
			</div>

			<div class="form-footer">
				<button type="submit" name="do_reset" class="btn">Reset Password ></button>
			</div>

			<p class="password-instructions">Password must be at least 8 characters, and contain at least one number and one symbol.</p>
		</form>
		<?php

	}

	public function user_page_content() {

		if( isset($_GET['logged_out']) && $_GET['logged_out'] ) {
			// Logged out message
			HC()->messages->add( 'info', 'You have been logged out. <a href="#login-popup" class="open-popup-link">Login</a> again?' );
		} elseif( isset($_GET['welcome']) && $_GET['welcome'] ) {
			// Welcome message
			HC()->messages->add( 'info', 'Welcome to ' . get_bloginfo( 'name' ) . '!' );
		} elseif( isset($_GET['password_reset']) && $_GET['password_reset'] ) {
			// Welcome message
			HC()->messages->add( 'success', 'Your password has been reset. <a href="#login-popup" class="open-popup-link">Login?</a>' );
		}

		HC()->messages->display();

		if( $this->show_pw_reset_form )
			$this->password_reset_form();

		if( $this->show_login_form )
			HC()->messages->add_and_display( 'info', '<a href="#login-popup" class="open-popup-link">Login</a> or <a href="#register-popup" class="open-popup-link">register</a> to view your account.' );

	}

	private function die_with_error( $message ) {

		$output = array(
			'status'  => 'error',
			'message' => $message,
		);
		echo json_encode($output);
		wp_die();

	}

	private function die_with_success( $message, $user_id, $redirect_params = array() ) {

		$profile_id = HC()->profiles->get_user_profile_id( $user_id );
		$url        = !empty($profile_id) ? get_permalink($profile_id) : HC()->profiles->get_url();

		$url = add_query_arg(
			$redirect_params,
			$url
		);

		$output = array(
			'status'      => 'success',
			'message'     => $message,
			'redirect_to' => $url,
		);
		echo json_encode($output);
		wp_die();

	}

	private function check_password_strength( $password, $is_admin ) {

		if( $is_admin ) {
			$zxcvbn   = new Zxcvbn();
			$strength = $zxcvbn->passwordStrength( $password );

			return $strength['score'] >= 3;
		} else {
			$length     = strlen($password);
			$has_number = preg_match('/\d/', $password) > 0;
			$has_symbol = preg_match('/\W/', $password) > 0;

			return $length >= 8 && $has_number && $has_symbol;
		}

	}

	public function ajax_register() {

		if( empty($_POST['email']) ) {
			$this->die_with_error( 'You must enter a valid email.' );
		} else {
			$email = sanitize_email($_POST['email']);
		}

		if( empty($_POST['password']) ) {
			$this->die_with_error( 'You must enter a password.' );
		} else {
			$password = $_POST['password'];
		}

		$strong = $this->check_password_strength( $password, false );
		if( !$strong )
			$this->die_with_error( 'You must choose a stronger password.' );

		if( email_exists($email) || username_exists($email) )
			$this->die_with_error( 'A user with this email already exists.' );

		$args = array(
			'user_login' => $email,
			'user_email' => $email,
			'user_pass'  => $password,
			'role'       => 'subscriber',
		);
		$user_id = wp_insert_user( $args );

		if( is_wp_error($user_id) || 0 === $user_id ) {
			$this->die_with_error( 'An error occurred when creating your account. Please refresh the page and try again.' );
		} else {
			HC()->profiles->create_profile_for_user( $user_id );

			// Set login cookie
			wp_set_auth_cookie( $user_id );
			$this->die_with_success( 'Registration successful, redirecting...', $user_id );
		}

	}

	public function ajax_login() {

		if( empty($_POST['log']) || empty($_POST['pwd']) )
			$this->die_with_error( 'You must enter an email and password.' );

		$email = sanitize_email($_POST['log']);
		$user  = get_user_by( 'email', $email );
		if( empty($user) )
			$this->die_with_error( 'Your email or password is incorrect.' );

		$_POST['log'] = $user->user_login;
		$signon       = wp_signon();
		if( is_wp_error($signon) ) {
			$this->die_with_error( 'Your email or password is incorrect.' );
		} else {
			$this->die_with_success( 'Login successful, redirecting...', $user->ID );
		}

	}

	public function ajax_reset_password() {

		global $wpdb, $wp_hasher;

		$output = array();

		if( empty($_POST['email']) ) {
			$this->die_with_error( 'You must enter a valid email.' );
		} else {
			$email = sanitize_email($_POST['email']);
		}

		$user = get_user_by( 'email', $email );
		if( empty($user) )
			$this->die_with_error( 'There is no user registered with that email address.' );

		do_action( 'lostpassword_post' );

		$user_login = $user->user_login;
		$user_email = $user->user_email;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user->ID );
		if( !$allow )
			$this->die_with_error( 'You are not allowed to reset your password.' );

		$key = wp_generate_password( 20, false );
		do_action( 'retrieve_password_key', $user_login, $key );

		// Now insert the key, hashed, into the DB.
		if( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login) );

		$url = add_query_arg(
			array(
				'reset_password' => true,
				'key'            => $key,
				'login'          => rawurlencode($user_login),
			),
			HC()->profiles->get_url()
		);

		$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= $url . "\r\n";

		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$title    = sprintf( __('[%s] Password Reset'), $blogname );
		$title    = apply_filters( 'retrieve_password_title', $title );

		if( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) )
			$this->die_with_error( 'The password reset email could not be sent. Please refresh the page and try again.' );

		$output = array(
			'status'  => 'success',
			'message' => 'Check your email for the password reset link.',
		);
		echo json_encode($output);
		wp_die();

	}

	public function ajax_facebook() {

		global $wpdb;

		// Stop if no token
		if( !isset($_POST['token']) )
			$this->die_with_error( 'Invalid Facebook authorization. Please refresh the page and try again.' );

		// Get user info from FB
		$url = add_query_arg(
			array(
				'access_token' => $_POST['token'],
				'fields'       => 'first_name,last_name,email',
			),
			'https://graph.facebook.com/me'
		);
		$response = wp_remote_get( $url );

		// Stop if not retreived
		if(
			is_wp_error($response) ||
			200 !== $response['response']['code']
		)
			$this->die_with_error( 'Invalid Facebook authorization. Please refresh the page and try again.' );

		// Build array
		$body = $response['body'];
		$body = json_decode( $body, true );

		// Stop if no FB ID
		if( !isset($body['id']) ) {
			$this->die_with_error( 'Facebook user not found. Please refresh the page and try again.' );
		} else {
			$facebook_id = sanitize_text_field($body['id']);
		}

		// Stop if no email
		if( !isset($body['email']) ) {
			$this->die_with_error( 'Your Facebook account doesn\'t appear to have an associated email address. Please register directly.' );
		} else {
			$email = $body['email'];
		}

		// Try to find FB ID in WP DB
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT user_id
				FROM $wpdb->usermeta
				WHERE meta_key = '_hc_facebook_id'
				AND meta_value = %s
				LIMIT 1
				",
				$facebook_id
			)
		);

		if( 0 === count($result) ) {
			// Register
			if( email_exists($email) || username_exists($email) ) {
				$this->die_with_error( 'A user with this email already exists.' );
			} else {
				// Save name, if available
				$first_name = $body['first_name'] ? sanitize_text_field($body['first_name']) : '';
				$last_name  = $body['last_name'] ? sanitize_text_field($body['last_name']) : '';

				$args = array(
					'user_login' => $email,
					'user_email' => $email,
					'user_pass'  => wp_generate_password( 32, false ),
					'role'       => 'subscriber',
					'first_name' => $first_name,
					'last_name'  => $last_name,
				);
				$user_id = wp_insert_user( $args );

				if( is_wp_error($user_id) || 0 === $user_id ) {
					$this->die_with_error( 'An error occurred when creating your account. Please refresh the page and try again.' );
				} else {
					HC()->profiles->create_profile_for_user( $user_id );

					// Save FB ID
					update_user_meta( $user_id, '_hc_facebook_id', $facebook_id );

					// Set login cookie
					wp_set_auth_cookie( $user_id );
					$this->die_with_success( 'Registration successful, redirecting...', $user_id, array('welcome', true) );
				}
			}
		} else {
			// Login
			$user = get_user_by( 'id', $result[0]->user_id );
			if( empty($user) || is_wp_error($user) ) {
				$this->die_with_error( 'User not found.' );
			} else {
				if( user_can( $user, 'edit_posts') ) {
					$this->die_with_error( 'Administrators cannot login via Facebook' );
				} else {
					// Set login cookie
					wp_set_auth_cookie( $user->ID );
					$this->die_with_success( 'Login successful, redirecting...', $user->ID );
				}
			}
		}

	}

	public function display_modal() {

		if( is_user_logged_in() )
			return;

		?>
		<aside id="login-popup" class="white-popup login-popup mfp-hide clearfix">
			<i class="ico-favicon"></i>

			<h2>Welcome to Honeycombers!</h2>

			<p class="lead">Lorem ipsum...</p>

			<a href="#" class="btn btn-facebook btn-icon hide-no-fb" rel="nofollow"><i class="ico-facebook"></i> <span>Login Via Facebook</span></a>

			<span class="or hide-no-fb"><span>Or</span></span>

			<form action="#" method="post">
				<div class="field">
					<label for="user_login_email" class="screen-reader-text">Email</label>
					<input type="email" name="log" id="user_login_email" placeholder="Email" required>
				</div>

				<div class="field">
					<label for="user_login_password" class="screen-reader-text">Password</label>
					<input type="password" name="pwd" id="user_login_password" placeholder="Password" required>
				</div>

				<div class="forgot-remember-bar clearfix">
					<div class="one-half first left">
						<a href="#password-popup" class="open-popup-link" rel="nofollow">Forgot your password?</a>
					</div>

					<div class="one-half right">
						<label class="checkbox">
							<input name="rememberme" type="checkbox" value="forever">
							Stay logged in
						</label>
					</div>
				</div>

				<button type="submit" name="wp-submit" class="btn">Log In</button>

				<p class="join">Don't have an account? <a href="#register-popup" class="open-popup-link" rel="nofollow">Join here</a></p>
			</form>

			<div class="messages"></div>
		</aside>
		<?php

		?>
		<aside id="password-popup" class="white-popup password-popup mfp-hide clearfix">
			<h2>Forgot your password? No problem.</h2>

			<div class="two-thirds first">
				<p>Instructions for resetting your password <br> will be sent to your email</p>

				<form action="#" method="post" autocomplete="off">
					<div class="field">
						<label for="forgot_password_email" class="screen-reader-text">Email</label>
						<input type="email" name="user_login" id="forgot_password_email" placeholder="Email" required>
					</div>

					<div class="form-footer">
						<button type="submit" name="wp-submit" class="btn">Send ></button>
					</div>
				</form>
			</div>

			<div class="messages"></div>
		</aside>
		<?php

		?>
		<aside id="register-popup" class="white-popup register-popup mfp-hide clearfix">
			<h2>Memorable trips start with a great travel kit</h2>

			<div class="two-thirds first">

				<p class="hide-no-fb">Register to create your Travel Kit</p>

				<a href="#" class="btn btn-facebook btn-icon hide-no-fb" rel="nofollow"><i class="ico-facebook"></i> <span>Facebook</span></a>

				<p class="hide-no-fb">or</p>

				<form action="#" method="post" autocomplete="off">
					<div class="field">
						<label for="user_register_email" class="screen-reader-text">Email</label>
						<input type="email" name="email" id="user_register_email" placeholder="Email" required>
					</div>

					<div class="field">
						<label for="user_register_password" class="screen-reader-text">Password</label>
						<input type="password" name="password" id="user_register_password" placeholder="Password" required>
						<p class="password-instructions">Password must be at least 8 characters, and contain at least one number and one symbol.</p>
					</div>

					<div class="form-footer">
						<button type="submit" name="wp-submit" class="btn">Sign Up ></button>

						<br><br>

						<p><em><strong>Already have an account?</strong></em> <a href="#login-popup" class="open-popup-link btn btn-small" rel="nofollow">Log In ></a></p>
					</div>
				</form>

			</div>

			<div class="messages"></div>
		</aside>
		<?php
	}

}

return new HC_Users();
