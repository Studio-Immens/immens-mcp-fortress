<?php
namespace Immens_MCP_Fortress\Access_Points;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Access_Point_Repository {

	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'immens_mcp_access_points';
	}

	public function create( array $data ) {
		global $wpdb;

		$raw_key   = 'imf_' . bin2hex( random_bytes( 32 ) );
		$key_hash  = hash( 'sha256', $raw_key );
		$key_pfx   = substr( $raw_key, 0, 18 );

		$insert_data = array(
			'name'            => sanitize_text_field( $data['name'] ),
			'api_key_hash'    => $key_hash,
			'api_key_prefix'  => $key_pfx,
			'is_enabled'      => isset( $data['is_enabled'] ) ? (int) $data['is_enabled'] : 1,
			'ip_whitelist'    => isset( $data['ip_whitelist'] ) ? sanitize_textarea_field( $data['ip_whitelist'] ) : '',
			'tool_permissions' => isset( $data['tool_permissions'] ) ? wp_json_encode( $data['tool_permissions'] ) : wp_json_encode( Access_Point_Schema::get_default_tool_permissions() ),
			'wp_user_id'      => isset( $data['wp_user_id'] ) ? absint( $data['wp_user_id'] ) : 0,
			'rate_limit'      => isset( $data['rate_limit'] ) ? absint( $data['rate_limit'] ) : 60,
			'is_pro'          => isset( $data['is_pro'] ) ? (int) $data['is_pro'] : 0,
			'created_at'      => current_time( 'mysql', true ),
			'updated_at'      => current_time( 'mysql', true ),
		);

		$result = $wpdb->insert( $this->table, $insert_data );

		if ( false === $result ) {
			return new \WP_Error(
				'access_point_create_failed',
				__( 'Failed to create access point.', 'immens-mcp-fortress' )
			);
		}

		return array(
			'id'         => $wpdb->insert_id,
			'raw_key'    => $raw_key,
			'key_prefix' => $key_pfx,
		);
	}

	public function get_by_id( $id ) {
		global $wpdb;
		$cache_key = 'imf_ap_id_' . absint( $id );
		$cached    = wp_cache_get( $cache_key, 'immens_mcp_fortress' );

		if ( false !== $cached ) {
			return $cached;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM `{$this->table}` WHERE id = %d", absint( $id ) ),
			ARRAY_A
		);

		if ( null !== $row ) {
			wp_cache_set( $cache_key, $row, 'immens_mcp_fortress', 120 );
		}

		return $row;
	}

	public function get_by_key_hash( $key_hash ) {
		global $wpdb;
		$cache_key = 'imf_ap_kh_' . $key_hash;
		$cached    = wp_cache_get( $cache_key, 'immens_mcp_fortress' );

		if ( false !== $cached ) {
			return $cached;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$this->table}` WHERE api_key_hash = %s AND is_enabled = 1",
				$key_hash
			),
			ARRAY_A
		);

		if ( null !== $row ) {
			wp_cache_set( $cache_key, $row, 'immens_mcp_fortress', 120 );
		}

		return $row;
	}

	public function get_all( $limit = 50, $offset = 0 ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, name, api_key_prefix, is_enabled, ip_whitelist, tool_permissions, wp_user_id, rate_limit, is_pro, created_at, updated_at, last_used_at FROM `{$this->table}` ORDER BY created_at DESC LIMIT %d OFFSET %d",
				absint( $limit ),
				absint( $offset )
			),
			ARRAY_A
		);
	}

	public function count() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$this->table}`" );
	}

	public function update( $id, array $data ) {
		global $wpdb;

		$this->invalidate_cache( $id );

		$update = array( 'updated_at' => current_time( 'mysql', true ) );
		$formats = array( '%s' );

		if ( isset( $data['name'] ) ) {
			$update['name'] = sanitize_text_field( $data['name'] );
			$formats[] = '%s';
		}
		if ( isset( $data['is_enabled'] ) ) {
			$update['is_enabled'] = (int) $data['is_enabled'];
			$formats[] = '%d';
		}
		if ( isset( $data['ip_whitelist'] ) ) {
			$update['ip_whitelist'] = sanitize_textarea_field( $data['ip_whitelist'] );
			$formats[] = '%s';
		}
		if ( isset( $data['tool_permissions'] ) ) {
			$update['tool_permissions'] = wp_json_encode( $data['tool_permissions'] );
			$formats[] = '%s';
		}
		if ( isset( $data['wp_user_id'] ) ) {
			$update['wp_user_id'] = absint( $data['wp_user_id'] );
			$formats[] = '%d';
		}
		if ( isset( $data['rate_limit'] ) ) {
			$update['rate_limit'] = absint( $data['rate_limit'] );
			$formats[] = '%d';
		}
		if ( isset( $data['is_pro'] ) ) {
			$update['is_pro'] = (int) $data['is_pro'];
			$formats[] = '%d';
		}

		return $wpdb->update(
			$this->table,
			$update,
			array( 'id' => absint( $id ) ),
			$formats,
			array( '%d' )
		);
	}

	public function regenerate_key( $id ) {
		global $wpdb;

		$this->invalidate_cache( $id );

		$raw_key  = 'imf_' . bin2hex( random_bytes( 32 ) );
		$key_hash = hash( 'sha256', $raw_key );
		$key_pfx  = substr( $raw_key, 0, 18 );

		$wpdb->update(
			$this->table,
			array(
				'api_key_hash'   => $key_hash,
				'api_key_prefix' => $key_pfx,
				'updated_at'     => current_time( 'mysql', true ),
			),
			array( 'id' => absint( $id ) ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		return array(
			'raw_key'    => $raw_key,
			'key_prefix' => $key_pfx,
		);
	}

	public function delete( $id ) {
		global $wpdb;
		$this->invalidate_cache( $id );
		return $wpdb->delete( $this->table, array( 'id' => absint( $id ) ), array( '%d' ) );
	}

	public function update_last_used( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"UPDATE `{$this->table}` SET last_used_at = UTC_TIMESTAMP() WHERE id = %d AND (last_used_at IS NULL OR last_used_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 5 MINUTE))",
			absint( $id )
		) );
	}

	public function get_tool_permissions( $id ) {
		$row = $this->get_by_id( $id );
		if ( ! $row || empty( $row['tool_permissions'] ) ) {
			return null;
		}
		$perms = json_decode( $row['tool_permissions'], true );
		return is_array( $perms ) ? $perms : null;
	}

	private function invalidate_cache( $id ) {
		$row = $this->get_by_id( $id );
		if ( $row && ! empty( $row['api_key_hash'] ) ) {
			wp_cache_delete( 'imf_ap_kh_' . $row['api_key_hash'], 'immens_mcp_fortress' );
		}
		wp_cache_delete( 'imf_ap_id_' . absint( $id ), 'immens_mcp_fortress' );
	}
}
