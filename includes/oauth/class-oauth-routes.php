<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OAuth_Routes {

	public function register_routes() {
		\register_rest_route( 'immens-mcp-fortress/v1', '/oauth/token', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_token' ),
			'permission_callback' => '__return_true',
		) );

		\register_rest_route( 'immens-mcp-fortress/v1', '/oauth/register', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_register' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function handle_token( \WP_REST_Request $request ) {
		$endpoint = new Token_Endpoint();
		$result   = $endpoint->handle( $request );

		if ( is_wp_error( $result ) ) {
			$err_data = $result->get_error_data();
			$status   = is_array( $err_data ) && isset( $err_data['status'] ) ? (int) $err_data['status'] : 400;
			return new \WP_REST_Response( array(
				'error'             => $result->get_error_code(),
				'error_description' => $result->get_error_message(),
			), $status );
		}

		return new \WP_REST_Response( $result, 200 );
	}

	public function handle_register( \WP_REST_Request $request ) {
		$body = $request->get_json_params();

		if ( empty( $body ) ) {
			$body = $request->get_params();
		}

		$client_name   = isset( $body['client_name'] ) ? sanitize_text_field( $body['client_name'] ) : 'MCP Client';
		$redirect_uris = isset( $body['redirect_uris'] ) ? (array) $body['redirect_uris'] : array( home_url() );
		$client_id     = bin2hex( random_bytes( 16 ) );

		$registry = new Client_Registry();
		$client_secret = bin2hex( random_bytes( 32 ) );
		$registry->register_client( $client_id, $client_name, $redirect_uris );

		return new \WP_REST_Response( array(
			'client_id'              => $client_id,
			'client_secret'          => $client_secret,
			'client_id_issued_at'    => time(),
			'client_secret_expires_at' => 0,
			'redirect_uris'          => $redirect_uris,
			'grant_types'            => array( 'authorization_code', 'refresh_token' ),
			'client_name'            => $client_name,
		), 201 );
	}
}
