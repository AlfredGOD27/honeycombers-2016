<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Gravity_Forms {
	public function __construct() {

		add_action( 'gform_paypal_fulfillment', array($this, 'fulfill_paypal_order'), 10, 4 );

	}

	public function fulfill_paypal_order( $entry, $feed, $transaction_id, $amount ) {

		if( 'Paid' !== $entry['payment_status'] )
			return;

		$user_id = $entry['created_by'];

		switch( $entry[1] ) {
			case 'Events - Upgrade|200':
				HC()->events->editor->add_points( $user_id, 'upgrade', 3, $entry['id'] );
				break;
			case 'Events - Premium|1500':
				HC()->events->editor->add_points( $user_id, 'premium', 5, $entry['id'] );
				break;
		}

	}
}

return new HC_Gravity_Forms();
