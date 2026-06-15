<?php
namespace Immens_MCP_Fortress\Access_Points;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Access_Point_Manager {

	private $repository;

	public function __construct() {
		$this->repository = new Access_Point_Repository();
	}

	public function create_access_point( $name, $wp_user_id = 0, $tool_permissions = null, $ip_whitelist = '', $rate_limit = 60 ) {
		$data = array(
			'name'             => $name,
			'wp_user_id'       => $wp_user_id,
			'tool_permissions' => $tool_permissions ?? Access_Point_Schema::get_default_tool_permissions(),
			'ip_whitelist'     => $ip_whitelist,
			'rate_limit'       => $rate_limit,
			'is_enabled'       => 1,
		);

		return $this->repository->create( $data );
	}

	public function get_access_point( $id ) {
		return $this->repository->get_by_id( $id );
	}

	public function get_all_access_points( $limit = 50, $offset = 0 ) {
		return $this->repository->get_all( $limit, $offset );
	}

	public function count_access_points() {
		return $this->repository->count();
	}

	public function update_access_point( $id, array $data ) {
		if ( isset( $data['tool_permissions'] ) ) {
			$existing = $this->get_access_point( $id );
			if ( $existing && ! empty( $existing['tool_permissions'] ) ) {
				$existing_perms = json_decode( $existing['tool_permissions'], true );
				if ( is_array( $existing_perms ) ) {
					$merged = $data['tool_permissions'];
					foreach ( $existing_perms as $cat => $perms ) {
						if ( ! isset( $merged[ $cat ] ) ) {
							$merged[ $cat ] = $perms;
						}
					}
					$data['tool_permissions'] = $merged;
				}
			}
		}
		return $this->repository->update( $id, $data );
	}

	public function toggle_access_point( $id, $enabled ) {
		return $this->repository->update( $id, array( 'is_enabled' => (int) $enabled ) );
	}

	public function regenerate_key( $id ) {
		return $this->repository->regenerate_key( $id );
	}

	public function delete_access_point( $id ) {
		return $this->repository->delete( $id );
	}

	public function validate_api_key( $raw_key ) {
		if ( empty( $raw_key ) || 0 !== strpos( $raw_key, 'imf_' ) ) {
			return false;
		}

		$key_hash = hash( 'sha256', $raw_key );
		$access_point = $this->repository->get_by_key_hash( $key_hash );

		if ( ! $access_point ) {
			return false;
		}

		if ( empty( $access_point['is_enabled'] ) ) {
			return false;
		}

		return $access_point;
	}

	public function get_allowed_tools( $id ) {
		$permissions = $this->repository->get_tool_permissions( $id );
		return Access_Point_Schema::tool_permissions_to_allowed_tools( $permissions );
	}

	public function update_last_used( $id ) {
		$this->repository->update_last_used( $id );
	}

	public function get_repository() {
		return $this->repository;
	}
}
