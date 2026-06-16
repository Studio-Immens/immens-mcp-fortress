<?php
namespace Immens_MCP_Fortress\Tools\REST_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;
use Immens_MCP_Fortress\REST_API\REST_API_Registry;
use Immens_MCP_Fortress\Access_Points\Access_Point_Manager;
use Immens_MCP_Fortress\MCP\Server;

class REST_API_Request_Tool extends Base_Tool {

	public function get_name() {
		return 'wp_rest_api_request';
	}

	public function get_description() {
		return 'Execute any WordPress REST API endpoint directly. Supports all registered routes across all active plugins. Use for operations not covered by the dedicated MCP tools.';
	}

	public function get_category() {
		return 'rest-api';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'method' => array(
					'type'        => 'string',
					'description' => 'HTTP method',
					'enum'        => array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ),
				),
				'route'  => array(
					'type'        => 'string',
					'description' => 'REST API route (e.g. /wp/v2/posts, /wc/v3/products). Use GET /wp/v2 to list available routes.',
				),
				'params' => array(
					'type'        => 'object',
					'description' => 'Query parameters (key-value pairs)',
				),
				'body'   => array(
					'type'        => 'object',
					'description' => 'Request body for POST/PUT/PATCH requests',
				),
			),
			'required'   => array( 'method', 'route' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'method', 'route' ) );

		$method = strtoupper( $arguments['method'] );
		$route  = '/' . ltrim( $arguments['route'], '/' );
		$params = isset( $arguments['params'] ) ? $arguments['params'] : array();
		$body   = isset( $arguments['body'] ) ? $arguments['body'] : null;

		if ( ! in_array( $method, array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) ) {
			throw new \InvalidArgumentException(
				'Invalid HTTP method. Allowed: GET, POST, PUT, PATCH, DELETE.'
			);
		}

		$access_point_id = $this->get_access_point_id();
		if ( $access_point_id > 0 ) {
			$manager      = new Access_Point_Manager();
			$access_point = $manager->get_access_point( $access_point_id );
			$registry     = new REST_API_Registry();

			$namespace = $this->extract_namespace( $route );

			if ( $access_point && ! $registry->is_namespace_allowed( $namespace, $access_point ) ) {
				throw new \RuntimeException(
					'Access denied: this REST API namespace is not enabled for this access point.'
				);
			}

			if ( $access_point && in_array( $method, array( 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) ) {
				if ( ! $registry->is_write_allowed( $namespace, $access_point ) ) {
					throw new \RuntimeException(
						'Access denied: write operations are not allowed for this REST API namespace.'
					);
				}
			}
		}

		$request = new \WP_REST_Request( $method, $route );

		if ( 'GET' === $method && ! empty( $params ) ) {
			foreach ( $params as $key => $value ) {
				$request->set_param( $key, $value );
			}
		} elseif ( in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) ) {
			if ( ! empty( $params ) ) {
				foreach ( $params as $key => $value ) {
					$request->set_param( $key, $value );
				}
			}
			if ( null !== $body ) {
				$request->set_header( 'content-type', 'application/json' );
				$request->set_body( wp_json_encode( $body ) );
			}
		}

		$server   = rest_get_server();
		$response = $server->dispatch( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();
			return array(
				'success' => false,
				'status'  => $response->get_status(),
				'error'   => $error->get_error_message(),
				'code'    => $error->get_error_code(),
			);
		}

		return array(
			'success' => true,
			'status'  => $response->get_status(),
			'data'    => $response->get_data(),
		);
	}

	private function get_access_point_id() {
		$session_id = Server::get_current_access_point_id();
		if ( $session_id > 0 ) {
			return $session_id;
		}

		foreach ( array( 'HTTP_MCP_SESSION_ID', 'REDIRECT_HTTP_MCP_SESSION_ID' ) as $server_var ) {
			if ( ! empty( $_SERVER[ $server_var ] ) ) {
				$session_id = sanitize_text_field( wp_unslash( $_SERVER[ $server_var ] ) );
				break;
			}
		}

		if ( empty( $session_id ) ) {
			return 0;
		}

		global $wpdb;
		$cache_key = 'imf_session_ap_' . $session_id;
		$cached    = wp_cache_get( $cache_key, 'immens_mcp_fortress' );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$table = $wpdb->prefix . 'immens_mcp_sessions';
		$result = $wpdb->get_var( $wpdb->prepare(
			"SELECT access_point_id FROM `{$table}` WHERE session_id = %s AND expires_at > UTC_TIMESTAMP()",
			$session_id
		) );

		if ( $result ) {
			wp_cache_set( $cache_key, $result, 'immens_mcp_fortress', 60 );
			return (int) $result;
		}

		return 0;
	}

	private function extract_namespace( $route ) {
		$route = ltrim( $route, '/' );
		$parts = explode( '/', $route );

		if ( count( $parts ) >= 2 ) {
			if ( 'wp' === $parts[0] ) {
				return $parts[0] . '/' . $parts[1];
			}
			return $parts[0] . '/' . $parts[1];
		}

		return $route;
	}
}
