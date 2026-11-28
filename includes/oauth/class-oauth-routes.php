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

		// Valida i redirect_uris: prima venivano accettati arbitrariamente, quindi
		// si poteva registrare un client che punta a un host controllato dall'attaccante.
		$validated_uris = array();
		$site_host      = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		foreach ( $redirect_uris as $uri ) {
			$uri = esc_url_raw( trim( (string) $uri ) );
			if ( '' === $uri ) {
				continue;
			}
			$parts = wp_parse_url( $uri );
			if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
				continue;
			}
			$host = strtolower( $parts['host'] );
			// Consentiti: client nativi locali (loopback) o stesso host del sito.
			$is_loopback = in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true );
			if ( ! $is_loopback && $host !== $site_host ) {
				continue;
			}
			$validated_uris[] = $uri;
		}

		if ( empty( $validated_uris ) ) {
			return new \WP_REST_Response( array(
				'error'             => 'invalid_redirect_uri',
				'error_description' => 'redirect_uris must be a loopback address or the site host.',
			), 400 );
		}

		$registry = new Client_Registry();
		$client_secret = bin2hex( random_bytes( 32 ) );
		$registry->register_client( $client_id, $client_name, $validated_uris, true, $client_secret );

		return new \WP_REST_Response( array(
			'client_id'              => $client_id,
			'client_secret'          => $client_secret,
			'client_id_issued_at'    => time(),
			'client_secret_expires_at' => 0,
			'redirect_uris'          => $validated_uris,
			'grant_types'            => array( 'authorization_code', 'refresh_token' ),
			'client_name'            => $client_name,
		), 201 );
	}
}
