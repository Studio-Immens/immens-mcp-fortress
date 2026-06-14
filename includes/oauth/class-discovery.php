<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Discovery {

	public function get_authorization_server_metadata( \WP_REST_Request $request ) {
		return array(
			'issuer'                           => home_url(),
			'authorization_endpoint'           => home_url( '/?immens_mcp_fortress_oauth=authorize' ),
			'token_endpoint'                   => home_url( '/?immens_mcp_fortress_oauth=token' ),
			'registration_endpoint'            => home_url( '/?immens_mcp_fortress_oauth=register' ),
			'revocation_endpoint'              => home_url( '/?immens_mcp_fortress_oauth=revoke' ),
			'scopes_supported'                 => array_keys( Scope_Map::get_all() ),
			'response_types_supported'         => array( 'code' ),
			'grant_types_supported'            => array( 'authorization_code', 'refresh_token' ),
			'token_endpoint_auth_methods_supported' => array( 'none' ),
			'code_challenge_methods_supported' => array( 'S256' ),
		);
	}

	public function get_protected_resource_metadata( \WP_REST_Request $request ) {
		return array(
			'resource'         => rest_url( 'immens-mcp-fortress/v1/mcp' ),
			'authorization_servers' => array( home_url() ),
			'bearer_methods_supported' => array( 'header' ),
		);
	}
}
