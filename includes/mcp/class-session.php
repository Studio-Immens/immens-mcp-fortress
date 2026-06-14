<?php
namespace Immens_MCP_Fortress\MCP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Session {

	const SESSION_TTL = 3600;

	public function create( $access_point_id, $wp_user_id, $protocol_version = '2025-11-25', $auth_source = 'bearer' ) {
		global $wpdb;

		$session_id = bin2hex( random_bytes( 32 ) );
		$table = $wpdb->prefix . 'immens_mcp_sessions';

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$table,
			array(
				'session_id'       => $session_id,
				'access_point_id'  => absint( $access_point_id ),
				'wp_user_id'       => absint( $wp_user_id ),
				'protocol_version' => $protocol_version,
				'auth_source'      => $auth_source,
				'created_at'       => current_time( 'mysql', true ),
				'expires_at'       => gmdate( 'Y-m-d H:i:s', time() + self::SESSION_TTL ),
			),
			array( '%s', '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		return $session_id;
	}

	public function validate( $session_id ) {
		global $wpdb;

		if ( ! self::is_valid_format( $session_id ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'immens_mcp_sessions';

		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE session_id = %s AND expires_at > UTC_TIMESTAMP()", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$session_id
			),
			ARRAY_A
		);

		return $row ? $row : false;
	}

	public function touch( $session_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_sessions';

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$table,
			array( 'expires_at' => gmdate( 'Y-m-d H:i:s', time() + self::SESSION_TTL ) ),
			array( 'session_id' => $session_id ),
			array( '%s' ),
			array( '%s' )
		);
	}

	public function destroy( $session_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_sessions';
		$wpdb->delete( $table, array( 'session_id' => $session_id ), array( '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	public static function is_valid_format( $session_id ) {
		return is_string( $session_id ) && 1 === preg_match( '/^[0-9a-f]{64}$/', $session_id );
	}
}
