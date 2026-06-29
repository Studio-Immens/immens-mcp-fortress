<?php
namespace Immens_MCP_Fortress\MCP;

use Immens_MCP_Fortress\Access_Points\Access_Point_Manager;
use Immens_MCP_Fortress\Access_Points\Access_Point_Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Transport {

	const NAMESPACE_V1   = 'immens-mcp-fortress/v1';
	const ROUTE          = '/mcp';
	const ROUTE_WITH_KEY = '/mcp/(?P<api_key>imf_[a-f0-9]{64})';
	const MAX_BATCH_SIZE = 20;

	private $server;
	private $access_point_manager;
	private $access_point_auth;

	public function __construct( Server $server, Access_Point_Manager $manager ) {
		$this->server                = $server;
		$this->access_point_manager  = $manager;
		$this->access_point_auth     = new Access_Point_Auth( $manager );
	}

		public function register_routes() {
		$handlers = array(
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_post' ),
				'permission_callback' => array( $this, 'transport_permission' ),
			),
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_get' ),
				'permission_callback' => array( $this, 'transport_permission' ),
			),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'handle_delete' ),
				'permission_callback' => array( $this, 'transport_permission' ),
			),
			array(
				'methods'             => 'OPTIONS',
				'callback'            => array( $this, 'handle_options' ),
				'permission_callback' => array( $this, 'transport_permission' ),
			),
		);

		\register_rest_route( self::NAMESPACE_V1, self::ROUTE, $handlers );
		\register_rest_route( self::NAMESPACE_V1, self::ROUTE_WITH_KEY, $handlers );
	}

	public function transport_permission() {
		return true;
	}

	private function inject_url_token( \WP_REST_Request $request ) {
		$api_key = $request->get_param( 'api_key' );
		if ( $api_key && ! $request->get_header( 'authorization' ) ) {
			$request->set_header( 'Authorization', 'Bearer ' . $api_key );
		}
	}

	public function handle_post( \WP_REST_Request $request ) {
		$this->inject_url_token( $request );

		$origin_error = $this->validate_origin( $request );
		if ( $origin_error ) {
			return $origin_error;
		}

		$content_type = $request->get_content_type();
		if ( ! $content_type || 'application/json' !== $content_type['value'] ) {
			return new \WP_REST_Response(
				JSON_RPC::error_response( null, Error_Codes::INVALID_REQUEST, 'Content-Type must be application/json' ),
				415
			);
		}

		$parsed = JSON_RPC::parse_request( $request->get_body() );
		if ( \is_wp_error( $parsed ) ) {
			return new \WP_REST_Response(
				JSON_RPC::error_response( null, Error_Codes::PARSE_ERROR, $parsed->get_error_message() ),
				400
			);
		}

		$access_point_id = $wp_user_id = $allowed_tools = null;
		$auth_source     = null;
		$rate_limit      = 60;

		$result = $this->access_point_auth->authenticate( $request );

		if ( \is_wp_error( $result ) && $request->get_header( 'authorization' ) ) {
			$ip       = trim( explode( ',', isset( $_SERVER['REMOTE_ADDR'] ) ? \sanitize_text_field( \wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' )[0] );
			$cache_key = 'immens_mcp_fortress_auth_fail_' . md5( $ip );

			if ( \wp_using_ext_object_cache() ) {
				\wp_cache_add( $cache_key, 0, 'immens_mcp_fortress', 60 );
				$new_fails = \wp_cache_incr( $cache_key, 1, 'immens_mcp_fortress' );
			} else {
				$new_fails = (int) \get_transient( $cache_key ) + 1;
				\set_transient( $cache_key, $new_fails, 60 );
			}

			if ( $new_fails > 20 ) {
				return new \WP_REST_Response(
					array( 'error' => 'Too many failed authentication attempts. Try again later.' ),
					429
				);
			}

			$this->server->log_auth_failure( $ip, $result->get_error_message() );
		}

		if ( \is_wp_error( $result ) ) {
			return $this->make_unauthorized_response( null, 401, 'invalid_token' );
		}

		$access_point_id = $result['access_point_id'];
		$wp_user_id      = $result['wp_user_id'];
		$allowed_tools   = $result['allowed_tools'];
		$rate_limit      = $result['rate_limit'];
		$auth_source     = 'bearer';

		$is_initialize = isset( $parsed['method'] ) && 'initialize' === $parsed['method'];

		if ( ! $is_initialize ) {
			$header_error = $this->validate_protocol_version_header( $request );
			if ( $header_error ) {
				return $header_error;
			}
		}

		return $this->process_single_message(
			$parsed,
			$access_point_id,
			$wp_user_id,
			$request,
			null,
			$allowed_tools,
			$auth_source,
			$rate_limit
		);
	}

	private function process_single_message(
		$message,
		$access_point_id,
		$wp_user_id,
		$request,
		$batch_revalidated = null,
		$allowed_tools = null,
		$auth_source = null,
		$rate_limit = 60
	) {
		$method = isset( $message['method'] ) ? $message['method'] : '';

		if ( empty( $method ) ) {
			$id = isset( $message['id'] ) ? $message['id'] : null;
			return new \WP_REST_Response(
				JSON_RPC::error_response( $id, Error_Codes::INVALID_REQUEST, 'Missing method' ),
				200
			);
		}

		if ( 'initialize' === $method ) {
			if ( null === $access_point_id ) {
				$id = isset( $message['id'] ) ? $message['id'] : null;
				return new \WP_REST_Response(
					JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Valid API key required' ),
					200
				);
			}

			if ( ! $this->set_current_user( $wp_user_id ) ) {
				$id = isset( $message['id'] ) ? $message['id'] : null;
				return new \WP_REST_Response(
					JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Access point user no longer exists' ),
					200
				);
			}

			$this->server->set_request_identity( $auth_source, $wp_user_id, $access_point_id );
			try {
				$response_data = $this->server->handle_message( $message, $access_point_id, $allowed_tools );
			} finally {
				$this->server->clear_request_identity();
			}

			$response = new \WP_REST_Response( $response_data, 200 );

			if ( ! isset( $response_data['error'] ) ) {
				$negotiated = $this->server->get_last_negotiated_version() ?? Server::PROTOCOL_VERSION;
				$session_id = $this->server->get_session_manager()->create(
					$access_point_id,
					$wp_user_id,
					$negotiated,
					$auth_source
				);
				$response->header( 'Mcp-Session-Id', $session_id );
			}

			$this->add_cors_headers( $response );
			return $response;
		}

		if ( 'notifications/initialized' === $method || JSON_RPC::is_notification( $message ) ) {
			if ( null === $access_point_id ) {
				return $this->make_unauthorized_response( null );
			}

			if ( ! $this->set_current_user( $wp_user_id ) ) {
				return new \WP_REST_Response( null, 401 );
			}

			$this->server->set_request_identity( $auth_source, $wp_user_id, $access_point_id );
			try {
				$this->server->handle_message( $message, $access_point_id, $allowed_tools );
			} finally {
				$this->server->clear_request_identity();
			}

			$response = new \WP_REST_Response( null, 202 );
			$this->add_cors_headers( $response );
			return $response;
		}

		$session_id  = $request->get_header( 'mcp-session-id' );
		$revalidated = $batch_revalidated;

		if ( null === $revalidated && $session_id ) {
			if ( ! Session::is_valid_format( $session_id ) ) {
				return new \WP_REST_Response( null, 400 );
			}

			$revalidated = $this->revalidate_session( $session_id );
			if ( false === $revalidated ) {
				if ( null === $access_point_id ) {
					return new \WP_REST_Response( null, 404 );
				}
				$revalidated = null;
			}
		}

		if ( null === $access_point_id ) {
			$id = isset( $message['id'] ) ? $message['id'] : null;
			return new \WP_REST_Response(
				JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Valid API key required' ),
				200
			);
		}

		if ( $revalidated ) {
			$session_source = isset( $revalidated['auth_source'] ) ? $revalidated['auth_source'] : 'bearer';
			if ( (int) $revalidated['access_point_id'] !== (int) $access_point_id
				|| $session_source !== $auth_source ) {
				$id = isset( $message['id'] ) ? $message['id'] : null;
				return new \WP_REST_Response(
					JSON_RPC::error_response( $id, Error_Codes::FORBIDDEN, 'API key does not match session owner' ),
					200
				);
			}
		}

		if ( ! $this->set_current_user( $wp_user_id ) ) {
			$id = isset( $message['id'] ) ? $message['id'] : null;
			return new \WP_REST_Response(
				JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Access point user no longer exists' ),
				200
			);
		}

		$this->access_point_manager->update_last_used( $access_point_id );

		$this->server->set_request_identity( $auth_source, $wp_user_id, $access_point_id );
		try {
			$response_data = $this->server->handle_message( $message, $access_point_id, $allowed_tools );
		} finally {
			$this->server->clear_request_identity();
		}

		$response = new \WP_REST_Response( $response_data, 200 );
		$this->add_cors_headers( $response );
		return $response;
	}

	public function handle_get( \WP_REST_Request $request ) {
		$this->inject_url_token( $request );

		$origin_error = $this->validate_origin( $request );
		if ( $origin_error ) {
			return $origin_error;
		}

		$accept = $request->get_header( 'accept' );

		if ( $accept && false !== strpos( $accept, 'text/event-stream' ) ) {
			return $this->handle_sse( $request );
		}

		$response = new \WP_REST_Response( null, 204 );
		$this->add_cors_headers( $response );
		return $response;
	}

	private function handle_sse( \WP_REST_Request $request ) {
		$result = $this->access_point_auth->authenticate( $request );

		if ( \is_wp_error( $result ) ) {
			status_header( 401 );
			header( 'Content-Type: text/event-stream' );
			header( 'Cache-Control: no-cache' );
			header( 'Connection: keep-alive' );
			echo "event: error\ndata: " . wp_json_encode( array(
				'error' => $result->get_error_message(),
			) ) . "\n\n";
			flush();
			exit;
		}

		$access_point_id = $result['access_point_id'];
		$wp_user_id      = $result['wp_user_id'];

		if ( ! $this->set_current_user( $wp_user_id ) ) {
			status_header( 401 );
			header( 'Content-Type: text/event-stream' );
			echo "event: error\ndata: " . wp_json_encode( array(
				'error' => 'User not found',
			) ) . "\n\n";
			exit;
		}

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ) ?: '', 'set_time_limit' ) ) {
			@set_time_limit( 0 );
		}
		header( 'Content-Type: text/event-stream' );
		header( 'Cache-Control: no-cache' );
		header( 'Connection: keep-alive' );
		header( 'X-Accel-Buffering: no' );

		$session_id   = $request->get_header( 'mcp-session-id' );
		$last_event_id = $request->get_header( 'last-event-id' );

		ob_implicit_flush( true );
		ob_end_flush();

		if ( ! $session_id ) {
			$negotiated = Server::PROTOCOL_VERSION;
			$session_id = $this->server->get_session_manager()->create(
				$access_point_id,
				$wp_user_id,
				$negotiated,
				'sse'
			);

			$endpoint_url = rest_url( self::NAMESPACE_V1 . self::ROUTE );

			echo "event: endpoint\n";
			echo "data: " . wp_json_encode( array( 'uri' => $endpoint_url ) ) . "\n\n";
			flush();
		}

		$max_runtime = 300;
		$start_time  = time();
		$poll_interval = 1;

		while ( ( time() - $start_time ) < $max_runtime ) {
			if ( connection_aborted() ) {
				break;
			}

			echo ": heartbeat\n\n";
			flush();

			sleep( $poll_interval );
		}

		$this->server->get_session_manager()->destroy( $session_id );
		exit;
	}

	public function handle_delete( \WP_REST_Request $request ) {
		$this->inject_url_token( $request );

		$origin_error = $this->validate_origin( $request );
		if ( $origin_error ) {
			return $origin_error;
		}

		$result = $this->access_point_auth->authenticate( $request );

		if ( \is_wp_error( $result ) ) {
			return $this->make_unauthorized_response( array( 'error' => 'Authentication required' ) );
		}

		$access_point_id = $result['access_point_id'];
		$session_id = $request->get_header( 'mcp-session-id' );

		if ( $session_id && Session::is_valid_format( $session_id ) ) {
			$session_data = $this->server->get_session_manager()->validate( $session_id );
			if ( $session_data && (int) $session_data['access_point_id'] === (int) $access_point_id ) {
				$this->server->get_session_manager()->destroy( $session_id );
			}
		}

		$response = new \WP_REST_Response( null, 204 );
		$this->add_cors_headers( $response );
		return $response;
	}

	public function handle_options( \WP_REST_Request $request ) {
		$response = new \WP_REST_Response( null, 204 );
		$this->add_cors_headers( $response );
		return $response;
	}

	private function validate_origin( \WP_REST_Request $request ) {
		$origin = $request->get_header( 'origin' );
		if ( empty( $origin ) ) {
			return null;
		}

		$allowed = array( rtrim( \get_site_url(), '/' ) );
		if ( ! in_array( rtrim( $origin, '/' ), $allowed, true ) ) {
			return new \WP_REST_Response( null, 403 );
		}

		return null;
	}

	private function validate_protocol_version_header( \WP_REST_Request $request ) {
		$header_version = $request->get_header( 'mcp-protocol-version' );
		if ( empty( $header_version ) ) {
			return null;
		}

		if ( ! in_array( $header_version, Server::SUPPORTED_PROTOCOL_VERSIONS, true ) ) {
			return new \WP_REST_Response(
				array(
					'error'     => 'unsupported_protocol_version',
					'supported' => Server::SUPPORTED_PROTOCOL_VERSIONS,
				),
				400
			);
		}

		$session_version = $this->get_session_protocol_version( $request );
		if ( $session_version && $header_version !== $session_version ) {
			return new \WP_REST_Response(
				array(
					'error'   => 'protocol_version_mismatch',
					'message' => 'MCP-Protocol-Version header does not match the negotiated session version.',
				),
				400
			);
		}

		return null;
	}

	private function get_session_protocol_version( \WP_REST_Request $request ) {
		$session_id = $request->get_header( 'mcp-session-id' );
		if ( ! $session_id || ! Session::is_valid_format( $session_id ) ) {
			return null;
		}

		$session_data = $this->server->get_session_manager()->validate( $session_id );
		if ( ! $session_data ) {
			return null;
		}

		return isset( $session_data['protocol_version'] ) ? $session_data['protocol_version'] : '2025-03-26';
	}

	private function add_cors_headers( \WP_REST_Response $response ) {
		$response->header( 'Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS' );
		$response->header( 'Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, Mcp-Session-Id, Mcp-Protocol-Version, Last-Event-ID' );
		$response->header( 'Access-Control-Expose-Headers', 'Content-Type, Mcp-Session-Id' );
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, private' );
		$response->header( 'Pragma', 'no-cache' );
	}

	private function revalidate_session( $session_id ) {
		$session_data = $this->server->get_session_manager()->validate( $session_id );
		if ( ! $session_data ) {
			return false;
		}

		$access_point_id = (int) $session_data['access_point_id'];
		$access_point    = $this->access_point_manager->get_access_point( $access_point_id );

		if ( ! $access_point || empty( $access_point['is_enabled'] ) ) {
			$this->server->get_session_manager()->destroy( $session_id );
			return false;
		}

		if ( ! $this->access_point_auth->check_ip_allowed( $access_point_id ) ) {
			$this->server->get_session_manager()->destroy( $session_id );
			return false;
		}

		$this->server->get_session_manager()->touch( $session_id );

		return array(
			'access_point_id' => $access_point_id,
			'wp_user_id'      => (int) $session_data['wp_user_id'],
			'auth_source'     => isset( $session_data['auth_source'] ) ? $session_data['auth_source'] : 'bearer',
		);
	}

	private function set_current_user( $user_id ) {
		$user_id = (int) $user_id;
		if ( $user_id > 0 && \get_userdata( $user_id ) ) {
			\wp_set_current_user( $user_id );
			return true;
		}
		return false;
	}

	private function make_unauthorized_response( $data, $http_status = 401, $error_code = null ) {
		$response = new \WP_REST_Response( $data, $http_status );

		$params = array();
		if ( $error_code ) {
			$params[] = 'error="' . $error_code . '"';
			$params[] = 'error_description="The access token is invalid or expired"';
		}

		$challenge = 'Bearer' . ( $params ? ' ' . implode( ', ', $params ) : '' );
		$response->header( 'WWW-Authenticate', $challenge );
		$this->add_cors_headers( $response );
		return $response;
	}
}
