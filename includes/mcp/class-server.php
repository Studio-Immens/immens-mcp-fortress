<?php
namespace Immens_MCP_Fortress\MCP;

use Immens_MCP_Fortress\Access_Points\Access_Point_Manager;
use Immens_MCP_Fortress\Auth\Permission_Guard;
use Immens_MCP_Fortress\Tools\Tool_Registry;
use Immens_MCP_Fortress\Resources\Resource_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Server {

	const PROTOCOL_VERSION = '2025-11-25';
	const SERVER_NAME      = 'immens-mcp-fortress';

	const SUPPORTED_PROTOCOL_VERSIONS = array( '2025-11-25', '2025-06-18', '2025-03-26' );

	private $tool_registry;
	private $resource_registry;
	private $access_point_manager;
	private $session_manager;
	private $permission_guard;
	private $disabled_tools;
	private $audit_log_enabled;
	private $last_negotiated_version;

	private $request_auth_source   = null;
	private $request_wp_user_id    = 0;
	private $request_access_point_id = 0;

	public function __construct(
		Tool_Registry $tool_registry,
		Resource_Registry $resource_registry,
		Access_Point_Manager $access_point_manager
	) {
		$this->tool_registry         = $tool_registry;
		$this->resource_registry     = $resource_registry;
		$this->access_point_manager  = $access_point_manager;
		$this->session_manager       = new Session();
		$this->permission_guard      = new Permission_Guard( $access_point_manager );
		$this->disabled_tools        = (array) get_option( 'immens_mcp_fortress_disabled_tools', array() );
		$this->audit_log_enabled     = (bool) get_option( 'immens_mcp_fortress_audit_log_enabled', true );

		register_shutdown_function( function () {
			$err = error_get_last();
			if ( ! $err ) {
				return;
			}
			if ( ! in_array( $err['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ), true ) ) {
				return;
			}
			if ( ! class_exists( '\\Immens_MCP_Fortress\\History\\Change_Context' ) ) {
				return;
			}
			if ( ! \Immens_MCP_Fortress\History\Change_Context::is_active() ) {
				return;
			}
			$audit_id = \Immens_MCP_Fortress\History\Change_Context::get( 'audit_id' );
			if ( $audit_id ) {
				$this->update_audit_status( (int) $audit_id, 'error' );
			}
		} );
	}

	public function set_request_identity( $auth_source, $wp_user_id, $access_point_id = 0 ) {
		$this->request_auth_source     = $auth_source;
		$this->request_wp_user_id      = (int) $wp_user_id;
		$this->request_access_point_id = (int) $access_point_id;
	}

	public function clear_request_identity() {
		$this->request_auth_source     = null;
		$this->request_wp_user_id      = 0;
		$this->request_access_point_id = 0;
	}

	public function handle_message( $message, $access_point_id = null, $allowed_tools = null ) {
		$method = isset( $message['method'] ) ? $message['method'] : '';
		$params = isset( $message['params'] ) ? $message['params'] : array();
		$id     = isset( $message['id'] ) ? $message['id'] : null;

		if ( JSON_RPC::is_notification( $message ) ) {
			return null;
		}

		switch ( $method ) {
			case 'initialize':
				return $this->handle_initialize( $id, $params, $access_point_id );
			case 'ping':
				return JSON_RPC::success_response( $id, new \stdClass() );
			case 'tools/list':
				return $this->handle_tools_list( $id, $params, $access_point_id, $allowed_tools );
			case 'tools/call':
				return $this->handle_tools_call( $id, $params, $access_point_id, $allowed_tools );
			case 'resources/list':
				return $this->handle_resources_list( $id, $params, $access_point_id );
			case 'resources/read':
				return $this->handle_resources_read( $id, $params, $access_point_id );
			case 'prompts/list':
				return JSON_RPC::success_response( $id, array( 'prompts' => array() ) );
			case 'prompts/get':
				return JSON_RPC::error_response( $id, Error_Codes::METHOD_NOT_FOUND, 'No prompts available' );
			default:
				return JSON_RPC::error_response( $id, Error_Codes::METHOD_NOT_FOUND, 'Method not found' );
		}
	}

	private function handle_initialize( $id, $params, $access_point_id ) {
		if ( ! $this->check_rate_limit( $access_point_id ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::RATE_LIMITED, 'Rate limit exceeded.' );
		}

		$client_version = isset( $params['protocolVersion'] ) ? $params['protocolVersion'] : null;

		if ( $client_version && in_array( $client_version, self::SUPPORTED_PROTOCOL_VERSIONS, true ) ) {
			$negotiated_version = $client_version;
		} else {
			$negotiated_version = self::PROTOCOL_VERSION;
		}

		$this->last_negotiated_version = $negotiated_version;

		return JSON_RPC::success_response( $id, array(
			'protocolVersion' => $negotiated_version,
			'capabilities'    => array(
				'tools'     => new \stdClass(),
				'resources' => new \stdClass(),
			),
			'serverInfo'      => array(
				'name'    => self::SERVER_NAME,
				'version' => IMMENS_MCP_FORTRESS_VERSION,
			),
			'instructions'    => 'Immens MCP Fortress WordPress Server. Use tools to manage posts, pages, media, blocks, templates, styles, and site settings.',
		) );
	}

	public function get_last_negotiated_version() {
		return isset( $this->last_negotiated_version ) ? $this->last_negotiated_version : null;
	}

	private function handle_tools_list( $id, $params, $access_point_id, $allowed_tools = null ) {
		if ( null === $access_point_id ) {
			return JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Authentication required' );
		}

		if ( ! $this->check_rate_limit( $access_point_id ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::RATE_LIMITED, 'Rate limit exceeded.' );
		}

		$all_tools = $this->tool_registry->get_all_definitions();

		if ( $allowed_tools && ! in_array( '*', $allowed_tools, true ) ) {
			$all_tools = array_values( array_filter( $all_tools, function ( $tool ) use ( $allowed_tools ) {
				if ( in_array( $tool['name'], $allowed_tools, true ) ) {
					return true;
				}
				foreach ( $allowed_tools as $pattern ) {
					if ( false !== strpos( $pattern, '*' ) && fnmatch( $pattern, $tool['name'] ) ) {
						return true;
					}
				}
				return false;
			} ) );
		}

		if ( ! empty( $this->disabled_tools ) ) {
			$all_tools = array_values( array_filter( $all_tools, function ( $tool ) {
				return ! in_array( $tool['name'], $this->disabled_tools, true );
			} ) );
		}

		return JSON_RPC::success_response( $id, array( 'tools' => $all_tools ) );
	}

	private function handle_tools_call( $id, $params, $access_point_id, $allowed_tools = null ) {
		$tool_name = isset( $params['name'] ) ? $params['name'] : '';
		$arguments = isset( $params['arguments'] ) ? $params['arguments'] : array();

		if ( empty( $tool_name ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::INVALID_PARAMS, 'Missing tool name' );
		}

		if ( null === $access_point_id ) {
			return JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Authentication required' );
		}

		if ( null !== $allowed_tools && ! $this->permission_guard->can_use_tool_with_scope( $allowed_tools, $tool_name ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::FORBIDDEN, 'Access point does not have permission to use this tool' );
		}

		if ( ! $this->check_rate_limit( $access_point_id ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::RATE_LIMITED, 'Rate limit exceeded.' );
		}

		$tool = $this->tool_registry->get_tool( $tool_name );

		if ( null === $tool ) {
			return JSON_RPC::error_response( $id, Error_Codes::INVALID_PARAMS, 'Unknown tool' );
		}

		$required_cap = $tool->get_required_capability();
		if ( $required_cap && ! current_user_can( $required_cap ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::FORBIDDEN, 'Insufficient WordPress permissions for this tool' );
		}

		if ( ! empty( $this->disabled_tools ) && in_array( $tool_name, $this->disabled_tools, true ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::FORBIDDEN, 'This tool has been disabled by the administrator.' );
		}

		$audit_id = $this->log_tool_call( $access_point_id, $tool_name, $arguments, 'pending' );
		$final_status = null;

		if ( class_exists( '\\Immens_MCP_Fortress\\History\\Change_Context' ) ) {
			\Immens_MCP_Fortress\History\Change_Context::set( array(
				'audit_id'         => $audit_id,
				'tool_name'        => $tool_name,
				'access_point_id'  => $access_point_id ? (int) $access_point_id : 0,
				'auth_source'      => $this->request_auth_source,
				'wp_user_id'       => $this->request_wp_user_id,
				'ip_address'       => self::get_client_ip(),
			) );
		}

		try {
			$result       = $tool->execute( $arguments );
			$final_status = 'success';
			return JSON_RPC::success_response( $id, array(
				'content' => array( array(
					'type' => 'text',
					'text' => is_string( $result ) ? $result : wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
				) ),
			) );
		} catch ( \Exception $e ) {
			$final_status = 'error';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'Immens MCP Fortress [%s]: %s', $tool_name, $e->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			return JSON_RPC::success_response( $id, array(
				'content' => array( array(
					'type'    => 'text',
					'text'    => 'Error: ' . self::sanitize_error_message( $e->getMessage() ),
				) ),
				'isError' => true,
			) );
		} catch ( \Error $e ) {
			$final_status = 'error';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'Immens MCP Fortress [%s]: %s', $tool_name, $e->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			return JSON_RPC::success_response( $id, array(
				'content' => array( array(
					'type'    => 'text',
					'text'    => 'Tool execution failed. Check server error logs for details.',
				) ),
				'isError' => true,
			) );
		} finally {
			$this->update_audit_status( $audit_id, null === $final_status ? 'error' : $final_status );
			if ( class_exists( '\\Immens_MCP_Fortress\\History\\Change_Context' ) ) {
				\Immens_MCP_Fortress\History\Change_Context::clear();
			}
			\Immens_MCP_Fortress\Tools\Base_Tool::flush_deferred_purges();
		}
	}

	public static function sanitize_error_message( $message ) {
		if ( ! is_string( $message ) || '' === $message ) {
			return 'Tool execution failed.';
		}

		$message = preg_replace( '/\s*Stack trace:.*$/s', '', $message );
		$message = preg_replace( '/\s+in\s+\S+\.php(?:\(\d+\)|:\d+| on line \d+)/', '', $message );
		$message = preg_replace_callback(
			'/\b(?:\d{1,3}\.){3}\d{1,3}\b/',
			function ( $m ) {
				$ip    = $m[0];
				$parts = array_map( 'intval', explode( '.', $ip ) );
				if ( 127 === $parts[0] || 10 === $parts[0] ) {
					return '[internal]';
				}
				if ( 192 === $parts[0] && 168 === $parts[1] ) {
					return '[internal]';
				}
				if ( 172 === $parts[0] && $parts[1] >= 16 && $parts[1] <= 31 ) {
					return '[internal]';
				}
				return $ip;
			},
			$message
		);

		$message = trim( $message );
		if ( strlen( $message ) > 200 ) {
			$message = substr( $message, 0, 200 ) . '...[truncated]';
		}

		return '' === $message ? 'Tool execution failed.' : $message;
	}

	private function handle_resources_list( $id, $params, $access_point_id ) {
		if ( null === $access_point_id ) {
			return JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Authentication required' );
		}

		if ( ! current_user_can( 'read' ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::FORBIDDEN, 'Insufficient permissions to list resources' );
		}

		return JSON_RPC::success_response( $id, array(
			'resources' => $this->resource_registry->get_all_definitions()
		) );
	}

	private function handle_resources_read( $id, $params, $access_point_id ) {
		if ( null === $access_point_id ) {
			return JSON_RPC::error_response( $id, Error_Codes::UNAUTHORIZED, 'Authentication required' );
		}

		if ( ! current_user_can( 'read' ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::FORBIDDEN, 'Insufficient permissions to read resources' );
		}

		$uri = isset( $params['uri'] ) ? $params['uri'] : '';
		if ( empty( $uri ) ) {
			return JSON_RPC::error_response( $id, Error_Codes::INVALID_PARAMS, 'Missing resource URI' );
		}

		$resource = $this->resource_registry->get_resource( $uri );
		if ( null === $resource ) {
			return JSON_RPC::error_response( $id, Error_Codes::RESOURCE_NOT_FOUND, 'Resource not found' );
		}

		try {
			$content = $resource->read();
			return JSON_RPC::success_response( $id, array(
				'contents' => array( array(
					'uri'      => $uri,
					'mimeType' => $resource->get_mime_type(),
					'text'     => is_string( $content ) ? $content : wp_json_encode( $content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
				) ),
			) );
		} catch ( \Throwable $e ) {
			return JSON_RPC::error_response( $id, Error_Codes::INTERNAL_ERROR, 'Failed to read resource' );
		}
	}

	private function check_rate_limit( $access_point_id ) {
		if ( null === $access_point_id ) {
			return true;
		}

		$access_point = $this->access_point_manager->get_access_point( $access_point_id );
		if ( ! $access_point ) {
			return false;
		}

		$limit     = isset( $access_point['rate_limit'] ) ? (int) $access_point['rate_limit'] : 60;
		$cache_key = 'imf_rate_' . (int) $access_point_id;

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

	public function log_auth_failure( $ip, $reason ) {
		if ( ! $this->audit_log_enabled ) {
			return;
		}
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'immens_mcp_audit_log',
			array(
				'access_point_id' => 0,
				'tool_name'       => '_auth_failure',
				'arguments'       => wp_json_encode( array( 'reason' => $reason ) ),
				'result_status'   => 'auth_failure',
				'ip_address'      => $ip,
				'created_at'      => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	private function log_tool_call( $access_point_id, $tool_name, $arguments, $status ) {
		if ( ! $this->audit_log_enabled ) {
			return 0;
		}
		global $wpdb;
		$safe_args = self::redact_sensitive_args( $arguments );
		$wpdb->insert(
			$wpdb->prefix . 'immens_mcp_audit_log',
			array(
				'access_point_id' => $access_point_id ? (int) $access_point_id : 0,
				'tool_name'       => $tool_name,
				'arguments'       => wp_json_encode( $safe_args ),
				'result_status'   => $status,
				'ip_address'      => self::get_client_ip(),
				'created_at'      => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		return (int) $wpdb->insert_id;
	}

	private function update_audit_status( $audit_id, $status ) {
		if ( ! $this->audit_log_enabled || ! $audit_id ) {
			return;
		}
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'immens_mcp_audit_log',
			array( 'result_status' => $status ),
			array( 'id' => (int) $audit_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	private static function redact_sensitive_args( $args ) {
		if ( ! is_array( $args ) ) {
			return $args;
		}
		$sensitive_pattern = '/^(password|pass|secret|token|api[_\-]?key|authorization|content_base64|private[_\-]?key|access[_\-]?token|client[_\-]?secret|credential)$/i';
		$result = array();
		foreach ( $args as $key => $value ) {
			if ( preg_match( $sensitive_pattern, $key ) ) {
				$result[ $key ] = '[REDACTED]';
			} elseif ( is_array( $value ) ) {
				$result[ $key ] = self::redact_sensitive_args( $value );
			} else {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}

	private static function get_client_ip() {
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			$ip = '';
		}
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	public function get_session_manager() {
		return $this->session_manager;
	}
}
