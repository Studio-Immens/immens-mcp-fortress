<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Authorization_Endpoint {

	public function handle_get( \WP_REST_Request $request ) {
		$response_type = $request->get_param( 'response_type' );
		$client_id     = $request->get_param( 'client_id' );
		$redirect_uri  = $request->get_param( 'redirect_uri' );
		$code_challenge = $request->get_param( 'code_challenge' );
		$code_challenge_method = $request->get_param( 'code_challenge_method' );
		$state         = $request->get_param( 'state' );
		$scope         = $request->get_param( 'scope' );

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
			$registry->register_client( $client_id, 'MCP Client ' . substr( $client_id, 0, 8 ) );
		}

		$consent = new Consent_Screen();
		return $consent->render( $client_id, $redirect_uri, $scope, $state, $code_challenge, $code_challenge_method );
	}

	public function handle_post( \WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error( 'not_authenticated', 'You must be logged in', array( 'status' => 401 ) );
		}

		$client_id     = $request->get_param( 'client_id' );
		$redirect_uri  = $request->get_param( 'redirect_uri' );
		$state         = $request->get_param( 'state' );
		$code_challenge = $request->get_param( 'code_challenge' );
		$code_challenge_method = $request->get_param( 'code_challenge_method' );
		$scope         = $request->get_param( 'scope' );

		$code = bin2hex( random_bytes( 32 ) );

		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_codes';
		$wpdb->insert( $table, array( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			'code_hash'             => hash( 'sha256', $code ),
			'client_id'             => $client_id,
			'wp_user_id'            => get_current_user_id(),
			'scopes'                => wp_json_encode( array_filter( explode( ' ', $scope ) ) ),
			'code_challenge'        => $code_challenge,
			'code_challenge_method' => $code_challenge_method ?: 'S256',
			'redirect_uri'          => $redirect_uri,
			'expires_at'            => gmdate( 'Y-m-d H:i:s', time() + 600 ),
			'created_at'            => current_time( 'mysql', true ),
		) );

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
