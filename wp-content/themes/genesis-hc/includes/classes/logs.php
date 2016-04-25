<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Logs {
	public function __construct() {

		global $wpdb;

		$this->table_name = $wpdb->prefix . 'logs';

	}

	private function maybe_create_table() {

		global $wpdb;

		$table_exists = $wpdb->get_results("SHOW TABLES LIKE '$this->table_name';");

		if( empty($table_exists) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $this->table_name (
				post_type varchar(32) NOT NULL,
				level varchar(32) NOT NULL,
				target_user_id bigint(20) NOT NULL,
				initiating_user_id bigint(20) NOT NULL,
				ref_id bigint(20) NOT NULL,
				amount bigint(20) NOT NULL,
				note varchar(255),
				timestamp TIMESTAMP NOT NULL
			) $charset_collate;";
			dbDelta( $sql );
		}

	}

	public function add( $data ) {

		global $wpdb;

		$this->maybe_create_table();

		$wpdb->insert(
			$this->table_name,
			array(
				'post_type'          => $data['post_type'],
				'level'              => $data['level'],
				'target_user_id'     => $data['target_user_id'],
				'initiating_user_id' => $data['initiating_user_id'],
				'ref_id'             => $data['ref_id'],
				'amount'             => $data['amount'],
				'note'               => !empty($data['note']) ? $data['note'] : '',
				'timestamp'          => date( 'Y-m-d H:i:s', time() ),
			),
			array(
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
			)
		);

	}

}

return new HC_Logs();
