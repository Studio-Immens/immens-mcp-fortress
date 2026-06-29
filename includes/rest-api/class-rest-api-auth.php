<?php
namespace Immens_MCP_Fortress\REST_API;

use Immens_MCP_Fortress\Access_Points\Access_Point_Manager;
use Immens_MCP_Fortress\Access_Points\Access_Point_Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API_Auth {

	private $manager;
	private $registry;
	private $auth;
	private $current_access_point_id = 0;

	public function __construct( Access_Point_Manager $manager, REST_API_Registry $registry ) {
		$this->manager  = $manager;
		$this->registry = $registry;
		$this->auth     = new Access_Point_Auth( $manager );
	}

	public function register() {
		add_filter( 'rest_authentication_errors', array( $this, 'authenticate' ), 0 );
		add_filter( 'rest_pre_dispatch', array( $this, 'check_route_permissions' ), 10, 3 );
	}

	public function authenticate( $result ) {
		if ( null !== $result ) {
			return $result;
		}

		$auth_header = $this->get_auth_header();
		if ( empty( $auth_header ) || 0 !== stripos( $auth_header, 'Bearer imf_' ) ) {
			return $result;
		}

		$raw_key = substr( $auth_header, 7 );

		if ( empty( $raw_key ) ) {
			return $result;
		}

		$access_point = $this->manager->validate_api_key( $raw_key );

		if ( false === $access_point ) {
			$ip = $this->get_client_ip();
			$this->track_failed_auth( $ip );
			return new \WP_Error(
				'rest_forbidden',
				__( 'Invalid API key.', 'immens-mcp-fortress' ),
				array( 'status' => 401 )
			);
		}

		if ( ! $this->auth->check_ip_allowed( $access_point['id'] ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'IP address not allowed for this access point.', 'immens-mcp-fortress' ),
				array( 'status' => 403 )
			);
		}

		if ( ! $this->check_rate_limit( $access_point['id'] ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Rate limit exceeded.', 'immens-mcp-fortress' ),
				array( 'status' => 429 )
			);
		}

		if ( ! $this->is_rest_api_enabled_for_ap( $access_point ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'REST API access is disabled for this access point.', 'immens-mcp-fortress' ),
				array( 'status' => 403 )
			);
		}

		$wp_user_id = (int) $access_point['wp_user_id'];
		if ( $wp_user_id > 0 ) {
			$user = \get_userdata( $wp_user_id );
			if ( ! $user ) {
				return new \WP_Error(
					'rest_forbidden',
					__( 'Access point user no longer exists.', 'immens-mcp-fortress' ),
					array( 'status' => 401 )
				);
			}
			\wp_set_current_user( $wp_user_id );
		} else {
			return new \WP_Error(
				'rest_forbidden',
				__( 'No user is configured for this access point.', 'immens-mcp-fortress' ),
				array( 'status' => 401 )
			);
		}

		$this->current_access_point_id = (int) $access_point['id'];
		$this->manager->update_last_used( $access_point['id'] );

		$route = $this->get_current_route();

		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'immens_mcp_audit_log',
			array(
				'access_point_id' => (int) $access_point['id'],
				'tool_name'       => 'rest_api:' . $this->get_request_method() . ':' . $route,
				'arguments'       => wp_json_encode( array( 'route' => $route ) ),
				'result_status'   => 'success',
				'ip_address'      => $this->get_client_ip(),
				'created_at'      => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return null;
	}

	public function check_route_permissions( $result, $server, $request ) {
		if ( 0 === $this->current_access_point_id ) {
			return $result;
		}

		$route = '/' . ltrim( $request->get_route(), '/' );
		if ( false !== strpos( $route, 'immens-mcp-fortress' ) ) {
			return $result;
		}

		$access_point = $this->manager->get_access_point( $this->current_access_point_id );
		if ( ! $access_point ) {
			return $result;
		}

		$route    = '/' . ltrim( $request->get_route(), '/' );
		$base_ns  = $this->extract_namespace_from_route( $route );

		$namespace = $route;
		$parts     = explode( '/', ltrim( $route, '/' ) );
		if ( count( $parts ) >= 2 ) {
			$namespace = $parts[0] . '/' . $parts[1];
		} elseif ( count( $parts ) === 1 ) {
			$namespace = $parts[0];
		}

		if ( ! $this->registry->is_namespace_allowed( $namespace, $access_point ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'This REST API namespace is not enabled for this access point.', 'immens-mcp-fortress' ),
				array( 'status' => 403 )
			);
		}

		$method = strtoupper( $request->get_method() );
		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) ) {
			if ( ! $this->registry->is_write_allowed( $namespace, $access_point ) ) {
				return new \WP_Error(
					'rest_forbidden',
					__( 'Write access is not allowed for this REST API namespace.', 'immens-mcp-fortress' ),
					array( 'status' => 403 )
				);
			}
		}

		return $result;
	}

	private function extract_namespace_from_route( $route ) {
		$route = ltrim( $route, '/' );
		$parts = explode( '/', $route );

		if ( count( $parts ) >= 2 ) {
			$candidate = $parts[0] . '/' . $parts[1];
			if ( false !== strpos( $candidate, 'wp/' ) ) {
				if ( count( $parts ) >= 3 && 'wp' === $parts[0] ) {
					return 'wp/' . $parts[1];
				}
			}
			return $candidate;
		}

		return $route;
	}

	private function is_rest_api_enabled_for_ap( $access_point ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			: '';

		if (
			false !== strpos( $request_uri, '/immens-mcp-fortress/' )
			|| false !== strpos( $request_uri, 'immens-mcp-fortress' )
		) {
			return true;
		}

		$route = $this->get_current_route();
		if ( false !== strpos( $route, 'immens-mcp-fortress' ) ) {
			return true;
		}

		if ( empty( $access_point['tool_permissions'] ) ) {
			return true;
		}
		$permissions = json_decode( $access_point['tool_permissions'], true );
		if ( ! is_array( $permissions ) ) {
			return true;
		}
		if ( ! isset( $permissions['rest-api'] ) ) {
			return true;
		}
		return ! empty( $permissions['rest-api']['read'] ) || ! empty( $permissions['rest-api']['write'] );
	}

	private function get_auth_header() {
		if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
		}
		if ( ! empty( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) );
		}
		return '';
	}

	private function get_client_ip() {
		$ip = '';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	private function get_request_method() {
		if ( ! empty( $_SERVER['REQUEST_METHOD'] ) ) {
			return strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) );
		}
		return 'GET';
	}

	private function get_current_route() {
		if ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) ) {
			return '/' . ltrim( $GLOBALS['wp']->query_vars['rest_route'], '/' );
		}
		return '/';
	}

	private function check_rate_limit( $access_point_id ) {
		$access_point = $this->manager->get_access_point( $access_point_id );
		if ( ! $access_point ) {
			return false;
		}

		$limit     = isset( $access_point['rate_limit'] ) ? (int) $access_point['rate_limit'] : 60;
		$cache_key = 'immens_mcp_fortress_rest_rate_' . (int) $access_point_id;

		if ( wp_using_ext_object_cache() ) {
			wp_cache_add( $cache_key, 0, 'immens_mcp_fortress', 60 );
			$new_count = wp_cache_incr( $cache_key, 1, 'immens_mcp_fortress' );
			return $new_count <= $limit;
		}

		$current = (int) get_transient( $cache_key );
		if ( $current >= $limit ) {
			return false;
		}
		set_transient( $cache_key, $current + 1, 60 );
		return true;
	}

	private function track_failed_auth( $ip ) {
		$cache_key = 'immens_mcp_fortress_rest_auth_fail_' . md5( $ip );

		if ( wp_using_ext_object_cache() ) {
			wp_cache_add( $cache_key, 0, 'immens_mcp_fortress', 60 );
			wp_cache_incr( $cache_key, 1, 'immens_mcp_fortress' );
		} else {
			$fails = (int) get_transient( $cache_key ) + 1;
			set_transient( $cache_key, $fails, 60 );
		}

		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'immens_mcp_audit_log',
			array(
				'access_point_id' => 0,
				'tool_name'       => 'rest_api_auth_failure',
				'arguments'       => wp_json_encode( array( 'reason' => 'invalid_key' ) ),
				'result_status'   => 'auth_failure',
				'ip_address'      => $ip,
				'created_at'      => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}
}
