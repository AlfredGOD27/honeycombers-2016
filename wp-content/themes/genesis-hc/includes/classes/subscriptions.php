<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Subscriptions {
	public function __construct() {

		add_action( 'wp_ajax_hc_ajax_subscribe', array($this, 'ajax_subscribe') );
		add_action( 'wp_ajax_nopriv_hc_ajax_subscribe', array($this, 'ajax_subscribe') );

	}

	private function setup_api() {

		if( !isset($this->api_key) )
			$this->api_key = get_option( 'options__hc_mailchimp_api_key' );

		if( !isset($this->list_id) )
			$this->list_id = get_option( 'options__hc_mailchimp_list_id' );

	}

	private function subscribe( $email, $list_id = false ) {

		$this->setup_api();

		if( false === $list_id )
			$list_id = $this->list_id;

		// Build query
		$url = 'https://us11.api.mailchimp.com/3.0/';
		$url .= 'lists/' . $list_id . '/members/' . md5( strtolower($email) );

		$args = array(
			'method'  => 'PUT',
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'un_not_needed' . ':' . $this->api_key ),
			),
			'body' => array(
				'status'        => 'subscribed',
				'email_address' => $email,
			),
		);

		$args['body'] = json_encode($args['body']);

		return wp_remote_post( $url, $args );

	}

	public function ajax_subscribe() {

		$output = array();

		$email = $_POST['email'];
		if( empty($email) ) {
			$output = array(
				'status'  => 'error',
				'message' => __( 'Email not valid.', CHILD_THEME_TEXT_DOMAIN ),
			);
		} else {
			$result = $this->subscribe( $email );

			if(200 === $result['response']['code'] ) {
				$output = array(
					'status'  => 'success',
					'message' => 'Successfully subscribed',
				);
			} else {
				$output = array(
					'status'  => 'error',
					'message' => 'Failed to subscribe',
				);
			}
		}

		$output = json_encode($output);
		echo $output;
		wp_die();

	}

	public function display_form() {

		?>
		<form class="subscribe-form">
			<label for="subscribe-email">Email</label>
			<div class="email-container">
				<input id="subscribe-email" type="email" required>
			</div>

			<button type="submit" class="btn">Sign Up</button>
		</form>
		<?php

	}

}

return new HC_Subscriptions();
