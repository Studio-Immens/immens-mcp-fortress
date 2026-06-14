<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OAuth_Token_Validator {

	private $token_manager;

	public function __construct( OAuth_Token_Manager $token_manager ) {
		$this->token_manager = $token_manager;
	}

	public function authenticate( \WP_REST_Request $request ) {
		$auth_header = $request->get_header( 'authorization' );

		if ( empty( $auth_header ) || 0 !== stripos( $auth_header, 'Bearer ' ) ) {
			return new \WP_Error( 'no_auth', 'Missing or invalid Bearer token' );
		}

		$raw_token = substr( $auth_header, 7 );

		if ( 0 !== strpos( $raw_token, 'imf_oat_' ) ) {
			return new \WP_Error( 'not_oauth', 'Not an OAuth token' );
		}

		$token = $this->token_manager->validate_access_token( $raw_token );

		if ( ! $token ) {
			return new \WP_Error( 'invalid_token', 'Invalid or expired OAuth token' );
		}

		$scopes = json_decode( $token['scopes'], true );
		$allowed_tools = array();

		if ( is_array( $scopes ) ) {
			if ( in_array( 'mcp:write', $scopes, true ) ) {
				$allowed_tools = array( '*' );
			} elseif ( in_array( 'mcp:read', $scopes, true ) ) {
				$allowed_tools = array( 'wp_list_*', 'wp_get_*', 'wp_count_*', 'wp_search', 'wp_history_list', 'wp_history_get' );
			}
		}

		return array(
			'token_id'      => (int) $token['id'],
			'wp_user_id'    => (int) $token['wp_user_id'],
			'allowed_tools' => $allowed_tools,
			'client_id'     => $token['client_id'],
		);
	}
}
