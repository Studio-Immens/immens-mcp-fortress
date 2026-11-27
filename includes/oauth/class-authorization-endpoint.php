<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Authorization_Endpoint {

	private function is_allowed_redirect_uri( $client, $redirect_uri ) {
		if ( empty( $redirect_uri ) ) {
			return false;
		}
		$parts = wp_parse_url( $redirect_uri );
		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return false;
		}
		if ( ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
			return false;
		}
		if ( ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) ) {
			return false;
		}

		$host = strtolower( $parts['host'] );

		// Native / local MCP clients (Claude Desktop, Cursor, ...).
		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			return true;
		}

		// Exact match against the client's registered redirect URIs.
		if ( is_array( $client ) && ! empty( $client['redirect_uris'] ) ) {
			$registered = json_decode( $client['redirect_uris'], true );
			if ( is_array( $registered ) ) {
				foreach ( $registered as $uri ) {
					if ( esc_url_raw( $uri ) === $redirect_uri ) {
						return true;
					}
				}
			}
		}

		// Same host as this WordPress site.
		$site_host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		if ( '' !== $site_host && $host === $site_host ) {
			return true;
		}

		return false;
	}

	public function handle_get( \WP_REST_Request $request ) {
		$response_type = sanitize_key( $request->get_param( 'response_type' ) );
		$client_id     = sanitize_text_field( $request->get_param( 'client_id' ) );
		$redirect_uri  = esc_url_raw( $request->get_param( 'redirect_uri' ) );
		$code_challenge = sanitize_text_field( $request->get_param( 'code_challenge' ) );
		$code_challenge_method = sanitize_key( $request->get_param( 'code_challenge_method' ) );
		$state         = sanitize_text_field( $request->get_param( 'state' ) );
		$scope         = sanitize_text_field( $request->get_param( 'scope' ) );

		if ( 'code' !== $response_type ) {
			return new \WP_Error( 'unsupported_response_type', 'Only authorization_code is supported', array( 'status' => 400 ) );
		}

		if ( ! is_user_logged_in() ) {
			auth_redirect();
			exit;
		}

		$registry = new Client_Registry();
		$client   = $registry->get_client( $client_id );

		if ( ! $client ) {
			$registry->register_client( $client_id, 'MCP Client ' . substr( $client_id, 0, 8 ), array( $redirect_uri ) );
			$client = $registry->get_client( $client_id );
		}

		if ( ! $this->is_allowed_redirect_uri( $client, $redirect_uri ) ) {
			return new \WP_Error( 'invalid_redirect_uri', 'The redirect URI is not allowed for this client', array( 'status' => 400 ) );
		}

		$consent = new Consent_Screen();
		return $consent->render( $client_id, $redirect_uri, $scope, $state, $code_challenge, $code_challenge_method );
	}

	public function handle_post( \WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error( 'not_authenticated', 'You must be logged in', array( 'status' => 401 ) );
		}
		$nonce = $request->get_param( '_immens_mcp_fortress_oauth_nonce' );

		if ( ! $nonce || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'immens_mcp_fortress_oauth_consent' ) ) {
			return new \WP_Error( 'invalid_nonce', 'Security check failed. Please try again.', array( 'status' => 403 ) );
		}

		$client_id     = sanitize_text_field( $request->get_param( 'client_id' ) );
		$redirect_uri  = esc_url_raw( $request->get_param( 'redirect_uri' ) );
		$state         = sanitize_text_field( $request->get_param( 'state' ) );
		$code_challenge = sanitize_text_field( $request->get_param( 'code_challenge' ) );
		$code_challenge_method = sanitize_key( $request->get_param( 'code_challenge_method' ) );
		$scope         = sanitize_text_field( $request->get_param( 'scope' ) );

		$registry = new Client_Registry();
		$client   = $registry->get_client( $client_id );
		if ( ! $this->is_allowed_redirect_uri( $client, $redirect_uri ) ) {
			return new \WP_Error( 'invalid_redirect_uri', 'The redirect URI is not allowed for this client', array( 'status' => 400 ) );
		}

		$code = bin2hex( random_bytes( 32 ) );

		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_codes';
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$table,
			array(
				'code_hash'             => hash( 'sha256', $code ),
				'client_id'             => $client_id,
				'wp_user_id'            => get_current_user_id(),
				'scopes'                => wp_json_encode( array_filter( explode( ' ', $scope ) ) ),
				'code_challenge'        => $code_challenge,
				'code_challenge_method' => $code_challenge_method ?: 'S256',
				'redirect_uri'          => $redirect_uri,
				'expires_at'            => gmdate( 'Y-m-d H:i:s', time() + 600 ),
				'created_at'            => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		$redirect = $redirect_uri;
		$redirect .= ( false === strpos( $redirect, '?' ) ? '?' : '&' );
		$redirect .= 'code=' . urlencode( $code );
		if ( $state ) {
			$redirect .= '&state=' . urlencode( $state );
		}

		$response = new \WP_REST_Response( null, 302 );
		$response->header( 'Location', $redirect );
		return $response;
	}
}
