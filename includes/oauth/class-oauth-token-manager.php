<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OAuth_Token_Manager {

	public function create_token( $client_id, $wp_user_id, $scopes = array() ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_tokens';

		$raw_access  = 'imf_oat_' . bin2hex( random_bytes( 32 ) );
		$raw_refresh = 'imf_ort_' . bin2hex( random_bytes( 32 ) );

		$wpdb->insert( $table, array( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			'access_token_hash'  => hash( 'sha256', $raw_access ),
			'refresh_token_hash' => hash( 'sha256', $raw_refresh ),
			'client_id'          => $client_id,
			'wp_user_id'         => absint( $wp_user_id ),
			'scopes'             => wp_json_encode( $scopes ),
			'is_active'          => 1,
			'access_token_prefix' => substr( $raw_access, 0, 20 ),
			'expires_at'         => gmdate( 'Y-m-d H:i:s', time() + HOUR_IN_SECONDS ),
			'refresh_expires_at' => gmdate( 'Y-m-d H:i:s', time() + MONTH_IN_SECONDS ),
			'created_at'         => current_time( 'mysql', true ),
		) );

		return array(
			'token_id'       => $wpdb->insert_id,
			'access_token'   => $raw_access,
			'refresh_token'  => $raw_refresh,
			'expires_in'     => HOUR_IN_SECONDS,
		);
	}

	public function validate_access_token( $token ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'immens_mcp_oauth_tokens';
		$hash   = hash( 'sha256', $token );

		return $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT * FROM `{$table}` WHERE access_token_hash = %s AND is_active = 1 AND expires_at > UTC_TIMESTAMP()", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$hash
		), ARRAY_A );
	}

	public function validate_refresh_token( $token ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_tokens';
		$hash  = hash( 'sha256', $token );

		return $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT * FROM `{$table}` WHERE refresh_token_hash = %s AND is_active = 1 AND refresh_expires_at > UTC_TIMESTAMP()", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$hash
		), ARRAY_A );
	}

	public function revoke_token( $token_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_tokens';
		$wpdb->update( $table, array( 'is_active' => 0 ), array( 'id' => absint( $token_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}
