<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Gravity_Forms {
	public function __construct() {

		add_action( 'gform_paypal_fulfillment', array($this, 'fulfill_paypal_order'), 10, 4 );

	}

	public function fulfill_paypal_order( $entry, $feed, $transaction_id, $amount ) {

		$content = '';
		$content .= print_r($entry, true);
		$content .= print_r($feed, true);
		$content .= print_r($transaction_id, true);
		$content .= print_r($amount, true);

		wp_mail( 'me@cooperdukes.com', 'test', $content );

	}
}

return new HC_Gravity_Forms();
