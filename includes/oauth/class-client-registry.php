<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Client_Registry {

	public function register_client( $client_id, $client_name, $redirect_uris = array(), $is_public = true ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_clients';

		$wpdb->insert( $table, array( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			'client_id'    => $client_id,
			'client_name'  => $client_name,
			'redirect_uris' => wp_json_encode( $redirect_uris ),
			'is_public'    => $is_public ? 1 : 0,
			'created_at'   => current_time( 'mysql', true ),
		) );

		return $wpdb->insert_id;
	}

	public function get_client( $client_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_clients';
		return $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT * FROM `{$table}` WHERE client_id = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$client_id
		), ARRAY_A );
	}
}
