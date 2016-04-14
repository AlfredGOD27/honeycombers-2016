<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Migration {
	public function __construct() {

		if( isset($_GET['hc_do_migration']) )
			add_action( 'admin_init', array($this, 'do_migration'), 1 );

	}

	public function do_migration() {

		global $wpdb;

		if( !current_user_can('manage_options') )
			return;

		// Delete old meta_keys
		$keys = array(
		);

		foreach( $keys as $key ) {
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM $wpdb->postmeta
					WHERE meta_key LIKE %s
					",
					$key
				)
			);

			echo 'Deleted ' . $key . '<br>';
		}

		// Delete option keys
		$keys = array(
			'_site_transient_%',
			'_transient_%',
		);

		foreach( $keys as $key ) {
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM $wpdb->options
					WHERE option_name LIKE %s
					",
					$key
				)
			);

			echo 'Deleted ' . $key . '<br>';
		}

		// Delete user keys
		$keys = array(
		);

		foreach( $keys as $key ) {
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM $wpdb->usermeta
					WHERE meta_key LIKE %s
					",
					$key
				)
			);

			echo 'Deleted ' . $key . '<br>';
		}

		// Update meta_keys
		$keys = array(
			'where_website'      => '_hc_listing_website',
			'where_phone'        => '_hc_listing_phone',
			'where_email'        => '_hc_listing_email',
			'where_address'      => '_hc_listing_address_text',
			'entry_location_map' => '_hc_listing_address_map',
		);

		foreach( $keys as $from => $to ) {
			$wpdb->update(
				$wpdb->postmeta,
				array(
					'meta_key' => $to,
				),
				array('meta_key' => $from),
				array('%s'),
				array('%s')
			);

			echo 'Updated ' . $from . ' > ' . $to . '<br>';
		}

		// Migrate directory post type to listings
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_type' => 'listing',
			),
			array(
				'post_type' => 'directory',
			)
		);

		exit;

	}
}

return new HC_Migration();
